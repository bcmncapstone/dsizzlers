<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Models\Admin;
use App\Models\Franchisee;
use App\Models\FranchisorStaff;
use App\Models\FranchiseeStaff;

class PasswordResetController extends Controller
{
    /**
     * Supported password reset targets per role.
     */
    protected array $roles = [
        'admin' => [
            'model' => Admin::class,
            'id_column' => 'admin_id',
            'username_column' => 'admin_username',
            'email_column' => 'admin_email',
            'password_column' => 'admin_pass',
            'login_route' => 'admin.login',
            'label' => 'Franchisor',
        ],
        'franchisee' => [
            'model' => Franchisee::class,
            'id_column' => 'franchisee_id',
            'username_column' => 'franchisee_username',
            'email_column' => 'franchisee_email',
            'password_column' => 'franchisee_pass',
            'login_route' => 'login.franchisee',
            'label' => 'Franchisee',
        ],
        'franchisor-staff' => [
            'model' => FranchisorStaff::class,
            'id_column' => 'astaff_id',
            'username_column' => 'astaff_username',
            'email_column' => 'astaff_email',
            'password_column' => 'astaff_pass',
            'login_route' => 'login.franchisorStaff',
            'label' => 'Franchisor Staff',
        ],
        'franchisee-staff' => [
            'model' => FranchiseeStaff::class,
            'id_column' => 'fstaff_id',
            'username_column' => 'fstaff_username',
            'email_column' => 'fstaff_email',
            'password_column' => 'fstaff_pass',
            'login_route' => 'login.franchiseeStaff',
            'label' => 'Franchisee Staff',
        ],
    ];

    public function showForgotForm(Request $request, string $role): View
    {
        $config = $this->resolveRole($role);
        $username = trim((string) $request->query('username', ''));
        $email = '';
        $model = $config['model'];
        $user = null;

        if ($username !== '') {
            $user = $model::query()->where($config['username_column'], $username)->first();
        } elseif ($role === 'admin') {
            // Franchisor login commonly uses the seeded single admin account.
            $user = $model::query()->first();
            $username = $user ? (string) ($user->{$config['username_column']} ?? '') : '';
        }

        $email = $user ? (string) ($user->{$config['email_column']} ?? '') : '';

        return view('auth.forgot-password', [
            'role' => $role,
            'roleLabel' => $config['label'],
            'loginRoute' => $config['login_route'],
            'prefilledUsername' => $username,
            'prefilledEmail' => $email,
        ]);
    }

    public function lookupEmail(Request $request, string $role): JsonResponse
    {
        $config = $this->resolveRole($role);
        $username = trim((string) $request->query('username', ''));

        if ($username === '') {
            return response()->json(['email' => '']);
        }

        $model = $config['model'];
        $user = $model::query()->where($config['username_column'], $username)->first();

        return response()->json([
            'email' => $user ? (string) ($user->{$config['email_column']} ?? '') : '',
        ]);
    }

    public function sendResetLink(Request $request, string $role): RedirectResponse
    {
        $config = $this->resolveRole($role);

        $request->validate([
            'username' => ['required', 'string'],
        ]);

        $username = trim($request->string('username')->toString());
        $model = $config['model'];
        $user = $model::query()->where($config['username_column'], $username)->first();

        // Keep response generic to avoid exposing whether an email exists.
        if ($user && !empty($user->{$config['email_column']})) {
            $email = (string) $user->{$config['email_column']};
            $plainToken = Str::random(64);

            $user->forceFill([
                'reset_password_token' => Hash::make($plainToken),
                'reset_password_expires_at' => Carbon::now()->addMinutes(60),
            ])->save();

            $resetUrl = URL::route('password.reset.form', [
                'role' => $role,
                'token' => $plainToken,
                'email' => $email,
            ]);

            Mail::raw(
                "You requested a password reset for your {$config['label']} account.\n\n" .
                "Click this link to reset your password:\n{$resetUrl}\n\n" .
                "This link will expire in 60 minutes.\n\n" .
                "If you did not request this, you can ignore this email.",
                function ($message) use ($email) {
                    $message->to($email)->subject('D Sizzlers Password Reset');
                }
            );
        }

        return back()->with('status', 'If the account exists and has an email on file, a password reset link has been sent.');
    }

    public function showResetForm(Request $request, string $role, string $token): View
    {
        $config = $this->resolveRole($role);

        return view('auth.reset-password', [
            'role' => $role,
            'roleLabel' => $config['label'],
            'token' => $token,
            'email' => $request->query('email', ''),
            'loginRoute' => $config['login_route'],
        ]);
    }

    public function reset(Request $request, string $role): RedirectResponse
    {
        $config = $this->resolveRole($role);

        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[^A-Za-z0-9]).+$/',
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number and one special character.',
        ]);

        $email = $request->string('email')->toString();
        $plainToken = $request->string('token')->toString();
        $model = $config['model'];

        $user = $model::query()->where($config['email_column'], $email)->first();

        if (!$user || !$user->reset_password_token || !Hash::check($plainToken, $user->reset_password_token)) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'This password reset token is invalid.']);
        }

        $expiresAt = $user->reset_password_expires_at ? Carbon::parse($user->reset_password_expires_at) : null;
        if (!$expiresAt || Carbon::now()->greaterThan($expiresAt)) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'This password reset token has expired.']);
        }

        $user->forceFill([
            $config['password_column'] => Hash::make($request->string('password')->toString()),
            'reset_password_token' => null,
            'reset_password_expires_at' => null,
        ])->save();

        return redirect()->route($config['login_route'])
            ->with('status', 'Your password has been reset. You can now log in.');
    }

    protected function resolveRole(string $role): array
    {
        abort_unless(isset($this->roles[$role]), 404);

        return $this->roles[$role];
    }
}
