<div class="border-t border-layer-line pt-6 space-y-4">
    <!-- Header with Collapse Toggle -->
    <button type="button"
            class="hs-collapse-toggle w-full flex items-center justify-between cursor-pointer py-2 hover:text-primary-hover transition-colors"
            id="seo-collapse-toggle"
            aria-expanded="false"
            aria-controls="seo-collapse-content"
            data-hs-collapse="#seo-collapse-content">
        <div class="flex items-center gap-2">
            <h3 class="text-lg font-semibold text-foreground">SEO & Social Media</h3>
            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded dark:bg-primary-500/20 dark:text-primary-400">Optional</span>
        </div>
        <svg class="hs-collapse-open:rotate-180 shrink-0 size-5 text-muted-foreground-1 transition-transform duration-200" fill="none"
             stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <!-- Collapsible Content -->
    <div id="seo-collapse-content"
         class="hs-collapse hidden w-full overflow-hidden transition-[height] duration-300"
         aria-labelledby="seo-collapse-toggle">
        <div class="space-y-6 pt-4">

            <!-- Auto-fill Toggle -->
            <div class="flex items-center">
                <input type="checkbox"
                       id="autoFillSeo"
                       checked
                       class="shrink-0 size-4 bg-transparent border-line-3 rounded-sm shadow-2xs text-primary focus:ring-0 focus:ring-offset-0 checked:bg-blue-500 checked:border-primary-checked disabled:opacity-50 disabled:pointer-events-none">
                <label for="autoFillSeo" class="text-sm ms-3 text-muted-foreground-1">Auto-fill SEO fields from title and excerpt</label>
            </div>

            <!-- Meta Title -->
            <div>
                <label for="metaTitle" class="block font-medium mb-2 text-sm text-foreground">
                    Meta Title
                    <span class="text-xs text-muted-foreground-1 font-normal">(Search engine title)</span>
                </label>
                <input
                    type="text"
                    id="metaTitle"
                    name="meta_title"
                    value="{{ $savedPost->meta_title ?? '' }}"
                    maxlength="80"
                    placeholder="Leave empty to use post title"
                    class="py-2.5 sm:py-3 px-4 rounded-lg block w-full bg-layer border-layer-line sm:text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus disabled:opacity-50 disabled:pointer-events-none"
                >
                <p class="text-xs mt-1 text-muted-foreground-1">
                    <span id="metaTitleCount">{{ $savedPost ? strlen($savedPost->meta_title ?? '') : 0 }}</span>/80 characters
                    <span id="metaTitleWarning" class="hidden text-orange-600"> - Getting close to limit!</span>
                </p>
            </div>

            <!-- Meta Description -->
            <div>
                <label for="metaDescription" class="block font-medium mb-2 text-sm text-foreground">
                    Meta Description
                    <span class="text-xs text-muted-foreground-1 font-normal">(Search engine description)</span>
                </label>
                <textarea
                    id="metaDescription"
                    name="meta_description"
                    rows="2"
                    maxlength="160"
                    placeholder="Leave empty to use excerpt"
                    class="py-2.5 sm:py-3 px-4 block w-full bg-layer border-layer-line rounded-lg sm:text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus disabled:opacity-50 disabled:pointer-events-none [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-none [&::-webkit-scrollbar-track]:bg-scrollbar-track [&::-webkit-scrollbar-thumb]:bg-scrollbar-thumb"
                >{{ $savedPost->meta_description ?? '' }}</textarea>
                <p class="text-xs mt-1 text-muted-foreground-1">
                    <span id="metaDescriptionCount">{{ $savedPost ? strlen($savedPost->meta_description ?? '') : 0 }}</span>/160 characters
                    <span id="metaDescriptionWarning" class="hidden text-orange-600"> - Almost at limit!</span>
                </p>
            </div>

            <!-- Focus Keyword -->
            <div>
                <label for="focusKeyword" class="block font-medium mb-2 text-sm text-foreground">
                    Focus Keyword
                    <span class="text-xs text-muted-foreground-1 font-normal">(Main keyword for this post)</span>
                </label>
                <input
                    type="text"
                    id="focusKeyword"
                    name="focus_keyword"
                    value="{{ $savedPost->focus_keyword ?? '' }}"
                    maxlength="100"
                    placeholder="e.g., Laravel SEO optimization"
                    class="py-2.5 sm:py-3 px-4 rounded-lg block w-full bg-layer border-layer-line sm:text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus disabled:opacity-50 disabled:pointer-events-none"
                >
                <p class="text-xs text-muted-foreground-1 mt-1 flex items-center gap-1">
                    <svg class="shrink-0 size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    This helps you focus your content on one main topic
                </p>
            </div>

            <!-- Image Alt Text -->
            <div>
                <label for="imageAlt" class="block font-medium mb-2 text-sm text-foreground">
                    Featured Image Alt Text
                    <span class="text-xs text-muted-foreground-1 font-normal">(For accessibility & SEO)</span>
                </label>
                <input
                    type="text"
                    id="imageAlt"
                    name="image_alt"
                    value="{{ $savedPost->image_alt ?? '' }}"
                    maxlength="255"
                    placeholder="Describe the featured image..."
                    class="py-2.5 sm:py-3 px-4 rounded-lg block w-full bg-layer border-layer-line sm:text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus disabled:opacity-50 disabled:pointer-events-none"
                >
                <p class="text-xs text-muted-foreground-1 mt-1 flex items-center gap-1">
                    <svg class="shrink-0 size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Helps screen readers and improves SEO
                </p>
            </div>

            <!-- Open Graph (Facebook) -->
            <div class="border-t border-layer-line pt-4">
                <h4 class="font-medium mb-3 flex items-center gap-2 text-foreground">
                    <svg class="shrink-0 size-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    Facebook / Open Graph
                </h4>

                <div class="space-y-3">
                    <div>
                        <label for="ogTitle" class="block text-sm font-medium mb-1 text-foreground">OG Title</label>
                        <input
                            type="text"
                            id="ogTitle"
                            name="og_title"
                            value="{{ $savedPost->og_title ?? '' }}"
                            maxlength="80"
                            placeholder="Auto-filled from meta title"
                            class="py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg block w-full bg-layer border-layer-line text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus disabled:opacity-50 disabled:pointer-events-none"
                        >
                        <p class="text-xs text-muted-foreground-1 mt-1">
                            <span id="ogTitleCount">{{ $savedPost ? strlen($savedPost->og_title ?? '') : 0 }}</span>/80
                        </p>
                    </div>

                    <div>
                        <label for="ogDescription" class="block text-sm font-medium mb-1 text-foreground">OG Description</label>
                        <textarea
                            id="ogDescription"
                            name="og_description"
                            rows="2"
                            maxlength="160"
                            placeholder="Auto-filled from meta description"
                            class="py-2 sm:py-2.5 px-3 sm:px-4 block w-full bg-layer border-layer-line rounded-lg text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus disabled:opacity-50 disabled:pointer-events-none [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-none [&::-webkit-scrollbar-track]:bg-scrollbar-track [&::-webkit-scrollbar-thumb]:bg-scrollbar-thumb"
                        >{{ $savedPost->og_description ?? '' }}</textarea>
                        <p class="text-xs text-muted-foreground-1 mt-1">
                            <span id="ogDescription">{{ $savedPost ? strlen($savedPost->og_description ?? '') : 0 }}</span>/160
                        </p>
                    </div>

                    <div>
                        <label for="ogImage" class="block text-sm font-medium mb-1 text-foreground">OG Image URL</label>
                        <input
                            type="text"
                            id="ogImage"
                            name="og_image"
                            value="{{ $savedPost->og_image ?? '' }}"
                            placeholder="Auto-filled from featured image"
                            class="py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg block w-full bg-layer border-layer-line text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus disabled:opacity-50 disabled:pointer-events-none"
                        >
                    </div>
                </div>
            </div>

            <!-- Twitter Card -->
            <div class="border-t border-layer-line pt-4">
                <h4 class="font-medium mb-3 flex items-center gap-2 text-foreground">
                    <svg class="shrink-0 size-5 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                    </svg>
                    Twitter Card
                </h4>

                <div class="space-y-3">
                    <div>
                        <label for="twitterTitle" class="block text-sm font-medium mb-1 text-foreground">Twitter Title</label>
                        <input
                            type="text"
                            id="twitterTitle"
                            name="twitter_title"
                            value="{{ $savedPost->twitter_title ?? '' }}"
                            maxlength="80"
                            placeholder="Auto-filled from OG title"
                            class="py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg block w-full bg-layer border-layer-line text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus disabled:opacity-50 disabled:pointer-events-none"
                        >
                        <p class="text-xs text-muted-foreground-1 mt-1">
                            <span id="twitterTitleCount">{{ $savedPost ? strlen($savedPost->twitter_title ?? '') : 0 }}</span>/80
                        </p>
                    </div>

                    <div>
                        <label for="twitterDescription" class="block text-sm font-medium mb-1 text-foreground">Twitter Description</label>
                        <textarea
                            id="twitterDescription"
                            name="twitter_description"
                            rows="2"
                            maxlength="160"
                            placeholder="Auto-filled from OG description"
                            class="py-2 sm:py-2.5 px-3 sm:px-4 block w-full bg-layer border-layer-line rounded-lg text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus disabled:opacity-50 disabled:pointer-events-none [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-none [&::-webkit-scrollbar-track]:bg-scrollbar-track [&::-webkit-scrollbar-thumb]:bg-scrollbar-thumb"
                        >{{ $savedPost->twitter_description ?? '' }}</textarea>
                        <p class="text-xs text-muted-foreground-1 mt-1">
                            <span id="twitterDescription">{{ $savedPost ? strlen($savedPost->twitter_description ?? '') : 0 }}</span>/160
                        </p>
                    </div>

                    <div>
                        <label for="twitterImage" class="block text-sm font-medium mb-1 text-foreground">Twitter Image URL</label>
                        <input
                            type="text"
                            id="twitterImage"
                            name="twitter_image"
                            value="{{ $savedPost->twitter_image ?? '' }}"
                            placeholder="Auto-filled from OG image"
                            class="py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg block w-full bg-layer border-layer-line text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus disabled:opacity-50 disabled:pointer-events-none"
                        >
                    </div>

                    <div id="seoPreview" class="border-t pt-4 hidden">
                        <h4 class="font-medium mb-3">Google Search Preview</h4>
                        <div class="bg-white border rounded-lg p-4">
                            <div id="previewTitle" class="text-blue-600 text-xl"></div>
                            <div id="previewUrl" class="text-green-700 text-sm mt-1"></div>
                            <div id="previewDescription" class="text-gray-600 text-sm mt-2"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
