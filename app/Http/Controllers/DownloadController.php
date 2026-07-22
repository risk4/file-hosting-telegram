<?php

namespace App\Http\Controllers;

use App\Models\TeleFile;
use App\Services\TelegramService;

class DownloadController extends Controller
{
    public function __construct(private TelegramService $telegram) {}

    /**
     * Show file info page with download button
     */
    public function show(string $uuid)
    {
        $file = TeleFile::where('uuid', $uuid)->where('is_public', true)->firstOrFail();
        return view('public.download', compact('file'));
    }

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
                'Content-Disposition' => $this->contentDisposition('attachment', $file->original_name),
                'Content-Length'      => strlen($response->body()),
                'X-Content-Type-Options' => 'nosniff',
            ]);
        } catch (\Exception $e) {
            report($e);
            abort(500, 'Gagal mengunduh file.');
        }
    }

    public function preview(string $uuid)
    {
        $file = TeleFile::where('uuid', $uuid)->where('is_public', true)->firstOrFail();

        if (!$this->canPreviewInline($file)) {
            abort(404);
        }

        try {
            $response = $this->telegram->downloadFile(
                $file->telegram_chat_id,
                $file->telegram_message_id
            );

            return response($response->body(), 200, [
                'Content-Type'        => $file->mime_type ?? 'application/octet-stream',
                'Content-Disposition' => $this->contentDisposition('inline', $file->original_name),
                'X-Content-Type-Options' => 'nosniff',
                'Content-Security-Policy' => "default-src 'none'; img-src 'self' data:; style-src 'unsafe-inline'; sandbox",
            ]);
        } catch (\Exception $e) {
            report($e);
            abort(500, 'Gagal memuat preview file.');
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
                'Content-Disposition' => $this->contentDisposition('attachment', $file->original_name),
                'X-Content-Type-Options' => 'nosniff',
            ]);
        } catch (\Exception $e) {
            report($e);
            abort(500, 'Gagal mengunduh file.');
        }
    }

    private function canPreviewInline(TeleFile $file): bool
    {
        $extension = strtolower(pathinfo($file->original_name, PATHINFO_EXTENSION));
        $mime = strtolower((string) $file->mime_type);

        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'], true)
            && (str_starts_with($mime, 'image/') || $mime === 'application/pdf')
            && $mime !== 'image/svg+xml';
    }

    private function contentDisposition(string $type, string $filename): string
    {
        $fallback = str_replace('%', '', str_replace(['\\', '/', '"'], '_', basename($filename)));
        $fallback = preg_replace('/[\x00-\x1F\x7F]+/', '', $fallback) ?: 'download';

        return sprintf('%s; filename="%s"; filename*=UTF-8\'\'%s', $type, $fallback, rawurlencode($filename));
    }
}
