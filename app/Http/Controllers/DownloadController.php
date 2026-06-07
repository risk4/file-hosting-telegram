<?php

namespace App\Http\Controllers;

use App\Models\TeleFile;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class DownloadController extends Controller
{
    public function __construct(private TelegramService $telegram) {}

    public function download(string $uuid)
    {
        $file = TeleFile::where('uuid', $uuid)->where('is_public', true)->firstOrFail();

        try {
            $response = $this->telegram->downloadFile(
                $file->telegram_chat_id,
                $file->telegram_message_id
            );

            if (!$response->successful()) {
                abort(500, 'Gagal mengunduh file dari Telegram.');
            }

            $file->incrementDownload();

            return response($response->body(), 200, [
                'Content-Type'        => $file->mime_type ?? 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . rawurlencode($file->original_name) . '"',
                'Content-Length'      => strlen($response->body()),
            ]);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }
    }

    public function preview(string $uuid)
    {
        $file = TeleFile::where('uuid', $uuid)->where('is_public', true)->firstOrFail();

        if (!in_array($file->type, ['image', 'doc'])) {
            abort(404);
        }

        try {
            $response = $this->telegram->downloadFile(
                $file->telegram_chat_id,
                $file->telegram_message_id
            );

            return response($response->body(), 200, [
                'Content-Type'        => $file->mime_type ?? 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="' . rawurlencode($file->original_name) . '"',
            ]);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }
    }

    // Download untuk admin (tidak perlu is_public)
    public function adminDownload(string $uuid)
    {
        $file = TeleFile::where('uuid', $uuid)->firstOrFail();

        try {
            $response = $this->telegram->downloadFile(
                $file->telegram_chat_id,
                $file->telegram_message_id
            );

            return response($response->body(), 200, [
                'Content-Type'        => $file->mime_type ?? 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . rawurlencode($file->original_name) . '"',
            ]);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }
    }
}
