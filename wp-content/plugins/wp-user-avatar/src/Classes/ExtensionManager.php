<?php

namespace ProfilePress\Core\Classes;

use ProfilePress\Core\Membership\PaymentMethods\AbstractPaymentMethod;

class ExtensionManager
{
    const DB_OPTION_NAME = 'ppress_extension_manager';

    const EMAIL_CONFIRMATION = 'email_confirmation';
    const PAYPAL = 'paypal';
    const MOLLIE = 'mollie';
    const RAZORPAY = 'razorpay';
    const PAYSTACK = 'paystack';
    const RECEIPT = 'receipt';
    const JOIN_BUDDYPRESS_GROUPS = 'join_buddypress_groups';
    const BUDDYPRESS_SYNC = 'buddypress_sync';
    const MULTISITE = 'multisite';
    const WOOCOMMERCE = 'woocommerce';
    const AKISMET = 'akismet';
    const CAMPAIGN_MONITOR = 'campaign_monitor';
    const MAILCHIMP = 'mailchimp';
    const POLYLANG = 'polylang';
    const PASSWORDLESS_LOGIN = 'passwordless_login';
    const USER_MODERATION = 'user_moderation';
    const RECAPTCHA = 'recaptcha';
    const SOCIAL_LOGIN = 'social_login';
    const CUSTOM_FIELDS = 'custom_fields';
    const TWOFA = 'TWOFA';
    const METERED_PAYWALL = 'metered_paywall';
    const LEARNDASH = 'learndash';
    const TUTORLMS = 'tutorlms';
    const SENSEI_LMS = 'sensei_lms';
    const LIFTERLMS = 'lifterlms';
    const INVITATION_CODES = 'invitation_codes';

    public static function is_premium()
    {
        return class_exists('\ProfilePress\Libsodium\Libsodium') &&
               defined('PROFILEPRESS_PRO_DETACH_LIBSODIUM');
    }

    public static function class_map()
    {
        return [
            self::EMAIL_CONFIRMATION     => 'ProfilePress\Libsodium\EmailConfirmation',
            self::PAYPAL                 => 'ProfilePress\Libsodium\PayPal\Init',
            self::MOLLIE                 => 'ProfilePress\Libsodium\Mollie\Init',
            self::RAZORPAY               => 'ProfilePress\Libsodium\Razorpay\Init',
            self::PAYSTACK               => 'ProfilePress\Libsodium\Paystack\Init',
            self::RECEIPT                => 'ProfilePress\Libsodium\Receipt\Init',
            self::JOIN_BUDDYPRESS_GROUPS => 'ProfilePress\Libsodium\BuddyPressJoinGroupSelect\Init',
            self::BUDDYPRESS_SYNC        => 'ProfilePress\Libsodium\BuddyPressProfileSync',
            self::MULTISITE              => 'ProfilePress\Libsodium\MultisiteIntegration\Init',
            self::WOOCOMMERCE            => 'ProfilePress\Libsodium\MultisiteIntegration\Init',
            self::AKISMET                => 'ProfilePress\Libsodium\AkismetIntegration',
            self::CAMPAIGN_MONITOR       => 'ProfilePress\Libsodium\CampaignMonitorIntegration\Init',
            self::MAILCHIMP              => 'ProfilePress\Libsodium\MailchimpIntegration\Init',
            self::POLYLANG               => 'ProfilePress\Libsodium\PolylangIntegration',
            self::PASSWORDLESS_LOGIN     => 'ProfilePress\Libsodium\PasswordlessLogin',
            self::USER_MODERATION        => 'ProfilePress\Libsodium\UserModeration\UserModeration',
            self::RECAPTCHA              => 'ProfilePress\Libsodium\Recaptcha\Init',
            self::SOCIAL_LOGIN           => 'ProfilePress\Libsodium\SocialLogin\Init',
            self::CUSTOM_FIELDS          => 'ProfilePress\Libsodium\CustomProfileFields\Init',
            self::TWOFA                  => 'ProfilePress\Libsodium\TWOFA\Init',
            self::METERED_PAYWALL        => 'ProfilePress\Libsodium\MeteredPaywall\Init',
            self::LEARNDASH              => 'ProfilePress\Libsodium\Learndash\Init',
            self::TUTORLMS               => 'ProfilePress\Core\Integrations\TutorLMS\Init',
            self::SENSEI_LMS             => 'ProfilePress\Libsodium\SenseiLMS\Init',
            self::LIFTERLMS              => 'ProfilePress\Libsodium\LifterLMS',
            self::INVITATION_CODES       => 'ProfilePress\Libsodium\InvitationCodes\Init'
        ];
    }

    public static function available_extensions()
    {
        return apply_filters('ppress_available_extensions', [
            self::PAYPAL                 => [
                'title'       => esc_html__('PayPal', 'wp-user-avatar'),
                'url'         => 'https://profilepress.com/addons/paypal/?utm_source=liteplugin&utm_medium=extension-page&utm_campaign=learn-more',
                'setting_url' => AbstractPaymentMethod::get_payment_method_admin_page_url(self::PAYPAL),
                'description' => esc_html__('Accept payments and sell subscriptions via PayPal.', 'wp-user-avatar'),
                'icon'        => '<svg viewBox="0 0 576 512" xmlns="http://www.w3.org/2000/svg"><path d="m186.3 258.2c0 12.2-9.7 21.5-22 21.5-9.2 0-16-5.2-16-15 0-12.2 9.5-22 21.7-22 9.3 0 16.3 5.7 16.3 15.5zm-105.8-48.5h-4.7c-1.5 0-3 1-3.2 2.7l-4.3 26.7 8.2-.3c11 0 19.5-1.5 21.5-14.2 2.3-13.4-6.2-14.9-17.5-14.9zm284 0h-4.5c-1.8 0-3 1-3.2 2.7l-4.2 26.7 8-.3c13 0 22-3 22-18-.1-10.6-9.6-11.1-18.1-11.1zm211.5-129.7v352c0 26.5-21.5 48-48 48h-480c-26.5 0-48-21.5-48-48v-352c0-26.5 21.5-48 48-48h480c26.5 0 48 21.5 48 48zm-447.7 135.4c0-21-16.2-28-34.7-28h-40c-2.5 0-5 2-5.2 4.7l-16.4 102.1c-.3 2 1.2 4 3.2 4h19c2.7 0 5.2-2.9 5.5-5.7l4.5-26.6c1-7.2 13.2-4.7 18-4.7 28.6 0 46.1-17 46.1-45.8zm84.2 8.8h-19c-3.8 0-4 5.5-4.2 8.2-5.8-8.5-14.2-10-23.7-10-24.5 0-43.2 21.5-43.2 45.2 0 19.5 12.2 32.2 31.7 32.2 9 0 20.2-4.9 26.5-11.9-.5 1.5-1 4.7-1 6.2 0 2.3 1 4 3.2 4h17.2c2.7 0 5-2.9 5.5-5.7l10.2-64.3c.3-1.9-1.2-3.9-3.2-3.9zm40.5 97.9 63.7-92.6c.5-.5.5-1 .5-1.7 0-1.7-1.5-3.5-3.2-3.5h-19.2c-1.7 0-3.5 1-4.5 2.5l-26.5 39-11-37.5c-.8-2.2-3-4-5.5-4h-18.7c-1.7 0-3.2 1.8-3.2 3.5 0 1.2 19.5 56.8 21.2 62.1-2.7 3.8-20.5 28.6-20.5 31.6 0 1.8 1.5 3.2 3.2 3.2h19.2c1.8-.1 3.5-1.1 4.5-2.6zm159.3-106.7c0-21-16.2-28-34.7-28h-39.7c-2.7 0-5.2 2-5.5 4.7l-16.2 102c-.2 2 1.3 4 3.2 4h20.5c2 0 3.5-1.5 4-3.2l4.5-29c1-7.2 13.2-4.7 18-4.7 28.4 0 45.9-17 45.9-45.8zm84.2 8.8h-19c-3.8 0-4 5.5-4.3 8.2-5.5-8.5-14-10-23.7-10-24.5 0-43.2 21.5-43.2 45.2 0 19.5 12.2 32.2 31.7 32.2 9.3 0 20.5-4.9 26.5-11.9-.3 1.5-1 4.7-1 6.2 0 2.3 1 4 3.2 4h17.3c2.7 0 5-2.9 5.5-5.7l10.2-64.3c.3-1.9-1.2-3.9-3.2-3.9zm47.5-33.3c0-2-1.5-3.5-3.2-3.5h-18.5c-1.5 0-3 1.2-3.2 2.7l-16.2 104-.3.5c0 1.8 1.5 3.5 3.5 3.5h16.5c2.5 0 5-2.9 5.2-5.7l16.2-101.2zm-90 51.8c-12.2 0-21.7 9.7-21.7 22 0 9.7 7 15 16.2 15 12 0 21.7-9.2 21.7-21.5.1-9.8-6.9-15.5-16.2-15.5z"/></svg>',
            ],
            self::MOLLIE                 => [
                'title'       => esc_html__('Mollie', 'wp-user-avatar'),
                'url'         => 'https://profilepress.com/addons/mollie/?utm_source=liteplugin&utm_medium=extension-page&utm_campaign=learn-more',
                'setting_url' => AbstractPaymentMethod::get_payment_method_admin_page_url(self::MOLLIE),
                'description' => esc_html__('Accept payments and sell subscriptions via Mollie.', 'wp-user-avatar'),
                'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22"><g fill="none" class="nc-icon-wrapper"><path d="M22 11a11.05 11.05 0 0 1-.42 3.024C20.265 18.629 16.025 22 11 22 4.925 22 0 17.075 0 11S4.925 0 11 0c4.819 0 8.914 3.099 10.401 7.412C21.79 8.537 22 9.744 22 11z" fill="#000"/><path d="M17.787 6.609A5.129 5.129 0 0 1 19 9.937V16h-2.547V9.861c-.006-1.206-.953-2.191-2.105-2.191a2.5 2.5 0 0 0-.21.011c-1.03.11-1.895 1.137-1.895 2.244V16H9.696V9.879c-.005-1.213-.947-2.204-2.1-2.204-.066 0-.138.006-.21.012-1.025.111-1.894 1.136-1.894 2.25V16H3V9.861C3 7.18 5.077 5 7.624 5c1.274 0 2.487.56 3.362 1.533a4.521 4.521 0 0 1 3.788-1.516c1.163.105 2.232.67 3.013 1.592z" fill="#fff"/></g></svg>'
            ],
            self::RAZORPAY               => [
                'title'       => esc_html__('Razorpay', 'wp-user-avatar'),
                'url'         => 'https://profilepress.com/addons/razorpay/?utm_source=liteplugin&utm_medium=extension-page&utm_campaign=learn-more',
                'setting_url' => AbstractPaymentMethod::get_payment_method_admin_page_url(self::RAZORPAY),
                'description' => esc_html__('Accept payments and sell subscriptions via Razorpay.', 'wp-user-avatar'),
                'icon'        => '<svg viewBox="0 0 576 512" xmlns="http://www.w3.org/2000/svg"><path d="m64 32c-35.3 0-64 28.7-64 64v32h576v-32c0-35.3-28.7-64-64-64zm512 192h-576v192c0 35.3 28.7 64 64 64h448c35.3 0 64-28.7 64-64zm-464 128h64c8.8 0 16 7.2 16 16s-7.2 16-16 16h-64c-8.8 0-16-7.2-16-16s7.2-16 16-16zm112 16c0-8.8 7.2-16 16-16h128c8.8 0 16 7.2 16 16s-7.2 16-16 16h-128c-8.8 0-16-7.2-16-16z"/></svg>'
            ],
            self::PAYSTACK               => [
                'title'       => esc_html__('Paystack', 'wp-user-avatar'),
                'url'         => 'https://profilepress.com/addons/paystack/?utm_source=liteplugin&utm_medium=extension-page&utm_campaign=learn-more',
                'setting_url' => AbstractPaymentMethod::get_payment_method_admin_page_url(self::PAYSTACK),
                'description' => esc_html__('Accept payments and sell subscriptions via Paystack.', 'wp-user-avatar'),
                'icon'        => '<svg viewBox="0 0 576 512" xmlns="http://www.w3.org/2000/svg"><path d="m64 32c-35.3 0-64 28.7-64 64v32h576v-32c0-35.3-28.7-64-64-64zm512 192h-576v192c0 35.3 28.7 64 64 64h448c35.3 0 64-28.7 64-64zm-464 128h64c8.8 0 16 7.2 16 16s-7.2 16-16 16h-64c-8.8 0-16-7.2-16-16s7.2-16 16-16zm112 16c0-8.8 7.2-16 16-16h128c8.8 0 16 7.2 16 16s-7.2 16-16 16h-128c-8.8 0-16-7.2-16-16z"/></svg>'
            ],
            self::RECEIPT                => [
                'title'       => esc_html__('Receipt', 'wp-user-avatar'),
                'url'         => 'https://profilepress.com/addons/receipt/?utm_source=liteplugin&utm_medium=extension-page&utm_campaign=learn-more',
                'setting_url' => add_query_arg(['view' => 'payments', 'section' => 'settings'], PPRESS_SETTINGS_SETTING_PAGE) . '#receipt_disable_free_order_row',
                'description' => esc_html__('Allow customers to view, print and download as PDF, the receipt of their orders.', 'wp-user-avatar'),
                'icon'        => '<svg viewBox="0 0 384 512" xmlns="http://www.w3.org/2000/svg"><path d="m14 2.2c8.5-3.9 18.5-2.5 25.6 3.6l40.4 34.6 40.4-34.6c9-7.7 22.3-7.7 31.2 0l40.4 34.6 40.4-34.6c9-7.7 22.2-7.7 31.2 0l40.4 34.6 40.4-34.6c7.1-6.1 17.1-7.5 25.6-3.6s14 12.4 14 21.8v464c0 9.4-5.5 17.9-14 21.8s-18.5 2.5-25.6-3.6l-40.4-34.6-40.4 34.6c-9 7.7-22.2 7.7-31.2 0l-40.4-34.6-40.4 34.6c-9 7.7-22.3 7.7-31.2 0l-40.4-34.6-40.4 34.6c-7.1 6.1-17.1 7.5-25.6 3.6s-14-12.4-14-21.8v-464c0-9.4 5.5-17.9 14-21.8zm82 141.8c-8.8 0-16 7.2-16 16s7.2 16 16 16h192c8.8 0 16-7.2 16-16s-7.2-16-16-16zm-16 208c0 8.8 7.2 16 16 16h192c8.8 0 16-7.2 16-16s-7.2-16-16-16h-192c-8.8 0-16 7.2-16 16zm16-112c-8.8 0-16 7.2-16 16s7.2 16 16 16h192c8.8 0 16-7.2 16-16s-7.2-16-16-16z"/></svg>'
            ],
            self::CUSTOM_FIELDS          => [
                'title'       => esc_html__('Custom Fields', 'wp-user-avatar'),
                'url'         => 'https://profilepress.com/addons/custom-fields/?utm_source=liteplugin&utm_medium=extension-page&utm_campaign=learn-more',
                'setting_url' => PPRESS_CUSTOM_FIELDS_SETTINGS_PAGE,
                'description' => esc_html__('Collect unlimited additional information from users besides the standard profile data.', 'wp-user-avatar'),
                'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M567.938 243.908L462.25 85.374A48.003 48.003 0 0 0 422.311 64H153.689a48 48 0 0 0-39.938 21.374L8.062 243.908A47.994 47.994 0 0 0 0 270.533V400c0 26.51 21.49 48 48 48h480c26.51 0 48-21.49 48-48V270.533a47.994 47.994 0 0 0-8.062-26.625zM162.252 128h251.497l85.333 128H376l-32 64H232l-32-64H76.918l85.334-128z"></path></svg>'
            ],
            self::EMAIL_CONFIRMATION     => [
                'title'       => esc_html__('Email Confirmation', 'wp-user-avatar'),
                'url'         => 'https://profilepress.com/addons/email-confirmation/?utm_source=liteplugin&utm_medium=extension-page&utm_campaign=learn-more',
                'setting_url' => PPRESS_SETTINGS_SETTING_GENERAL_PAGE . '#pp_ec_settings',
                'description' => esc_html__('Ensure newly registered users confirm their email addresses before they can log in.', 'wp-user-avatar'),
                'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M567.938 243.908L462.25 85.374A48.003 48.003 0 0 0 422.311 64H153.689a48 48 0 0 0-39.938 21.374L8.062 243.908A47.994 47.994 0 0 0 0 270.533V400c0 26.51 21.49 48 48 48h480c26.51 0 48-21.49 48-48V270.533a47.994 47.994 0 0 0-8.062-26.625zM162.252 128h251.497l85.333 128H376l-32 64H232l-32-64H76.918l85.334-128z"></path></svg>'
            ],
            self::TWOFA                  => [
                'title'       => esc_html__('Two-Factor Authentication (2FA)', 'wp-user-avatar'),
                'url'         => 'https://profilepress.com/addons/2fa/?utm_source=liteplugin&utm_medium=extension-page&utm_campaign=learn-more',
                'setting_url' => PPRESS_SETTINGS_SETTING_GENERAL_PAGE . '#pp_2fa_settings',
                'description' => esc_html__('Adds an additional layer of security to users accounts by requiring more than just a password to log in.', 'wp-user-avatar'),
                'icon'        => '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAABmJLR0QA/wD/AP+gvaeTAAACPElEQVRoge3Zz4tNYRzH8dcMIk0pViI1TZqIQn6NmR0rLPz6A6wsrGZsxJZkRU2zslLKRjOsbGQhaiwQSZkUsiPSRH6UsHjO7R63e++ce+4591y3865v5+n58X2eT8/v81BSUlLyP9GXstxynMJuLM2oLT/xEJfwLSOfTVkcVfgnJ5uN6sidIzmKqNihVhuVRvnGWHgWV1P4qMdxjEThTbjVSuE0QuJl5nAlhY96jKgKabld/Rk1onDynlR7sT1h3s2x8ChON8k7g1fxiLyFHMR4inL7ImvEnBoh5dBKwXNhlUvLUaxqlNhJIXcx0Ub5nZoI6ZmhVYSQPmzFiljcALZhUSxuLdYndVqEkCk8wQtBzDI8xWPVU8IevMZLHEvitAgho9F3DQaxGkNR3Fj03YUlQvvGJKCTk73CWVzAPTwTDokXsR/nojzXhH1kAJNJnBYh5HZkcc5EVuEjDrTitFy1uo1OChlXvTjdFJbhE/gq2WVrSzPnRcwRwop1X3UFa5uihOzI2mHeQ+u8sEcMReF6XMcGTMfiJmPl6tmdWid598inyMS+Fd7jpHBJgi+xtM/Czp6YolatG8IPhpmFMial03Pkg9AL0wtlbJVOCnkg/EqqHWKZ0Ekhj/J03jM7e1490u/fi1OcefzOusK8hKzDmwZpg3ibdYU9M7R6RkheQ+sdVjZIm8+jwjRCfsXCw8JRPAuGG9SRG4d14UNPGrry6a2dx9AJ4WEmq8fQH4KIy/iekc+SkpKSLuUvcZyz9d129/cAAAAASUVORK5CYII="/>'
            ],
            self::USER_MODERATION        => [
                'title'       => esc_html__('User Moderation', 'wp-user-avatar'),
                'url'         => 'https://profilepress.com/addons/user-moderation/?utm_source=liteplugin&utm_medium=extension-page&utm_campaign=learn-more',
                'setting_url' => PPRESS_SETTINGS_SETTING_GENERAL_PAGE . '#user_moderation',
                'description' => esc_html__('Decide whether to approve newly registered users or not. You can also block and unblock users at any time.', 'wp-user-avatar'),
                'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path fill="currentColor" d="M622.3 271.1l-115.2-45c-4.1-1.6-12.6-3.7-22.2 0l-115.2 45c-10.7 4.2-17.7 14-17.7 24.9 0 111.6 68.7 188.8 132.9 213.9 9.6 3.7 18 1.6 22.2 0C558.4 489.9 640 420.5 640 296c0-10.9-7-20.7-17.7-24.9zM496 462.4V273.3l95.5 37.3c-5.6 87.1-60.9 135.4-95.5 151.8zM224 256c70.7 0 128-57.3 128-128S294.7 0 224 0 96 57.3 96 128s57.3 128 128 128zm96 40c0-2.5.8-4.8 1.1-7.2-2.5-.1-4.9-.8-7.5-.8h-16.7c-22.2 10.2-46.9 16-72.9 16s-50.6-5.8-72.9-16h-16.7C60.2 288 0 348.2 0 422.4V464c0 26.5 21.5 48 48 48h352c6.8 0 13.3-1.5 19.2-4-54-42.9-99.2-116.7-99.2-212z"></path></svg>'
            ],
            self::SOCIAL_LOGIN           => [
                'title'       => esc_html__('Social Login', 'wp-user-avatar'),
                'url'         => 'https://profilepress.com/addons/social-login/?utm_source=liteplugin&utm_medium=extension-page&utm_campaign=learn-more',
                'setting_url' => PPRESS_SETTINGS_SETTING_GENERAL_PAGE . '#social_login_settings',
                'description' => esc_html__('Let users easily register/login to your site using their social network accounts (Facebook, Twitter, Google, LinkedIn, Yahoo, Microsoft, Amazon, GitHub, WordPress.com, VK).', 'wp-user-avatar'),
                'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path fill="currentColor" d="M96 224c35.3 0 64-28.7 64-64s-28.7-64-64-64-64 28.7-64 64 28.7 64 64 64zm448 0c35.3 0 64-28.7 64-64s-28.7-64-64-64-64 28.7-64 64 28.7 64 64 64zm32 32h-64c-17.6 0-33.5 7.1-45.1 18.6 40.3 22.1 68.9 62 75.1 109.4h66c17.7 0 32-14.3 32-32v-32c0-35.3-28.7-64-64-64zm-256 0c61.9 0 112-50.1 112-112S381.9 32 320 32 208 82.1 208 144s50.1 112 112 112zm76.8 32h-8.3c-20.8 10-43.9 16-68.5 16s-47.6-6-68.5-16h-8.3C179.6 288 128 339.6 128 403.2V432c0 26.5 21.5 48 48 48h288c26.5 0 48-21.5 48-48v-28.8c0-63.6-51.6-115.2-115.2-115.2zm-223.7-13.4C161.5 263.1 145.6 256 128 256H64c-35.3 0-64 28.7-64 64v32c0 17.7 14.3 32 32 32h65.9c6.3-47.4 34.9-87.3 75.2-109.4z"></path></svg>'
            ],
            self::PASSWORDLESS_LOGIN     => [
                'title'       => esc_html__('Passwordless Login', 'wp-user-avatar'),
                'url'         => 'https://profilepress.com/addons/passwordless-login/?utm_source=liteplugin&utm_medium=extension-page&utm_campaign=learn-more',
                'setting_url' => PPRESS_SETTINGS_SETTING_GENERAL_PAGE . '#login_settings?passwordless_disable_admin_row',
                'description' => esc_html__('Let users log in to your website via a one-time URL sent to their email addresses.', 'wp-user-avatar'),
                'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M416 448h-84c-6.6 0-12-5.4-12-12v-40c0-6.6 5.4-12 12-12h84c17.7 0 32-14.3 32-32V160c0-17.7-14.3-32-32-32h-84c-6.6 0-12-5.4-12-12V76c0-6.6 5.4-12 12-12h84c53 0 96 43 96 96v192c0 53-43 96-96 96zm-47-201L201 79c-15-15-41-4.5-41 17v96H24c-13.3 0-24 10.7-24 24v96c0 13.3 10.7 24 24 24h136v96c0 21.5 26 32 41 17l168-168c9.3-9.4 9.3-24.6 0-34z"></path></svg>'
            ],
            self::RECAPTCHA              => [
                'title'       => esc_html__('Google reCAPTCHA', 'wp-user-avatar'),
                'url'         => 'https://profilepress.com/addons/recaptcha/?utm_source=liteplugin&utm_medium=extension-page&utm_campaign=learn-more',
                'setting_url' => PPRESS_SETTINGS_SETTING_GENERAL_PAGE . '#recaptcha',
                'description' => esc_html__('Protect your forms against spam and bot attacks.', 'wp-user-avatar'),
                'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M224,192a16,16,0,1,0,16,16A16,16,0,0,0,224,192ZM466.5,83.68l-192-80A57.4,57.4,0,0,0,256.05,0a57.4,57.4,0,0,0-18.46,3.67l-192,80A47.93,47.93,0,0,0,16,128C16,326.5,130.5,463.72,237.5,508.32a48.09,48.09,0,0,0,36.91,0C360.09,472.61,496,349.3,496,128A48,48,0,0,0,466.5,83.68ZM384,256H371.88c-28.51,0-42.79,34.47-22.63,54.63l8.58,8.57a16,16,0,1,1-22.63,22.63l-8.57-8.58C306.47,313.09,272,327.37,272,355.88V368a16,16,0,0,1-32,0V355.88c0-28.51-34.47-42.79-54.63-22.63l-8.57,8.58a16,16,0,0,1-22.63-22.63l8.58-8.57c20.16-20.16,5.88-54.63-22.63-54.63H128a16,16,0,0,1,0-32h12.12c28.51,0,42.79-34.47,22.63-54.63l-8.58-8.57a16,16,0,0,1,22.63-22.63l8.57,8.58c20.16,20.16,54.63,5.88,54.63-22.63V112a16,16,0,0,1,32,0v12.12c0,28.51,34.47,42.79,54.63,22.63l8.57-8.58a16,16,0,0,1,22.63,22.63l-8.58,8.57C329.09,189.53,343.37,224,371.88,224H384a16,16,0,0,1,0,32Zm-96,0a16,16,0,1,0,16,16A16,16,0,0,0,288,256Z"></path></svg>'
            ],
            self::METERED_PAYWALL        => [
                'title'       => esc_html__('Metered Paywall', 'wp-user-avatar'),
                'url'         => 'https://profilepress.com/addons/metered-paywall/?utm_source=liteplugin&utm_medium=extension-page&utm_campaign=learn-more',
                'setting_url' => add_query_arg('view', 'metered-paywall', PPRESS_CONTENT_PROTECTION_SETTINGS_PAGE),
                'description' => esc_html__('Let guest and visitors view limited number of restricted content.', 'wp-user-avatar'),
                'icon'        => '<span class="dashicons dashicons-welcome-view-site"></span>'
            ],
            self::MULTISITE              => [
                'title'        => esc_html__('Site Creation', 'wp-user-avatar'),
                'url'          => 'https://profilepress.com/addons/site-creation/?utm_source=liteplugin&utm_medium=extension-page&utm_campaign=learn-more',
                'description'  => esc_html__('Allow users to create new sites on a multisite network via a registration form powered by ProfilePress.', 'wp-user-avatar'),
                'icon'         => '<span class="dashicons dashicons-networking"></span>',
                'is_available' => function () {
                    return is_multisite() ? true : esc_html__('This is not a multisite installation', 'wp-user-avatar');
                }
            ],
            self::INVITATION_CODES       => [
                'title'       => esc_html__('Invite Codes', 'wp-user-avatar'),
                'url'         => 'https://profilepress.com/addons/invite-codes/?utm_source=liteplugin&utm_medium=extension-page&utm_campaign=learn-more',
                'description' => esc_html__('Restrict WordPress registration to only users with invite codes.', 'wp-user-avatar'),
                'icon'        => '<svg viewBox="0 0 640 512" xmlns="http://www.w3.org/2000/svg"><path d="m610.5 341.3c2.6-14.1 2.6-28.5 0-42.6l25.8-14.9c3-1.7 4.3-5.2 3.3-8.5-6.7-21.6-18.2-41.2-33.2-57.4-2.3-2.5-6-3.1-9-1.4l-25.8 14.9c-10.9-9.3-23.4-16.5-36.9-21.3v-29.8c0-3.4-2.4-6.4-5.7-7.1-22.3-5-45-4.8-66.2 0-3.3.7-5.7 3.7-5.7 7.1v29.8c-13.5 4.8-26 12-36.9 21.3l-25.8-14.9c-2.9-1.7-6.7-1.1-9 1.4-15 16.2-26.5 35.8-33.2 57.4-1 3.3.4 6.8 3.3 8.5l25.8 14.9c-2.6 14.1-2.6 28.5 0 42.6l-25.8 14.9c-3 1.7-4.3 5.2-3.3 8.5 6.7 21.6 18.2 41.1 33.2 57.4 2.3 2.5 6 3.1 9 1.4l25.8-14.9c10.9 9.3 23.4 16.5 36.9 21.3v29.8c0 3.4 2.4 6.4 5.7 7.1 22.3 5 45 4.8 66.2 0 3.3-.7 5.7-3.7 5.7-7.1v-29.8c13.5-4.8 26-12 36.9-21.3l25.8 14.9c2.9 1.7 6.7 1.1 9-1.4 15-16.2 26.5-35.8 33.2-57.4 1-3.3-.4-6.8-3.3-8.5zm-114.5 27.2c-26.8 0-48.5-21.8-48.5-48.5s21.8-48.5 48.5-48.5 48.5 21.8 48.5 48.5-21.7 48.5-48.5 48.5zm-400-144.5c35.3 0 64-28.7 64-64s-28.7-64-64-64-64 28.7-64 64 28.7 64 64 64zm224 32c1.9 0 3.7-.5 5.6-.6 8.3-21.7 20.5-42.1 36.3-59.2 7.4-8 17.9-12.6 28.9-12.6 6.9 0 13.7 1.8 19.6 5.3l7.9 4.6c.8-.5 1.6-.9 2.4-1.4 7-14.6 11.2-30.8 11.2-48 0-61.9-50.1-112-112-112s-111.9 50-111.9 111.9 50.1 112 112 112zm105.2 194.5c-2.3-1.2-4.6-2.6-6.8-3.9-8.2 4.8-15.3 9.8-27.5 9.8-10.9 0-21.4-4.6-28.9-12.6-18.3-19.8-32.3-43.9-40.2-69.6-10.7-34.5 24.9-49.7 25.8-50.3-.1-2.6-.1-5.2 0-7.8l-7.9-4.6c-3.8-2.2-7-5-9.8-8.1-3.3.2-6.5.6-9.8.6-24.6 0-47.6-6-68.5-16h-8.3c-63.7 0-115.3 51.6-115.3 115.2v28.8c0 26.5 21.5 48 48 48h255.4c-3.7-6-6.2-12.8-6.2-20.3zm-252.1-175.9c-11.6-11.5-27.5-18.6-45.1-18.6h-64c-35.3 0-64 28.7-64 64v32c0 17.7 14.3 32 32 32h65.9c6.3-47.4 34.9-87.3 75.2-109.4z"/></svg>',
                'setting_url' => add_query_arg('view', 'invite-codes', PPRESS_SETTINGS_SETTING_PAGE)
            ],
            self::MAILCHIMP              => [
                'title'       => esc_html__('Mailchimp', 'wp-user-avatar'),
                'url'         => 'https://profilepress.com/addons/mailchimp/?utm_source=liteplugin&utm_medium=extension-page&utm_campaign=learn-more',
                'setting_url' => add_query_arg(['view' => 'integrations', 'section' => 'mailchimp'], PPRESS_SETTINGS_SETTING_PAGE),
                'description' => esc_html__('Subscribe members to your Mailchimp audiences when they register or subscribe to a membership plan. It can also automatically sync membership and profile changes with Mailchimp.', 'wp-user-avatar'),
                'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M330.61 243.52a36.15 36.15 0 0 1 9.3 0c1.66-3.83 1.95-10.43.45-17.61-2.23-10.67-5.25-17.14-11.48-16.13s-6.47 8.74-4.24 19.42c1.26 6 3.49 11.14 6 14.32zM277.05 252c4.47 2 7.2 3.26 8.28 2.13 1.89-1.94-3.48-9.39-12.12-13.09a31.44 31.44 0 0 0-30.61 3.68c-3 2.18-5.81 5.22-5.41 7.06.85 3.74 10-2.71 22.6-3.48 7-.44 12.8 1.75 17.26 3.71zm-9 5.13c-9.07 1.42-15 6.53-13.47 10.1.9.34 1.17.81 5.21-.81a37 37 0 0 1 18.72-1.95c2.92.34 4.31.52 4.94-.49 1.46-2.22-5.71-8-15.39-6.85zm54.17 17.1c3.38-6.87-10.9-13.93-14.3-7s10.92 13.88 14.32 6.97zm15.66-20.47c-7.66-.13-7.95 15.8-.26 15.93s7.98-15.81.28-15.96zm-218.79 78.9c-1.32.31-6 1.45-8.47-2.35-5.2-8 11.11-20.38 3-35.77-9.1-17.47-27.82-13.54-35.05-5.54-8.71 9.6-8.72 23.54-5 24.08 4.27.57 4.08-6.47 7.38-11.63a12.83 12.83 0 0 1 17.85-3.72c11.59 7.59 1.37 17.76 2.28 28.62 1.39 16.68 18.42 16.37 21.58 9a2.08 2.08 0 0 0-.2-2.33c.03.89.68-1.3-3.35-.39zm299.72-17.07c-3.35-11.73-2.57-9.22-6.78-20.52 2.45-3.67 15.29-24-3.07-43.25-10.4-10.92-33.9-16.54-41.1-18.54-1.5-11.39 4.65-58.7-21.52-83 20.79-21.55 33.76-45.29 33.73-65.65-.06-39.16-48.15-51-107.42-26.47l-12.55 5.33c-.06-.05-22.71-22.27-23.05-22.57C169.5-18-41.77 216.81 25.78 273.85l14.76 12.51a72.49 72.49 0 0 0-4.1 33.5c3.36 33.4 36 60.42 67.53 60.38 57.73 133.06 267.9 133.28 322.29 3 1.74-4.47 9.11-24.61 9.11-42.38s-10.09-25.27-16.53-25.27zm-316 48.16c-22.82-.61-47.46-21.15-49.91-45.51-6.17-61.31 74.26-75.27 84-12.33 4.54 29.64-4.67 58.49-34.12 57.81zM84.3 249.55C69.14 252.5 55.78 261.09 47.6 273c-4.88-4.07-14-12-15.59-15-13.01-24.85 14.24-73 33.3-100.21C112.42 90.56 186.19 39.68 220.36 48.91c5.55 1.57 23.94 22.89 23.94 22.89s-34.15 18.94-65.8 45.35c-42.66 32.85-74.89 80.59-94.2 132.4zM323.18 350.7s-35.74 5.3-69.51-7.07c6.21-20.16 27 6.1 96.4-13.81 15.29-4.38 35.37-13 51-25.35a102.85 102.85 0 0 1 7.12 24.28c3.66-.66 14.25-.52 11.44 18.1-3.29 19.87-11.73 36-25.93 50.84A106.86 106.86 0 0 1 362.55 421a132.45 132.45 0 0 1-20.34 8.58c-53.51 17.48-108.3-1.74-126-43a66.33 66.33 0 0 1-3.55-9.74c-7.53-27.2-1.14-59.83 18.84-80.37 1.23-1.31 2.48-2.85 2.48-4.79a8.45 8.45 0 0 0-1.92-4.54c-7-10.13-31.19-27.4-26.33-60.83 3.5-24 24.49-40.91 44.07-39.91l5 .29c8.48.5 15.89 1.59 22.88 1.88 11.69.5 22.2-1.19 34.64-11.56 4.2-3.5 7.57-6.54 13.26-7.51a17.45 17.45 0 0 1 13.6 2.24c10 6.64 11.4 22.73 11.92 34.49.29 6.72 1.1 23 1.38 27.63.63 10.67 3.43 12.17 9.11 14 3.19 1.05 6.15 1.83 10.51 3.06 13.21 3.71 21 7.48 26 12.31a16.38 16.38 0 0 1 4.74 9.29c1.56 11.37-8.82 25.4-36.31 38.16-46.71 21.68-93.68 14.45-100.48 13.68-20.15-2.71-31.63 23.32-19.55 41.15 22.64 33.41 122.4 20 151.37-21.35.69-1 .12-1.59-.73-1-41.77 28.58-97.06 38.21-128.46 26-4.77-1.85-14.73-6.44-15.94-16.67 43.6 13.49 71 .74 71 .74s2.03-2.79-.56-2.53zm-68.47-5.7zm-83.4-187.5c16.74-19.35 37.36-36.18 55.83-45.63a.73.73 0 0 1 1 1c-1.46 2.66-4.29 8.34-5.19 12.65a.75.75 0 0 0 1.16.79c11.49-7.83 31.48-16.22 49-17.3a.77.77 0 0 1 .52 1.38 41.86 41.86 0 0 0-7.71 7.74.75.75 0 0 0 .59 1.19c12.31.09 29.66 4.4 41 10.74.76.43.22 1.91-.64 1.72-69.55-15.94-123.08 18.53-134.5 26.83a.76.76 0 0 1-1-1.12z"></path></svg>'
            ],
            self::CAMPAIGN_MONITOR       => [
                'title'       => esc_html__('Campaign Monitor', 'wp-user-avatar'),
                'url'         => 'https://profilepress.com/addons/campaign-monitor/?utm_source=liteplugin&utm_medium=extension-page&utm_campaign=learn-more',
                'setting_url' => add_query_arg(['view' => 'integrations', 'section' => 'campaign-monitor'], PPRESS_SETTINGS_SETTING_PAGE),
                'description' => esc_html__('Subscribe members to your Campaign Monitor lists when they register or subscribe to a membership plan. It can also automatically sync membership and profile changes with Campaign Monitor.', 'wp-user-avatar'),
                'icon'        => '<span class="dashicons dashicons-email"></span>'
            ],
            self::WOOCOMMERCE            => [
                'title'        => esc_html__('WooCommerce', 'wp-user-avatar'),
                'url'          => 'https://profilepress.com/addons/woocommerce/?utm_source=liteplugin&utm_medium=extension-page&utm_campaign=learn-more',
                'setting_url'  => PPRESS_SETTINGS_SETTING_GENERAL_PAGE . '#pp_wi_settings',
                'description'  => esc_html__('Create WooCommerce membership sites, members-only discounts and stores, manage WooCommerce billing and shipping fields, replaces WooCommerce login and edit account forms in checkout and “My Account” pages with that of ProfilePress.', 'wp-user-avatar'),
                'icon'         => '<span class="dashicons dashicons-cart"></span>',
                'is_available' => function () {
                    return class_exists('WooCommerce') ? true : esc_html__('WooCommerce is not active', 'wp-user-avatar');
                }
            ],
            self::TUTORLMS               => [
                'title'        => esc_html__('Tutor LMS', 'wp-user-avatar'),
                'setting_url'  => PPRESS_SETTINGS_SETTING_GENERAL_PAGE . '#pp_tutorlms_settings',
                'url'          => 'https://profilepress.com/addons/tutor-lms/?utm_source=liteplugin&utm_medium=extension-page&utm_campaign=learn-more',
                'description'  => esc_html__('Sell access to Tutor LMS courses, and enroll users after registration to specific courses.', 'wp-user-avatar'),
                'icon'         => '<span class="dashicons dashicons-welcome-learn-more"></span>',
                'is_available' => function () {
                    return class_exists('\TUTOR\TUTOR') ? true : esc_html__('Tutor LMS is not active', 'wp-user-avatar');
                }
            ],
            self::LEARNDASH              => [
                'title'        => esc_html__('LearnDash', 'wp-user-avatar'),
                'setting_url'  => PPRESS_SETTINGS_SETTING_GENERAL_PAGE . '#pp_ld_settings',
                'url'          => 'https://profilepress.com/addons/learndash/?utm_source=liteplugin&utm_medium=extension-page&utm_campaign=learn-more',
                'description'  => esc_html__('Sell access to LearnDash courses and groups, enroll users after registration to specific courses and groups, and let users view their enrolled courses from the My Account page.', 'wp-user-avatar'),
                'icon'         => '<svg fill="none" height="84" viewBox="0 0 70 84" width="70" xmlns="http://www.w3.org/2000/svg"><g fill="#1e1e1e"><path d="m34.5815 21.2887c-1.1646-.0025-2.3181.225-3.3945.6695s-2.0544 1.0972-2.8779 1.9206c-.8234.8235-1.4761 1.8015-1.9206 2.8778-.4445 1.0764-.672 2.23-.6694 3.3945v51.2478c.0121.4305.1885.84.4931 1.1445.3045.3046.714.481 1.1445.4931 4.2642-.0076 8.3516-1.705 11.3669-4.7202 3.0153-3.0153 4.7126-7.1027 4.7203-11.367v-36.7982c.0025-1.1645-.225-2.3181-.6695-3.3945-.4444-1.0763-1.0971-2.0543-1.9206-2.8778-.8235-.8234-1.8015-1.4761-2.8778-1.9206-1.0764-.4445-2.23-.672-3.3945-.6695z"/><path d="m8.86236 46.3348c-1.16454-.0025-2.31812.225-3.3945.6695-1.07638.4444-2.05437 1.0971-2.87783 1.9206-.82345.8235-1.47616 1.8014-1.920633 2.8778-.444478 1.0764-.6719769 2.23-.669437 3.3945v27.1652c.0121336.4305.188563.84.493085 1.1445s.714045.481 1.144535.4931c4.26423-.0076 8.35163-1.705 11.36692-4.7203 3.0153-3.0152 4.7126-7.1026 4.7202-11.3669v-12.7156c.0026-1.1645-.2249-2.3181-.6694-3.3945s-1.0972-2.0543-1.9206-2.8778c-.8235-.8235-1.8015-1.4762-2.8778-1.9206-1.0764-.4445-2.23-.672-3.39454-.6695z"/><path d="m60.3036.00002103c-1.1646-.00253989-2.3181.22495897-3.3945.66943597-1.0764.444473-2.0544 1.097183-2.8778 1.920633-.8235.82346-1.4762 1.80145-1.9207 2.87783s-.672 2.22996-.6694 3.3945v73.11478c.0121.4305.1885.84.4931 1.1445.3045.3045.714.481 1.1445.4931 4.2642-.0076 8.3516-1.705 11.3669-4.7203 3.0153-3.0152 4.7126-7.1027 4.7203-11.3669v-58.66518c.0025-1.16454-.225-2.31812-.6695-3.3945-.4444-1.07638-1.0971-2.05437-1.9206-2.87783-.8235-.82345-1.8015-1.47616-2.8778-1.920633-1.0764-.444477-2.23-.67197586-3.3945-.66943597z"/></g></svg>',
                'is_available' => function () {
                    return class_exists('\SFWD_LMS') ? true : esc_html__('LearnDash is not active', 'wp-user-avatar');
                }
            ],
            self::LIFTERLMS              => [
                'title'        => esc_html__('LifterLMS', 'wp-user-avatar'),
                'setting_url'  => PPRESS_SETTINGS_SETTING_GENERAL_PAGE . '#pp_lifterlms_settings',
                'url'          => 'https://profilepress.com/addons/lifterlms/?utm_source=liteplugin&utm_medium=extension-page&utm_campaign=learn-more',
                'description'  => esc_html__('Sell access to LifterLMS courses and memberships, enroll users after registration to specific courses and memberships, and let users view their enrolled courses from the My Account page.', 'wp-user-avatar'),
                'icon'         => '<svg clip-rule="evenodd" fill-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="1.41421" viewBox="0 0 85 85" xmlns="http://www.w3.org/2000/svg"><path d="m.015.169h84v84h-84z" fill="none"/><path d="m29.061 50.631-2.258-1.29-6.066 10.452c-5.483-7.613-6.58-17.873-2.322-26.712l.064-.065c.258-.581.581-1.097.839-1.613 4.323-7.485 11.873-12.067 19.873-12.905 1.42-1.935 2.969-3.614 4.711-5.226-11.421-.645-22.843 5.032-28.972 15.615-7.872 13.679-4.258 30.841 7.872 40.263l6.065-18.003c.065-.128.13-.323.194-.516m36.908-16.712c3.227 7.421 3.033 16.195-1.291 23.681-.257.516-.58 1.031-.903 1.548l-.064.066c-5.549 8.129-14.97 12.323-24.326 11.355l6.066-10.453-2.259-1.291c-.129.13-.258.259-.387.389l-12.518 14.259c14.196 5.808 30.907.323 38.779-13.357 6.13-10.581 5.356-23.293-.967-32.842-.517 2.257-1.162 4.516-2.13 6.645"/><path d="m44.999 50.243c-1.614 2.13-4.194 3.228-6.968 3.485-.839.065-1.614-.387-2.001-1.161-1.162-2.517-1.548-5.291-.451-7.743l-12.648-7.291c-.838-.516-1.225-1.356-.967-2.258.193-.904.967-1.55 1.871-1.55l12.84-.451c.968-3.936 2.581-7.678 4.904-11.163 3.678-5.484 8.904-9.549 15.034-12.001 1.485-.581 2.968-1.096 4.453-1.484 1.096-.258 2.193.388 2.451 1.421.452 1.482.775 3.031 1.033 4.579.903 6.582-.065 13.163-2.903 19.099-1.807 3.743-4.324 6.97-7.228 9.808l6.001 11.292c.452.839.323 1.807-.387 2.452-.645.645-1.614.71-2.387.258zm9.549-27.035c1.936 1.162 2.581 3.614 1.485 5.549-1.098 1.936-3.613 2.582-5.55 1.485-1.935-1.098-2.58-3.614-1.484-5.55 1.162-1.935 3.614-2.581 5.549-1.484"/><path d="m26.093 72.118 13.679-15.551c-.516.065-1.032.129-1.549.194-2.064.129-4-.968-4.902-2.903-.259-.452-.453-.904-.646-1.42z"/></svg>',
                'is_available' => function () {
                    return class_exists('\LifterLMS') ? true : esc_html__('LifterLMS is not active', 'wp-user-avatar');
                }
            ],
            self::SENSEI_LMS             => [
                'title'        => esc_html__('Sensei LMS', 'wp-user-avatar'),
                'setting_url'  => PPRESS_SETTINGS_SETTING_GENERAL_PAGE . '#pp_sensei_settings',
                'url'          => 'https://profilepress.com/addons/sensei-lms/?utm_source=liteplugin&utm_medium=extension-page&utm_campaign=learn-more',
                'description'  => esc_html__('Sell access to Sensei courses and groups, and enroll users after registration to specific courses.', 'wp-user-avatar'),
                'icon'         => '<span class="dashicons dashicons-welcome-learn-more"></span>',
                'is_available' => function () {
                    return function_exists('Sensei') ? true : esc_html__('Sensei LMS is not active', 'wp-user-avatar');
                }
            ],
            self::JOIN_BUDDYPRESS_GROUPS => [
                'title'        => esc_html__('Join BuddyPress Groups', 'wp-user-avatar'),
                'url'          => 'https://profilepress.com/addons/join-buddypress-groups/?utm_source=liteplugin&utm_medium=extension-page&utm_campaign=learn-more',
                'description'  => esc_html__('Let users select the BuddyPress groups to join during registration.', 'wp-user-avatar'),
                'icon'         => '<span class="dashicons dashicons-buddicons-buddypress-logo"></span>',
                'is_available' => function () {
                    return class_exists('BuddyPress') ? true : esc_html__('BuddyPress plugin is not active', 'wp-user-avatar');
                }
            ],
            self::BUDDYPRESS_SYNC        => [
                'title'        => esc_html__('BuddyPress Profile Sync', 'wp-user-avatar'),
                'url'          => 'https://profilepress.com/addons/buddypress-profile-sync/?utm_source=liteplugin&utm_medium=extension-page&utm_campaign=learn-more',
                'description'  => esc_html__('It provides a 2-way synchronization between WordPress profile fields and BuddyPress extended profile.', 'wp-user-avatar'),
                'icon'         => '<span class="dashicons dashicons-buddicons-buddypress-logo"></span>',
                'is_available' => function () {
                    return class_exists('BuddyPress') ? true : esc_html__('BuddyPress plugin is not active', 'wp-user-avatar');
                }
            ],
            self::AKISMET                => [
                'title'       => esc_html__('Akismet', 'wp-user-avatar'),
                'url'         => 'https://profilepress.com/addons/akismet/?utm_source=liteplugin&utm_medium=extension-page&utm_campaign=learn-more',
                'setting_url' => PPRESS_SETTINGS_SETTING_GENERAL_PAGE . '#pp_aki_settings',
                'description' => esc_html__('Block spam and bot user registrations with Akismet and keep your membership site safe and secured.', 'wp-user-avatar'),
                'icon'        => '<span class="dashicons dashicons-shield"></span>'
            ],
            self::POLYLANG               => [
                'title'        => esc_html__('Polylang', 'wp-user-avatar'),
                'url'          => 'https://profilepress.com/addons/polylang/?utm_source=liteplugin&utm_medium=extension-page&utm_campaign=learn-more',
                'description'  => esc_html__('It allows you to build multilingual login, registration, password reset and edit profile forms.', 'wp-user-avatar'),
                'icon'         => '<span class="dashicons dashicons-flag"></span>',
                'is_available' => function () {
                    return class_exists('Polylang') ? true : esc_html__('Polylang plugin is not active', 'wp-user-avatar');
                }
            ],
        ]);
    }

    public static function is_enabled($extension_id)
    {
        return class_exists(ppress_var(self::class_map(), $extension_id)) &&
               ppress_var(get_option(self::DB_OPTION_NAME, []), $extension_id) == 'true';
    }
}