<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class WhatsAppSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
{
    \App\Models\SystemSetting::insertOrIgnore([
        ['key' => 'whatsapp_gateway_url', 'value' => 'https://api.ultramsg.com/instanceXXXX/', 'group' => 'whatsapp', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
        ['key' => 'whatsapp_token', 'value' => 'TOKEN_HERE', 'group' => 'whatsapp', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
        ['key' => 'whatsapp_active', 'value' => '0', 'group' => 'whatsapp', 'type' => 'boolean', 'created_at' => now(), 'updated_at' => now()],
    ]);
}
}
