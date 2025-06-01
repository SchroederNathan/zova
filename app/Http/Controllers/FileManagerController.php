<?php

namespace App\Http\Controllers;

use App\Models\StorageConnection;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileManagerController extends Controller
{
    /**
     * Display the file manager dashboard
     */
    public function index()
    {
        $connections = auth()->user()->storageConnections()
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('files.index', compact('connections'));
    }

    /**
     * Browse files in a specific storage connection
     */
    public function browse(StorageConnection $connection, Request $request)
    {
        // Ensure user owns this connection
        $this->authorize('view', $connection);

        $path = $request->get('path', '');
        $path = ltrim($path, '/'); // Remove leading slash

        try {
            // Get the storage disk for this connection
            $disk = $connection->getDisk();
            
            // List files and directories
            $items = $this->listItems($disk, $path);
            
            // Get breadcrumbs for navigation
            $breadcrumbs = $this->getBreadcrumbs($path);
            
            return view('files.browse', compact('connection', 'items', 'path', 'breadcrumbs'));
            
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to connect to storage: ' . $e->getMessage()]);
        }
    }

    /**
     * List files and directories in the given path
     */
    private function listItems($disk, $path)
    {
        $items = [];
        
        try {
            // Get all files and directories
            $files = $disk->files($path);
            $directories = $disk->directories($path);
            
            // Normalize the current path for comparison
            $currentPath = rtrim($path, '/');
            
            // Process directories first
            foreach ($directories as $directory) {
                // Skip the current directory itself
                $normalizedDirectory = rtrim($directory, '/');
                if ($normalizedDirectory === $currentPath) {
                    continue;
                }
                
                $name = basename($directory);
                
                // Also skip if the directory name is the same as the current path basename
                // This handles cases where the current directory appears in the listing
                if (!empty($currentPath) && $name === basename($currentPath)) {
                    continue;
                }
                
                $items[] = [
                    'name' => $name,
                    'path' => $directory,
                    'type' => 'folder',
                    'size' => null,
                    'last_modified' => null,
                    'icon' => 'folder',
                ];
            }
            
            // Process files
            foreach ($files as $file) {
                $name = basename($file);
                $extension = pathinfo($name, PATHINFO_EXTENSION);
                
                try {
                    $size = $disk->size($file);
                    $lastModified = $disk->lastModified($file);
                } catch (\Exception $e) {
                    $size = 0;
                    $lastModified = null;
                }
                
                $items[] = [
                    'name' => $name,
                    'path' => $file,
                    'type' => 'file',
                    'size' => $size,
                    'last_modified' => $lastModified ? date('Y-m-d H:i:s', $lastModified) : null,
                    'extension' => $extension,
                    'icon' => $this->getFileIcon($extension),
                    'human_size' => $this->formatBytes($size),
                ];
            }
            
            // Sort: folders first, then files, both alphabetically
            usort($items, function ($a, $b) {
                if ($a['type'] !== $b['type']) {
                    return $a['type'] === 'folder' ? -1 : 1;
                }
                return strcasecmp($a['name'], $b['name']);
            });
            
        } catch (\Exception $e) {
            // If we can't list the directory, return empty array
            // The error will be handled by the calling method
        }
        
        return $items;
    }

    /**
     * Generate breadcrumbs for navigation
     */
    private function getBreadcrumbs($path)
    {
        if (empty($path)) {
            return [];
        }

        $parts = explode('/', trim($path, '/'));
        $breadcrumbs = [];
        $currentPath = '';

        foreach ($parts as $part) {
            if (empty($part)) continue;
            
            $currentPath .= ($currentPath ? '/' : '') . $part;
            $breadcrumbs[] = [
                'name' => $part,
                'path' => $currentPath,
            ];
        }

        return $breadcrumbs;
    }

    /**
     * Get file icon based on extension
     */
    private function getFileIcon($extension)
    {
        return match(strtolower($extension)) {
            'pdf' => 'file-pdf',
            'doc', 'docx' => 'file-word',
            'xls', 'xlsx' => 'file-excel',
            'ppt', 'pptx' => 'file-powerpoint',
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg' => 'file-image',
            'mp4', 'avi', 'mov', 'wmv' => 'file-video',
            'mp3', 'wav', 'flac' => 'file-audio',
            'zip', 'rar', '7z', 'tar', 'gz' => 'file-archive',
            'txt', 'md' => 'file-text',
            'html', 'css', 'js', 'php', 'py', 'java' => 'file-code',
            default => 'file',
        };
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes)
    {
        if ($bytes === 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
