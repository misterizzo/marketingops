<?php

namespace ProfilePress\Libsodium\UserModeration;

use ProfilePress\Core\Classes\ExtensionManager as EM;
use WP_User, WP_Error;

class UserModeration
{
    public static $instance_flag = false;

    public static function initialize()
    {
        self::$instance_flag = true;

        if ( ! defined('ProfilePress\Core\Classes\ExtensionManager::USER_MODERATION')) return;

        if ( ! self::moderation_is_active()) return;

        add_filter('user_row_actions', [__CLASS__, 'moderation_links'], 10, 2);
        add_action('load-users.php', [__CLASS__, 'act_on_moderation_orders']);
        add_filter('authenticate', [__CLASS__, 'login_authentication'], PHP_INT_MAX - 1, 3);
        add_action('ppress_admin_notices', [__CLASS__, 'admin_notices']);
        add_action('ppress_admin_notices', [__CLASS__, 'pending_users_at_a_glance']);
        add_action('init', [__CLASS__, 'act_on_approval_url_request'], 10, 0);
        add_action('init', [__CLASS__, 'act_on_rejection_url_request'], 10, 0);
        add_action('restrict_manage_users', [__CLASS__, 'bulk_moderation_button']);
        add_action('load-users.php', [__CLASS__, 'act_on_bulk_moderation']);
        add_filter('dashboard_glance_items', [__CLASS__, 'pending_users_at_a_glance']);

        if (is_admin()) {
            add_filter('views_users', [__CLASS__, 'add_status_links']);
            add_action('pre_user_query', [__CLASS__, 'pre_user_query']);
        }

        add_filter('ppress_settings_page_args', [__CLASS__, 'settings_page']);

        add_filter('ppress_email_notifications', [__CLASS__, 'email_notifications']);
    }

    public static function moderation_is_active()
    {
        return EM::is_enabled(EM::USER_MODERATION);
    }

    public static function settings_page($settings)
    {
        $settings['user_moderation'] = apply_filters('ppress_user_moderation_page', [
            'tab_title' => esc_html__('User Moderation', 'profilepress-pro'),
            'dashicon'  => 'dashicons-shield',
            [
                'section_title'                     => esc_html__('Moderation Settings', 'profilepress-pro'),
                'blocked_error_message'             => [
                    'type'        => 'text',
                    'label'       => esc_html__('Blocked Error Message', 'profilepress-pro'),
                    'description' => esc_html__('Error message displayed when a blocked user tries to login.', 'profilepress-pro')
                ],
                'pending_error_message'             => [
                    'type'        => 'text',
                    'label'       => esc_html__('Pending Error Message', 'profilepress-pro'),
                    'description' => esc_html__('Error message displayed when a registered user yet to be approved tries to login.', 'profilepress-pro')
                ],
                'rejected_error_message'            => [
                    'type'        => 'text',
                    'label'       => esc_html__('Rejected Error Message', 'profilepress-pro'),
                    'description' => esc_html__('Error message displayed when a rejected user tries to login.', 'profilepress-pro')
                ],
                'customize_moderation_email_notice' => [
                    'type'        => 'arbitrary',
                    'data'        => '',
                    'description' => sprintf(
                        '<div class="ppress-settings-page-notice">' . esc_html__('To customize the email sent when a user account is pending approval, blocked, unblock and more, go to the %semail settings%s.', 'profilepress-pro'),
                        '<a target="_blank" href="' . PPRESS_SETTINGS_EMAIL_SETTING_PAGE . '">', '</a>'
                    )
                ]
            ]
        ]);

        return $settings;
    }

    public static function email_notifications($emails)
    {
        $site_title = ppress_site_title();

        $emails[] = [
            'type'         => 'account',
            'key'          => 'account_pending_review',
            'title'        => esc_html__('Account Awaiting Approval', 'profilepress-pro'),
            'subject'      => esc_html__('Your Account is Awaiting Review', 'profilepress-pro'),
            'message'      => ppress_user_moderation_msg_default('pending'),
            'description'  => esc_html__('Email that is sent to the user informing them their account is pending review.', 'profilepress-pro'),
            'recipient'    => esc_html__('Users', 'profilepress-pro'),
            'placeholders' => [
                '{{username}}'   => esc_html__('Username of user.', 'profilepress-pro'),
                '{{email}}'      => esc_html__('Email address of user.', 'profilepress-pro'),
                '{{first_name}}' => esc_html__('First name of user.', 'profilepress-pro'),
                '{{last_name}}'  => esc_html__('Last name of user.', 'profilepress-pro')
            ]
        ];
        $emails[] = [
            'type'         => 'account',
            'key'          => 'account_approved',
            'title'        => esc_html__('Account Approved Email', 'profilepress-pro'),
            'subject'      => esc_html__('Your Account is Approved', 'profilepress-pro'),
            'message'      => ppress_user_moderation_msg_default('approved'),
            'description'  => esc_html__('Email that is sent to the user informing them their account has been approved.', 'profilepress-pro'),
            'recipient'    => esc_html__('Users', 'profilepress-pro'),
            'placeholders' => [
                '{{username}}'   => esc_html__('Username of user.', 'profilepress-pro'),
                '{{email}}'      => esc_html__('Email address of user.', 'profilepress-pro'),
                '{{first_name}}' => esc_html__('First name of user.', 'profilepress-pro'),
                '{{last_name}}'  => esc_html__('Last name of user.', 'profilepress-pro')
            ]
        ];
        $emails[] = [
            'type'         => 'account',
            'key'          => 'account_rejected',
            'title'        => esc_html__('Account Rejected Email', 'profilepress-pro'),
            'subject'      => esc_html__('Your Account Has Been Rejected', 'profilepress-pro'),
            'message'      => ppress_user_moderation_msg_default('rejected'),
            'description'  => esc_html__('Email that is sent to the user informing them their account was rejected.', 'profilepress-pro'),
            'recipient'    => esc_html__('Users', 'profilepress-pro'),
            'placeholders' => [
                '{{username}}'   => esc_html__('Username of user.', 'profilepress-pro'),
                '{{email}}'      => esc_html__('Email address of user.', 'profilepress-pro'),
                '{{first_name}}' => esc_html__('First name of user.', 'profilepress-pro'),
                '{{last_name}}'  => esc_html__('Last name of user.', 'profilepress-pro')
            ]
        ];
        $emails[] = [
            'type'         => 'account',
            'key'          => 'account_blocked',
            'title'        => esc_html__('Account Blocked Email', 'profilepress-pro'),
            'subject'      => esc_html__('Your Account is Blocked', 'profilepress-pro'),
            'message'      => ppress_user_moderation_msg_default('blocked'),
            'description'  => esc_html__('Email that is sent to the user informing them their account has been blocked.', 'profilepress-pro'),
            'recipient'    => esc_html__('Users', 'profilepress-pro'),
            'placeholders' => [
                '{{username}}'   => esc_html__('Username of user.', 'profilepress-pro'),
                '{{email}}'      => esc_html__('Email address of user.', 'profilepress-pro'),
                '{{first_name}}' => esc_html__('First name of user.', 'profilepress-pro'),
                '{{last_name}}'  => esc_html__('Last name of user.', 'profilepress-pro')
            ]
        ];
        $emails[] = [
            'type'         => 'account',
            'key'          => 'account_unblocked',
            'title'        => esc_html__('Account Unblocked Email', 'profilepress-pro'),
            'subject'      => esc_html__('Your Account is Unblocked', 'profilepress-pro'),
            'message'      => ppress_user_moderation_msg_default('unblocked'),
            'description'  => esc_html__('Email that is sent to the user informing them their account has been unblocked.', 'profilepress-pro'),
            'recipient'    => esc_html__('Users', 'profilepress-pro'),
            'placeholders' => [
                '{{username}}'   => esc_html__('Username of user.', 'profilepress-pro'),
                '{{email}}'      => esc_html__('Email address of user.', 'profilepress-pro'),
                '{{first_name}}' => esc_html__('First name of user.', 'profilepress-pro'),
                '{{last_name}}'  => esc_html__('Last name of user.', 'profilepress-pro')
            ]
        ];
        $emails[] = [
            'type'         => 'account',
            'key'          => 'account_approval_admin',
            'title'        => esc_html__('Account Needs Approval Admin Notification', 'profilepress-pro'),
            'subject'      => sprintf(__('[%s] New User Pending Moderation', 'profilepress-pro'), $site_title),
            'message'      => ppress_user_moderation_msg_default('admin_notification'),
            'description'  => esc_html__('Email that is sent to admins to inform them a new user accounts is awaiting review.', 'profilepress-pro'),
            'recipient'    => ppress_get_admin_notification_emails(),
            'placeholders' => [
                '{{approval_url}}'  => esc_html__('URL to approve user pending approval.', 'profilepress-pro'),
                '{{rejection_url}}' => esc_html__('URL to reject user pending approval.', 'profilepress-pro'),
                '{{username}}'      => esc_html__('Username of user pending approval.', 'profilepress-pro'),
                '{{email}}'         => esc_html__('Email address of user pending approval.', 'profilepress-pro'),
                '{{first_name}}'    => esc_html__('First name of user pending approval.', 'profilepress-pro'),
                '{{last_name}}'     => esc_html__('Last name of user pending approval.', 'profilepress-pro'),
                '{{field_key}}'     => sprintf(
                    esc_html__('Replace "field_key" with the %scustom field key%s or usermeta key.', 'profilepress-pro'),
                    '<a href="' . PPRESS_CUSTOM_FIELDS_SETTINGS_PAGE . '" target="_blank">', '</a>'
                )
            ]
        ];

        return $emails;
    }

    /**
     * @param \WP_User_Query $query
     */
    public static function pre_user_query($query)
    {
        if ('ppress_rejected' === $query->query_vars['role']) {

            unset($query->query_vars['meta_query'][0]);

            $query->set('role', '');
            $query->set('meta_key', 'ppress_user_reject');
            $query->set('meta_value', 'reject');
            $query->prepare_query();
        }

        if ('ppress_blocked' === $query->query_vars['role']) {

            unset($query->query_vars['meta_query'][0]);

            $query->set('role', '');
            $query->set('meta_key', 'ppress_user_block');
            $query->set('meta_value', 'block');
            $query->prepare_query();
        }

        if ('ppress_pending' === $query->query_vars['role']) {

            unset($query->query_vars['meta_query'][0]);

            $query->set('role', '');
            $query->set('meta_key', 'ppress_user_pending');
            $query->set('meta_value', 'pending');
            $query->prepare_query();
        }
    }

    public static function add_status_links($links)
    {
        $pending_users = get_users(
            array(
                'meta_key'   => 'ppress_user_pending',
                'meta_value' => 'pending',
            )
        );

        $rejected_users = get_users(
            array(
                'meta_key'   => 'ppress_user_reject',
                'meta_value' => 'reject',
            )
        );

        $blocked_users = get_users(
            array(
                'meta_key'   => 'ppress_user_block',
                'meta_value' => 'block',
            )
        );

        if ($rejected_users) {

            $links['ppress_rejected'] = sprintf(
                '<a href="%1$s" class="%2$s">%3$s <span class="count">(%4$s)</span></a>',
                esc_url(add_query_arg(['role' => 'ppress_rejected'], 'users.php')),
                'ppress_rejected' === self::get_role() ? 'current' : '',
                esc_html__('Rejected', 'profilepress-pro'),
                count($rejected_users)
            );
        }

        if ($blocked_users) {

            $links['ppress_blocked'] = sprintf(
                '<a href="%1$s" class="%2$s">%3$s <span class="count">(%4$s)</span></a>',
                esc_url(add_query_arg(['role' => 'ppress_blocked'], 'users.php')),
                'ppress_blocked' === self::get_role() ? 'current' : '',
                esc_html__('Blocked', 'profilepress-pro'),
                count($blocked_users)
            );
        }

        if ($pending_users) {

            $links['ppress_pending'] = sprintf(
                '<a href="%1$s" class="%2$s">%3$s <span class="count">(%4$s)</span></a>',
                esc_url(add_query_arg(array('role' => 'ppress_pending'), 'users.php')),
                'ppress_pending' === self::get_role() ? 'current' : '',
                esc_html__('Pending', 'profilepress-pro'),
                count($pending_users)
            );
        }

        return $links;
    }

    public static function get_role()
    {
        return isset($_REQUEST['role']) ? esc_html($_REQUEST['role']) : false;
    }

    /**
     * Act on admin user approval request.
     */
    public static function act_on_approval_url_request()
    {
        if (isset($_GET['action']) && isset($_GET['id'])) {
            if (is_user_logged_in()) {
                // user ID to approve.
                $user_id = absint($_GET['id']);

                if ($_GET['action'] == 'ppress_approve_user' && current_user_can('manage_options')) {
                    self::approve_user($user_id);
                    wp_safe_redirect(add_query_arg('update', 'approve', admin_url('users.php')));
                    exit;
                }
            }
        }
    }

    /**
     * Act on admin user rejection request.
     */
    public static function act_on_rejection_url_request()
    {
        if (isset($_GET['action']) && isset($_GET['id'])) {

            if (is_user_logged_in()) {
                // user ID to approve.
                $user_id = absint($_GET['id']);

                if ($_GET['action'] == 'ppress_reject_user' && current_user_can('manage_options')) {
                    self::reject_user($user_id);
                    wp_safe_redirect(add_query_arg('update', 'reject', admin_url('users.php')));
                    exit;
                }
            }
        }
    }

    /**
     * Approve pending users
     *
     * @param $user_id
     *
     * @return void
     */
    public static function approve_user($user_id)
    {
        delete_user_meta($user_id, 'ppress_user_pending');
        delete_user_meta($user_id, 'ppress_user_reject');

        UserModerationNotification::approve($user_id);

        do_action('ppress_after_approve_user', $user_id);
    }

    /**
     * Approve pending users
     *
     * @param $user_id
     *
     * @return void
     */
    public static function reject_user($user_id)
    {
        delete_user_meta($user_id, 'ppress_user_pending');
        update_user_meta($user_id, 'ppress_user_reject', 'reject');

        UserModerationNotification::reject($user_id);

        do_action('ppress_after_reject_user', $user_id);
    }

    /**
     * Add moderation links to user_row_actions
     *
     * @param $actions
     * @param $user_object
     *
     * @return mixed
     */
    public static function moderation_links($actions, $user_object)
    {
        $current_user = wp_get_current_user();

        // do not display button for admin
        if ($current_user->ID != $user_object->ID && current_user_can('manage_options')) {

            if ( ! self::is_pending($user_object->ID) && ! self::is_rejected($user_object->ID)) {

                if (self::is_block($user_object->ID)) {
                    $actions['ppress_unblock'] = sprintf('<a href="%1$s">%2$s</a>',
                        esc_url_raw(
                            add_query_arg(
                                array(
                                    'action'   => 'ppress_unblock',
                                    'user'     => $user_object->ID,
                                    '_wpnonce' => wp_create_nonce('user-unblock')
                                ),
                                admin_url('users.php')
                            )
                        ),
                        esc_html__('Unblock', 'profilepress-pro')
                    );
                } else {
                    $actions['ppress_block'] = sprintf('<a href="%1$s">%2$s</a>',
                        esc_url(
                            add_query_arg(
                                array(
                                    'action'   => 'ppress_block',
                                    'user'     => $user_object->ID,
                                    '_wpnonce' => wp_create_nonce('user-block')
                                ),
                                admin_url('users.php')
                            )
                        ),
                        esc_html__('Block', 'profilepress-pro')
                    );
                }
            }

            if (self::is_pending($user_object->ID)) {

                $actions['ppress_user_approve'] = sprintf('<a href="%1$s">%2$s</a>',
                    esc_url(
                        add_query_arg(
                            array(
                                'action'   => 'ppress_user_approve',
                                'user'     => $user_object->ID,
                                '_wpnonce' => wp_create_nonce('user-approve')
                            ),
                            admin_url('users.php')
                        )
                    ),
                    esc_html__('Approve', 'profilepress-pro')
                );

                $actions['ppress_user_reject'] = sprintf('<a href="%1$s">%2$s</a>',
                    esc_url(
                        add_query_arg(
                            array(
                                'action'   => 'ppress_user_reject',
                                'user'     => $user_object->ID,
                                '_wpnonce' => wp_create_nonce('user-reject')
                            ),
                            admin_url('users.php')
                        )
                    ),
                    esc_html__('Reject', 'profilepress-pro')
                );
            }

            if (self::is_rejected($user_object->ID)) {
                $actions['ppress_user_approve'] = sprintf('<a href="%1$s">%2$s</a>',
                    esc_url(
                        add_query_arg(
                            array(
                                'action'   => 'ppress_user_approve',
                                'user'     => $user_object->ID,
                                '_wpnonce' => wp_create_nonce('user-approve')
                            ),
                            admin_url('users.php')
                        )
                    ),
                    esc_html__('Approve', 'profilepress-pro')
                );
            }
        }

        return $actions;
    }

    /**
     * Check if a user is blocked
     *
     * @param $user_id
     *
     * @return bool
     */
    public static function is_block($user_id)
    {
        return get_user_meta($user_id, 'ppress_user_block', true) == 'block';
    }

    /**
     * Check if a user is pending approval
     *
     * @param $user_id
     *
     * @return bool
     */
    public static function is_pending($user_id)
    {
        return get_user_meta($user_id, 'ppress_user_pending', true) == 'pending';
    }

    /**
     * Check if a user is rejected
     *
     * @param $user_id
     *
     * @return bool
     */
    public static function is_rejected($user_id)
    {
        return get_user_meta($user_id, 'ppress_user_reject', true) == 'reject';
    }

    /**
     * Block and unblock user depending on command
     */
    public static function act_on_moderation_orders()
    {
        $user_id = isset($_GET['user']) ? absint($_GET['user']) : '';

        if (isset($_GET['action'])) {

            if ('ppress_block' == $_GET['action'] && check_admin_referer('user-block')) {

                self::block_user($user_id);

                wp_safe_redirect(add_query_arg('update', 'block', admin_url('users.php')));
                exit;
            }

            if ('ppress_unblock' == $_GET['action'] && check_admin_referer('user-unblock')) {

                self::unblock_user($user_id);

                wp_safe_redirect(add_query_arg('update', 'unblock', admin_url('users.php')));
                exit;
            }

            if ('ppress_user_approve' == $_GET['action'] && check_admin_referer('user-approve')) {

                self::approve_user($user_id);

                wp_safe_redirect(add_query_arg('update', 'approve', admin_url('users.php')));
                exit;
            }

            if ('ppress_user_reject' == $_GET['action'] && check_admin_referer('user-reject')) {

                self::reject_user($user_id);

                wp_safe_redirect(add_query_arg('update', 'reject', admin_url('users.php')));
                exit;
            }
        }
    }

    /**
     * Block a user
     *
     * @param int $user_id
     *
     * @return void
     */
    public static function block_user($user_id)
    {
        update_user_meta($user_id, 'ppress_user_block', 'block');

        UserModerationNotification::block($user_id);

        do_action('ppress_after_block_user', $user_id);
    }

    /**
     * Unblock a user
     *
     * @param $user_id
     *
     * @return void
     */
    public static function unblock_user($user_id)
    {
        delete_user_meta($user_id, 'ppress_user_block');

        UserModerationNotification::unblock($user_id);

        do_action('ppress_after_unblock_user', $user_id);
    }

    public static function admin_notices()
    {
        if (isset($_GET['update'])) {

            if (in_array($_GET['update'], ['block', 'unblock', 'approve', 'reject'])) {

                echo '<div class="updated notice is-dismissible">';
                echo '<p>';

                switch ($_GET['update']) {
                    case 'block':
                        _e('User Blocked', 'profilepress-pro');
                        break;
                    case 'unblock':
                        _e('User Unblocked', 'profilepress-pro');
                        break;
                    case 'approve':
                        _e('User Approved', 'profilepress-pro');
                        break;
                    case 'reject':
                        _e('User Rejected', 'profilepress-pro');
                        break;
                }

                echo '</p>';
                echo '</div>';
            }
        }
    }

    /**
     * Authenticate if user is blocked
     *
     * @param $user
     * @param $username
     * @param $password
     *
     * @return WP_User|WP_Error
     */
    public static function login_authentication($user, $username, $password)
    {
        if (is_email($username)) {
            $user_object = get_user_by('email', $username);
        } else {
            $user_object = get_user_by('login', $username);
        }

        if ($user_object) {

            if (self::is_block($user_object->ID)) {
                return new WP_Error('user_blocked', self::blocked_error_notice());
            }

            if (self::is_pending($user_object->ID)) {
                return new WP_Error('user_pending', self::pending_error_notice());
            }

            if (self::is_rejected($user_object->ID)) {
                return new WP_Error('user_reject', self::rejected_error_notice());
            }
        }

        return $user;
    }

    /**
     * Add bulk moderation select dropdown to top of user WP_List_Table class
     */
    public static function bulk_moderation_button($which)
    {
        $id        = 'bottom' === $which ? 'bulk_moderation2' : 'bulk_moderation';
        $button_id = 'bottom' === $which ? 'pp_bulk_moderation_submit2' : 'pp_bulk_moderation_submit';

        echo '</div>'; ?>
        <div class="alignleft actions">
        <label class="screen-reader-text" for="<?= $id; ?>"><?php _e('Bulk User Moderation&hellip;') ?></label>
        <select name="<?= $id; ?>" id="<?= $id; ?>">
            <option value=""><?php _e('Bulk Moderation', 'profilepress-pro') ?></option>
            <option value="ppress_approve"><?php _e('Approve', 'profilepress-pro') ?></option>
            <option value="ppress_reject"><?php _e('Reject', 'profilepress-pro') ?></option>
            <option value="ppress_block"><?php _e('Block', 'profilepress-pro') ?></option>
            <option value="ppress_unblock"><?php _e('Unblock', 'profilepress-pro') ?></option>
            <option value="ppress_pending"><?php _e('Set to Pending', 'profilepress-pro') ?></option>
        </select>
        <?php
        submit_button(__('Apply', 'profilepress-pro'), 'button', $button_id, false);
    }

    /**
     * Act on bulk moderation command.
     */
    public static function act_on_bulk_moderation()
    {
        if (isset($_GET['pp_bulk_moderation_submit']) || isset($_GET['pp_bulk_moderation_submit2'])) {

            $moderation_action = ! empty($_GET['bulk_moderation2']) ? $_GET['bulk_moderation2'] : $_GET['bulk_moderation'];
            $moderation_action = sanitize_text_field($moderation_action);

            if ( ! is_array($_GET['users'])) return;

            if ( ! current_user_can('manage_options')) return;

            check_admin_referer('bulk-users');

            $selected_users = array_map('absint', $_GET['users']);

            foreach ($selected_users as $selected_user) {
                switch ($moderation_action) {
                    case 'ppress_approve':
                        self::approve_user($selected_user);
                        break;
                    case 'ppress_reject':
                        self::reject_user($selected_user);
                        break;
                    case 'ppress_block':
                        self::block_user($selected_user);
                        break;
                    case 'ppress_unblock':
                        self::unblock_user($selected_user);
                        break;
                    case 'ppress_pending':
                        self::make_pending($selected_user);
                        break;
                }
            }

            wp_safe_redirect(admin_url('users.php'));
            exit;
        }
    }

    /**
     * Make a registered user pending.
     *
     * @param int $user_id
     *
     * @return void
     */
    public static function make_pending($user_id)
    {
        update_user_meta($user_id, 'ppress_user_pending', 'pending');

        delete_user_meta($user_id, 'ppress_user_block');
        delete_user_meta($user_id, 'ppress_user_reject');

        do_action('ppress_after_make_pending_user', $user_id);
    }

    /**
     * Add number of users pending activation.
     *
     * @param $items
     *
     * @return array|mixed|void
     */
    public static function pending_users_at_a_glance($items)
    {
        if ( ! current_user_can('manage_options')) {
            return;
        }

        $obj = new \WP_User_Query(array(
            'meta_key'    => 'ppress_user_pending',
            'meta_value'  => 'pending',
            'count_total' => true
        ));

        $count = $obj->get_total();

        // we are checking if argument is array because "at a glance" dashboard has it set as an array.
        if ( ! is_array($items)) {
            if ($count > 0) {
                echo '<div class="updated notice is-dismissible"><p>';
                echo esc_html__('Hi admin', 'profilepress-pro') . ', <a href="' . admin_url('users.php?role=ppress_pending') . '">' . sprintf(
                        _n(
                            esc_html__('1 user is pending approval', 'profilepress-pro'),
                            esc_html__('%s users are pending approval', 'profilepress-pro'),
                            $count,
                            'profilepress-pro'
                        ),
                        $count
                    )
                     . '</a>';
                echo '.</p>';
                echo '</div>';
            }

            return;
        }

        if ($count > 0) {
            $items[] = '<style>#dashboard_right_now a.pp-pending-user-glance:before {content: "\f110";}</style>
                        <a class="pp-pending-user-glance" href="' . admin_url('users.php') . '">' . sprintf(
                    _n(
                        esc_html__('1 Pending User', 'profilepress-pro'),
                        esc_html__('%s Pending Users', 'profilepress-pro'),
                        $count,
                        'profilepress-pro'
                    ),
                    $count
                )
                       . '</a>';
        }

        return $items;
    }

    public static function pending_user_notice()
    {
        if (isset($_GET['update'])) {

            if ($_GET['update'] == 'block' || $_GET['update'] == 'unblock' || $_GET['update'] == 'approve') {

                echo '<div class="updated notice is-dismissible">';
                echo '<p>';

                if ($_GET['update'] == 'block') {
                    _e('User Blocked', 'profilepress-pro');
                } elseif ($_GET['update'] == 'unblock') {
                    _e('User Unblocked', 'profilepress-pro');
                } elseif ($_GET['update'] == 'approve') {
                    _e('User Approved', 'profilepress-pro');
                }
                echo '</p>';
                echo '</div>';
            }
        }
    }

    /**
     * block error message.
     *
     * @return string
     */
    public static function blocked_error_notice()
    {
        $default = sprintf(__('%s ERROR %s: This account is blocked', 'profilepress-pro'), '<strong>', '</strong>');

        return htmlspecialchars_decode(ppress_get_setting('blocked_error_message', $default, true));
    }

    /**
     *  Pending error message.
     *
     * @return string
     */
    public static function pending_error_notice()
    {
        $default = sprintf(__('%s ERROR %s: This account is pending approval', 'profilepress-pro'), '<strong>', '</strong>');

        return htmlspecialchars_decode(ppress_get_setting('pending_error_message', $default, true));
    }

    /**
     *  Rejected error message.
     *
     * @return string
     */
    public static function rejected_error_notice()
    {
        $default = sprintf(__('%s ERROR %s: This account has been rejected', 'profilepress-pro'), '<strong>', '</strong>');

        return htmlspecialchars_decode(ppress_get_setting('rejected_error_message', $default, true));
    }
}
