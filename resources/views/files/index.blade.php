<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ __('File Manager') }}</h1>
            <a href="{{ route('storage-connections.create') }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition duration-200">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Storage Connection
            </a>
        </div>
    </x-slot>

    {{-- Success Message --}}
    @if (session('success'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if($connections->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($connections as $connection)
                <div class="divide-y divide-gray-200 border border-gray-200 rounded-lg bg-white shadow-sm hover:shadow-md transition-shadow duration-300">
                    <div class="px-4 py-5 sm:px-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">
                                {{ $connection->name }}
                            </h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($connection->provider === 's3') bg-orange-100 text-orange-800
                                @elseif($connection->provider === 'gcs') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $connection->provider_name }}
                            </span>
                        </div>
                    </div>

                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center mb-4">
                            {{-- Status Indicator --}}
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full mr-2 
                                            {{ $connection->is_active ? 'bg-green-400' : 'bg-red-400' }}">
                                </div>
                                <span class="text-sm text-gray-600">
                                    {{ $connection->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex space-x-2">
                            {{-- Browse Files Button --}}
                            <a href="{{ route('files.browse', $connection) }}" 
                               class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-center py-2 px-3 rounded-md text-sm font-medium transition duration-200">
                                Browse Files
                            </a>

                            {{-- Settings Dropdown --}}
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open"
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 p-2 rounded-md transition duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z">
                                        </path>
                                    </svg>
                                </button>

                                {{-- Dropdown Menu --}}
                                <div x-show="open" @click.away="open = false"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="transform opacity-100 scale-100"
                                    x-transition:leave-end="transform opacity-0 scale-95"
                                    class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200"
                                    style="display: none;">
                                    <div class="py-1">
                                        <a href="{{ route('storage-connections.edit', $connection) }}"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Edit Connection
                                        </a>
                                        <button onclick="testConnection({{ $connection->id }})"
                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Test Connection
                                        </button>
                                        <form action="{{ route('storage-connections.destroy', $connection) }}"
                                            method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this connection?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                Delete Connection
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2V7z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No storage connections</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by creating your first storage connection to browse files.</p>
            <div class="mt-6">
                <a href="{{ route('storage-connections.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Storage Connection
                </a>
            </div>
        </div>
    @endif

    {{-- JavaScript for testing connections --}}
    <script>
        function testConnection(connectionId) {
            // Show loading state
            const button = event.target;
            const originalText = button.textContent;
            button.textContent = 'Testing...';
            button.disabled = true;

            // Make AJAX request to test connection
            fetch(`/storage-connections/${connectionId}/test`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                })
                .catch(error => {
                    alert('Error testing connection');
                })
                .finally(() => {
                    button.textContent = originalText;
                    button.disabled = false;
                });
        }
    </script>
</x-app-layout> 