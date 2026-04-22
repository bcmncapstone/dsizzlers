<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->date('contract_start_date')->nullable()->after('contract_file');
        });

        DB::table('branches')
            ->select('branch_id', 'contract_expiration')
            ->whereNotNull('contract_expiration')
            ->orderBy('branch_id')
            ->get()
            ->each(function ($branch): void {
                $startDate = Carbon::parse($branch->contract_expiration)
                    ->subYearsNoOverflow(3)
                    ->toDateString();

                DB::table('branches')
                    ->where('branch_id', $branch->branch_id)
                    ->update(['contract_start_date' => $startDate]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('contract_start_date');
        });
    }
};
