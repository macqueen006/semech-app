<x-app-layout>
        <div class="w-full px-4 py-10 sm:px-6 lg:px-8 mx-auto">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <h1 class="text-2xl font-bold text-gray-800">Create Category</h1>
                </div>

                <!-- Form -->
                <form id="categoryForm" class="p-6">
                    @csrf

                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium mb-2">Name</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        <span id="nameError" class="text-red-600 text-sm hidden"></span>
                    </div>

                    <div class="mb-4">
                        <label for="backgroundColor" class="block text-sm font-medium mb-2">Background Color</label>
                        <div class="flex gap-2 items-center">
                            <input
                                type="color"
                                id="backgroundColorPicker"
                                class="h-10 w-20 border-0 rounded cursor-pointer"
                                value="#000000"
                            >
                            <input
                                type="text"
                                id="backgroundColor"
                                name="backgroundColor"
                                class="py-3 px-4 block flex-1 border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="#000000"
                                value="#000000"
                            >
                        </div>
                        <span id="backgroundColorError" class="text-red-600 text-sm hidden"></span>
                    </div>

                    <div class="mb-4">
                        <label for="textColor" class="block text-sm font-medium mb-2">Text Color</label>
                        <div class="flex gap-2 items-center">
                            <input
                                type="color"
                                id="textColorPicker"
                                class="h-10 w-20 border-0 rounded cursor-pointer"
                                value="#FFFFFF"
                            >
                            <input
                                type="text"
                                id="textColor"
                                name="textColor"
                                class="py-3 px-4 block flex-1 border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="#FFFFFF"
                                value="#FFFFFF"
                            >
                        </div>
                        <span id="textColorError" class="text-red-600 text-sm hidden"></span>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium mb-2">Preview</label>
                        <span id="preview" class="inline-block px-4 py-2 rounded text-white" style="background: #000000; color: #FFFFFF;">
                    Category Name
                </span>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="py-3 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700">
                            Create Category
                        </button>
                        <a href="{{ route('admin.categories.index') }}" class="py-3 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
        @push('scripts')
        <script>
            function updatePreview() {
                const name = document.getElementById('name').value || 'Category Name';
                const bgColor = document.getElementById('backgroundColor').value;
                const textColor = document.getElementById('textColor').value;
                const preview = document.getElementById('preview');

                preview.textContent = name;
                preview.style.background = bgColor;
                preview.style.color = textColor;
            }

            // Name input - update preview
            document.getElementById('name').addEventListener('input', function() {
                updatePreview();
            });

            // Color pickers sync with text inputs
            document.getElementById('backgroundColorPicker').addEventListener('input', function() {
                document.getElementById('backgroundColor').value = this.value;
                updatePreview();
            });

            document.getElementById('textColorPicker').addEventListener('input', function() {
                document.getElementById('textColor').value = this.value;
                updatePreview();
            });

            document.getElementById('backgroundColor').addEventListener('input', function() {
                document.getElementById('backgroundColorPicker').value = this.value;
                updatePreview();
            });

            document.getElementById('textColor').addEventListener('input', function() {
                document.getElementById('textColorPicker').value = this.value;
                updatePreview();
            });

            // Form submission
            document.getElementById('categoryForm').addEventListener('submit', function(e) {
                e.preventDefault();

                // Clear previous errors
                document.querySelectorAll('[id$="Error"]').forEach(el => {
                    el.classList.add('hidden');
                    el.textContent = '';
                });

                const formData = new FormData(this);

                fetch('{{ route("admin.categories.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(errorData => {
                                throw errorData;
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            window.location.href = '{{ route("admin.categories.index") }}';
                        }
                    })
                    .catch(error => {
                        if (error.errors) {
                            showToast('Please fix the validation errors', 'error');
                            Object.keys(error.errors).forEach(key => {
                                const errorEl = document.getElementById(key + 'Error');
                                if (errorEl) {
                                    errorEl.textContent = error.errors[key][0];
                                    errorEl.classList.remove('hidden');
                                }
                            });
                        }
                    });
            });
        </script>
        @endpush
</x-app-layout>
