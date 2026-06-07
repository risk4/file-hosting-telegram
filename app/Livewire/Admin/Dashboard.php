<?php

namespace App\Livewire\Admin;

use App\Models\TeleFile;
use App\Services\TelegramService;
use Livewire\Component;

class Dashboard extends Component
{
    public array $stats        = [];
    public array $sidecarStatus = [];
    public array $recentFiles  = [];

    public function mount(): void
    {
        $this->loadStats();
        $this->checkSidecar();
    }

    public function loadStats(): void
    {
        $this->stats = [
            'total_files'     => TeleFile::count(),
            'total_size'      => TeleFile::sum('size'),
            'total_downloads' => TeleFile::sum('download_count'),
            'total_notes'     => TeleFile::ofType('note')->count(),
            'images'          => TeleFile::ofType('image')->count(),
            'videos'          => TeleFile::ofType('video')->count(),
            'docs'            => TeleFile::ofType('doc')->count(),
            'guest_uploads'   => TeleFile::where('uploaded_by', 'guest')->count(),
            'today_uploads'   => TeleFile::whereDate('created_at', today())->count(),
            'week_uploads'    => TeleFile::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];

        $this->recentFiles = TeleFile::latest()->take(8)->get()->toArray();
    }

    public function checkSidecar(): void
    {
        $this->sidecarStatus = app(TelegramService::class)->status();
    }

    public function refreshAll(): void
    {
        $this->loadStats();
        $this->checkSidecar();
        $this->dispatch('notify', type: 'success', message: 'Data diperbarui');
    }

    public function formattedSize(int $bytes): string
    {
        if ($bytes < 1024)       return $bytes . ' B';
        if ($bytes < 1048576)    return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';
        return round($bytes / 1073741824, 2) . ' GB';
    }

    public function render()
    {
        return view('livewire.admin.dashboard')
            ->layout('layouts.admin', ['title' => 'Dashboard']);
    }
}
