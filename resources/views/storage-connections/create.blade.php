<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('storage-connections.index') }}" 
               class="mr-4 text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Add Storage Connection') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                <form action="{{ route('storage-connections.store') }}" method="POST" enctype="multipart/form-data" id="connection-form">
                    @csrf
                    
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Connection Details</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Configure your cloud storage connection settings.
                        </p>
                    </div>

                    <div class="px-6 py-6 space-y-6">
                        {{-- Connection Name --}}
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                Connection Name
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   value="{{ old('name') }}"
                                   placeholder="e.g., My S3 Bucket"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('name') border-red-300 @enderror">
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Provider Selection --}}
                        <div>
                            <label for="provider" class="block text-sm font-medium text-gray-700">
                                Storage Provider
                            </label>
                            <select name="provider" 
                                    id="provider" 
                                    onchange="showProviderConfig()"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('provider') border-red-300 @enderror">
                                <option value="">Select a provider</option>
                                <option value="s3" {{ old('provider') === 's3' ? 'selected' : '' }}>Amazon S3</option>
                                <option value="gcs" {{ old('provider') === 'gcs' ? 'selected' : '' }}>Google Cloud Storage</option>
                                <option value="nas" {{ old('provider') === 'nas' ? 'selected' : '' }}>Network Attached Storage</option>
                            </select>
                            @error('provider')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- S3 Configuration --}}
                        <div id="s3-config" class="space-y-4 hidden">
                            <div class="bg-orange-50 border border-orange-200 rounded-md p-4">
                                <h4 class="text-sm font-medium text-orange-800 mb-2">Amazon S3 Configuration</h4>
                                <p class="text-sm text-orange-700">
                                    You can find these credentials in your AWS IAM console.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="aws_access_key" class="block text-sm font-medium text-gray-700">
                                        Access Key ID
                                    </label>
                                    <input type="text" 
                                           name="aws_access_key" 
                                           id="aws_access_key" 
                                           value="{{ old('aws_access_key') }}"
                                           placeholder="AKIAIOSFODNN7EXAMPLE"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('aws_access_key') border-red-300 @enderror">
                                    @error('aws_access_key')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="aws_secret_key" class="block text-sm font-medium text-gray-700">
                                        Secret Access Key
                                    </label>
                                    <input type="password" 
                                           name="aws_secret_key" 
                                           id="aws_secret_key" 
                                           value="{{ old('aws_secret_key') }}"
                                           placeholder="wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('aws_secret_key') border-red-300 @enderror">
                                    @error('aws_secret_key')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="aws_region" class="block text-sm font-medium text-gray-700">
                                        Region
                                    </label>
                                    <input type="text" 
                                           name="aws_region" 
                                           id="aws_region"
                                           value="{{ old('aws_region') }}"
                                           placeholder="us-east-1, us-east-2, eu-west-1, etc."
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('aws_region') border-red-300 @enderror">
                                    <p class="mt-1 text-sm text-gray-500">
                                        Enter the AWS region where your bucket is located (e.g., us-east-1, us-east-2, eu-west-1)
                                    </p>
                                    @error('aws_region')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="aws_bucket" class="block text-sm font-medium text-gray-700">
                                        Bucket Name
                                    </label>
                                    <input type="text" 
                                           name="aws_bucket" 
                                           id="aws_bucket" 
                                           value="{{ old('aws_bucket') }}"
                                           placeholder="my-bucket-name"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('aws_bucket') border-red-300 @enderror">
                                    @error('aws_bucket')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="aws_endpoint" class="block text-sm font-medium text-gray-700">
                                    Custom Endpoint (Optional)
                                </label>
                                <input type="url" 
                                       name="aws_endpoint" 
                                       id="aws_endpoint" 
                                       value="{{ old('aws_endpoint') }}"
                                       placeholder="https://s3.example.com"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('aws_endpoint') border-red-300 @enderror">
                                <p class="mt-1 text-sm text-gray-500">
                                    For S3-compatible services like DigitalOcean Spaces or MinIO
                                </p>
                                @error('aws_endpoint')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Google Cloud Storage Configuration --}}
                        <div id="gcs-config" class="space-y-4 hidden">
                            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                                <h4 class="text-sm font-medium text-blue-800 mb-2">Google Cloud Storage Configuration</h4>
                                <p class="text-sm text-blue-700">
                                    Download a service account key file from the Google Cloud Console.
                                </p>
                            </div>

                            <div>
                                <label for="gcs_project_id" class="block text-sm font-medium text-gray-700">
                                    Project ID
                                </label>
                                <input type="text" 
                                       name="gcs_project_id" 
                                       id="gcs_project_id" 
                                       value="{{ old('gcs_project_id') }}"
                                       placeholder="my-project-123456"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('gcs_project_id') border-red-300 @enderror">
                                @error('gcs_project_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="gcs_key_file" class="block text-sm font-medium text-gray-700">
                                    Service Account Key File
                                </label>
                                <input type="file" 
                                       name="gcs_key_file" 
                                       id="gcs_key_file" 
                                       accept=".json"
                                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('gcs_key_file') border-red-300 @enderror">
                                @error('gcs_key_file')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="gcs_bucket" class="block text-sm font-medium text-gray-700">
                                    Bucket Name
                                </label>
                                <input type="text" 
                                       name="gcs_bucket" 
                                       id="gcs_bucket" 
                                       value="{{ old('gcs_bucket') }}"
                                       placeholder="my-gcs-bucket"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('gcs_bucket') border-red-300 @enderror">
                                @error('gcs_bucket')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- NAS Configuration --}}
                        <div id="nas-config" class="space-y-4 hidden">
                            <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                                <h4 class="text-sm font-medium text-gray-800 mb-2">Network Attached Storage Configuration</h4>
                                <p class="text-sm text-gray-700">
                                    Specify the local or network path to your storage location.
                                </p>
                            </div>

                            <div>
                                <label for="nas_root_path" class="block text-sm font-medium text-gray-700">
                                    Root Path
                                </label>
                                <input type="text" 
                                       name="nas_root_path" 
                                       id="nas_root_path" 
                                       value="{{ old('nas_root_path') }}"
                                       placeholder="/mnt/nas or \\server\share"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('nas_root_path') border-red-300 @enderror">
                                <p class="mt-1 text-sm text-gray-500">
                                    Full path to the directory you want to manage
                                </p>
                                @error('nas_root_path')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

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

                        {{-- Debug Information (remove in production) --}}
                        @if(config('app.debug'))
                            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                                <h4 class="text-sm font-medium text-blue-800">Debug Info</h4>
                                <p class="text-sm text-blue-700">
                                    Form action: {{ route('storage-connections.store') }}<br>
                                    CSRF Token: {{ csrf_token() }}<br>
                                    User ID: {{ auth()->id() }}
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Form Actions --}}
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                        <a href="{{ route('storage-connections.index') }}" 
                           class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg transition duration-200">
                            Cancel
                        </a>
                        <button type="button" 
                                onclick="testFormData()"
                                class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                            Test Form
                        </button>
                        <button type="button" 
                                onclick="testSubmit()"
                                class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                            Test Submit
                        </button>
                        <button type="submit" 
                                id="submit-btn"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span id="submit-text">Create Connection</span>
                            <span id="submit-loading" class="hidden">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Creating...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Show/hide configuration sections based on provider selection
        function showProviderConfig() {
            const provider = document.getElementById('provider').value;
            
            // Hide all config sections
            document.getElementById('s3-config').classList.add('hidden');
            document.getElementById('gcs-config').classList.add('hidden');
            document.getElementById('nas-config').classList.add('hidden');
            
            // Show selected provider config
            if (provider) {
                document.getElementById(provider + '-config').classList.remove('hidden');
            }
        }

        // Test form data function
        function testFormData() {
            const form = document.getElementById('connection-form');
            const formData = new FormData(form);
            
            console.log('=== FORM TEST ===');
            console.log('Form action:', form.action);
            console.log('Form method:', form.method);
            
            let hasData = false;
            for (let [key, value] of formData.entries()) {
                console.log(key + ': ' + (key === 'aws_secret_key' ? '[HIDDEN]' : value));
                hasData = true;
            }
            
            if (!hasData) {
                console.log('No form data found!');
            }
            
            // Check required fields for S3
            const provider = formData.get('provider');
            if (provider === 's3') {
                const required = ['name', 'aws_access_key', 'aws_secret_key', 'aws_region', 'aws_bucket'];
                const missing = required.filter(field => !formData.get(field));
                if (missing.length > 0) {
                    console.log('Missing required S3 fields:', missing);
                } else {
                    console.log('All required S3 fields present');
                }
                
                // Specifically check secret key
                const secretKey = formData.get('aws_secret_key');
                console.log('Secret key present:', !!secretKey);
                console.log('Secret key length:', secretKey ? secretKey.length : 0);
            }
            
            alert('Check browser console for form data details');
        }

        // Test form submission function
        function testSubmit() {
            const form = document.getElementById('connection-form');
            const formData = new FormData(form);
            
            console.log('=== TEST SUBMIT ===');
            
            fetch('/test-form', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Test submit response:', data);
                alert('Test submit successful! Check console and logs.');
            })
            .catch(error => {
                console.error('Test submit error:', error);
                alert('Test submit failed! Check console.');
            });
        }

        // Handle form submission
        document.getElementById('connection-form').addEventListener('submit', function(e) {
            console.log('Form submission started');
            
            const submitBtn = document.getElementById('submit-btn');
            const submitText = document.getElementById('submit-text');
            const submitLoading = document.getElementById('submit-loading');
            
            // Show loading state
            submitBtn.disabled = true;
            submitText.classList.add('hidden');
            submitLoading.classList.remove('hidden');
            
            // Log form data for debugging
            const formData = new FormData(this);
            console.log('Form data:');
            for (let [key, value] of formData.entries()) {
                if (key !== 'aws_secret_key' && key !== 'gcs_key_file') {
                    console.log(key + ': ' + value);
                }
            }
            
            // Don't prevent default - let the form submit normally
            // The loading state will be reset if there are validation errors
        });

        // Reset loading state if there are errors (page reload)
        document.addEventListener('DOMContentLoaded', function() {
            const submitBtn = document.getElementById('submit-btn');
            const submitText = document.getElementById('submit-text');
            const submitLoading = document.getElementById('submit-loading');
            
            if (submitBtn) {
                submitBtn.disabled = false;
                submitText.classList.remove('hidden');
                submitLoading.classList.add('hidden');
            }
        });

        // Initialize provider config on page load
        document.addEventListener('DOMContentLoaded', function() {
            showProviderConfig();
        });
    </script>
</x-app-layout> 