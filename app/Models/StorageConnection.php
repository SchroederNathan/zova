<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class StorageConnection extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'provider',
        'is_active',
        'last_synced_at',
        // S3 fields
        's3_access_key',
        's3_secret_key',
        's3_region', 
        's3_bucket',
        's3_endpoint',
        's3_url',
        // GCS fields
        'gcs_project_id',
        'gcs_key_file',
        'gcs_bucket',
        // NAS fields
        'nas_root_path',
        'nas_type',
        'nas_host',
        'nas_share',
        'nas_username',
        'nas_password',
        'nas_mount_point',
        'nas_domain',
        'nas_is_mounted',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
        'nas_is_mounted' => 'boolean',
    ];

    protected $hidden = [
        's3_secret_key', // Hide sensitive credentials
        'gcs_key_file',
        'nas_password', // Hide NAS password
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
                        'has_access_key' => !empty($this->s3_access_key),
                        'has_secret_key' => !empty($this->s3_secret_key),
                        'region' => $this->s3_region ?? 'null',
                        'bucket' => $this->s3_bucket ?? 'null',
                        'endpoint' => $this->s3_endpoint ?? 'null',
                    ]);

                    $disk = Storage::build([
                        'driver' => 's3',
                        'key' => $this->s3_access_key,
                        'secret' => $this->s3_secret_key,
                        'region' => $this->s3_region,
                        'bucket' => $this->s3_bucket,
                        'url' => $this->s3_url,
                        'endpoint' => $this->s3_endpoint,
                    ]);

                    \Log::info('S3 disk created successfully');
                    return $disk;
                
                case 'gcs':
                    return Storage::build([
                        'driver' => 'gcs',
                        'project_id' => $this->gcs_project_id,
                        'key_file' => $this->gcs_key_file,
                        'bucket' => $this->gcs_bucket,
                    ]);
                
                case 'nas':
                    // For NAS, we need to ensure the share is mounted first
                    if ($this->nas_type === 'smb') {
                        if (!$this->nas_is_mounted) {
                            throw new \Exception('NAS share is not mounted. Please mount the share first.');
                        }
                        $rootPath = $this->nas_mount_point;
                    } else {
                        $rootPath = $this->nas_root_path;
                    }
                    
                    return Storage::build([
                        'driver' => 'local',
                        'root' => $rootPath,
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
     * Mount SMB/CIFS share to local filesystem
     */
    public function mountNasShare(): bool
    {
        if ($this->provider !== 'nas' || $this->nas_type !== 'smb') {
            return false;
        }

        if ($this->nas_is_mounted) {
            return true; // Already mounted
        }

        try {
            // Create mount point directory
            $mountPoint = $this->nas_mount_point;
            if (!File::exists($mountPoint)) {
                File::makeDirectory($mountPoint, 0755, true);
            }

            // Build the SMB share URL
            $shareUrl = "//{$this->nas_host}/{$this->nas_share}";
            
            // Determine OS and mount command
            $os = PHP_OS_FAMILY;
            $command = '';
            
            if ($os === 'Linux') {
                // Linux CIFS mount
                $options = "username={$this->nas_username},password={$this->nas_password}";
                if ($this->nas_domain) {
                    $options .= ",domain={$this->nas_domain}";
                }
                $options .= ",uid=" . posix_getuid() . ",gid=" . posix_getgid() . ",file_mode=0664,dir_mode=0775";
                
                $command = "sudo mount -t cifs '{$shareUrl}' '{$mountPoint}' -o {$options}";
                
            } elseif ($os === 'Darwin') {
                // macOS SMB mount
                $credentials = $this->nas_username;
                if ($this->nas_domain) {
                    $credentials = "{$this->nas_domain};{$this->nas_username}";
                }
                
                $command = "mount -t smbfs //{$credentials}:{$this->nas_password}@{$this->nas_host}/{$this->nas_share} '{$mountPoint}'";
                
            } elseif ($os === 'Windows') {
                // Windows net use command
                $driveLetter = $this->getAvailableDriveLetter();
                $command = "net use {$driveLetter}: \\\\{$this->nas_host}\\{$this->nas_share} /user:{$this->nas_username} {$this->nas_password}";
                $mountPoint = $driveLetter . ':';
                
            } else {
                throw new \Exception("Unsupported operating system: {$os}");
            }

            \Log::info('Mounting NAS share', [
                'connection_id' => $this->id,
                'host' => $this->nas_host,
                'share' => $this->nas_share,
                'mount_point' => $mountPoint,
                'os' => $os
            ]);

            // Execute mount command
            $result = Process::run($command);
            
            if ($result->successful()) {
                // Update mount status and mount point
                $this->update([
                    'nas_is_mounted' => true,
                    'nas_mount_point' => $mountPoint
                ]);
                
                \Log::info('NAS share mounted successfully', [
                    'connection_id' => $this->id,
                    'mount_point' => $mountPoint
                ]);
                
                return true;
            } else {
                \Log::error('Failed to mount NAS share', [
                    'connection_id' => $this->id,
                    'error' => $result->errorOutput(),
                    'exit_code' => $result->exitCode()
                ]);
                
                return false;
            }

        } catch (\Exception $e) {
            \Log::error('Exception while mounting NAS share', [
                'connection_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }

    /**
     * Unmount SMB/CIFS share
     */
    public function unmountNasShare(): bool
    {
        if ($this->provider !== 'nas' || $this->nas_type !== 'smb' || !$this->nas_is_mounted) {
            return true; // Nothing to unmount
        }

        try {
            $os = PHP_OS_FAMILY;
            $command = '';
            
            if ($os === 'Linux' || $os === 'Darwin') {
                $command = "sudo umount '{$this->nas_mount_point}'";
            } elseif ($os === 'Windows') {
                // Extract drive letter from mount point
                $driveLetter = substr($this->nas_mount_point, 0, 2);
                $command = "net use {$driveLetter} /delete";
            }

            \Log::info('Unmounting NAS share', [
                'connection_id' => $this->id,
                'mount_point' => $this->nas_mount_point,
                'os' => $os
            ]);

            $result = Process::run($command);
            
            if ($result->successful()) {
                $this->update(['nas_is_mounted' => false]);
                
                \Log::info('NAS share unmounted successfully', [
                    'connection_id' => $this->id
                ]);
                
                return true;
            } else {
                \Log::warning('Failed to unmount NAS share', [
                    'connection_id' => $this->id,
                    'error' => $result->errorOutput()
                ]);
                
                return false;
            }

        } catch (\Exception $e) {
            \Log::error('Exception while unmounting NAS share', [
                'connection_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Get available drive letter for Windows
     */
    private function getAvailableDriveLetter(): string
    {
        for ($letter = ord('Z'); $letter >= ord('A'); $letter--) {
            $drive = chr($letter);
            if (!is_dir($drive . ':')) {
                return $drive;
            }
        }
        throw new \Exception('No available drive letters');
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

            // For NAS SMB connections, try mounting first
            if ($this->provider === 'nas' && $this->nas_type === 'smb') {
                if (!$this->mountNasShare()) {
                    return false;
                }
            }

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
            } elseif ($this->provider === 'gcs') {
                // For GCS, try to list the root directory
                try {
                    $files = $disk->files('/');
                    \Log::info('GCS connection test successful', ['file_count' => count($files)]);
                    return true;
                } catch (\Exception $e) {
                    // If listing fails, try a simple directory check
                    try {
                        $directories = $disk->directories('/');
                        \Log::info('GCS connection test successful via directories', ['dir_count' => count($directories)]);
                        return true;
                    } catch (\Exception $e2) {
                        \Log::warning('GCS connection test failed', ['error' => $e2->getMessage()]);
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

    /**
     * Cleanup when deleting the connection
     */
    protected static function booted()
    {
        static::deleting(function ($connection) {
            // Unmount NAS share when deleting connection
            if ($connection->provider === 'nas' && $connection->nas_type === 'smb') {
                $connection->unmountNasShare();
            }
        });
    }
}
