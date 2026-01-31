<x-guest-layout>
    @section('meta')
        <title>Home</title>
    @endsection

    <div>
        <!-- Hero Section -->
        @if($trendingTopics->isNotEmpty())
            <x-sections.trending-topics :posts="$trendingTopics"/>
        @endif
        <!-- End Hero Section -->
        <x-ads.header/>
        <!-- Highlighted Posts  -->
        @if($highlightedPosts->isNotEmpty())
            <x-sections.highlighted-posts :posts="$highlightedPosts"/>
        @endif
        <!-- End Highlighted Posts -->

        <!-- Popular Posts -->
        @if($popularPosts->isNotEmpty())
            <x-sections.popular-posts :posts="$popularPosts"/>
        @endif
        <!-- End Popular Posts -->

        <!-- Newsletter Subscription -->
        <x-sections.newsletter-banner/>
        <!-- End Newsletter Subscription -->

        <!-- Newsletter Modal -->
        {{--    <x-modals.newsletter-modal :showNewsletterModal="$showNewsletterModal"/>--}}

        <!-- Recent Posts -->
        <x-sections.recent-posts :posts="$allPosts" loadMore="loadMore"/>
        <!-- End Recent Posts -->
    </div>
</x-guest-layout>
