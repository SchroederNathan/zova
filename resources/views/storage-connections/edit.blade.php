<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('files.index') }}" 
               class="mr-4 text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Storage Connection') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                {{-- Update Form --}}
                <form action="{{ route('storage-connections.update', $storageConnection) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Edit Connection</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Update your storage connection settings.
                        </p>
                    </div>

                    <div class="px-6 py-6 space-y-6">
                        {{-- Success Message --}}
                        @if (session('success'))
                            <div class="bg-green-50 border border-green-200 rounded-md p-4">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div class="ml-3">
                                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Error Messages --}}
                        @if ($errors->any())
                            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                                        <div class="mt-2 text-sm text-red-700">
                                            <ul class="list-disc pl-5 space-y-1">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Connection Info (Read-only) --}}
                        <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                            <h4 class="text-sm font-medium text-gray-800 mb-2">Connection Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-600">Provider:</span>
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($storageConnection->provider === 's3') bg-orange-100 text-orange-800
                                        @elseif($storageConnection->provider === 'gcs') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ $storageConnection->provider_name }}
                                    </span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Created:</span>
                                    <span class="ml-2 text-gray-800">{{ $storageConnection->created_at->format('M j, Y') }}</span>
                                </div>
                                @if($storageConnection->provider === 's3')
                                    <div>
                                        <span class="font-medium text-gray-600">Region:</span>
                                        <span class="ml-2 text-gray-800">{{ $storageConnection->s3_region ?? 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Bucket:</span>
                                        <span class="ml-2 text-gray-800">{{ $storageConnection->s3_bucket ?? 'N/A' }}</span>
                                    </div>
                                @elseif($storageConnection->provider === 'gcs')
                                    <div>
                                        <span class="font-medium text-gray-600">Project:</span>
                                        <span class="ml-2 text-gray-800">{{ $storageConnection->gcs_project_id ?? 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Bucket:</span>
                                        <span class="ml-2 text-gray-800">{{ $storageConnection->gcs_bucket ?? 'N/A' }}</span>
                                    </div>
                                @elseif($storageConnection->provider === 'nas')
                                    <div>
                                        <span class="font-medium text-gray-600">Type:</span>
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($storageConnection->nas_type === 'local') bg-blue-100 text-blue-800
                                            @else bg-green-100 text-green-800
                                            @endif">
                                            {{ $storageConnection->nas_type === 'local' ? 'Local Filesystem' : 'SMB/CIFS Share' }}
                                        </span>
                                    </div>
                                    @if($storageConnection->nas_type === 'local')
                                        <div class="md:col-span-2">
                                            <span class="font-medium text-gray-600">Path:</span>
                                            <span class="ml-2 text-gray-800 font-mono text-xs">{{ $storageConnection->nas_root_path ?? 'N/A' }}</span>
                                        </div>
                                    @else
                                        <div>
                                            <span class="font-medium text-gray-600">Host:</span>
                                            <span class="ml-2 text-gray-800">{{ $storageConnection->nas_host ?? 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-600">Share:</span>
                                            <span class="ml-2 text-gray-800">{{ $storageConnection->nas_share ?? 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-600">Username:</span>
                                            <span class="ml-2 text-gray-800">{{ $storageConnection->nas_username ?? 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-600">Mount Status:</span>
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($storageConnection->nas_is_mounted) bg-green-100 text-green-800
                                                @else bg-red-100 text-red-800
                                                @endif">
                                                {{ $storageConnection->nas_is_mounted ? 'Mounted' : 'Not Mounted' }}
                                            </span>
                                        </div>
                                        @if($storageConnection->nas_is_mounted && $storageConnection->nas_mount_point)
                                            <div class="md:col-span-2">
                                                <span class="font-medium text-gray-600">Mount Point:</span>
                                                <span class="ml-2 text-gray-800 font-mono text-xs">{{ $storageConnection->nas_mount_point }}</span>
                                            </div>
                                        @endif
                                    @endif
                                @endif
                            </div>
                        </div>

                        {{-- Editable Fields --}}
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                Connection Name
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   value="{{ old('name', $storageConnection->name) }}"
                                   placeholder="e.g., My S3 Bucket"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('name') border-red-300 @enderror">
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', $storageConnection->is_active) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">
                                    Active connection
                                </span>
                            </label>
                            <p class="mt-1 text-sm text-gray-500">
                                Inactive connections will not appear in the file manager.
                            </p>
                        </div>

                        {{-- Test Connection Button --}}
                        <div>
                            <button type="button" 
                                    onclick="testConnection()"
                                    class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Test Connection
                            </button>
                        </div>

                        {{-- NAS Mount Controls --}}
                        @if($storageConnection->provider === 'nas' && $storageConnection->nas_type === 'smb')
                            <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                                <h4 class="text-sm font-medium text-gray-800 mb-2">NAS Share Management</h4>
                                <p class="text-sm text-gray-600 mb-4">
                                    Control the mounting of your SMB/CIFS network share.
                                </p>
                                
                                <div class="flex space-x-3">
                                    @if($storageConnection->nas_is_mounted)
                                        <button type="button" 
                                                onclick="unmountNas()"
                                                class="bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Unmount Share
                                        </button>
                                    @else
                                        <button type="button" 
                                                onclick="mountNas()"
                                                class="bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3"></path>
                                            </svg>
                                            Mount Share
                                        </button>
                                    @endif
                                    
                                    <button type="button" 
                                            onclick="refreshMountStatus()"
                                            class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Refresh Status
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Update Form Actions --}}
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                        <a href="{{ route('files.index') }}" 
                           class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg transition duration-200">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                            Update Connection
                        </button>
                    </div>
                </form>

                {{-- Delete Form (Separate) --}}
                <div class="px-6 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Danger Zone</h4>
                            <p class="text-sm text-gray-500">Permanently delete this storage connection.</p>
                        </div>
                        <form action="{{ route('storage-connections.destroy', $storageConnection) }}" 
                              method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this connection? This action cannot be undone.')"
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                Delete Connection
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function testConnection() {
            const button = event.target;
            const originalText = button.innerHTML;
            
            // Show loading state
            button.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-700 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Testing...';
            button.disabled = true;

            // Make AJAX request to test connection
            fetch(`{{ route('storage-connections.test', $storageConnection) }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Connection test successful!');
                } else {
                    alert('❌ Connection test failed: ' + data.message);
                }
            })
            .catch(error => {
                alert('❌ Error testing connection: ' + error.message);
            })
            .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        function mountNas() {
            const button = event.target;
            const originalText = button.innerHTML;
            
            // Show loading state
            button.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Mounting...';
            button.disabled = true;

            // Make AJAX request to mount NAS
            fetch(`{{ route('storage-connections.mount-nas', $storageConnection) }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    // Reload page to update UI
                    window.location.reload();
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(error => {
                alert('❌ Error mounting NAS: ' + error.message);
            })
            .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        function unmountNas() {
            if (!confirm('Are you sure you want to unmount this NAS share?')) {
                return;
            }

            const button = event.target;
            const originalText = button.innerHTML;
            
            // Show loading state
            button.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Unmounting...';
            button.disabled = true;

            // Make AJAX request to unmount NAS
            fetch(`{{ route('storage-connections.unmount-nas', $storageConnection) }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    // Reload page to update UI
                    window.location.reload();
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(error => {
                alert('❌ Error unmounting NAS: ' + error.message);
            })
            .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        function refreshMountStatus() {
            // Simply reload the page to get fresh status
            window.location.reload();
        }
    </script>
</x-app-layout>
