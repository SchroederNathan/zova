<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('files.index') }}" 
                   class="mr-4 text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ $connection->name }}
                    </h2>
                    <p class="text-sm text-gray-600">{{ $connection->provider_name }}</p>
                </div>
            </div>
            
            <div class="flex space-x-2">
                <button onclick="refreshFiles()" 
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition duration-200">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>
    </x-slot>

    {{-- Main container with Alpine.js state for file preview --}}
    <div class="py-6" 
         x-data="{
             previewOpen: false,
             previewFile: null,
             previewLoading: false,
             previewContent: '',
             previewError: null,
             
             openPreview(file) {
                 this.previewFile = file;
                 this.previewOpen = true;
                 this.previewLoading = true;
                 this.previewError = null;
                 this.loadPreview(file);
             },
             
             closePreview() {
                 this.previewOpen = false;
                 this.previewFile = null;
                 this.previewContent = '';
                 this.previewError = null;
             },
             
             async loadPreview(file) {
                 try {
                     // For images, we don't need to load content via AJAX
                     // The image will be loaded directly via the img src attribute
                     const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'].includes((file.extension || '').toLowerCase());
                     
                     if (isImage) {
                         // For images, just mark as loaded - no content fetching needed
                         this.previewLoading = false;
                         return;
                     }
                     
                     // For text files, fetch content via AJAX
                     const isTextFile = ['txt', 'md', 'css', 'js', 'php', 'py', 'html', 'json', 'xml', 'yaml', 'yml'].includes((file.extension || '').toLowerCase());
                     
                     if (isTextFile) {
                         const response = await fetch(`/files/{{ $connection->id }}/preview/${encodeURIComponent(file.path)}`, {
                             headers: {
                                 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                                 'Accept': 'application/json'
                             }
                         });
                         
                         if (!response.ok) {
                             throw new Error('Failed to load preview');
                         }
                         
                         const data = await response.json();
                         this.previewContent = data.content || '';
                         this.previewLoading = false;
                     } else {
                         // For other file types, just mark as loaded
                         this.previewLoading = false;
                     }
                 } catch (error) {
                     this.previewError = error.message;
                     this.previewLoading = false;
                 }
             }
         }">
        
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Error Messages --}}
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Connection Error</h3>
                            <p class="mt-1 text-sm text-red-700">{{ $errors->first('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Breadcrumbs --}}
            @if(count($breadcrumbs) > 0)
                <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 px-4 py-3">
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol role="list" class="flex items-center space-x-4">
                            <li>
                                <div>
                                    <a href="{{ route('files.browse', $connection) }}" 
                                       class="text-gray-400 hover:text-gray-500">
                                        <svg class="size-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                            <path fill-rule="evenodd" d="M9.293 2.293a1 1 0 0 1 1.414 0l7 7A1 1 0 0 1 17 11h-1v6a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6H3a1 1 0 0 1-.707-1.707l7-7Z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="sr-only">Home</span>
                                    </a>
                                </div>
                            </li>
                            
                            @foreach($breadcrumbs as $breadcrumb)
                                <li>
                                    <div class="flex items-center">
                                        <svg class="size-5 shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                            <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                                        </svg>
                                        <a href="{{ route('files.browse', ['connection' => $connection, 'path' => $breadcrumb['path']]) }}" 
                                           class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">{{ $breadcrumb['name'] }}</a>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                </div>
            @endif

            {{-- Main Content Area with Flex Layout --}}
            <div class="flex relative">
                {{-- File List Container --}}
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden transition-all duration-300"
                     :class="previewOpen ? 'w-2/3 mr-4' : 'w-full'">
                    @if(count($items) > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Size
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Modified
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($items as $item)
                                    <tr class="hover:bg-gray-50 cursor-pointer"
                                        @if($item['type'] === 'file')
                                            @click="openPreview({
                                                name: '{{ $item['name'] }}',
                                                path: '{{ $item['path'] }}',
                                                type: '{{ $item['type'] }}',
                                                size: '{{ $item['human_size'] }}',
                                                extension: '{{ $item['extension'] ?? '' }}',
                                                last_modified: '{{ $item['last_modified'] ? \Carbon\Carbon::parse($item['last_modified'])->format('M j, Y g:i A') : '' }}',
                                                icon: '{{ $item['icon'] }}'
                                            })"
                                        @endif>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                {{-- File/Folder Icon --}}
                                                @if($item['type'] === 'folder')
                                                    <svg class="w-8 h-8 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-8 h-8 text-gray-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @endif
                                                
                                                {{-- File/Folder Name --}}
                                                @if($item['type'] === 'folder')
                                                    <a href="{{ route('files.browse', ['connection' => $connection, 'path' => $item['path']]) }}" 
                                                       class="text-blue-600 hover:text-blue-800 font-medium"
                                                       @click.stop>
                                                        {{ $item['name'] }}
                                                    </a>
                                                @else
                                                    <span class="text-gray-900 font-medium">{{ $item['name'] }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $item['type'] === 'folder' ? '-' : $item['human_size'] }}
                                        </td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $item['last_modified'] ? \Carbon\Carbon::parse($item['last_modified'])->format('M j, Y g:i A') : '-' }}
                                        </td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                @if($item['type'] === 'file')
                                                    <a href="{{ route('files.download', ['connection' => $connection, 'path' => $item['path']]) }}" 
                                                       class="text-blue-600 hover:text-blue-900"
                                                       @click.stop>
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                    </a>
                                                @endif
                                                
                                                <button onclick="deleteItem('{{ $item['path'] }}', '{{ $item['type'] }}')" 
                                                        class="text-red-600 hover:text-red-900"
                                                        @click.stop>
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2V7z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No files found</h3>
                            <p class="mt-1 text-sm text-gray-500">This folder appears to be empty.</p>
                        </div>
                    @endif
                </div>

                {{-- File Preview Panel --}}
                <div x-show="previewOpen"
                     x-transition:enter="transition-all ease-out duration-300"
                     x-transition:enter-start="max-w-0 opacity-0"
                     x-transition:enter-end="max-w-md opacity-100"
                     x-transition:leave="transition-all ease-in duration-300"
                     x-transition:leave-start="max-w-md opacity-100"
                     x-transition:leave-end="max-w-0 opacity-0"
                     class="w-1/3 bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden"
                     style="display: none;">
                    
                    {{-- Panel Content Container with overflow hidden during animation --}}
                    <div class="h-full" x-show="previewOpen" 
                         x-transition:enter="transition-opacity ease-out duration-150 delay-150"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition-opacity ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0">
                        
                        {{-- Preview Header --}}
                        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                    </svg>
                                    <h3 class="text-sm font-medium text-gray-900" x-text="previewFile && previewFile.name"></h3>
                                </div>
                                <button @click="closePreview()" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Preview Content --}}
                        <div class="flex-1 overflow-y-auto">
                            {{-- Loading State --}}
                            <div x-show="previewLoading" class="flex items-center justify-center py-12">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                            </div>

                            {{-- Error State --}}
                            <div x-show="previewError" class="p-4">
                                <div class="text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Preview not available</h3>
                                    <p class="mt-1 text-sm text-gray-500" x-text="previewError"></p>
                                </div>
                            </div>

                            {{-- Preview Content Area --}}
                            <div x-show="!previewLoading && !previewError" class="p-4">
                                {{-- Image Preview --}}
                                <template x-if="previewFile && ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes((previewFile.extension || '').toLowerCase())">
                                    <div class="text-center">
                                        <img :src="`/files/{{ $connection->id }}/preview/${encodeURIComponent(previewFile.path || '')}`" 
                                             :alt="previewFile.name"
                                             class="max-w-full h-auto rounded-lg shadow-sm border border-gray-200">
                                    </div>
                                </template>

                                {{-- Text Preview --}}
                                <template x-if="previewFile && ['txt', 'md', 'css', 'js', 'php', 'py', 'html'].includes((previewFile.extension || '').toLowerCase())">
                                    <pre class="bg-gray-100 rounded-lg p-4 text-sm overflow-x-auto whitespace-pre-wrap" x-text="previewContent"></pre>
                                </template>

                                {{-- PDF or other non-previewable files --}}
                                <template x-if="previewFile && !['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'txt', 'md', 'css', 'js', 'php', 'py', 'html'].includes((previewFile.extension || '').toLowerCase())">
                                    <div class="text-center py-8">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">Preview not available</h3>
                                        <p class="mt-1 text-sm text-gray-500">This file type cannot be previewed</p>
                                        <div class="mt-4">
                                            <a :href="`/files/{{ $connection->id }}/download/${encodeURIComponent(previewFile.path || '')}`"
                                               class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                                Download File
                                            </a>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- File Metadata --}}
                            <div x-show="previewFile && !previewLoading" class="border-t border-gray-200 bg-gray-50 p-4">
                                <h4 class="text-sm font-medium text-gray-900 mb-3">File Details</h4>
                                <dl class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <dt class="text-gray-500">Name:</dt>
                                        <dd class="text-gray-900 font-medium" x-text="previewFile && previewFile.name"></dd>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <dt class="text-gray-500">Size:</dt>
                                        <dd class="text-gray-900" x-text="previewFile && previewFile.size"></dd>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <dt class="text-gray-500">Modified:</dt>
                                        <dd class="text-gray-900" x-text="previewFile && previewFile.last_modified"></dd>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <dt class="text-gray-500">Type:</dt>
                                        <dd class="text-gray-900" x-text="previewFile && previewFile.extension ? previewFile.extension.toUpperCase() : 'Unknown'"></dd>
                                    </div>
                                </dl>
                                
                                {{-- Action Buttons --}}
                                <div class="mt-4 flex space-x-2">
                                    <a :href="`/files/{{ $connection->id }}/download/${encodeURIComponent(previewFile.path || '')}`"
                                       class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-center py-2 px-3 rounded-md text-sm font-medium transition duration-200">
                                        Download
                                    </a>
                                    <button @click="deleteItem(previewFile.path, previewFile.type)"
                                            class="bg-red-600 hover:bg-red-700 text-white py-2 px-3 rounded-md text-sm font-medium transition duration-200">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function refreshFiles() {
            window.location.reload();
        }

        function deleteItem(path, type) {
            if (confirm(`Are you sure you want to delete this ${type}?`)) {
                // TODO: Implement delete functionality
                alert('Delete functionality will be implemented next!');
            }
        }
    </script>
</x-app-layout> 