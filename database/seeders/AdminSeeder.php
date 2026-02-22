<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('admins')->insert([
            'admin_fname'     => 'System',
            'admin_lname'     => 'Administrator',
            'admin_contactNo' => env('ADMIN_CONTACT', '09512347433'),
            'admin_email'     => env('ADMIN_EMAIL', 'bcmn.capstone@gmail.com'),
            'admin_username'  => env('ADMIN_USERNAME', 'admin'),
            'admin_pass'      => Hash::make(env('ADMIN_PASSWORD', 'TempPassword123!')),
            'admin_status'    => 'Active',
        ]);
    }
}