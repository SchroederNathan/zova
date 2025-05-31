<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class File extends Model
{
    protected $fillable = [
        'storage_connection_id',
        'path',
        'name',
        'type',
        'size',
        'mime_type',
        'extension',
        'parent_path',
        'last_modified',
        'etag',
        'metadata',
    ];

    protected $casts = [
        'size' => 'integer',
        'last_modified' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the storage connection this file belongs to
     */
    public function storageConnection(): BelongsTo
    {
        return $this->belongsTo(StorageConnection::class);
    }

    /**
     * Check if this is a file (not a folder)
     */
    public function isFile(): bool
    {
        return $this->type === 'file';
    }

    /**
     * Check if this is a folder
     */
    public function isFolder(): bool
    {
        return $this->type === 'folder';
    }

    /**
     * Get human readable file size
     */
    public function getHumanSizeAttribute(): string
    {
        if ($this->isFolder() || !$this->size) {
            return '-';
        }

        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get file icon based on type/extension
     */
    public function getIconAttribute(): string
    {
        if ($this->isFolder()) {
            return 'folder';
        }

        return match($this->extension) {
            'pdf' => 'file-pdf',
            'doc', 'docx' => 'file-word',
            'xls', 'xlsx' => 'file-excel',
            'ppt', 'pptx' => 'file-powerpoint',
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'file-image',
            'mp4', 'avi', 'mov', 'wmv' => 'file-video',
            'mp3', 'wav', 'flac' => 'file-audio',
            'zip', 'rar', '7z', 'tar', 'gz' => 'file-archive',
            'txt', 'md' => 'file-text',
            'html', 'css', 'js', 'php', 'py', 'java' => 'file-code',
            default => 'file',
        };
    }

    /**
     * Get breadcrumb path as array
     */
    public function getBreadcrumbsAttribute(): array
    {
        if (!$this->parent_path) {
            return [];
        }

        $parts = explode('/', trim($this->parent_path, '/'));
        $breadcrumbs = [];
        $currentPath = '';

        foreach ($parts as $part) {
            if (empty($part)) continue;
            
            $currentPath .= '/' . $part;
            $breadcrumbs[] = [
                'name' => $part,
                'path' => $currentPath,
            ];
        }

        return $breadcrumbs;
    }

    /**
     * Check if file is an image
     */
    public function isImage(): bool
    {
        return in_array($this->extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
    }

    /**
     * Check if file can be previewed
     */
    public function canPreview(): bool
    {
        return $this->isImage() || 
               in_array($this->extension, ['pdf', 'txt', 'md']) ||
               Str::startsWith($this->mime_type, 'text/');
    }

    /**
     * Get download URL
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('files.download', [
            'connection' => $this->storage_connection_id,
            'path' => ltrim($this->path, '/'),
        ]);
    }
}
