<section class="footer">
    <!-- Marquee -->
    {{--<section class="newmarquee-container" data-direction="left" data-speed="30" data-pauseonhover="true">
        <div id="newmarquee-content" class="flex items-center gap-6 py-5">
            <p class="footer-item">Lorem ipsum dolor sit amet, consectetur adipisicing elit.</p>
            <p class="footer-item">Magni repellat nemo nesciunt rerum asperiores assumenda ex quos dignissimos.</p>
        </div>
    </section>--}}
    <!-- End Marquee -->
    <div class="footer-top">
        <div class="section-container">
            <div class="footer-wrap">
                <div class="footer-block">
                    <div class="footer-left">
                        <h5 class="text-white">
                            Subscribe to our updates for timely information, helpful tips,
                            and special announcements.
                        </h5>
                        <div class="footer-form w-form">
                            <form method="POST" action="{{ route('newsletter.subscribe-footer') }}">
                                @csrf
                                <input type="hidden" name="form_type" value="footer">
                                <label for="footer_email" class="mb-[5px]"></label>
                                <input
                                    class="footer-field"
                                    name="footer_email"
                                    placeholder="Enter your e-mail..."
                                    type="email"
                                    id="footer-email"
                                    value="{{ old('footer_email') }}"
                                    />
                                <button type="submit" class="primary-button full-width">
                                    SUBSCRIBE
                                </button>
                                <p class="footer-small">
                                    By subscribing to our newsletter, you agree to and
                                    acknowledge that you have read our Privacy Policy and
                                    Terms &amp; Conditions.
                                </p>

                                @if (session()->has('newsletter_success'))
                                    <div class="success-message mt-2">
                                        {{ session('newsletter_success') }}
                                    </div>
                                @endif

                                @error('footer_email')
                                <div class="error-message mt-2">
                                    {{ $message }}
                                </div>
                                @enderror
                            </form>
                        </div>
                    </div>
                    <div class="footer-right">
                        <div class="menu-block">
                            <div class="menu-title">PAGES</div>

                            @php
                                $currentRoute = request()->route()->getName();
                            @endphp
                            <div class="menu-list">
                                <a href="{{route('home.index')}}"
                                   class="footer-link {{ $currentRoute === 'home.index' ? 'current': '' }}">Home</a>
                                <a href="{{route('about')}}"
                                   class="footer-link {{$currentRoute === 'about'? 'current': ''}}">About us</a>
                                <a href="{{route('articles')}}"
                                   class="footer-link {{$currentRoute === 'articles'? 'current': ''}}">Blog</a>
                                <a href="{{route('south.west')}}"
                                   class="footer-link {{$currentRoute === 'south.west'? 'current': ''}}">Swgdi</a>
                                <a href="{{route('contact')}}"
                                   class="footer-link {{$currentRoute === 'contact'? 'current': ''}}">Contact us</a>
                            </div>
                        </div>
                        <div class="menu-block">
                            <div class="menu-title">CATEGORIES</div>
                              @php
                                  $categories = \App\Models\Category::withCount(['posts' => function($q) {
                                      $q->isLive()->notExpired();
                                  }])
                                  ->withSum(['posts' => function($q) {
                                      $q->isLive()
                                        ->notExpired()
                                        ->where('created_at', '>=', now()->subDays(30));
                                  }], 'view_count')
                                  ->get()
                                  ->filter(fn($category) => $category->posts_count > 0)
                                  ->sortByDesc('posts_sum_view_count')
                                  ->sortByDesc('posts_count')
                                  ->take(6);

                                  $currentCategorySlug = request()->route('slug');
                              @endphp

                            <div>
                                <div role="list" class="menu-list">
                                    @forelse($categories as $category)
                                        <div role="listitem">
                                            <a href="{{ route('category.show', $category->slug) }}"
                                               class="footer-link {{ $currentCategorySlug === $category->slug ? 'text-secondary' : '' }}">
                                                {{ $category->name }}
                                            </a>
                                        </div>
                                    @empty
                                        <div role="listitem" class="w-full">
                                            <a href="{{ route('home.index') }}" class="footer-link">No categories
                                                yet</a>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        <div class="menu-block">
                            <div class="menu-title">RESOURCES</div>
                            <div class="menu-list">
                                <a href="{{route('privacy.policy')}}"
                                   class="footer-link {{ $currentRoute === 'privacy.policy' ? 'current': '' }}">Privacy
                                    Policy</a>
                                <a href="{{route('advertise')}}"
                                   class="footer-link {{ $currentRoute === 'advertise' ? 'current': '' }}">Advertise</a>
                                <a href="{{route('terms.conditions')}}"
                                   class="footer-link {{ $currentRoute === 'terms.conditions' ? 'current': '' }}">Terms
                                    &amp;
                                    Conditions</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p class="body-small text-font-color">
                        Designed by
                        <a href="https://github.com/macqueen006" target="_blank" class="footer-link underline">Macqueen006</a>.
                        Powered by
                        <span class="footer-link">Magnet</span>.
                    </p>
                    <div class="footer-copyright">
                        <div class="footer-social">
                            <a href="https://twitter.com/" target="_blank" class="social-icon">
                                <img
                                    src="https://cdn.prod.website-files.com/65f28c561f2b91ff16d74d11/66458d7a2287a53a49a62cb8_ic-twitter.svg"
                                    loading="lazy" alt="Twitter X Icon"/>
                            </a>
                            <a href="https://www.facebook.com/" target="_blank" class="social-icon">
                                <img
                                    src="https://cdn.prod.website-files.com/65f28c561f2b91ff16d74d11/66458d9610f7c34789422f5d_ic-facebook.svg"
                                    loading="lazy" alt="Facebook Icon"/>
                            </a>
                            <a href="https://www.youtube.com/" target="_blank" class="social-icon">
                                <img
                                    src="https://cdn.prod.website-files.com/65f28c561f2b91ff16d74d11/66458da3661c87c6c9729e5a_ic-youtube.svg"
                                    loading="lazy" alt="Youtube Icon"/>
                            </a>
                            <a href="https://www.instagram.com/" target="_blank" class="social-icon">
                                <img
                                    src="https://cdn.prod.website-files.com/65f28c561f2b91ff16d74d11/66458db10362151aa24dfed3_ic-insta.svg"
                                    loading="lazy" width="17" alt="Instagram Icon"/>
                            </a>
                            <a href="https://rss.com/" target="_blank" class="social-icon">
                                <img
                                    src="https://cdn.prod.website-files.com/65f28c561f2b91ff16d74d11/66458e7b28a09bbe18f99364_rass.svg"
                                    loading="lazy" alt="Rss Icon"/>
                            </a>
                            <a href="https://www.reddit.com/" target="_blank" class="social-icon">
                                <img
                                    src="https://cdn.prod.website-files.com/65f28c561f2b91ff16d74d11/66458e06feb49074e11880d0_vector.svg"
                                    loading="lazy" alt="Discord Icon"/>
                            </a>
                        </div>
                        <p class="body-small">© {{date('Y')}} Semech.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
