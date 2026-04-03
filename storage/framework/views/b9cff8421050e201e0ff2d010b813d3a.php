<footer class="bg-primary">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-6 order-1 order-lg-0">
                <h5 class="text-white mb-4 pb-1"><?php echo e(__('messages.web.contact_us')); ?></h5>
                <div class="footer-info">
                    <?php if(!empty(getSettingValue('landline_no'))): ?>
                    <div class="d-flex align-items-center footer-info__block mb-3 pb-1">
                        <div class="footer-info__footer-icon fs-5 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-phone text-primary"></i>
                        </div>
                        <a href="tel:<?php echo e(getSettingValue('landline_no')); ?>"
                            class="text-decoration-none text-white footer-info__contact-label">
                            <?php echo e(getSettingValue('landline_no')); ?>

                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if(!empty(getSettingValue('contact_no'))): ?>
                    <div class="d-flex align-items-center footer-info__block mb-3 pb-1">
                        <div class="footer-info__footer-icon fs-5 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-mobile text-primary"></i>
                        </div>
                        <a href="tel:+<?php echo e(getSettingValue('country_code')); ?> <?php echo e(getSettingValue('contact_no')); ?>"
                            class="text-decoration-none text-white footer-info__contact-label">
                            +<?php echo e(getSettingValue('country_code')); ?> <?php echo e(getSettingValue('contact_no')); ?>

                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if(!empty(getSettingValue('email'))): ?>
                    <div class="d-flex align-items-center footer-info__block mb-3 pb-1">
                        <div class="footer-info__footer-icon fs-5 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-envelope text-primary"></i>
                        </div>
                        <a href="mailto:<?php echo e(getSettingValue('email')); ?>"
                            class="text-decoration-none text-white footer-info__contact-label">
                            <?php echo e(getSettingValue('email')); ?>

                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 order-2 order-lg-2">
                <h5 class="text-white mb-4 pb-1"><?php echo e(__('messages.web.quick_links')); ?></h5>
                <ul>
                    <li>
                        <a href="<?php echo e(route('medicalAboutUs')); ?>"
                            class="text-decoration-none  mb-2 d-block fw-light <?php echo e(Request::is('medical-about-us*') ? 'text-black' : 'text-white'); ?>"><?php echo e(__('messages.web.about_us')); ?></a>
                    </li>
                    <li>
                        <a href="<?php echo e(route('medicalContact')); ?>"
                            class="text-decoration-none  mb-2 d-block fw-light <?php echo e(Request::is('medical-contact*') ? 'text-black' : 'text-white'); ?>"><?php echo e(__('messages.web.contact_us')); ?></a>
                    </li>
                    <li>
                        <a href="<?php echo e(route('terms.conditions')); ?>"
                            class="text-decoration-none mb-2 d-block fw-light <?php echo e(Request::is('terms-conditions*') ? 'text-black' : 'text-white'); ?>"><?php echo e(__('messages.terms_conditions')); ?></a>
                    </li>
                    <li>
                        <a href="<?php echo e(route('privacy.policy')); ?>"
                            class="text-decoration-none mb-2 d-block fw-light <?php echo e(Request::is('privacy-policy*') ? 'text-black' : 'text-white'); ?>"><?php echo e(__('messages.privacy_policy')); ?></a>
                    </li>
                </ul>
            </div>

            <div class="col-12 order-4 border-top-primary text-center mt-lg-5 mt-4">
                <p class="text-white fw-light py-4 mb-0"><?php echo e(__('messages.web.all_rights_reserved')); ?> © <?php echo e(date('Y')); ?> <?php echo e(getAppName()); ?></p>
            </div>
        </div>
    </div>
</footer><?php /**PATH C:\Projects\NORSUCLINIC\resources\views/fronts/layouts/footer.blade.php ENDPATH**/ ?>