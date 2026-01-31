<section class="mt-10">
    <div class="section-container">
        <div class="newsletter">
            <div class="newsletter-left">
                <div class="newsletter-title">
                    <h3>Stay informed with our latest news and updates.</h3>
                    <p class="body-large">
                        Be the first to know about emerging trends.
                    </p>
                </div>
                <p class="cta-subtext">
                    From breaking news to thought-provoking opinion pieces, our
                    newsletter keeps you informed &amp; engaged.
                </p>
            </div>
            <div class="newsletter-right">
                <div>
                    <form method="POST" action="{{ route('newsletter.subscribe') }}">
                        @csrf
                        <input type="hidden" name="form_type" value="main">
                        <label for="email" class="mb-[5px]"></label>
                        <input
                            class="newsletter-field"
                            name="email"
                            id="email"
                            placeholder="Enter your e-mail..."
                            type="email"
                            value="{{ old('email') }}"

                        />
                        <button type="submit" class="primary-button full-width">
                            SIGN UP
                        </button>
                    </form>

                    @if (session()->has('newsletter_success'))
                        <div class="success-message">
                            <div class="flex items-center justify-between">
                                <div>{{ session('newsletter_success') }}</div>
                            </div>
                        </div>
                    @endif

                    @error('email')
                    <div class="error-message">
                        <div class="flex items-center justify-between">
                            <div>{{ $message }}</div>
                        </div>
                    </div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    </div>
</section>
