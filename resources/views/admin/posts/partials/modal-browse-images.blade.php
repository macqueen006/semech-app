<div id="browseImagesModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-6xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Select Image from Storage</h3>
                <button type="button" id="closeBrowseModal" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div id="browseLoading" class="flex justify-center items-center py-12">
                <svg class="animate-spin h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="ml-2 text-gray-600">Loading images...</span>
            </div>

            <div id="browseGrid" class="hidden grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <!-- Images will be inserted here -->
            </div>

            <div id="browseEmpty" class="hidden col-span-full text-center py-12 text-gray-500">
                No images found in storage.
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" id="closeBrowseModalBtn" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
