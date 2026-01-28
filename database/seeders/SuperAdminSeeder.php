<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User; // <-- سنقوم باستيراد موديل المستخدم

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // التحقق إذا كان السوبر أدمن موجوداً بالفعل (حتى لا يتم إضافته مرتين)
        $admin = User::where('role', 'superadmin')->first();

        if (!$admin) {
            // إذا لم يكن موجوداً، قم بإنشائه
            User::create([
                'name' => 'Super Admin',
                'email' => 'deluxemarket2022@gmail.com', // <-- !! غيّر هذا إلى إيميلك
                'password' => Hash::make('Fslhggi@1400'), // <-- !! غيّر هذا إلى كلمة مرور قوية
                'role' => 'superadmin', // <-- تحديد الصلاحية
            ]);
            
            // طباعة رسالة نجاح في الواجهة
            $this->command->info('Super Admin user created successfully!');
        } else {
            // طباعة رسالة بأن الحساب موجود مسبقاً
            $this->command->info('Super Admin user already exists.');
        }
    }
}