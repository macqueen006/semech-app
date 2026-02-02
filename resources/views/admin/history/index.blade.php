<x-app-layout>
    <div class="p-4">
        <div class="history-header">
            <div>
                <h1>Change history</h1>
                <p class="text-muted">{{ $currentPost->title }}</p>
            </div>
            <a href="{{ route('admin.posts.edit', $id) }}" class="btn btn-secondary">
                ← Back to editing
            </a>
        </div>

        <!-- Current Version Card -->
        <div class="current-version-card">
            <div class="card-badge">Current version</div>
            <h3>{{ $currentPost->title }}</h3>
            <p class="excerpt">{{ Str::limit($currentPost->excerpt, 200) }}</p>

            <div class="version-meta">
                <div class="meta-item">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ $currentPost->updated_at->format('d.m.Y H:i') }}</span>
                </div>

                @if($currentPost->changeUser)
                    <div class="meta-item">
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>{{ $currentPost->changeUser->firstname }} {{ $currentPost->changeUser->lastname }}</span>
                    </div>
                @endif

                <div class="meta-item">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <a href="{{ route('admin.posts.history.show', [$id, 'current']) }}" class="view-link">
                        Preview
                    </a>
                </div>
            </div>
        </div>

        <!-- History Timeline -->
        <div class="history-timeline">
            <h3>Version history <span class="count">({{ $historyPosts->count() }})</span></h3>

            @forelse($historyPosts as $history)
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <div class="timeline-dot"></div>
                        @if(!$loop->last)
                            <div class="timeline-line"></div>
                        @endif
                    </div>

                    <div class="timeline-content">
                        <div class="timeline-header">
                            <h4>{{ $history->title }}</h4>
                            <span class="timeline-date">{{ $history->created_at->format('d.m.Y H:i') }}</span>
                        </div>

                        <p class="timeline-excerpt">{{ Str::limit($history->excerpt, 150) }}</p>

                        <div class="timeline-meta">
                            @if($history->changeUser)
                                <span class="meta-badge">
                                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    {{ $history->changeUser->firstname }} {{ $history->changeUser->lastname }}
                                </span>
                            @endif

                            @if($history->changelog)
                                <span class="meta-badge changelog">
                                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                    </svg>
                                    {{ $history->changelog }}
                                </span>
                            @endif

                            @if($history->scheduled_at)
                                <span class="meta-badge scheduling">
                                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $history->scheduled_at->format('M d, Y H:i') }}
                                </span>
                            @endif

                            @if($history->expires_at)
                                <span class="meta-badge expiration">
                                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $history->expires_at->format('M d, Y H:i') }}
                                </span>
                            @endif
                        </div>

                        <a href="{{ route('admin.posts.history.show', [$id, $history->id]) }}"
                           class="btn btn-sm btn-outline">
                            View details
                        </a>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p>No versions saved in history</p>
                </div>
            @endforelse
        </div>
    </div>

    @push('styles')
        <style>
            .history-header {
                display: flex;
                justify-content: space-between;
                align-items: start;
                margin-bottom: 2rem;
                padding-bottom: 1rem;
                border-bottom: 2px solid #e5e7eb;
            }

            .history-header h1 {
                font-size: 1.875rem;
                font-weight: 700;
                color: #111827;
                margin: 0;
            }

            .text-muted {
                color: #6b7280;
                margin-top: 0.25rem;
            }

            .current-version-card {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 2rem;
                border-radius: 12px;
                margin-bottom: 3rem;
                box-shadow: 0 10px 25px rgba(102, 126, 234, 0.2);
                position: relative;
                overflow: hidden;
            }

            .current-version-card::before {
                content: '';
                position: absolute;
                top: 0;
                right: 0;
                width: 300px;
                height: 300px;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 50%;
                transform: translate(50%, -50%);
            }

            .card-badge {
                display: inline-block;
                background: rgba(255, 255, 255, 0.2);
                padding: 0.25rem 0.75rem;
                border-radius: 9999px;
                font-size: 0.875rem;
                font-weight: 600;
                margin-bottom: 1rem;
            }

            .current-version-card h3 {
                font-size: 1.5rem;
                font-weight: 700;
                margin-bottom: 0.75rem;
                position: relative;
            }

            .excerpt {
                opacity: 0.9;
                line-height: 1.6;
                margin-bottom: 1.5rem;
                position: relative;
            }

            .version-meta {
                display: flex;
                gap: 1.5rem;
                flex-wrap: wrap;
                position: relative;
            }

            .meta-item {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.875rem;
            }

            .icon {
                width: 1.25rem;
                height: 1.25rem;
            }

            .view-link {
                color: white;
                text-decoration: underline;
                font-weight: 500;
            }

            .view-link:hover {
                opacity: 0.8;
            }

            .history-timeline {
                margin-top: 2rem;
            }

            .history-timeline h3 {
                font-size: 1.25rem;
                font-weight: 600;
                color: #111827;
                margin-bottom: 2rem;
            }

            .count {
                color: #6b7280;
                font-weight: 400;
            }

            .timeline-item {
                display: flex;
                gap: 1.5rem;
                margin-bottom: 2rem;
            }

            .timeline-marker {
                display: flex;
                flex-direction: column;
                align-items: center;
                width: 24px;
            }

            .timeline-dot {
                width: 12px;
                height: 12px;
                border-radius: 50%;
                background: #667eea;
                box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.2);
            }

            .timeline-line {
                width: 2px;
                flex: 1;
                background: #e5e7eb;
                margin-top: 0.5rem;
            }

            .timeline-content {
                flex: 1;
                background: white;
                padding: 1.5rem;
                border-radius: 8px;
                border: 1px solid #e5e7eb;
                transition: all 0.2s;
            }

            .timeline-content:hover {
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                border-color: #667eea;
            }

            .timeline-header {
                display: flex;
                justify-content: space-between;
                align-items: start;
                margin-bottom: 0.75rem;
            }

            .timeline-header h4 {
                font-size: 1.125rem;
                font-weight: 600;
                color: #111827;
                margin: 0;
                flex: 1;
            }

            .timeline-date {
                color: #6b7280;
                font-size: 0.875rem;
                white-space: nowrap;
                margin-left: 1rem;
            }

            .timeline-excerpt {
                color: #4b5563;
                line-height: 1.6;
                margin-bottom: 1rem;
            }

            .timeline-meta {
                display: flex;
                gap: 1rem;
                flex-wrap: wrap;
                margin-bottom: 1rem;
            }

            .meta-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.375rem;
                padding: 0.25rem 0.75rem;
                background: #f3f4f6;
                border-radius: 9999px;
                font-size: 0.875rem;
                color: #4b5563;
            }

            .meta-badge.changelog {
                background: #fef3c7;
                color: #92400e;
            }

            .btn {
                display: inline-block;
                padding: 0.5rem 1rem;
                border-radius: 6px;
                font-weight: 500;
                text-decoration: none;
                transition: all 0.2s;
            }

            .btn-secondary {
                background: #f3f4f6;
                color: #374151;
            }

            .btn-secondary:hover {
                background: #e5e7eb;
            }

            .btn-sm {
                padding: 0.375rem 0.75rem;
                font-size: 0.875rem;
            }

            .btn-outline {
                background: transparent;
                border: 1px solid #667eea;
                color: #667eea;
            }

            .btn-outline:hover {
                background: #667eea;
                color: white;
            }

            .empty-state {
                text-align: center;
                padding: 3rem 1rem;
                color: #9ca3af;
            }

            .empty-icon {
                width: 4rem;
                height: 4rem;
                margin: 0 auto 1rem;
                opacity: 0.5;
            }
        </style>
    @endpush
</x-app-layout>
