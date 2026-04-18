<?php

namespace App\Http\Controllers;

use App\Mail\BranchContractExpiredNotification;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin;
use App\Models\Franchisee;
use App\Models\FranchiseeStaff;
use Carbon\Carbon;
use Throwable;

class LoginController extends Controller
{
    // Franchisee login
    public function showFranchiseeLogin()
    {
        return view('auth.login-franchisee');
    }

    public function loginFranchisee(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $franchisee = Franchisee::where('franchisee_username', $credentials['username'])->first();
        if ($franchisee) {
            $loginError = $this->getFranchiseeLoginBlockMessage($franchisee);
            if ($loginError !== null) {
                return back()
                    ->withInput($request->only('username'))
                    ->withErrors(['login_error' => $loginError]);
            }
        }

        if (Auth::guard('franchisee')->attempt([
            'franchisee_username' => $credentials['username'],
            'password' => $credentials['password'],
        ])) {
            return redirect()->route('franchisee.dashboard');
        }

        return back()->withErrors(['login_error' => 'Invalid username or password.']);
    }

    // Franchisee Staff login
    public function showFranchiseeStaffLogin()
    {
        return view('auth.login-franchisee-staff');
    }

    public function loginFranchiseeStaff(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $staff = FranchiseeStaff::where('fstaff_username', $credentials['username'])->first();
        if ($staff && $staff->fstaff_status !== 'Active') {
            return back()->withErrors(['login_error' => 'Your account has been archived. Please contact your franchisee admin.']);
        }

        if (Auth::guard('franchisee_staff')->attempt([
            'fstaff_username' => $credentials['username'],
            'password' => $credentials['password'],
        ])) {
            return redirect()->route('franchisee-staff.dashboard');
        }

        return back()->withErrors(['login_error' => 'Invalid username or password.']);
    }

    // Franchisor Staff login
    public function showFranchisorStaffLogin()
    {
        return view('auth.login-franchisor-staff');
    }

    public function loginFranchisorStaff(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Auth::guard('franchisor_staff')->attempt([
            'astaff_username' => $credentials['username'],
            'password' => $credentials['password'],
        ])) {
            return redirect()->route('franchisor-staff.dashboard');
        }

        return back()->withErrors(['login_error' => 'Invalid username or password.']);
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    // Unified login for all roles from welcome page
    public function unifiedLogin(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
            'role_type' => 'required|in:franchisor,franchisee,franchisor-staff,franchisee-staff',
        ]);

        $role = $credentials['role_type'];
        $username = $credentials['username'];
        $password = $credentials['password'];

        // Franchisor login
        if ($role === 'franchisor') {
            $admin = Admin::where('admin_username', $username)->first();
            if ($admin && Hash::check($password, $admin->admin_pass)) {
                Auth::guard('admin')->login($admin);
                return redirect()->route('admin.dashboard');
            }
        }

        // Franchisee login
        if ($role === 'franchisee') {
            $franchisee = Franchisee::where('franchisee_username', $username)->first();
            if ($franchisee) {
                $loginError = $this->getFranchiseeLoginBlockMessage($franchisee);
                if ($loginError !== null) {
                    return back()
                        ->withInput($request->only('username', 'role_type'))
                        ->withErrors(['login_error' => $loginError]);
                }
            }

            if (Auth::guard('franchisee')->attempt([
                'franchisee_username' => $username,
                'password' => $password,
            ])) {
                return redirect()->route('franchisee.dashboard');
            }
        }

        // Franchisor Staff login
        if ($role === 'franchisor-staff') {
            if (Auth::guard('franchisor_staff')->attempt([
                'astaff_username' => $username,
                'password' => $password,
            ])) {
                return redirect()->route('franchisor-staff.dashboard');
            }
        }

        // Franchisee Staff login
        if ($role === 'franchisee-staff') {
            $staff = FranchiseeStaff::where('fstaff_username', $username)->first();
            if ($staff && $staff->fstaff_status !== 'Active') {
                return back()
                    ->withInput($request->only('username', 'role_type'))
                    ->withErrors(['login_error' => 'Your account has been archived. Please contact your franchisee admin.']);
            }

            if (Auth::guard('franchisee_staff')->attempt([
                'fstaff_username' => $username,
                'password' => $password,
            ])) {
                return redirect()->route('franchisee-staff.dashboard');
            }
        }

        return back()
            ->withInput($request->only('username', 'role_type'))
            ->withErrors(['login_error' => 'Invalid username or password for the selected role.']);
    }

    protected function getFranchiseeLoginBlockMessage(Franchisee $franchisee): ?string
    {
        $today = Carbon::today();
        $archivedBranchIds = $this->getArchivedBranchIds();
        $expiredBranchArchived = false;

        $branches = Branch::query()
            ->whereRaw('LOWER(TRIM(email)) = LOWER(TRIM(?))', [$franchisee->franchisee_email])
            ->orderByDesc('contract_expiration')
            ->orderByDesc('branch_id')
            ->get();

        foreach ($branches as $branch) {
            if (in_array((int) $branch->branch_id, $archivedBranchIds, true)) {
                continue;
            }

            if ($branch->contract_expiration !== null && $branch->contract_expiration->lte($today)) {
                $archivedBranchIds = $this->archiveExpiredBranch($branch, $archivedBranchIds);
                $expiredBranchArchived = true;
            }
        }

        $activeBranch = $branches->first(function (Branch $branch) use ($archivedBranchIds, $today) {
            return ! in_array((int) $branch->branch_id, $archivedBranchIds, true)
                && $branch->contract_expiration !== null
                && $branch->contract_expiration->gt($today);
        });

        if ($activeBranch) {
            return null;
        }

        $latestBranch = $branches->first();
        if (! $latestBranch) {
            return null;
        }

        if ($expiredBranchArchived) {
            return 'Your contract has expired and your account has been archived. Please contact the admin for renewal.';
        }

        if (in_array((int) $latestBranch->branch_id, $archivedBranchIds, true)) {
            return 'Your account has been archived. Please contact the admin for assistance.';
        }

        return null;
    }

    protected function archiveExpiredBranch(Branch $branch, array $archivedBranchIds): array
    {
        $branchId = (int) $branch->branch_id;

        if (! in_array($branchId, $archivedBranchIds, true)) {
            $archivedBranchIds[] = $branchId;
        }

        $branch->newQuery()
            ->whereKey($branch->getKey())
            ->update([
                'branch_status' => DB::raw('false'),
                'archived' => DB::raw('true'),
            ]);

        $expirationDate = optional($branch->contract_expiration)->toDateString() ?? Carbon::today()->toDateString();
        $notificationKey = $this->buildExpiredCacheKey($branchId, $expirationDate);

        if (! empty($branch->email) && ! Cache::has($notificationKey)) {
            try {
                Mail::to($branch->email)->send(new BranchContractExpiredNotification($branch));
                Cache::put($notificationKey, now()->toDateTimeString(), now()->addDays(30));
            } catch (Throwable $exception) {
                // Keep login flow working even if email delivery fails.
            }
        }

        $this->saveArchivedBranchIds($archivedBranchIds);

        return array_values(array_unique(array_map('intval', $archivedBranchIds)));
    }

    protected function getArchivedBranchIds(): array
    {
        if (! Storage::disk('local')->exists('archived_branches.json')) {
            return [];
        }

        $raw = Storage::disk('local')->get('archived_branches.json');
        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', $decoded)));
    }

    protected function saveArchivedBranchIds(array $ids): void
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        Storage::disk('local')->put('archived_branches.json', json_encode($ids));
    }

    protected function buildExpiredCacheKey(int $branchId, string $expirationDate): string
    {
        return "contract-expired-notification:branch:{$branchId}:{$expirationDate}";
    }
}
