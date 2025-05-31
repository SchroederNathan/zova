<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class StorageConnection extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'provider',
        'config',
        'is_active',
        'last_synced_at',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    protected $hidden = [
        'config', // Hide sensitive configuration data by default
    ];

    /**
     * Get the user that owns this storage connection
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all files for this storage connection
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    /**
     * Get the Laravel Storage disk instance for this connection
     */
    public function getDisk()
    {
        \Log::info('Creating disk', [
            'provider' => $this->provider,
            'connection_id' => $this->id,
        ]);

        try {
            switch ($this->provider) {
                case 's3':
                    \Log::info('Building S3 disk', [
                        'has_access_key' => !empty($this->config['access_key']),
                        'has_secret_key' => !empty($this->config['secret_key']),
                        'region' => $this->config['region'] ?? 'null',
                        'bucket' => $this->config['bucket'] ?? 'null',
                        'endpoint' => $this->config['endpoint'] ?? 'null',
                    ]);

                    $disk = Storage::build([
                        'driver' => 's3',
                        'key' => $this->config['access_key'],
                        'secret' => $this->config['secret_key'],
                        'region' => $this->config['region'],
                        'bucket' => $this->config['bucket'],
                        'url' => $this->config['url'] ?? null,
                        'endpoint' => $this->config['endpoint'] ?? null,
                    ]);

                    \Log::info('S3 disk created successfully');
                    return $disk;
                
                case 'gcs':
                    return Storage::build([
                        'driver' => 'gcs',
                        'project_id' => $this->config['project_id'],
                        'key_file' => $this->config['key_file'],
                        'bucket' => $this->config['bucket'],
                    ]);
                
                case 'nas':
                    return Storage::build([
                        'driver' => 'local',
                        'root' => $this->config['root_path'],
                    ]);
                
                default:
                    throw new \InvalidArgumentException("Unsupported provider: {$this->provider}");
            }
        } catch (\Exception $e) {
            \Log::error('Failed to create disk', [
                'provider' => $this->provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Test if the connection is working
     */
    public function testConnection(): bool
    {
        try {
            \Log::info('Testing connection', [
                'provider' => $this->provider,
                'connection_id' => $this->id,
            ]);

            $disk = $this->getDisk();
            
            \Log::info('Disk created successfully', [
                'provider' => $this->provider,
                'disk_class' => get_class($disk),
            ]);

            // Test basic connectivity with different approaches for different providers
            if ($this->provider === 's3') {
                // For S3, try to list the root directory
                try {
                    $files = $disk->files('/');
                    \Log::info('S3 connection test successful', ['file_count' => count($files)]);
                    return true;
                } catch (\Exception $e) {
                    // If listing fails, try a simple directory check
                    try {
                        $directories = $disk->directories('/');
                        \Log::info('S3 connection test successful via directories', ['dir_count' => count($directories)]);
                        return true;
                    } catch (\Exception $e2) {
                        \Log::warning('S3 connection test failed', ['error' => $e2->getMessage()]);
                        return false;
                    }
                }
            } else {
                // For other providers, use the original method
                $result = $disk->exists('.');
                \Log::info('Connection test completed', [
                    'provider' => $this->provider,
                    'result' => $result,
                ]);
                return $result;
            }
            
        } catch (\Exception $e) {
            \Log::error('Connection test failed', [
                'provider' => $this->provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Get display name for the provider
     */
    public function getProviderNameAttribute(): string
    {
        return match($this->provider) {
            's3' => 'Amazon S3',
            'gcs' => 'Google Cloud Storage',
            'nas' => 'Network Attached Storage',
            default => ucfirst($this->provider),
        };
    }
}
