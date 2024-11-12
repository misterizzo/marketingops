<?php

namespace ProfilePress\Libsodium;


use ProfilePress\Core\Base;
use ProfilePress\Core\Classes\ExtensionManager as EM;
use ProfilePress\Libsodium\CustomProfileFields;
use ProfilePress\Libsodium\Licensing\LicenseControl;
use ProfilePress\Libsodium\Licensing\Licensing;
use ProfilePress\Libsodium\ShortcodeBuilder;
use ProfilePress\Libsodium\UserModeration\UserModeration;

define('PROFILEPRESS_PRO_ROOT', wp_normalize_path(plugin_dir_path(PROFILEPRESS_PRO_SYSTEM_FILE_PATH)));
/** internally uses wp_normalize_path */
define('PROFILEPRESS_PRO_URL', plugin_dir_url(PROFILEPRESS_PRO_SYSTEM_FILE_PATH));

define('PROFILEPRESS_PRO_LIBSODIUM_ASSETS_URL', plugins_url('assets/', __FILE__));

define('PROFILEPRESS_PRO_ITEM_NAME', 'ProfilePress Pro');
define('PROFILEPRESS_PRO_ITEM_ID', 163588);

class Libsodium
{
    public function __construct()
    {
        Licensing::get_instance();

        $basename = plugin_basename(PROFILEPRESS_SYSTEM_FILE_PATH);
        add_filter('plugin_action_links_' . $basename, [$this, 'add_action_link'], 99, 2);
        add_filter('network_admin_plugin_action_links_' . $basename, [$this, 'add_action_link'], 99, 2);

        if ( ! LicenseControl::is_license_expired()) define('PROFILEPRESS_PRO_DETACH_LIBSODIUM', true);

        if ( ! defined('PROFILEPRESS_PRO_DETACH_LIBSODIUM')) return;
    }

    public static function run_install($networkwide = false)
    {
        if (is_multisite() && $networkwide) {

            $site_ids = get_sites(['fields' => 'ids', 'number' => 0]);

            foreach ($site_ids as $site_id) {
                switch_to_blog($site_id);
                self::on_activation();
                restore_current_blog();
            }
        } else {
            self::on_activation();
        }

        flush_rewrite_rules();
    }

    public static function multisite_new_blog_install($blog_id)
    {
        if (is_plugin_active_for_network('profilepress-pro/profilepress-pro.php')) {
            switch_to_blog($blog_id);
            self::on_activation();
            restore_current_blog();
        }
    }

    public static function create_db_tables()
    {
        global $wpdb;

        $collate = '';
        if ($wpdb->has_cap('collation')) {
            $collate = $wpdb->get_charset_collate();
        }

        $passwordless_login_table = Base::passwordless_login_db_table();
        $profile_fields_table     = Base::profile_fields_db_table();

        $sqls[] = "CREATE TABLE IF NOT EXISTS $profile_fields_table (
                  id mediumint(9) NOT NULL AUTO_INCREMENT,
                  label_name tinytext,
                  field_key varchar(100) DEFAULT NULL,
                  description varchar(500) NOT NULL,
                  type varchar(20) NOT NULL,
                  options varchar(3000) DEFAULT NULL,
                  PRIMARY KEY (id),
                  UNIQUE KEY field_key (field_key)
				) $collate;
				";
        $sqls[] = "CREATE TABLE IF NOT EXISTS $passwordless_login_table (
                      id mediumint(9) NOT NULL AUTO_INCREMENT,
                      user_id mediumint(9) NOT NULL,
                      token varchar(30) NOT NULL,
                      expires int(10) NOT NULL,
                      PRIMARY KEY (id)
				) $collate;
				";

        $sqls = apply_filters('ppress_pro_create_database_tables', $sqls, $collate);

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        foreach ($sqls as $sql) {
            dbDelta($sql);
        }
    }

    public static function on_activation()
    {
        // if plugin has been activated initially, return.
        if ( ! current_user_can('activate_plugins') || get_option('ppress_pro_plugin_activated') == 'true') return;

        self::create_db_tables();

        add_option(EM::DB_OPTION_NAME, array_fill_keys([EM::PAYPAL, EM::CUSTOM_FIELDS, EM::SOCIAL_LOGIN, EM::RECAPTCHA, EM::AKISMET], 'true'));

        // default contact information
        add_option(PPRESS_CONTACT_INFO_OPTION_NAME, ppress_social_network_fields());

        self::insert_custom_profile_field();

        ppress_update_settings('aki_spam_error', esc_html__('Spam registration detected. Please try again', 'profilepress-pro'));

        ppress_update_settings('passwordless_error', esc_html__('One-time login has expired or is invalid', 'profilepress-pro'));
        ppress_update_settings('passwordless_success_message', esc_html__('One-time login URL sent successfully to your email', 'profilepress-pro'));
        ppress_update_settings('passwordless_login_email_enabled', 'on');
        ppress_update_settings('passwordless_login_email_subject', sprintf(esc_html__("One-time login to %s", 'profilepress-pro'), ppress_site_title()));
        ppress_update_settings('account_pending_review_email_content', ppress_passwordless_login_message_default());

        ppress_update_settings('uec_expiration', 30);
        ppress_update_settings('uec_unconfirmed_age', 10);
        ppress_update_settings('uac_activate_delete_unconfirmed_users', 'true');
        ppress_update_settings('uec_success_message', '<div class="profilepress-login-status">' . __('Email successfully verified. You can now log in.', 'profilepress-pro') . '</div>');
        ppress_update_settings('uec_already_confirm_message', '<div class="profilepress-login-status">' . __('Email address for this user is already verified.', 'profilepress-pro') . '</div>');
        ppress_update_settings('uec_invalid_error', '<div class="profilepress-login-status">' . __('Confirmation link is invalid or has expired.', 'profilepress-pro') . ' <a href="{{resend_email_confirmation_link}}">' . __('Click to resend.', 'profilepress-pro') . '</a></div>');
        ppress_update_settings('uec_activation_resent', '<div class="profilepress-login-status">' . __('Confirmation email successfully resent', 'profilepress-pro') . '.</div>');
        ppress_update_settings('uec_unactivated_error', __('Account is pending email confirmation.', 'profilepress-pro') . ' <a href="{{resend_email_confirmation_link}}">' . __('Click to resend', 'profilepress-pro') . '</a>');
        ppress_update_settings('email_confirmation_email_enabled', 'on');
        ppress_update_settings('email_confirmation_email_subject', esc_html__('Confirm Your Email Address to Activate Your Account', 'profilepress-pro'));
        ppress_update_settings('email_confirmation_email_content', EmailConfirmation::default_email_message());

        ppress_update_settings('blocked_error_message', sprintf(esc_html__('%sERROR%s: This account is blocked', 'profilepress-pro'), '<strong>', '</strong>'));
        ppress_update_settings('pending_error_message', sprintf(esc_html__('%sERROR%s: This account is pending approval', 'profilepress-pro'), '<strong>', '</strong>'));
        ppress_update_settings('rejected_error_message', sprintf(esc_html__('%sERROR%s: This account has been rejected', 'profilepress-pro'), '<strong>', '</strong>'));
        ppress_update_settings('account_pending_review_email_enabled', 'on');
        ppress_update_settings('account_pending_review_email_subject', esc_html__('Account Awaiting Review', 'profilepress-pro'));
        ppress_update_settings('account_pending_review_email_content', ppress_user_moderation_msg_default('pending'));
        ppress_update_settings('account_approved_email_enabled', 'on');
        ppress_update_settings('account_approved_email_subject', esc_html__('Your Account is Approved', 'profilepress-pro'));
        ppress_update_settings('account_approved_email_content', ppress_user_moderation_msg_default('approved'));
        ppress_update_settings('account_rejected_email_enabled', 'on');
        ppress_update_settings('account_rejected_email_subject', esc_html__('Your Account Has Been Rejected', 'profilepress-pro'));
        ppress_update_settings('account_rejected_email_content', ppress_user_moderation_msg_default('rejected'));
        ppress_update_settings('account_blocked_email_enabled', 'on');
        ppress_update_settings('account_blocked_email_subject', esc_html__('Your Account is Blocked', 'profilepress-pro'));
        ppress_update_settings('account_blocked_email_content', ppress_user_moderation_msg_default('blocked'));
        ppress_update_settings('account_unblocked_email_enabled', 'on');
        ppress_update_settings('account_unblocked_email_subject', esc_html__('Your Account is Unblocked', 'profilepress-pro'));
        ppress_update_settings('account_unblocked_email_content', ppress_user_moderation_msg_default('unblocked'));
        ppress_update_settings('account_approval_admin_email_enabled', 'on');
        ppress_update_settings('account_approval_admin_email_subject', esc_html__('Account Awaiting Approval', 'profilepress-pro'));
        ppress_update_settings('account_approval_admin_email_content', ppress_user_moderation_msg_default('admin_notification'));

        ppress_update_settings('facebook_button_label', esc_html__('Sign in with Facebook', 'profilepress-pro'));
        ppress_update_settings('twitter_button_label', esc_html__('Sign in with X', 'profilepress-pro'));
        ppress_update_settings('google_button_label', esc_html__('Sign in with Google', 'profilepress-pro'));
        ppress_update_settings('linkedin_button_label', esc_html__('Sign in with LinkedIn', 'profilepress-pro'));
        ppress_update_settings('github_button_label', esc_html__('Sign in with GitHub', 'profilepress-pro'));
        ppress_update_settings('vk_button_label', esc_html__('Sign in with VK', 'profilepress-pro'));

        ppress_update_settings('recaptcha_error_message', esc_html__('ERROR: Please retry CAPTCHA', 'profilepress-pro'));

        ppress_update_settings('wci_my_account_tabs', ['billing', 'shipping']);

        // attempt to activate license, if found
        Licensing::get_instance()->activate_license(get_option('ppress_license_key', ''), true);

        add_option('ppress_pro_plugin_activated', 'true');

        flush_rewrite_rules();
    }

    public static function insert_custom_profile_field()
    {
        global $wpdb;

        $table = Base::profile_fields_db_table();

        $wpdb->insert(
            $table,
            [
                'label_name'  => esc_html__('Gender', 'profilepress-pro'),
                'field_key'   => 'gender',
                'description' => esc_html__('Gender of a user.', 'profilepress-pro'),
                'type'        => 'select',
                'options'     => 'Male, Female',
            ]
        );

        $wpdb->insert(
            $table,
            [
                'label_name'  => esc_html__('Country', 'profilepress-pro'),
                'field_key'   => 'country',
                'description' => esc_html__('The country users are from.', 'profilepress-pro'),
                'type'        => 'country'
            ]
        );
    }

    public function libsodium()
    {
        ShortcodeBuilder\Init::get_instance();

        if ( ! defined('PROFILEPRESS_PRO_DETACH_LIBSODIUM')) return $this;

        add_action('admin_enqueue_scripts', array($this, 'admin_js'));

        PremiumThemes\Init::get_instance();
        PayPal\Init::get_instance();
        Mollie\Init::get_instance();
        Razorpay\Init::get_instance();
        Paystack\Init::get_instance();
        CustomProfileFields\Init::init();
        EmailConfirmation::get_instance();
        SocialLogin\Init::get_instance();
        Recaptcha\Init::init();
        MailchimpIntegration\Init::get_instance();
        CampaignMonitorIntegration\Init::get_instance();
        AkismetIntegration::get_instance();
        BuddyPressJoinGroupSelect\Init::get_instance();
        TWOFA\Init::get_instance();
        LearnDash\Init::get_instance();
        SenseiLMS\Init::get_instance();
        LifterLMS::get_instance();
        InvitationCodes\Init::get_instance();

        return $this;
    }

    public function libplusdium()
    {
        if ( ! defined('PROFILEPRESS_PRO_DETACH_LIBSODIUM')) return $this;

        Receipt\Init::get_instance();
        UserModeration::initialize();
        PasswordlessLogin::get_instance();
        BuddyPressProfileSync::get_instance();
        MultisiteIntegration\Init::get_instance();
        WooCommerceIntegration\Init::get_instance();
        PolylangIntegration::get_instance();
        MeteredPaywall\Init::get_instance();

        return $this;
    }

    public function admin_js()
    {
        if (ppress_is_admin_page()) {
            wp_enqueue_script('ppress_pro_admin', PROFILEPRESS_PRO_LIBSODIUM_ASSETS_URL . 'admin.js', array('jquery', 'underscore'), false, true);
        }
    }

    /**
     * @param array $links Array of links for the plugins, adapted when the current plugin is found.
     * @param string $file The filename for the current plugin, which the filter loops through.
     *
     * @return array
     */
    public function add_action_link($links, $file)
    {
        // Remove Free 'deactivate' link if Premium is active as well. We don't want users to deactivate Free when Premium is active.
        unset($links['deactivate']);
        $no_deactivation_explanation = '<span style="color: #32373c">' . sprintf(
                __('Required by %s', 'profilepress-pro'), 'ProfilePress Pro'
            ) . '</span>';

        array_unshift($links, $no_deactivation_explanation);

        return $links;
    }

    /**
     * @return Libsodium|null
     */
    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}