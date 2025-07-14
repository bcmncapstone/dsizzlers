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
            'admin_contactNo' => '09512347433',
            'admin_email'     => 'bcmn.capstone@gmail.com',
            'admin_username'  => 'admin',
            'admin_pass'      => Hash::make('admin123'), // Secure password
            'admin_status'    => 'Active',
        ]);
    }
}
