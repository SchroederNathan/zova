<?php

namespace App\Http\Controllers;

use App\Models\StorageConnection;
use Illuminate\Http\Request;

class StorageConnectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get all storage connections for the authenticated user
        // with() eager loads the relationship to avoid N+1 queries
        $connections = auth()->user()->storageConnections()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('storage-connections.index', compact('connections'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('storage-connections.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Debug: Log the incoming request
        \Log::info('Storage connection creation attempt', [
            'user_id' => auth()->id(),
            'request_data' => $request->except(['aws_secret_key', 'gcs_key_file', 'nas_password']), // Don't log sensitive data
            'has_aws_secret_key' => $request->has('aws_secret_key'),
            'aws_secret_key_length' => $request->has('aws_secret_key') ? strlen($request->get('aws_secret_key')) : 0,
        ]);

        try {
            // Validate the incoming request data
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'provider' => 'required|in:s3,gcs,nas',
                // S3 Configuration
                'aws_access_key' => 'required_if:provider,s3|nullable|string',
                'aws_secret_key' => 'required_if:provider,s3|nullable|string',
                'aws_region' => 'required_if:provider,s3|nullable|string',
                'aws_bucket' => 'required_if:provider,s3|nullable|string',
                'aws_endpoint' => 'nullable|url',
                // GCS Configuration
                'gcs_project_id' => 'required_if:provider,gcs|nullable|string',
                'gcs_key_file' => 'required_if:provider,gcs|nullable|file|mimes:json',
                'gcs_bucket' => 'required_if:provider,gcs|nullable|string',
                // NAS Configuration
                'nas_type' => 'required_if:provider,nas|nullable|in:local,smb',
                'nas_root_path' => 'required_if:nas_type,local|nullable|string',
                'nas_host' => 'required_if:nas_type,smb|nullable|string',
                'nas_share' => 'required_if:nas_type,smb|nullable|string',
                'nas_username' => 'required_if:nas_type,smb|nullable|string',
                'nas_password' => 'required_if:nas_type,smb|nullable|string',
                'nas_domain' => 'nullable|string',
            ]);

            \Log::info('Validation passed', ['provider' => $validated['provider']]);

            // Prepare data for storage connection creation
            $connectionData = [
                'name' => $validated['name'],
                'provider' => $validated['provider'],
                'is_active' => true,
            ];

            // Add provider-specific fields based on the provider
            switch ($validated['provider']) {
                case 's3':
                    $connectionData['s3_access_key'] = $validated['aws_access_key'];
                    $connectionData['s3_secret_key'] = $validated['aws_secret_key'];
                    $connectionData['s3_region'] = $validated['aws_region'];
                    $connectionData['s3_bucket'] = $validated['aws_bucket'];
                    $connectionData['s3_endpoint'] = $validated['aws_endpoint'] ?? null;
                    break;
                    
                case 'gcs':
                    $connectionData['gcs_project_id'] = $validated['gcs_project_id'];
                    $connectionData['gcs_key_file'] = $this->handleGcsKeyFile($validated['gcs_key_file']);
                    $connectionData['gcs_bucket'] = $validated['gcs_bucket'];
                    break;
                    
                case 'nas':
                    $connectionData['nas_type'] = $validated['nas_type'];
                    
                    if ($validated['nas_type'] === 'local') {
                        $connectionData['nas_root_path'] = $validated['nas_root_path'];
                    } else {
                        // SMB/CIFS configuration
                        $connectionData['nas_host'] = $validated['nas_host'];
                        $connectionData['nas_share'] = $validated['nas_share'];
                        $connectionData['nas_username'] = $validated['nas_username'];
                        $connectionData['nas_password'] = $validated['nas_password'];
                        $connectionData['nas_domain'] = $validated['nas_domain'] ?? null;
                        
                        // Generate mount point path
                        $userId = auth()->id();
                        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $validated['name']);
                        $mountPoint = storage_path("app/nas_mounts/user_{$userId}/{$safeName}");
                        $connectionData['nas_mount_point'] = $mountPoint;
                        $connectionData['nas_is_mounted'] = false;
                    }
                    break;
            }

            \Log::info('Connection data prepared', ['provider' => $validated['provider']]);

            // Create the storage connection
            $connection = auth()->user()->storageConnections()->create($connectionData);

            \Log::info('Connection created', ['connection_id' => $connection->id]);

            // Test the connection
            \Log::info('Testing connection...');
            $testResult = $connection->testConnection();
            \Log::info('Connection test result', ['success' => $testResult]);

            if (!$testResult) {
                // If connection fails, delete it and return with error
                $connection->delete();
                \Log::warning('Connection test failed, connection deleted');
                return back()->withErrors(['connection' => 'Failed to connect to storage. Please check your credentials.'])->withInput();
            }

            \Log::info('Connection created successfully');
            return redirect()->route('storage-connections.index')
                ->with('success', 'Storage connection created successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Validation failed', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Storage connection creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'An unexpected error occurred: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StorageConnection $storageConnection)
    {
        // Ensure user owns this connection
        $this->authorize('view', $storageConnection);
        
        return view('storage-connections.show', compact('storageConnection'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StorageConnection $storageConnection)
    {
        $this->authorize('update', $storageConnection);
        
        return view('storage-connections.edit', compact('storageConnection'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StorageConnection $storageConnection)
    {
        $this->authorize('update', $storageConnection);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $storageConnection->update($validated);

        return redirect()->route('storage-connections.index')
            ->with('success', 'Storage connection updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StorageConnection $storageConnection)
    {
        $this->authorize('delete', $storageConnection);
        
        $storageConnection->delete();

        return redirect()->route('storage-connections.index')
            ->with('success', 'Storage connection deleted successfully!');
    }

    /**
     * Test a storage connection
     */
    public function test(StorageConnection $storageConnection)
    {
        $this->authorize('view', $storageConnection);
        
        $isWorking = $storageConnection->testConnection();
        
        return response()->json([
            'success' => $isWorking,
            'message' => $isWorking ? 'Connection successful!' : 'Connection failed!'
        ]);
    }

    /**
     * Mount NAS share
     */
    public function mountNas(StorageConnection $storageConnection)
    {
        $this->authorize('update', $storageConnection);
        
        if ($storageConnection->provider !== 'nas' || $storageConnection->nas_type !== 'smb') {
            return response()->json([
                'success' => false,
                'message' => 'This connection is not a mountable NAS share.'
            ]);
        }
        
        $success = $storageConnection->mountNasShare();
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'NAS share mounted successfully!' : 'Failed to mount NAS share.',
            'is_mounted' => $storageConnection->fresh()->nas_is_mounted
        ]);
    }

    /**
     * Unmount NAS share
     */
    public function unmountNas(StorageConnection $storageConnection)
    {
        $this->authorize('update', $storageConnection);
        
        if ($storageConnection->provider !== 'nas' || $storageConnection->nas_type !== 'smb') {
            return response()->json([
                'success' => false,
                'message' => 'This connection is not a mountable NAS share.'
            ]);
        }
        
        $success = $storageConnection->unmountNasShare();
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'NAS share unmounted successfully!' : 'Failed to unmount NAS share.',
            'is_mounted' => $storageConnection->fresh()->nas_is_mounted
        ]);
    }

    /**
     * Handle GCS key file upload
     */
    private function handleGcsKeyFile($file): string
    {
        // Store the JSON key file securely
        $path = $file->store('gcs-keys', 'local');
        // The 'local' disk stores files in storage/app/private by default
        return storage_path('app/private/' . $path);
    }
}
