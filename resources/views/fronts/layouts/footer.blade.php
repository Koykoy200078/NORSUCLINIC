<footer class="bg-primary">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-6 order-1 order-lg-0">
                <h5 class="text-white mb-4 pb-1">{{ __('messages.web.contact_us') }}</h5>
                <div class="footer-info">
                    @if (!empty(getSettingValue('landline_no')))
                    <div class="d-flex align-items-center footer-info__block mb-3 pb-1">
                        <div class="footer-info__footer-icon fs-5 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-phone text-primary"></i>
                        </div>
                        <a href="tel:{{ getSettingValue('landline_no') }}"
                            class="text-decoration-none text-white footer-info__contact-label">
                            {{ getSettingValue('landline_no') }}
                        </a>
                    </div>
                    @endif

                    @if (!empty(getSettingValue('contact_no')))
                    <div class="d-flex align-items-center footer-info__block mb-3 pb-1">
                        <div class="footer-info__footer-icon fs-5 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-mobile text-primary"></i>
                        </div>
                        <a href="tel:+{{ getSettingValue('country_code') }} {{ getSettingValue('contact_no') }}"
                            class="text-decoration-none text-white footer-info__contact-label">
                            +{{ getSettingValue('country_code') }} {{ getSettingValue('contact_no') }}
                        </a>
                    </div>
                    @endif

                    @if (!empty(getSettingValue('email')))
                    <div class="d-flex align-items-center footer-info__block mb-3 pb-1">
                        <div class="footer-info__footer-icon fs-5 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-envelope text-primary"></i>
                        </div>
                        <a href="mailto:{{ getSettingValue('email') }}"
                            class="text-decoration-none text-white footer-info__contact-label">
                            {{ getSettingValue('email') }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            <div class="col-lg-4 col-md-6 order-2 order-lg-2">
                <h5 class="text-white mb-4 pb-1">{{ __('messages.web.quick_links') }}</h5>
                <ul>
                    <li>
                        <a href="{{ route('medicalAboutUs') }}"
                            class="text-decoration-none  mb-2 d-block fw-light {{ Request::is('medical-about-us*') ? 'text-black' : 'text-white' }}">{{ __('messages.web.about_us') }}</a>
                    </li>
                    <li>
                        <a href="{{ route('medicalContact') }}"
                            class="text-decoration-none  mb-2 d-block fw-light {{ Request::is('medical-contact*') ? 'text-black' : 'text-white' }}">{{ __('messages.web.contact_us') }}</a>
                    </li>
                    <li>
                        <a href="{{ route('terms.conditions') }}"
                            class="text-decoration-none mb-2 d-block fw-light {{ Request::is('terms-conditions*') ? 'text-black' : 'text-white' }}">{{ __('messages.terms_conditions') }}</a>
                    </li>
                    <li>
                        <a href="{{ route('privacy.policy') }}"
                            class="text-decoration-none mb-2 d-block fw-light {{ Request::is('privacy-policy*') ? 'text-black' : 'text-white' }}">{{ __('messages.privacy_policy') }}</a>
                    </li>
                </ul>
            </div>

            <div class="col-12 order-4 border-top-primary text-center mt-lg-5 mt-4">
                <p class="text-white fw-light py-4 mb-0">{{__('messages.web.all_rights_reserved')}} © {{ date('Y') }} {{ getAppName() }}</p>
            </div>
        </div>
    </div>
</footer>
