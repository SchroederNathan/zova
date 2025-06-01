<?php

namespace App\Http\Controllers;

use App\Models\StorageConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class FileOperationController extends Controller
{
    /**
     * Download a file from storage
     */
    public function download(StorageConnection $connection, Request $request)
    {
        // Ensure user owns this connection
        $this->authorize('view', $connection);

        $path = $request->route('path');
        
        try {
            $disk = $connection->getDisk();
            
            // Check if file exists
            if (!$disk->exists($path)) {
                return back()->withErrors(['error' => 'File not found.']);
            }
            
            // Get file content and metadata
            $content = $disk->get($path);
            $filename = basename($path);
            $mimeType = $disk->mimeType($path) ?? 'application/octet-stream';
            
            // Return file download response
            return Response::make($content, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => strlen($content),
            ]);
            
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to download file: ' . $e->getMessage()]);
        }
    }

    /**
     * Preview a file from storage
     */
    public function preview(StorageConnection $connection, Request $request)
    {
        // Ensure user owns this connection
        $this->authorize('view', $connection);

        $path = $request->route('path');
        
        try {
            $disk = $connection->getDisk();
            
            // Check if file exists
            if (!$disk->exists($path)) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'File not found'], 404);
                }
                return back()->withErrors(['error' => 'File not found.']);
            }
            
            // Get file info
            $filename = basename($path);
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $mimeType = $disk->mimeType($path) ?? 'application/octet-stream';
            
            // Handle different file types
            if ($this->isImageFile($extension)) {
                // For images, return the actual file content
                $content = $disk->get($path);
                return Response::make($content, 200, [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => 'inline; filename="' . $filename . '"',
                    'Cache-Control' => 'public, max-age=3600',
                ]);
            } elseif ($this->isTextFile($extension) || str_starts_with($mimeType, 'text/')) {
                // For text files, return content in JSON for display
                $content = $disk->get($path);
                
                // Limit content size for preview (1MB max)
                if (strlen($content) > 1048576) {
                    $content = substr($content, 0, 1048576) . "\n\n... (content truncated)";
                }
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'content' => $content,
                        'type' => 'text',
                        'filename' => $filename,
                        'size' => $disk->size($path),
                    ]);
                }
                
                return Response::make($content, 200, [
                    'Content-Type' => 'text/plain; charset=utf-8',
                ]);
            } else {
                // For non-previewable files
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Preview not available for this file type',
                        'type' => 'unsupported',
                        'extension' => $extension,
                        'mimeType' => $mimeType,
                    ], 422);
                }
                
                return back()->withErrors(['error' => 'Preview not available for this file type.']);
            }
            
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to preview file: ' . $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => 'Failed to preview file: ' . $e->getMessage()]);
        }
    }

    /**
     * Check if file is an image
     */
    private function isImageFile(string $extension): bool
    {
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico']);
    }

    /**
     * Check if file is a text file
     */
    private function isTextFile(string $extension): bool
    {
        return in_array($extension, [
            'txt', 'md', 'markdown', 'csv', 'json', 'xml', 'yaml', 'yml',
            'html', 'htm', 'css', 'js', 'ts', 'jsx', 'tsx', 'vue',
            'php', 'py', 'rb', 'java', 'c', 'cpp', 'h', 'cs', 'go',
            'rs', 'swift', 'kt', 'scala', 'sh', 'bash', 'zsh',
            'sql', 'log', 'conf', 'ini', 'env', 'gitignore', 'dockerfile'
        ]);
    }

    /**
     * Upload files to storage
     */
    public function upload(StorageConnection $connection, Request $request)
    {
        $this->authorize('view', $connection);

        $request->validate([
            'files.*' => 'required|file|max:102400', // 100MB max
            'path' => 'nullable|string',
        ]);

        $path = $request->get('path', '');
        $uploadedFiles = [];
        $errors = [];

        try {
            $disk = $connection->getDisk();

            foreach ($request->file('files') as $file) {
                try {
                    $filename = $file->getClientOriginalName();
                    $filePath = $path ? $path . '/' . $filename : $filename;
                    
                    // Upload file
                    $disk->put($filePath, file_get_contents($file->getRealPath()));
                    $uploadedFiles[] = $filename;
                    
                } catch (\Exception $e) {
                    $errors[] = "Failed to upload {$file->getClientOriginalName()}: " . $e->getMessage();
                }
            }

            $message = count($uploadedFiles) . ' file(s) uploaded successfully.';
            if (count($errors) > 0) {
                $message .= ' ' . count($errors) . ' file(s) failed.';
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Upload failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete a file or folder
     */
    public function delete(StorageConnection $connection, Request $request)
    {
        $this->authorize('view', $connection);

        $path = $request->route('path');

        try {
            $disk = $connection->getDisk();

            if (!$disk->exists($path)) {
                return back()->withErrors(['error' => 'File or folder not found.']);
            }

            // Delete file or directory
            if ($disk->directoryExists($path)) {
                $disk->deleteDirectory($path);
                $message = 'Folder deleted successfully.';
            } else {
                $disk->delete($path);
                $message = 'File deleted successfully.';
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete: ' . $e->getMessage()]);
        }
    }

    /**
     * Create a new folder
     */
    public function createFolder(StorageConnection $connection, Request $request)
    {
        $this->authorize('view', $connection);

        $request->validate([
            'folder_name' => 'required|string|max:255',
            'path' => 'nullable|string',
        ]);

        $folderName = $request->get('folder_name');
        $currentPath = $request->get('path', '');
        $newFolderPath = $currentPath ? $currentPath . '/' . $folderName : $folderName;

        try {
            $disk = $connection->getDisk();

            // Check if folder already exists
            if ($disk->directoryExists($newFolderPath)) {
                return back()->withErrors(['error' => 'Folder already exists.']);
            }

            // Create folder by putting an empty file, then deleting it
            // This is a workaround since some storage systems don't support empty directories
            $tempFile = $newFolderPath . '/.gitkeep';
            $disk->put($tempFile, '');

            return back()->with('success', 'Folder created successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create folder: ' . $e->getMessage()]);
        }
    }
}
