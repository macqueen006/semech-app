<x-guest-layout>
    @section('meta')
        <title>South-West Geo Data Intelligence Platform</title>
    @endsection
    <div>
        <section class="title-section">
            <div class="hero-container">
                <div class="title-wrap">
                    <h1 class="main-title">{{$page->title}}</h1>
                </div>
            </div>
        </section>
        <section class="content-section pb-mobile-portrait xmd:pb-mobile-landing xlg:pb-desktop">
            <div class="hero-container">
                <div class="flex flex-col gap-[40px]">
                    {!! $page->content !!}
                </div>
            </div>
        </section>
    </div>
</x-guest-layout>

