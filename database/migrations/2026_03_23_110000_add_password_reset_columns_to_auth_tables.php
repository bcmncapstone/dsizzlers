<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('password_reset_tokens')) {
            Schema::drop('password_reset_tokens');
        }

        if (Schema::hasTable('admins') && !Schema::hasColumn('admins', 'reset_password_token')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->text('reset_password_token')->nullable();
                $table->timestamp('reset_password_expires_at')->nullable();
            });
        }

        if (Schema::hasTable('franchisees') && !Schema::hasColumn('franchisees', 'reset_password_token')) {
            Schema::table('franchisees', function (Blueprint $table) {
                $table->text('reset_password_token')->nullable();
                $table->timestamp('reset_password_expires_at')->nullable();
            });
        }

        if (Schema::hasTable('admin_staff') && !Schema::hasColumn('admin_staff', 'reset_password_token')) {
            Schema::table('admin_staff', function (Blueprint $table) {
                $table->text('reset_password_token')->nullable();
                $table->timestamp('reset_password_expires_at')->nullable();
            });
        }

        if (Schema::hasTable('franchisee_staff') && !Schema::hasColumn('franchisee_staff', 'reset_password_token')) {
            Schema::table('franchisee_staff', function (Blueprint $table) {
                $table->text('reset_password_token')->nullable();
                $table->timestamp('reset_password_expires_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('admins') && Schema::hasColumn('admins', 'reset_password_token')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->dropColumn(['reset_password_token', 'reset_password_expires_at']);
            });
        }

        if (Schema::hasTable('franchisees') && Schema::hasColumn('franchisees', 'reset_password_token')) {
            Schema::table('franchisees', function (Blueprint $table) {
                $table->dropColumn(['reset_password_token', 'reset_password_expires_at']);
            });
        }

        if (Schema::hasTable('admin_staff') && Schema::hasColumn('admin_staff', 'reset_password_token')) {
            Schema::table('admin_staff', function (Blueprint $table) {
                $table->dropColumn(['reset_password_token', 'reset_password_expires_at']);
            });
        }

        if (Schema::hasTable('franchisee_staff') && Schema::hasColumn('franchisee_staff', 'reset_password_token')) {
            Schema::table('franchisee_staff', function (Blueprint $table) {
                $table->dropColumn(['reset_password_token', 'reset_password_expires_at']);
            });
        }
    }
};
