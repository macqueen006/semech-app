<x-app-layout>
    <div class="p-4">
        <!-- Header -->
        <div class="page-header">
            <div>
                <h1>Version preview</h1>
                <p class="breadcrumb">
                    <a href="{{ route('admin.posts.index') }}">Posts</a>
                    <span>/</span>
                    <a href="{{ route('admin.posts.edit', $id) }}">{{ Str::limit($currentPost->title, 30) }}</a>
                    <span>/</span>
                    History
                </p>
            </div>
            <div class="header-actions">
                <a href="{{ route('admin.posts.history.index', $id) }}" class="btn btn-secondary">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    History
                </a>
                <a href="{{ route('admin.posts.edit', $id) }}" class="btn btn-primary">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </a>
            </div>
        </div>

        <!-- Version Selector -->
        <div class="version-selector">
            <label for="version-select">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                Compare with another version:
            </label>
            <select id="version-select" onchange="window.location.href = this.value">
                <option value="{{ route('admin.posts.history.show', [$id, 'current']) }}" {{ $historyId === 'current' ? 'selected' : '' }}>
                    Current version (now)
                </option>
                @foreach($historyPosts as $history)
                    <option value="{{ route('admin.posts.history.show', [$id, $history->id]) }}" {{ $historyId == $history->id ? 'selected' : '' }}>
                        {{ $history->created_at->diffForHumans() }} - {{ Str::limit($history->title, 50) }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Flash Messages -->
        <div id="flashMessage" class="hidden"></div>

        <!-- Post Content -->
        <div class="post-viewer">
            <div class="post-viewer-header">
                <div class="post-title-section">
                    <h2>{{ $post->title }}</h2>

                    <div class="post-badges">
                        @if($isCurrent)
                            <span class="badge badge-success">Current version</span>
                        @else
                            <span class="badge badge-warning">Historical version</span>
                        @endif

                        @if($post->is_published)
                            <span class="badge badge-primary">Published</span>
                        @else
                            <span class="badge badge-secondary">Draft</span>
                        @endif
                    </div>
                </div>

                @if(!$isCurrent)
                    <button onclick="revertVersion()" class="btn btn-warning">
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Restore this version
                    </button>
                @endif
            </div>

            <!-- Post Metadata -->
            <div class="post-metadata">
                <div class="metadata-grid">
                    <div class="metadata-item">
                        <span class="metadata-label">Created at</span>
                        <span class="metadata-value">{{ $post->created_at->format('d.m.Y H:i') }}</span>
                    </div>

                    <div class="metadata-item">
                        <span class="metadata-label">Last updated</span>
                        <span class="metadata-value">{{ $post->updated_at->format('d.m.Y H:i') }}</span>
                    </div>

                    <div class="metadata-item">
                        <span class="metadata-label">Category</span>
                        <span class="metadata-value">{{ $post->category->name ?? 'None' }}</span>
                    </div>

                    <div class="metadata-item">
                        <span class="metadata-label">Reading time</span>
                        <span class="metadata-value">{{ $post->read_time }} min</span>
                    </div>

                    @php
                        $status = getStatusBadge($post);
                    @endphp

                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium border {{ $status['color'] }}">
                        <i class="fa-solid {{ $status['icon'] }}"></i>
                        <span>{{ $status['text'] }}</span>
                    </div>

                    @if($post->changeUser)
                        <div class="metadata-item">
                            <span class="metadata-label">Last changed by</span>
                            <span class="metadata-value">
                                {{ $post->changeUser->firstname }} {{ $post->changeUser->lastname }}
                            </span>
                        </div>
                    @endif

                    @if($post->changelog)
                        <div class="metadata-item full-width">
                            <span class="metadata-label">Change note</span>
                            <span class="metadata-value changelog">{{ $post->changelog }}</span>
                        </div>
                    @endif

                    @if($post->scheduled_at)
                        <div class="metadata-item">
                            <span class="metadata-label">Scheduled for</span>
                            <span class="metadata-value">
                                {{ $post->scheduled_at->format('d.m.Y H:i') }}
                                @if($post->scheduled_at->isFuture())
                                    <span class="text-blue-600">({{ $post->scheduled_at->diffForHumans() }})</span>
                                @else
                                    <span class="text-gray-500">({{ $post->scheduled_at->diffForHumans() }})</span>
                                @endif
                            </span>
                        </div>
                    @endif

                    @if($post->expires_at)
                        <div class="metadata-item">
                            <span class="metadata-label">Expires at</span>
                            <span class="metadata-value">
                                {{ $post->expires_at->format('d.m.Y H:i') }}
                                @if($post->expires_at->isFuture())
                                    <span class="text-orange-600">({{ $post->expires_at->diffForHumans() }})</span>
                                @else
                                    <span class="text-red-600">(Expired {{ $post->expires_at->diffForHumans() }})</span>
                                @endif
                            </span>
                        </div>
                    @endif

                    @if($post->meta_title)
                        <div class="metadata-item">
                            <span class="metadata-label">SEO Title</span>
                            <span class="metadata-value">{{ $post->meta_title }}</span>
                        </div>
                    @endif

                    @if($post->meta_description)
                        <div class="metadata-item full-width">
                            <span class="metadata-label">Meta Description</span>
                            <span class="metadata-value">{{ $post->meta_description }}</span>
                        </div>
                    @endif

                    @if($post->focus_keyword)
                        <div class="metadata-item">
                            <span class="metadata-label">Focus Keyword</span>
                            <span class="metadata-value">
                                <span class="inline-block px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs">
                                    {{ $post->focus_keyword }}
                                </span>
                            </span>
                        </div>
                    @endif

                    @if($post->image_alt)
                        <div class="metadata-item">
                            <span class="metadata-label">Image Alt Text</span>
                            <span class="metadata-value">{{ $post->image_alt }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Excerpt -->
            <div class="post-section">
                <h3 class="section-title">Excerpt</h3>
                <div class="excerpt-content">
                    {{ $post->excerpt }}
                </div>
            </div>

            <!-- Body -->
            <div class="post-section">
                <h3 class="section-title">Content</h3>
                <div class="post-content">
                    {!! $post->body !!}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function revertVersion() {
                if (!confirm('Are you sure you want to restore this version? The current version will be saved in history.')) {
                    return;
                }

                const btn = event.target.closest('button');
                btn.disabled = true;
                btn.textContent = 'Restoring...';

                fetch('{{ route('admin.posts.history.revert', [$id, $historyId]) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showMessage(data.message, 'success');
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 1500);
                        } else {
                            showMessage(data.message, 'error');
                            btn.disabled = false;
                            btn.innerHTML = `
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Restore this version
                        `;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showMessage('An error occurred while restoring the post', 'error');
                        btn.disabled = false;
                        btn.innerHTML = `
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Restore this version
                    `;
                    });
            }

            function showMessage(message, type) {
                const flashMessage = document.getElementById('flashMessage');
                flashMessage.className = type === 'success' ? 'alert alert-success' : 'alert alert-error';
                flashMessage.innerHTML = `
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        ${type === 'success' ?
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>' :
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                }
                    </svg>
                    ${message}
                `;
                flashMessage.classList.remove('hidden');

                setTimeout(() => {
                    flashMessage.classList.add('hidden');
                }, 5000);
            }
        </script>
    @endpush

    @push('styles')
        <style>
            .page-header {
                display: flex;
                justify-content: space-between;
                align-items: start;
                margin-bottom: 2rem;
                padding-bottom: 1rem;
                border-bottom: 2px solid #e5e7eb;
            }

            .page-header h1 {
                font-size: 1.875rem;
                font-weight: 700;
                color: #111827;
                margin: 0 0 0.5rem 0;
            }

            .breadcrumb {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                color: #6b7280;
                font-size: 0.875rem;
            }

            .breadcrumb a {
                color: #667eea;
                text-decoration: none;
            }

            .breadcrumb a:hover {
                text-decoration: underline;
            }

            .header-actions {
                display: flex;
                gap: 0.5rem;
            }

            .version-selector {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 1.5rem;
                border-radius: 12px;
                margin-bottom: 2rem;
                color: white;
            }

            .version-selector label {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-weight: 600;
                margin-bottom: 0.75rem;
            }

            .version-selector select {
                width: 100%;
                padding: 0.75rem 1rem;
                border-radius: 8px;
                border: 2px solid rgba(255, 255, 255, 0.3);
                background: rgba(255, 255, 255, 0.95);
                color: #111827;
                font-size: 0.875rem;
                cursor: pointer;
            }

            .version-selector select:focus {
                outline: none;
                border-color: white;
                box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.2);
            }

            .alert {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                margin-bottom: 1.5rem;
            }

            .alert-success {
                background: #d1fae5;
                color: #065f46;
                border: 1px solid #6ee7b7;
            }

            .alert-error {
                background: #fee2e2;
                color: #991b1b;
                border: 1px solid #fca5a5;
            }

            .post-viewer {
                background: white;
                border-radius: 12px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                overflow: hidden;
            }

            .post-viewer-header {
                display: flex;
                justify-content: space-between;
                align-items: start;
                padding: 2rem;
                border-bottom: 1px solid #e5e7eb;
            }

            .post-title-section h2 {
                font-size: 1.875rem;
                font-weight: 700;
                color: #111827;
                margin: 0 0 0.75rem 0;
            }

            .post-badges {
                display: flex;
                gap: 0.5rem;
                flex-wrap: wrap;
            }

            .badge {
                padding: 0.25rem 0.75rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 600;
            }

            .badge-success {
                background: #d1fae5;
                color: #065f46;
            }

            .badge-warning {
                background: #fef3c7;
                color: #92400e;
            }

            .badge-primary {
                background: #dbeafe;
                color: #1e40af;
            }

            .badge-secondary {
                background: #f3f4f6;
                color: #4b5563;
            }

            .post-metadata {
                padding: 2rem;
                background: #f9fafb;
                border-bottom: 1px solid #e5e7eb;
            }

            .metadata-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1.5rem;
            }

            .metadata-item {
                display: flex;
                flex-direction: column;
                gap: 0.25rem;
            }

            .metadata-item.full-width {
                grid-column: 1 / -1;
            }

            .metadata-label {
                font-size: 0.75rem;
                font-weight: 600;
                text-transform: uppercase;
                color: #6b7280;
                letter-spacing: 0.05em;
            }

            .metadata-value {
                font-size: 0.875rem;
                color: #111827;
            }

            .metadata-value.changelog {
                font-style: italic;
                padding: 0.5rem;
                background: #fef3c7;
                border-radius: 4px;
                color: #92400e;
            }

            .featured-image {
                padding: 2rem;
                border-bottom: 1px solid #e5e7eb;
            }

            .featured-image img {
                width: 100%;
                height: auto;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            }

            .post-section {
                padding: 2rem;
                border-bottom: 1px solid #e5e7eb;
            }

            .post-section:last-child {
                border-bottom: none;
            }

            .section-title {
                font-size: 1.25rem;
                font-weight: 600;
                color: #111827;
                margin: 0 0 1rem 0;
            }

            .excerpt-content {
                color: #4b5563;
                line-height: 1.75;
                font-size: 1.125rem;
            }

            .post-content {
                color: #374151;
                line-height: 1.75;
            }

            .post-content h1, .post-content h2, .post-content h3 {
                color: #111827;
                font-weight: 600;
                margin-top: 2rem;
                margin-bottom: 1rem;
            }

            .post-content p {
                margin-bottom: 1rem;
            }

            .post-content img {
                max-width: 100%;
                height: auto;
                border-radius: 8px;
                margin: 1.5rem 0;
            }

            .post-content ul, .post-content ol {
                margin: 1rem 0;
                padding-left: 2rem;
            }

            .post-content li {
                margin-bottom: 0.5rem;
            }

            .btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.625rem 1.25rem;
                border-radius: 8px;
                font-weight: 500;
                font-size: 0.875rem;
                text-decoration: none;
                transition: all 0.2s;
                border: none;
                cursor: pointer;
            }

            .btn-primary {
                background: #667eea;
                color: white;
            }

            .btn-primary:hover {
                background: #5568d3;
            }

            .btn-secondary {
                background: #f3f4f6;
                color: #374151;
            }

            .btn-secondary:hover {
                background: #e5e7eb;
            }

            .btn-warning {
                background: #fbbf24;
                color: #78350f;
            }

            .btn-warning:hover {
                background: #f59e0b;
            }

            .icon {
                width: 1.25rem;
                height: 1.25rem;
            }
        </style>
    @endpush
</x-app-layout>

@php
    function getStatusBadge($post) {
        if (!$post->is_published) {
            return [
                'color' => 'bg-gray-100 text-gray-700 border-gray-200',
                'icon' => 'fa-file-lines',
                'text' => 'Draft'
            ];
        }

        if ($post->scheduled_at && $post->scheduled_at->isFuture()) {
            return [
                'color' => 'bg-blue-100 text-blue-700 border-blue-200',
                'icon' => 'fa-clock',
                'text' => 'Scheduled'
            ];
        }

        return [
            'color' => 'bg-green-100 text-green-700 border-green-200',
            'icon' => 'fa-circle-check',
            'text' => 'Published'
        ];
    }
@endphp
