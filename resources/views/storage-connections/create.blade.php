<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('files.index') }}" class="mr-4 text-gray-500 hover:text-gray-700">
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
            <div
                class="divide-y divide-gray-200 overflow-hidden rounded-lg bg-white shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-300">
                <form action="{{ route('storage-connections.store') }}" method="POST" enctype="multipart/form-data"
                    id="connection-form">
                    @csrf

                    {{-- Card Header --}}
                    <div class="px-4 py-5 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Connection Details</h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    Configure your cloud storage connection settings.
                                </p>
                            </div>
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="w-8 h-8 text-indigo-500">
                                    <rect width="20" height="8" x="2" y="2" rx="2" ry="2" />
                                    <rect width="20" height="8" x="2" y="14" rx="2" ry="2" />
                                    <line x1="6" x2="6.01" y1="6" y2="6" />
                                    <line x1="6" x2="6.01" y1="18" y2="18" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="px-4 py-5 sm:p-6">
                        <div class="space-y-6">
                            {{-- Connection Name --}}
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">
                                    Connection Name
                                </label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}"
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
                                <select name="provider" id="provider" onchange="showProviderConfig()"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('provider') border-red-300 @enderror">
                                    <option value="">Select a provider</option>
                                    <option value="s3" {{ old('provider') === 's3' ? 'selected' : '' }}>Amazon S3</option>
                                    <option value="gcs" {{ old('provider') === 'gcs' ? 'selected' : '' }}>Google Cloud
                                        Storage</option>
                                    <option value="nas" {{ old('provider') === 'nas' ? 'selected' : '' }}>Network Attached
                                        Storage</option>
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
                                        <input type="text" name="aws_access_key" id="aws_access_key"
                                            value="{{ old('aws_access_key') }}" placeholder="AKIAIOSFODNN7EXAMPLE"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('aws_access_key') border-red-300 @enderror">
                                        @error('aws_access_key')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="aws_secret_key" class="block text-sm font-medium text-gray-700">
                                            Secret Access Key
                                        </label>
                                        <input type="password" name="aws_secret_key" id="aws_secret_key"
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
                                        <input type="text" name="aws_region" id="aws_region"
                                            value="{{ old('aws_region') }}" placeholder="us-east-1, eu-west-1, etc."
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('aws_region') border-red-300 @enderror">

                                        @error('aws_region')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="aws_bucket" class="block text-sm font-medium text-gray-700">
                                            Bucket Name
                                        </label>
                                        <input type="text" name="aws_bucket" id="aws_bucket"
                                            value="{{ old('aws_bucket') }}" placeholder="my-bucket-name"
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
                                    <input type="url" name="aws_endpoint" id="aws_endpoint"
                                        value="{{ old('aws_endpoint') }}" placeholder="https://s3.example.com"
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
                                    <h4 class="text-sm font-medium text-blue-800 mb-2">Google Cloud Storage
                                        Configuration</h4>
                                    <p class="text-sm text-blue-700">
                                        Download a service account key file from the Google Cloud Console.
                                    </p>
                                </div>

                                <div>
                                    <label for="gcs_project_id" class="block text-sm font-medium text-gray-700">
                                        Project ID
                                    </label>
                                    <input type="text" name="gcs_project_id" id="gcs_project_id"
                                        value="{{ old('gcs_project_id') }}" placeholder="my-project-123456"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('gcs_project_id') border-red-300 @enderror">
                                    @error('gcs_project_id')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="gcs_key_file" class="block text-sm font-medium text-gray-700">
                                        Service Account Key File
                                    </label>
                                    <input type="file" name="gcs_key_file" id="gcs_key_file" accept=".json"
                                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('gcs_key_file') border-red-300 @enderror">
                                    @error('gcs_key_file')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="gcs_bucket" class="block text-sm font-medium text-gray-700">
                                        Bucket Name
                                    </label>
                                    <input type="text" name="gcs_bucket" id="gcs_bucket" value="{{ old('gcs_bucket') }}"
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
                                    <h4 class="text-sm font-medium text-gray-800 mb-2">Network Attached Storage
                                        Configuration</h4>
                                    <p class="text-sm text-gray-700">
                                        Connect to your NAS device either via local filesystem or SMB/CIFS network
                                        share.
                                    </p>
                                </div>

                                <div>
                                    <label for="nas_type" class="block text-sm font-medium text-gray-700">
                                        Connection Type
                                    </label>
                                    <select name="nas_type" id="nas_type" onchange="showNasTypeConfig()"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('nas_type') border-red-300 @enderror">
                                        <option value="">Select connection type</option>
                                        <option value="local" {{ old('nas_type') === 'local' ? 'selected' : '' }}>Local
                                            Filesystem</option>
                                        <option value="smb" {{ old('nas_type') === 'smb' ? 'selected' : '' }}>SMB/CIFS
                                            Network Share</option>
                                    </select>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Choose how to connect to your NAS storage
                                    </p>
                                    @error('nas_type')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Local NAS Configuration --}}
                                <div id="nas-local-config" class="space-y-4 hidden">
                                    <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
                                        <p class="text-sm text-blue-800">
                                            <strong>Local Filesystem:</strong> Use this if your NAS is already mounted
                                            to a local directory on this server.
                                        </p>
                                    </div>

                                    <div>
                                        <label for="nas_root_path" class="block text-sm font-medium text-gray-700">
                                            Root Path
                                        </label>
                                        <input type="text" name="nas_root_path" id="nas_root_path"
                                            value="{{ old('nas_root_path') }}"
                                            placeholder="/mnt/nas or /path/to/mounted/share"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('nas_root_path') border-red-300 @enderror">
                                        <p class="mt-1 text-sm text-gray-500">
                                            Full path to the directory you want to manage
                                        </p>
                                        @error('nas_root_path')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- SMB/CIFS NAS Configuration --}}
                                <div id="nas-smb-config" class="space-y-4 hidden">
                                    <div class="bg-green-50 border border-green-200 rounded-md p-3">
                                        <p class="text-sm text-green-800">
                                            <strong>SMB/CIFS Network Share:</strong> Connect directly to your NAS over
                                            the network. This will automatically mount the share for you.
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="nas_host" class="block text-sm font-medium text-gray-700">
                                                NAS Host/IP Address
                                            </label>
                                            <input type="text" name="nas_host" id="nas_host"
                                                value="{{ old('nas_host') }}" placeholder="192.168.1.32 or nas.local"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('nas_host') border-red-300 @enderror">
                                            @error('nas_host')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="nas_share" class="block text-sm font-medium text-gray-700">
                                                Share Name
                                            </label>
                                            <input type="text" name="nas_share" id="nas_share"
                                                value="{{ old('nas_share') }}" placeholder="home"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('nas_share') border-red-300 @enderror">
                                            @error('nas_share')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="nas_username" class="block text-sm font-medium text-gray-700">
                                                Username
                                            </label>
                                            <input type="text" name="nas_username" id="nas_username"
                                                value="{{ old('nas_username') }}" placeholder="your-username"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('nas_username') border-red-300 @enderror">
                                            @error('nas_username')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="nas_password" class="block text-sm font-medium text-gray-700">
                                                Password
                                            </label>
                                            <input type="password" name="nas_password" id="nas_password"
                                                value="{{ old('nas_password') }}" placeholder="your-password"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('nas_password') border-red-300 @enderror">
                                            @error('nas_password')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div>
                                        <label for="nas_domain" class="block text-sm font-medium text-gray-700">
                                            Domain (Optional)
                                        </label>
                                        <input type="text" name="nas_domain" id="nas_domain"
                                            value="{{ old('nas_domain') }}" placeholder="WORKGROUP or your-domain"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('nas_domain') border-red-300 @enderror">
                                        <p class="mt-1 text-sm text-gray-500">
                                            Windows domain or workgroup (leave empty if not applicable)
                                        </p>
                                        @error('nas_domain')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                                        <div class="flex">
                                            <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 9v2m0 4h.01m-6.938 4h13.876c1.318 0 2.387-1.069 2.387-2.387L21 12c0-1.318-1.069-2.387-2.387-2.387H5.387C4.069 9.613 3 10.682 3 12l.325 4.613c0 1.318 1.069 2.387 2.387 2.387z">
                                                </path>
                                            </svg>
                                            <div class="ml-3">
                                                <p class="text-sm text-yellow-800">
                                                    <strong>Note:</strong> For SMB connections, the system will
                                                    automatically mount your share to a secure location and manage it
                                                    for you. Make sure the Laravel server has the necessary permissions
                                                    to mount network shares.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Error Messages --}}
                            @if ($errors->any())
                                <div class="bg-red-50 border border-red-200 rounded-md p-4">
                                    <div class="flex">
                                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-red-800">There were errors with your
                                                submission</h3>
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


                        </div>
                    </div>

                    {{-- Card Footer --}}
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-end space-x-3">
                            <button type="submit" id="submit-btn"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span id="submit-text" class="flex items-center">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Create Connection
                                </span>
                                <span id="submit-loading" class="hidden flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Creating...
                                </span>
                            </button>
                        </div>
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

                // If NAS is selected, also check for NAS type
                if (provider === 'nas') {
                    showNasTypeConfig();
                }
            }
        }

        // Show/hide NAS type configuration sections
        function showNasTypeConfig() {
            const nasType = document.getElementById('nas_type').value;

            // Hide all NAS config sections
            document.getElementById('nas-local-config').classList.add('hidden');
            document.getElementById('nas-smb-config').classList.add('hidden');

            // Show selected NAS type config
            if (nasType === 'local') {
                document.getElementById('nas-local-config').classList.remove('hidden');
            } else if (nasType === 'smb') {
                document.getElementById('nas-smb-config').classList.remove('hidden');
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
        document.getElementById('connection-form').addEventListener('submit', function (e) {
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
        document.addEventListener('DOMContentLoaded', function () {
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
        document.addEventListener('DOMContentLoaded', function () {
            showProviderConfig();
        });
    </script>
</x-app-layout>