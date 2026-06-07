<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->timestamps();
        });

        // Default settings
        $defaults = [
            // Telegram
            ['key' => 'telegram_api_id',       'value' => '',      'group' => 'telegram', 'type' => 'string'],
            ['key' => 'telegram_api_hash',      'value' => '',      'group' => 'telegram', 'type' => 'string'],
            ['key' => 'telegram_chat_id',       'value' => 'me',    'group' => 'telegram', 'type' => 'string'],
            ['key' => 'telegram_session',       'value' => '',      'group' => 'telegram', 'type' => 'string'],
            ['key' => 'telegram_connected',     'value' => '0',     'group' => 'telegram', 'type' => 'boolean'],

            // Upload
            ['key' => 'max_upload_mb',          'value' => '2048',  'group' => 'upload',   'type' => 'integer'],
            ['key' => 'guest_upload_enabled',   'value' => '1',     'group' => 'upload',   'type' => 'boolean'],
            ['key' => 'allowed_extensions',     'value' => '',      'group' => 'upload',   'type' => 'string'],  // kosong = semua
            ['key' => 'guest_upload_label',     'value' => 'guest', 'group' => 'upload',   'type' => 'string'],

            // Public
            ['key' => 'site_name',              'value' => 'TeleStore',   'group' => 'general', 'type' => 'string'],
            ['key' => 'site_description',       'value' => 'Cloud storage berbasis Telegram', 'group' => 'general', 'type' => 'string'],
            ['key' => 'public_browse_enabled',  'value' => '1',     'group' => 'general',  'type' => 'boolean'],
            ['key' => 'public_upload_enabled',  'value' => '1',     'group' => 'general',  'type' => 'boolean'],
        ];

        foreach ($defaults as $setting) {
            \DB::table('settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
