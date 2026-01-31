<x-guest-layout>
    @section('meta')
        <title>Contact us</title>
    @endsection

    <div class="page-wrap">
        <section class="title-section">
            <div class="hero-container">
                <div class="title-wrap">
                    <h1 class="main-title">CONTACT US</h1>
                </div>
            </div>
        </section>

        <section>
            <div class="hero-container">
                <div class="contact-wrap">
                    <div class="pick-block contact">
                        <div class="contact-img">
                            <img
                                src="https://cdn.prod.website-files.com/65f28c561f2b91ff16d74d11/664d8b0085d7887942f1f359_contact-image.jpg"
                                loading="eager"
                                sizes="(max-width: 479px) 92vw, (max-width: 767px) 95vw, (max-width: 991px) 44vw, 36vw"
                                alt="Contact Image" class="cover-image"/>
                        </div>
                        <div class="social-block">
                            <div class="body-small text-black whitespace-nowrap">Follow me on:</div>
                            <div class="flex gap-[12px]">
                                <a href="#" target="_blank" class="social-link">
                                    <img
                                        src="https://cdn.prod.website-files.com/65f28c561f2b91ff16d74d11/664c86f1d67ccf45534dc613_ic-twitter.svg"
                                        loading="lazy" width="17" height="15.11" alt="Twitter Icon"/>
                                </a>
                                <a href="https://facebook.com/" target="_blank" class="social-link">
                                    <img
                                        src="https://cdn.prod.website-files.com/65f28c561f2b91ff16d74d11/664c86f19024ec9ac4086e95_ic-facebook.svg"
                                        loading="lazy" width="9.33" height="16" alt="Facebook Icon"/>
                                </a>
                                <a href="https://www.youtube.com/" target="_blank" class="social-link">
                                    <img
                                        src="https://cdn.prod.website-files.com/65f28c561f2b91ff16d74d11/664c86f00347c5223f1694f0_ic-youtube.svg"
                                        loading="lazy" width="15.11" height="10.67" alt="Youtube Icon"/>
                                </a>
                                <a href="https://www.instagram.com/" target="_blank" class="social-link">
                                    <img
                                        src="https://cdn.prod.website-files.com/65f28c561f2b91ff16d74d11/664c86f1cb8b1a5e675a9b84_ic-insta.svg"
                                        loading="lazy" width="16.25" height="16.25" alt="Instagram Icon"/>
                                </a>
                                <a href="https://rss.com/" target="_blank" class="social-link">
                                    <img
                                        src="https://cdn.prod.website-files.com/65f28c561f2b91ff16d74d11/664d8c21fa3ce7aa3df0d075_ic-ress.svg"
                                        loading="lazy" width="16.25" height="16.25" alt="Rss Icon"/>
                                </a>
                                <a href="https://www.reddit.com/" target="_blank" class="social-link">
                                    <img
                                        src="https://cdn.prod.website-files.com/65f28c561f2b91ff16d74d11/664d8c2bfa6a1715c6ab76af_ic-discord.svg"
                                        loading="lazy" width="16" height="16.25" alt="Social Icon"/>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="contact-wrapper">
                        <div class="hero-title">
                            <h5>Get in Touch</h5>
                            <p>We value your feedback and are always eager to hear from our readers, and partners.
                                Whether you have a question, or inquiry, getting in touch with us is easy.
                            </p>
                        </div>

                        <!-- Flash Messages -->
                        @if(session('success'))
                            <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded">
                                <div class="flex items-center">
                                    <i class="fa-solid fa-circle-check mr-3 text-xl"></i>
                                    <span>{{ session('success') }}</span>
                                </div>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                                <div class="flex items-center">
                                    <i class="fa-solid fa-circle-exclamation mr-3 text-xl"></i>
                                    <span>{{ session('error') }}</span>
                                </div>
                            </div>
                        @endif

                        <div class="contact-forms w-form">
                            <form action="{{ route('contact.submit') }}" method="POST" class="contact-form"
                                  id="contactForm">
                                @csrf
                                <div>
                                    <label for="name" class="sr-only">Name</label>
                                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                                           class="py-2.5 sm:py-3 px-4 block w-full border-gray-200 rounded-lg sm:text-sm bg-white focus:border-secondary focus:ring-secondary disabled:opacity-50 disabled:pointer-events-none"
                                           placeholder="Full name" required>
                                    @error('name')
                                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label for="email" class="sr-only">Email</label>
                                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                                           class="py-2.5 sm:py-3 px-4 block w-full border-gray-200 bg-white rounded-lg sm:text-sm focus:border-secondary focus:ring-secondary disabled:opacity-50 disabled:pointer-events-none"
                                           placeholder="Email address" required>
                                    @error('email')
                                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="xs:col-span-2">
                                    <label for="body" class="sr-only">Your Message</label>
                                    <textarea id="body" name="body"
                                              class="py-2 border-gray-200 bg-white px-3 sm:py-3 sm:px-4 block w-full rounded-lg sm:text-sm focus:border-secondary focus:ring-secondary disabled:opacity-50 disabled:pointer-events-none"
                                              rows="3" placeholder="Your Message" required>{{ old('body') }}</textarea>
                                    @error('body')
                                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="xs:col-span-2 flex justify-end pt-[5px]">
                                    <button type="submit" id="submitBtn"
                                            class="w-full sm:w-auto py-3 px-4 cursor-pointer uppercase inline-flex items-center justify-center gap-x-2 text-sm font-normal rounded-lg border border-gray-200 bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-hidden focus:bg-gray-200 disabled:opacity-50 disabled:pointer-events-none">
                                        Submit
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="hero-container">
                <div class="contact-block">
                    <div class="contact-inner">
                        <div class="contact-left">
                            <h4>General Information</h4>
                        </div>
                        <div class="contact-right">
                            <div class="hero-title space-large">
                                <p>If you have any questions, feedback, or inquiries, we're
                                    here to help. Feel free to reach out to us via the contact information provided
                                    below.</p>
                                <a href="mailto:help@Semechnews.com" class="contact-link color-black">help@Semechnews.com</a>
                            </div>
                            <div class="hero-title space-large">
                                <p>Your feedback helps us improve our services and better
                                    serve our readers.</p>
                                <a href="mailto:info@Semechnews.com" class="contact-link color-black">info@Semechnews.com</a>
                            </div>
                        </div>
                    </div>
                    <div class="contact-inner">
                        <div class="contact-left">
                            <h4>Advertising Sales</h4>
                        </div>
                        <div class="contact-right">
                            <div class="hero-title space-large">
                                <p>For advertising inquiries and partnership opportunities,
                                    please reach out to our advertising sales team using the contact information
                                    provided below.</p>
                                <a href="mailto:sales@example.com"
                                   class="contact-link color-black">sales@example.com</a>
                            </div>
                        </div>
                    </div>
                    <div class="contact-inner">
                        <div class="contact-left">
                            <h4>Broadcast Operation</h4>
                        </div>
                        <div class="contact-right">
                            <div class="hero-title space-large">
                                <p>For broadcast inquiries and media partnerships, please
                                    contact our broadcast information team using the information provided below.</p>
                                <div class="contact-data">
                                    <div class="contact">
                                        <div class="color-black">Email:</div>
                                        <a href="mailto:broadcast@example.com" class="contact-link">broadcast@example.com</a>
                                    </div>
                                    <div class="contact">
                                        <div class="color-black">Phone:</div>
                                        <a href="tel:(316)555-0116" class="contact-link">(316) 555-0116</a>
                                    </div>
                                    <div class="contact">
                                        <div class="color-black">Fax:</div>
                                        <div>(808) 555-0111</div>
                                    </div>
                                </div>
                            </div>
                            <div class="hero-title">
                                <div class="color-black">Address:</div>
                                <div>4517 Washington Ave. <br/>Manchester, Kentucky 39495</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section pt-0">
            <div class="hero-container">
                <div>
                    <div class="section-title">
                        <h2>HAVE ANY QUESTIONS?</h2>
                    </div>
                    <div class="faq-wrap">
                        <div class="faq-outer">
                            <div class="space-y-3">
                                <div x-data="{ open: true }" class="faq border-b border-gray-200">
                                    <button
                                        @click="open = !open"
                                        class="py-3 inline-flex items-center justify-between gap-x-3 w-full font-semibold text-start text-gray-800 hover:text-gray-500 rounded-lg"
                                        :aria-expanded="open">
                                        <span>How do I submit a story or article?</span>
                                        <svg class="size-4 transition-transform duration-300"
                                             :class="{ 'rotate-180': open }"
                                             xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m6 9 6 6 6-6"></path>
                                        </svg>
                                    </button>
                                    <div
                                        x-show="open"
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                                        x-transition:enter-end="opacity-100 transform translate-y-0"
                                        x-transition:leave="transition ease-in duration-200"
                                        x-transition:leave-start="opacity-100 transform translate-y-0"
                                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                                        class="pb-3">
                                        <p class="text-body">
                                            You can submit your story or article by using our contact form above or by
                                            emailing us directly at info@Semechnews.com. Please include your name,
                                            contact information, and attach your submission in a Word document or PDF
                                            format. Our editorial team will review your submission and get back to you
                                            within 3-5 business days.
                                        </p>
                                    </div>
                                </div>

                                <div x-data="{ open: false }" class="faq border-b border-gray-200">
                                    <button
                                        @click="open = !open"
                                        class="py-3 inline-flex items-center justify-between gap-x-3 w-full font-semibold text-start text-gray-800 hover:text-gray-500 rounded-lg"
                                        :aria-expanded="open">
                                        <span>What is your response time for inquiries?</span>
                                        <svg class="size-4 transition-transform duration-300"
                                             :class="{ 'rotate-180': open }"
                                             xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m6 9 6 6 6-6"></path>
                                        </svg>
                                    </button>
                                    <div
                                        x-show="open"
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                                        x-transition:enter-end="opacity-100 transform translate-y-0"
                                        x-transition:leave="transition ease-in duration-200"
                                        x-transition:leave-start="opacity-100 transform translate-y-0"
                                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                                        class="pb-3">
                                        <p class="text-body">
                                            We strive to respond to all inquiries within 24 hours during business days
                                            (Monday-Friday). For urgent matters, please mark your message as "urgent" in
                                            the subject line. During weekends and holidays, response times may be
                                            slightly longer, but we'll get back to you as soon as possible.
                                        </p>
                                    </div>
                                </div>

                                <div x-data="{ open: false }" class="faq border-b border-gray-200">
                                    <button
                                        @click="open = !open"
                                        class="py-3 inline-flex items-center justify-between gap-x-3 w-full font-semibold text-start text-gray-800 hover:text-gray-500 rounded-lg"
                                        :aria-expanded="open">
                                        <span>Can I advertise on your platform?</span>
                                        <svg class="size-4 transition-transform duration-300"
                                             :class="{ 'rotate-180': open }"
                                             xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m6 9 6 6 6-6"></path>
                                        </svg>
                                    </button>
                                    <div
                                        x-show="open"
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                                        x-transition:enter-end="opacity-100 transform translate-y-0"
                                        x-transition:leave="transition ease-in duration-200"
                                        x-transition:leave-start="opacity-100 transform translate-y-0"
                                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                                        class="pb-3">
                                        <p class="text-body">
                                            Yes! We offer various advertising opportunities including banner ads,
                                            sponsored content, and newsletter placements. For detailed information about
                                            our advertising packages and rates, please contact our advertising sales
                                            team at sales@example.com. We'll provide you with a comprehensive media kit
                                            and discuss options that best fit your marketing goals.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Image -->
                        <div class="faq-img">
                            <img
                                src="https://cdn.prod.website-files.com/65f28c561f2b91ff16d74d11/664d736504c87173239d9916_faq-image.jpg"
                                loading="lazy" sizes="(max-width: 767px) 92vw, 46vw" alt="Faq Image"
                                class="cover-image"/>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-guest-layout>
