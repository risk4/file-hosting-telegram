<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;
use Exception;

class TelegramService
{
    private string $sidecarUrl;
    private string $sidecarSecret;

    public function __construct()
    {
        $this->sidecarUrl    = config('telestore.sidecar_url', env('SIDECAR_URL', 'http://localhost:3001'));
        $this->sidecarSecret = config('telestore.sidecar_secret', env('SIDECAR_SECRET', ''));
    }

    private function headers(): array
    {
        return ['X-Sidecar-Secret' => $this->sidecarSecret];
    }

    // ── Auth / Status ─────────────────────────────────────
    public function status(): array
    {
        try {
            $res = Http::withHeaders($this->headers())
                ->timeout(5)
                ->get("{$this->sidecarUrl}/api/status");
            return $res->json();
        } catch (Exception $e) {
            return ['connected' => false, 'error' => 'Sidecar tidak bisa dijangkau: ' . $e->getMessage()];
        }
    }

    public function loginStart(string $apiId, string $apiHash, string $phone): array
    {
        $res = Http::withHeaders($this->headers())
            ->timeout(30)
            ->post("{$this->sidecarUrl}/api/login/start", compact('apiId', 'apiHash', 'phone'));
        return $res->json();
    }

    public function loginVerify(string $apiId, string $apiHash, string $phone, string $code, string $phoneCodeHash): array
    {
        $res = Http::withHeaders($this->headers())
            ->timeout(30)
            ->post("{$this->sidecarUrl}/api/login/verify", compact('apiId', 'apiHash', 'phone', 'code', 'phoneCodeHash'));
        return $res->json();
    }

    public function login2fa(string $apiId, string $apiHash, string $password): array
    {
        $res = Http::withHeaders($this->headers())
            ->timeout(30)
            ->post("{$this->sidecarUrl}/api/login/2fa", compact('apiId', 'apiHash', 'password'));
        return $res->json();
    }

    public function logout(): array
    {
        $res = Http::withHeaders($this->headers())
            ->timeout(10)
            ->post("{$this->sidecarUrl}/api/logout");
        return $res->json();
    }

    // ── Upload File ───────────────────────────────────────
    public function uploadFile(string $filePath, string $originalName, string $chatId): array
    {
        $maxMb  = (int) Setting::get('max_upload_mb', 2048);
        $fileSize = filesize($filePath);

        if ($fileSize > $maxMb * 1024 * 1024) {
            throw new Exception("File melebihi batas upload {$maxMb}MB.");
        }

        $res = Http::withHeaders($this->headers())
            ->timeout(0) // no timeout untuk file besar
            ->attach('file', fopen($filePath, 'r'), $originalName)
            ->post("{$this->sidecarUrl}/api/upload", [
                'chatId'  => $chatId,
                'caption' => "📁 {$originalName}",
            ]);

        if (!$res->successful()) {
            throw new Exception($res->json('error') ?? 'Upload gagal');
        }

        return $res->json();
    }

    // ── Download File ─────────────────────────────────────
    public function downloadFile(string $chatId, string $messageId): \Illuminate\Http\Client\Response
    {
        return Http::withHeaders($this->headers())
            ->timeout(0)
            ->post("{$this->sidecarUrl}/api/download", [
                'chatId'    => $chatId,
                'messageId' => $messageId,
            ]);
    }

    // ── Send Note ─────────────────────────────────────────
    public function sendNote(string $chatId, string $text, string $label): array
    {
        $res = Http::withHeaders($this->headers())
            ->timeout(30)
            ->post("{$this->sidecarUrl}/api/note", compact('chatId', 'text', 'label'));

        if (!$res->successful()) {
            throw new Exception($res->json('error') ?? 'Gagal menyimpan catatan');
        }

        return $res->json();
    }

    // ── Delete Message ────────────────────────────────────
    public function deleteMessage(string $chatId, string $messageId): array
    {
        $res = Http::withHeaders($this->headers())
            ->timeout(15)
            ->post("{$this->sidecarUrl}/api/delete", compact('chatId', 'messageId'));
        return $res->json();
    }

    // ── Helper: deteksi tipe file ─────────────────────────
    public static function detectType(string $filename, ?string $mime = null): string
    {
        $ext  = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime = $mime ?? '';

        if (in_array($ext, ['jpg','jpeg','png','gif','webp','bmp','svg','avif']) || str_starts_with($mime, 'image/')) {
            return 'image';
        }
        if (in_array($ext, ['mp4','mov','avi','mkv','webm','flv','wmv']) || str_starts_with($mime, 'video/')) {
            return 'video';
        }
        if (in_array($ext, ['pdf','doc','docx','xls','xlsx','ppt','pptx','txt','csv','json','md','zip','rar','7z'])) {
            return 'doc';
        }
        return 'other';
    }
}
