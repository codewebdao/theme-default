<?php
if (!function_exists('__')) {
    load_helpers(['languages']);
}
\App\Libraries\Fastlang::load('Contact', defined('APP_LANG') ? APP_LANG : 'en');

$contact_api_url = base_url('api/v2/form/contact');

$contact_js_i18n = [
    'success_default'    => __('contact.js.success_default'),
    'err_generic'        => __('contact.js.err_generic'),
    'err_network_title'  => __('contact.js.err_network_title'),
    'err_network_body'   => __('contact.js.err_network_body'),
    'err_config_title'   => __('contact.js.err_config_title'),
    'err_config_body'    => __('contact.js.err_config_body'),
    'err_title_fallback' => __('contact.js.err_title_fallback'),
    'warn_title_fallback'=> __('contact.js.warn_title_fallback'),
];

$langContact = defined('APP_LANG') ? APP_LANG : '';
$contact_site_email = trim((string) (option('site_email', $langContact) ?: ''));
?>
<section class="py-12 sm:py-24 bg-gradient-to-b from-[#E8F4FD] to-white relative overflow-hidden">
    <div class="absolute inset-0 overflow-hidden sm:block hidden">
        <img src="<?php echo e(theme_assets('images/bannerFeatures.webp')); ?>" alt="" class="w-full h-full sm:h-auto object-cover" />
    </div>


    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <!-- Header -->
        <div class="">
          
            <h2 class="text-[32px] sm:text-[40px] lg:text-[48px] font-medium text-home-heading text-center font-space">
                <?php echo e(__('contact.hero_title')); ?>
            </h2>
            <div class="text-center text-[14px] sm:text-[16px] md:text-[18px] lg:text-[20px] leading-[21px] sm:leading-[24px] md:leading-[27px] lg:leading-[30px]
        font-normal text-home-body mb-12 sm:mb-12 md:mb-16 lg:mb-24 px-4">
                <?php echo e(__('contact.hero_subtitle')); ?>
            </div>
        </div>


        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8 md:gap-10 lg:gap-[40px] xl:gap-[60px] mx-auto">
            <!-- Left Column: Contact Options -->
            <div class="space-y-4 sm:space-y-12 space-y-8">
                <!-- Contact Options Card -->
                <div class="bg-white rounded-home-lg p-8 lg:p-6 xl:p-8 shadow-sm space-y-8">
                    <!-- Email Support -->
                    <div class="flex items-start gap-4  ">
                        <div class="w-12 h-12 rounded-home-md bg-home-surface-light flex items-center justify-center flex-shrink-0">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M22 7L13.009 12.727C12.7039 12.9042 12.3573 12.9976 12.0045 12.9976C11.6517 12.9976 11.3051 12.9042 11 12.727L2 7M4 4H20C21.1046 4 22 4.89543 22 6V18C22 19.1046 21.1046 20 20 20H4C2.89543 20 2 19.1046 2 18V6C2 4.89543 2.89543 4 4 4Z"
                                    stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-[18px] leading-[27px] text-home-heading mb-1 font-semibold font-plus"><?php echo e(__('contact.email_support_title')); ?></h3>
                            <?php if ($contact_site_email !== '') : ?>
                            <a href="mailto:<?php echo e($contact_site_email); ?>"
                                class="text-home-overflow text-sm hover:text-home-primary text-sm">
                                <?php echo e($contact_site_email); ?>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Community Forum -->
                    <div class="flex items-start gap-4 ">
                        <div class="w-12 h-12 rounded-home-md bg-home-surface-light flex items-center justify-center flex-shrink-0">
                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect width="48" height="48" rx="6" fill="var(--home-primary)" fill-opacity="0.1" />
                                <path
                                    d="M34 29C34 29.5304 33.7893 30.0391 33.4142 30.4142C33.0391 30.7893 32.5304 31 32 31H18.828C18.2976 31.0001 17.789 31.2109 17.414 31.586L15.212 33.788C15.1127 33.8873 14.9862 33.9549 14.8485 33.9823C14.7108 34.0097 14.568 33.9956 14.4383 33.9419C14.3086 33.8881 14.1977 33.7971 14.1197 33.6804C14.0417 33.5637 14 33.4264 14 33.286V17C14 16.4696 14.2107 15.9609 14.5858 15.5858C14.9609 15.2107 15.4696 15 16 15H32C32.5304 15 33.0391 15.2107 33.4142 15.5858C33.7893 15.9609 34 16.4696 34 17V29Z"
                                    stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>

                        </div>
                        <div>
                            <h3 class="text-[18px] leading-[27px] text-home-heading mb-1 font-semibold font-plus"><?php echo e(__('contact.community_forum_title')); ?></h3>
                            <a href="https://forum.laragon.net"
                                class="text-home-primary text-sm hover:text-home-primary text-sm">
                                forum.laragon.net
                            </a>
                        </div>
                    </div>

                    <!-- Report Issue -->
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-home-md bg-home-surface-light flex items-center justify-center flex-shrink-0">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M12 20V11M12 20C13.5913 20 15.1174 19.3679 16.2426 18.2426C17.3679 17.1174 18 15.5913 18 14V11C18 9.93913 17.5786 8.92172 16.8284 8.17157C16.0783 7.42143 15.0609 7 14 7H10C8.93913 7 7.92172 7.42143 7.17157 8.17157C6.42143 8.92172 6 9.93913 6 11V14C6 15.5913 6.63214 17.1174 7.75736 18.2426C8.88258 19.3679 10.4087 20 12 20ZM14.12 3.88L16 2M21 21C21.0012 19.9712 20.6059 18.9816 19.8964 18.2367C19.1868 17.4918 18.2176 17.0489 17.19 17M21 5C20.9989 5.98215 20.6364 6.92956 19.9818 7.66169C19.3271 8.39383 18.4259 8.85951 17.45 8.97M22 13H18M3 21C2.99884 19.9712 3.39409 18.9816 4.10362 18.2367C4.81315 17.4918 5.78241 17.0489 6.81 17M3 5C3.00113 5.98215 3.36357 6.92956 4.01825 7.66169C4.67293 8.39383 5.57408 8.85951 6.55 8.97M6 13H2M8 2L9.88 3.88M9 7.13V6C9 5.20435 9.31607 4.44129 9.87868 3.87868C10.4413 3.31607 11.2044 3 12 3C12.7956 3 13.5587 3.31607 14.1213 3.87868C14.6839 4.44129 15 5.20435 15 6V7.13"
                                    stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-[18px] leading-[27px] text-home-heading mb-1 font-semibold font-plus"><?php echo e(__('contact.report_issue_title')); ?></h3>
                            <a class="text-home-primary text-sm hover:text-home-primary text-sm">
                                <?php echo e(__('contact.report_issue_link')); ?>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- How to Report Issues Card -->
                <div class="bg-[#FFF7ED] rounded-home-lg  p-8 lg:p-6 xl:p-8 shadow-sm">
                    <div class="flex items-center gap-4 mb-4">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M12 16V12M12 8H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z"
                                stroke="#DE3232" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <h3 class="text-[18px] leading-[27px] font-semibold text-home-heading font-plus">
                            <?php echo e(__('contact.how_to_report_heading')); ?>
                        </h3>

                    </div>
                    <ul class="space-y-4 text-sm mt-4 sm:mt-6 md:mt-8">
                        <li class="flex items-center gap-2 font-plus">
                            <svg width="6" height="6" viewBox="0 0 6 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="3" cy="3" r="3" fill="#DB3D23" />
                            </svg>

                            <div class="text-home-body flex items-center gap-2 mt-2"><?php echo e(__('contact.how_to_report_li_1')); ?></div>
                        </li>
                        <li class="flex items-center gap-2 font-plus">
                            <svg width="6" height="6" viewBox="0 0 6 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="3" cy="3" r="3" fill="#DB3D23" />
                            </svg>

                            <div class="text-home-body flex items-center gap-2 mt-2"><?php echo e(__('contact.how_to_report_li_2')); ?></div>
                        </li>
                        <li class="flex items-center gap-2 font-plus">
                            <svg width="6" height="6" viewBox="0 0 6 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="3" cy="3" r="3" fill="#DB3D23" />
                            </svg>

                            <div class="text-home-body flex items-center gap-2 mt-2 font-plus"><?php echo e(__('contact.how_to_report_li_3')); ?></div>
                        </li>
                        <li class="flex items-center gap-2 font-plus">
                            <svg width="6" height="6" viewBox="0 0 6 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="3" cy="3" r="3" fill="#DB3D23" />
                            </svg>

                            <div class="text-home-body flex items-center gap-2 mt-2 font-plus"><?php echo e(__('contact.how_to_report_li_4')); ?></div>
                        </li>

                    </ul>
                </div>

                <!-- Response Time Card -->
                <div class="flex flex-col items-start gap-4 sm:gap-6 md:gap-8 self-stretch
                p-8 lg:p-6 xl:p-8 rounded-[12px] bg-white
                shadow-[0_2.667px_8px_0_rgba(43,140,238,0.05)]">

                    <div class="flex items-center gap-4">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M12 16V12M12 8H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z"
                                stroke="#DE3232" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>


                        <h3 class="text-[18px] leading-[27px] font-semibold text-home-heading font-plus">
                            <?php echo e(__('contact.response_time_heading')); ?>
                        </h3>
                    </div>

                    <p class="text-sm leading-[24px] text-home-body font-plus">
                        <?php echo e(__('contact.response_time_body')); ?>
                    </p>
                </div>

            </div>

            <!-- Right Column: Contact Form (mobile/tablet: full width, no container padding; lg+: normal) -->
            <div class="lg:col-span-2 w-screen relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] max-w-[100vw] box-border lg:w-auto lg:left-auto lg:right-auto lg:ml-0 lg:mr-0 lg:relative lg:max-w-none">
                <div class="bg-white rounded-home-lg pt-12 px-4 sm:px-6 md:px-8 lg:p-8 xl:p-12">
                    <div class="flex items-center gap-3 sm:gap-4 mb-6 sm:mb-8 md:mb-10 lg:mb-12">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M10.9134 13.0852C10.7223 12.8945 10.4945 12.7444 10.2438 12.644L2.31381 9.46399C2.21912 9.426 2.13833 9.35996 2.08226 9.27472C2.0262 9.18949 1.99755 9.08914 2.00016 8.98715C2.00278 8.88517 2.03652 8.78642 2.09688 8.70417C2.15723 8.62191 2.2413 8.56009 2.33781 8.527L21.3378 2.027C21.4264 1.99499 21.5223 1.98888 21.6143 2.00939C21.7062 2.02989 21.7904 2.07616 21.857 2.14277C21.9236 2.20939 21.9699 2.2936 21.9904 2.38555C22.0109 2.4775 22.0048 2.57339 21.9728 2.662L15.4728 21.662C15.4397 21.7585 15.3779 21.8426 15.2956 21.9029C15.2134 21.9633 15.1146 21.997 15.0126 21.9996C14.9107 22.0022 14.8103 21.9736 14.7251 21.9175C14.6398 21.8615 14.5738 21.7807 14.5358 21.686L11.3558 13.754C11.255 13.5035 11.1045 13.276 10.9134 13.0852ZM10.9134 13.0852L21.8538 2.147"
                                stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>

                        <h2 class="text-[24px] leading-[36px] font-semibold text-home-heading font-plus"><?php echo e(__('contact.form_card_title')); ?></h2>
                    </div>

                    <form id="contactForm" class="space-y-8" data-contact-api="<?php echo e($contact_api_url); ?>" novalidate>
                        <!-- Name -->
                        <div class="flex flex-col md:flex-row gap-8">
                            <!-- Name -->
                            <div class="w-full md:w-1/2">
                                <label for="name" class="block text-md font-plus font-medium text-home-heading mb-2 font-plus">
                                    <?php echo e(__('contact.field_name_label')); ?>
                                </label>

                                <div
                                    class="p-[1px] rounded-home-md bg-home-surface focus-within:bg-gradient-to-br focus-within:from-home-accent focus-within:to-home-primary transition">
                                    <input type="text" id="name" name="name" placeholder="<?php echo e(__('contact.field_name_placeholder')); ?>" class="w-full px-4 py-3 rounded-[7px]
                                               bg-gray-50 text-home-heading
                                               placeholder:text-[#9CA3AF]
                                               outline-none" required />
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="w-full md:w-1/2">
                                <label for="email" class="block text-md font-plus font-medium text-home-heading mb-2 font-plus">
                                    <?php echo e(__('contact.field_email_label')); ?>
                                </label>

                                <div class="p-[1px] rounded-home-md bg-home-surface
                                           focus-within:bg-gradient-to-br focus-within:from-home-accent focus-within:to-home-primary
                                           transition">
                                    <input type="email" id="email" name="email" placeholder="<?php echo e(__('contact.field_email_placeholder')); ?>" class="w-full px-4 py-3 rounded-[7px]
                                               bg-gray-50 text-home-heading
                                               placeholder:text-[#9CA3AF]
                                               outline-none" required />
                                </div>
                            </div>
                        </div>

                        <!-- Type of Inquiry -->
                        <div class="w-full overflow-hidden">
                            <label for="inquiry" class="block text-md font-plus text-home-heading mb-2 font-plus">
                                <?php echo e(__('contact.field_inquiry_label')); ?>
                            </label>

                            <div class="p-[1px] rounded-home-md bg-home-surface
                                       focus-within:bg-gradient-to-br focus-within:from-home-accent focus-within:to-home-primary
                                       transition relative overflow-hidden">
                                <select id="inquiry" name="inquiry" class="w-full max-w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-[7px]
                                           bg-gray-50 text-home-heading text-md font-plus
                                           outline-none appearance-none
                                           pr-8 sm:pr-10
                                           truncate" required>
                                    <option value="general"><?php echo e(__('contact.inquiry_general')); ?></option>
                                    <option value="bug"><?php echo e(__('contact.inquiry_bug')); ?></option>
                                    <option value="feature"><?php echo e(__('contact.inquiry_feature')); ?></option>
                                    <option value="feedback"><?php echo e(__('contact.inquiry_feedback')); ?></option>
                                    <option value="other"><?php echo e(__('contact.inquiry_other')); ?></option>
                                </select>
                                <!-- Custom dropdown arrow -->
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 sm:pr-4">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#9CA3AF]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>


                        <!-- Subject -->
                        <div>
                            <label for="subject" class="block text-md font-plus font-medium text-home-heading mb-2 font-plus">
                                <?php echo e(__('contact.field_subject_label')); ?>
                            </label>

                            <div class="p-[1px] rounded-home-md bg-home-surface
                                       focus-within:bg-gradient-to-br focus-within:from-home-accent focus-within:to-home-primary
                                       transition">
                                <input type="text" id="subject" name="subject" placeholder="<?php echo e(__('contact.field_subject_placeholder')); ?>"
                                    class="w-full px-4 py-3 rounded-[7px]
                                           bg-gray-50 text-home-heading
                                           placeholder:text-[#9CA3AF]
                                           outline-none" required />
                            </div>
                        </div>


                        <!-- Message -->
                        <div>
                            <label for="message" class="block text-md font-plus font-medium text-home-heading mb-2">
                                <?php echo e(__('contact.field_message_label')); ?>
                            </label>

                            <div class="p-[1px] rounded-home-md bg-home-surface
                                       focus-within:bg-gradient-to-br focus-within:from-home-accent focus-within:to-home-primary
                                       transition">
                                <textarea id="message" name="message" rows="6"
                                    placeholder="<?php echo e(__('contact.field_message_placeholder')); ?>" class="w-full px-4 py-3
                                           rounded-[7px]
                                           bg-gray-50 text-home-heading
                                           placeholder:text-[#9CA3AF]
                                           outline-none resize-none
                                           block" required></textarea>
                            </div>
                        </div>



                        <!-- Submit: đủ field → POST JSON api/v2/form/contact (lưu fast_contact_*) -->
                        <button type="submit" id="contactSubmitBtn" disabled class="
                            w-full px-6 sm:px-8 mt-12 mb-4 sm:mb-6 py-2.5 sm:py-3 text-[15px] sm:text-[17px] font-semibold rounded-home-md font-plus
                            flex items-center justify-center gap-2 transition-all
                            bg-home-primary text-white border border-transparent
                            disabled:opacity-50 disabled:cursor-not-allowed
                        ">
                            <svg width="26" height="26" viewBox="0 0 26 26" fill="none"
                                xmlns="http://www.w3.org/2000/svg" class="md:stroke-white stroke-[#FFFFFF]">
                                <path
                                    d="M11.6926 14.0198C11.4879 13.8154 11.2438 13.6546 10.9752 13.5471L2.4788 10.14C2.37735 10.0993 2.29079 10.0285 2.23072 9.93716C2.17065 9.84584 2.13995 9.73833 2.14275 9.62905C2.14555 9.51978 2.18171 9.41398 2.24638 9.32585C2.31104 9.23772 2.40112 9.17149 2.50451 9.13603L22.8617 2.17174C22.9566 2.13745 23.0593 2.13091 23.1578 2.15288C23.2564 2.17484 23.3466 2.22441 23.418 2.29579C23.4893 2.36716 23.5389 2.45739 23.5609 2.55591C23.5828 2.65443 23.5763 2.75716 23.542 2.8521L16.5777 23.2092C16.5423 23.3126 16.476 23.4027 16.3879 23.4674C16.2998 23.532 16.194 23.5682 16.0847 23.571C15.9754 23.5738 15.8679 23.5431 15.7766 23.483C15.6853 23.423 15.6145 23.3364 15.5738 23.235L12.1667 14.7364C12.0586 14.468 11.8974 14.2242 11.6926 14.0198Z"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>

                            <?php echo e(__('contact.submit')); ?>
                        </button>

                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="<?php echo e(theme_assets('js/notification.js')); ?>"></script>
<script>
var CONTACT_FORM_I18N = <?php echo json_encode($contact_js_i18n, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
(function () {
    var form = document.getElementById('contactForm');
    if (!form) return;
    var btn = document.getElementById('contactSubmitBtn');
    var nameEl = document.getElementById('name');
    var emailEl = document.getElementById('email');
    var inquiryEl = document.getElementById('inquiry');
    var subjectEl = document.getElementById('subject');
    var messageEl = document.getElementById('message');
    var apiUrl = form.getAttribute('data-contact-api') || '';
    if (!btn || !nameEl || !emailEl || !inquiryEl || !subjectEl || !messageEl) return;

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function clearNotices() {
        if (typeof FastNotice !== 'undefined' && FastNotice.clear) {
            FastNotice.clear();
        }
    }

    function notifySuccess(body, titleOpt) {
        if (typeof FastNotice === 'undefined') return;
        var opts = { duration: 4500, position: 'bottom-right' };
        if (titleOpt) {
            opts.title = esc(titleOpt);
        }
        FastNotice.success(esc(body || ' '), opts);
    }

    function notifyError(title, body) {
        if (typeof FastNotice === 'undefined') return;
        var L = window.CONTACT_FORM_I18N || {};
        FastNotice.error(esc(body || ' '), {
            title: esc(title || L.err_title_fallback || 'Error'),
            duration: 6000,
            position: 'bottom-right'
        });
    }

    function notifyWarning(title, body) {
        if (typeof FastNotice === 'undefined') return;
        var L = window.CONTACT_FORM_I18N || {};
        FastNotice.warning(esc(body || ' '), {
            title: esc(title || L.warn_title_fallback || 'Warning'),
            duration: 5500,
            position: 'bottom-right'
        });
    }

    function formatApiErrors(errors) {
        if (!errors || typeof errors !== 'object') return '';
        var parts = [];
        Object.keys(errors).forEach(function (k) {
            var v = errors[k];
            if (Array.isArray(v)) parts.push(v.join(' '));
            else if (typeof v === 'string') parts.push(v);
        });
        return parts.join(' ');
    }

    function isFilled() {
        return (nameEl.value || '').trim() !== ''
            && (emailEl.value || '').trim() !== ''
            && (subjectEl.value || '').trim() !== ''
            && (inquiryEl.value || '').trim() !== ''
            && (messageEl.value || '').trim() !== '';
    }

    function updateButton() {
        btn.disabled = !isFilled();
    }

    [nameEl, emailEl, inquiryEl, subjectEl, messageEl].forEach(function (el) {
        el.addEventListener('input', function () {
            clearNotices();
            updateButton();
        });
        el.addEventListener('change', function () {
            clearNotices();
            updateButton();
        });
    });
    updateButton();
    window.addEventListener('load', updateButton);

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearNotices();
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        if (!apiUrl) {
            var L0 = window.CONTACT_FORM_I18N || {};
            notifyError(L0.err_config_title || 'Configuration error', L0.err_config_body || '');
            return;
        }

        var payload = {
            name: (nameEl.value || '').trim(),
            email: (emailEl.value || '').trim(),
            inquiry: (inquiryEl.value || '').trim(),
            subject: (subjectEl.value || '').trim(),
            message: (messageEl.value || '').trim()
        };

        btn.disabled = true;
        btn.setAttribute('aria-busy', 'true');

        fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        })
            .then(function (res) {
                return res.text().then(function (text) {
                    var data = null;
                    try {
                        data = text ? JSON.parse(text) : null;
                    } catch (err) {
                        data = null;
                    }
                    return { ok: res.ok, status: res.status, data: data };
                });
            })
            .then(function (result) {
                btn.setAttribute('aria-busy', 'false');
                btn.disabled = false;
                updateButton();

                var d = result.data;
                if (result.ok && d && d.success) {
                    var L1 = window.CONTACT_FORM_I18N || {};
                    notifySuccess(d.message || L1.success_default || '');
                    form.reset();
                    updateButton();
                    return;
                }

                var L2 = window.CONTACT_FORM_I18N || {};
                var msg = (d && d.message) ? d.message : (L2.err_generic || '');
                var detail = (d && d.errors) ? formatApiErrors(d.errors) : '';
                if (result.status === 422) {
                    notifyWarning(msg, detail);
                } else {
                    notifyError(msg, detail);
                }
            })
            .catch(function () {
                btn.setAttribute('aria-busy', 'false');
                btn.disabled = false;
                updateButton();
                var L3 = window.CONTACT_FORM_I18N || {};
                notifyError(L3.err_network_title || '', L3.err_network_body || '');
            });
    });
})();
</script>