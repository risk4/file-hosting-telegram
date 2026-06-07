<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use App\Services\TelegramService;
use Livewire\Component;

class Settings extends Component
{
    // Telegram
    public string $telegramApiId   = '';
    public string $telegramApiHash = '';
    public string $telegramChatId  = 'me';
    public bool   $telegramConnected = false;
    public array  $telegramUser    = [];

    // Login flow
    public string $loginStep      = 'credentials'; // credentials | otp | twofa | connected
    public string $loginPhone     = '';
    public string $loginOtp       = '';
    public string $login2fa       = '';
    public string $phoneCodeHash  = '';
    public string $loginMessage   = '';

    // Upload
    public int    $maxUploadMb         = 2048;
    public bool   $guestUploadEnabled  = true;
    public string $allowedExtensions   = '';
    public string $guestUploadLabel    = 'guest';

    // General
    public string $siteName        = 'TeleStore';
    public string $siteDescription = 'Cloud storage berbasis Telegram';
    public bool   $publicBrowse    = true;
    public bool   $publicUpload    = true;

    public function mount(): void
    {
        $this->telegramApiId      = Setting::get('telegram_api_id', '');
        $this->telegramApiHash    = Setting::get('telegram_api_hash', '');
        $this->telegramChatId     = Setting::get('telegram_chat_id', 'me');
        $this->telegramConnected  = (bool) Setting::get('telegram_connected', false);

        $this->maxUploadMb        = (int) Setting::get('max_upload_mb', 2048);
        $this->guestUploadEnabled = (bool) Setting::get('guest_upload_enabled', true);
        $this->allowedExtensions  = Setting::get('allowed_extensions', '');
        $this->guestUploadLabel   = Setting::get('guest_upload_label', 'guest');

        $this->siteName           = Setting::get('site_name', 'TeleStore');
        $this->siteDescription    = Setting::get('site_description', '');
        $this->publicBrowse       = (bool) Setting::get('public_browse_enabled', true);
        $this->publicUpload       = (bool) Setting::get('public_upload_enabled', true);

        if ($this->telegramConnected) {
            $this->loginStep = 'connected';
            $status = app(TelegramService::class)->status();
            if ($status['connected'] ?? false) {
                $this->telegramUser = $status['user'] ?? [];
            } else {
                $this->telegramConnected = false;
                $this->loginStep = 'credentials';
            }
        }
    }

    // ── Telegram Login Steps ──────────────────────────────
    public function startLogin(): void
    {
        $this->validate([
            'telegramApiId'   => 'required|string',
            'telegramApiHash' => 'required|string',
            'loginPhone'      => 'required|string',
        ]);

        $result = app(TelegramService::class)->loginStart(
            $this->telegramApiId,
            $this->telegramApiHash,
            $this->loginPhone
        );

        if (isset($result['error'])) {
            $this->loginMessage = '❌ ' . $result['error'];
            return;
        }

        if ($result['status'] === 'already_logged_in') {
            $this->onConnected($result['user'] ?? []);
            return;
        }

        $this->phoneCodeHash = $result['phoneCodeHash'] ?? '';
        $this->loginStep     = 'otp';
        $this->loginMessage  = '✅ Kode OTP dikirim ke Telegram kamu';
    }

    public function verifyOtp(): void
    {
        $this->validate(['loginOtp' => 'required|string']);

        $result = app(TelegramService::class)->loginVerify(
            $this->telegramApiId,
            $this->telegramApiHash,
            $this->loginPhone,
            $this->loginOtp,
            $this->phoneCodeHash
        );

        if (isset($result['error'])) {
            $this->loginMessage = '❌ ' . $result['error'];
            return;
        }

        if ($result['status'] === 'need_2fa') {
            $this->loginStep    = 'twofa';
            $this->loginMessage = '🔒 Masukkan password 2FA';
            return;
        }

        $this->onConnected($result['user'] ?? []);
    }

    public function verify2fa(): void
    {
        $this->validate(['login2fa' => 'required|string']);

        $result = app(TelegramService::class)->login2fa(
            $this->telegramApiId,
            $this->telegramApiHash,
            $this->login2fa
        );

        if (isset($result['error'])) {
            $this->loginMessage = '❌ ' . $result['error'];
            return;
        }

        $this->onConnected($result['user'] ?? []);
    }

    private function onConnected(array $user): void
    {
        $this->telegramUser      = $user;
        $this->telegramConnected = true;
        $this->loginStep         = 'connected';
        $this->loginMessage      = '';

        Setting::set('telegram_api_id',   $this->telegramApiId);
        Setting::set('telegram_api_hash', $this->telegramApiHash);
        Setting::set('telegram_connected', '1');

        $this->dispatch('notify', type: 'success', message: 'Terhubung ke Telegram!');
    }

    public function disconnectTelegram(): void
    {
        app(TelegramService::class)->logout();
        Setting::set('telegram_connected', '0');
        Setting::set('telegram_session', '');
        $this->telegramConnected = false;
        $this->loginStep         = 'credentials';
        $this->telegramUser      = [];
        $this->dispatch('notify', type: 'info', message: 'Telegram diputus');
    }

    // ── Save Sections ─────────────────────────────────────
    public function saveTelegramSettings(): void
    {
        $this->validate([
            'telegramChatId' => 'required|string',
        ]);

        Setting::set('telegram_chat_id', $this->telegramChatId);
        $this->dispatch('notify', type: 'success', message: 'Pengaturan Telegram disimpan');
    }

    public function saveUploadSettings(): void
    {
        $this->validate([
            'maxUploadMb'      => 'required|integer|min:1|max:2048',
            'guestUploadLabel' => 'nullable|string|max:50',
        ]);

        Setting::set('max_upload_mb',        $this->maxUploadMb);
        Setting::set('guest_upload_enabled', $this->guestUploadEnabled ? '1' : '0');
        Setting::set('allowed_extensions',   $this->allowedExtensions);
        Setting::set('guest_upload_label',   $this->guestUploadLabel);
        $this->dispatch('notify', type: 'success', message: 'Pengaturan upload disimpan');
    }

    public function saveGeneralSettings(): void
    {
        $this->validate([
            'siteName'        => 'required|string|max:100',
            'siteDescription' => 'nullable|string|max:255',
        ]);

        Setting::set('site_name',              $this->siteName);
        Setting::set('site_description',       $this->siteDescription);
        Setting::set('public_browse_enabled',  $this->publicBrowse ? '1' : '0');
        Setting::set('public_upload_enabled',  $this->publicUpload ? '1' : '0');
        $this->dispatch('notify', type: 'success', message: 'Pengaturan umum disimpan');
    }

    public function render()
    {
        return view('livewire.admin.settings')
            ->layout('layouts.admin', ['title' => 'Pengaturan']);
    }
}
