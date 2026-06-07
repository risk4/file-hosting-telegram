<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TeleFile extends Model
{
    protected $table = 'tele_files';

    protected $fillable = [
        'uuid', 'name', 'original_name', 'type', 'mime_type',
        'size', 'category', 'telegram_message_id', 'telegram_chat_id',
        'description', 'content', 'is_public', 'uploaded_by', 'download_count',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'size'      => 'integer',
        'download_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->uuid = $model->uuid ?? Str::uuid();
        });
    }

    // ── Scopes ────────────────────────────────────────────
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhere('category', 'like', "%{$term}%");
        });
    }

    // ── Accessors ─────────────────────────────────────────
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        if ($bytes < 1024)        return $bytes . ' B';
        if ($bytes < 1048576)     return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1073741824)  return round($bytes / 1048576, 1) . ' MB';
        return round($bytes / 1073741824, 2) . ' GB';
    }

    public function getIconAttribute(): string
    {
        return match($this->type) {
            'image' => '🖼️',
            'video' => '🎬',
            'doc'   => '📄',
            'note'  => '📝',
            default => '📦',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'image' => 'emerald',
            'video' => 'orange',
            'doc'   => 'blue',
            'note'  => 'yellow',
            default => 'gray',
        };
    }

    public function isNote(): bool
    {
        return $this->type === 'note';
    }

    public function incrementDownload(): void
    {
        $this->increment('download_count');
    }
}
