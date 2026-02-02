<div class="comments-list">
    @forelse($comments as $comment)
        <div class="comment-card" data-comment-id="{{ $comment->id }}">
            <div class="comment-header">
                <div class="comment-author">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <strong>{{ $comment->name }}</strong>
                </div>
                <div class="comment-meta">
                    <span class="comment-date">{{ $comment->created_at->diffForHumans() }}</span>
                    @if($comment->post)
                        <a href="{{ route('admin.posts.show', $comment->post->slug ?? '#') }}" class="post-link" target="_blank">
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            {{ Str::limit($comment->post->title ?? 'No title', 40) }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="comment-body">
                {{ $comment->body }}
            </div>

            <div class="comment-actions">
                <a href="{{ route('admin.comments.edit', $comment->id) }}" class="btn btn-sm btn-outline">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </a>
                <button
                    class="btn btn-sm btn-danger delete-comment-btn"
                    data-comment-id="{{ $comment->id }}"
                    data-comment-name="{{ $comment->name }}">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete
                </button>
            </div>
        </div>
    @empty
        <div class="empty-state">
            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
            </svg>
            <h3>No comments</h3>
            <p>No comments matching the search criteria were found.</p>
        </div>
    @endforelse
    @push('styles')
            <style>
                .comments-list {
                    display: flex;
                    flex-direction: column;
                    gap: 1rem;
                }

                .comment-card {
                    background: white;
                    border-radius: 12px;
                    padding: 1.5rem;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                    transition: all 0.2s;
                }

                .comment-card:hover {
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                }

                .comment-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: start;
                    margin-bottom: 1rem;
                    flex-wrap: wrap;
                    gap: 0.5rem;
                }

                .comment-author {
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    color: #111827;
                }

                .comment-meta {
                    display: flex;
                    align-items: center;
                    gap: 1rem;
                    font-size: 0.875rem;
                    color: #6b7280;
                    flex-wrap: wrap;
                }

                .post-link {
                    display: inline-flex;
                    align-items: center;
                    gap: 0.375rem;
                    color: #667eea;
                    text-decoration: none;
                    transition: color 0.2s;
                }

                .post-link:hover {
                    color: #5568d3;
                    text-decoration: underline;
                }

                .comment-body {
                    color: #374151;
                    line-height: 1.6;
                    margin-bottom: 1rem;
                    padding: 1rem;
                    background: #f9fafb;
                    border-radius: 8px;
                }

                .comment-actions {
                    display: flex;
                    gap: 0.5rem;
                }

                .btn {
                    display: inline-flex;
                    align-items: center;
                    gap: 0.375rem;
                    padding: 0.5rem 1rem;
                    border-radius: 8px;
                    font-weight: 500;
                    font-size: 0.875rem;
                    text-decoration: none;
                    transition: all 0.2s;
                    border: none;
                    cursor: pointer;
                }

                .btn-sm {
                    padding: 0.375rem 0.75rem;
                    font-size: 0.813rem;
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

                .btn-danger {
                    background: transparent;
                    border: 1px solid #dc2626;
                    color: #dc2626;
                }

                .btn-danger:hover {
                    background: #dc2626;
                    color: white;
                }

                .icon {
                    width: 1.125rem;
                    height: 1.125rem;
                }

                .empty-state {
                    text-align: center;
                    padding: 4rem 2rem;
                    background: white;
                    border-radius: 12px;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                }

                .empty-icon {
                    width: 4rem;
                    height: 4rem;
                    margin: 0 auto 1rem;
                    color: #d1d5db;
                }

                .empty-state h3 {
                    font-size: 1.25rem;
                    font-weight: 600;
                    color: #111827;
                    margin: 0 0 0.5rem 0;
                }

                .empty-state p {
                    color: #6b7280;
                    margin: 0;
                }
            </style>
    @endpush
</div>

