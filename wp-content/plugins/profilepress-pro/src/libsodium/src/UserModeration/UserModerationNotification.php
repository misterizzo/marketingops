<?php

namespace ProfilePress\Libsodium\UserModeration;

class UserModerationNotification
{
    public static function send_mail($user_id, $subject, $message)
    {
        ppress_send_email(
            self::user_id_email($user_id),
            self::parse_placeholders($user_id, $subject),
            self::parse_placeholders($user_id, $message)
        );
    }

    /**
     * Return formatted email message by replacing placeholders with actual values
     *
     * @param int $user_id ID of user
     * @param string $content message to format
     *
     * @return mixed string
     */
    public static function parse_placeholders($user_id, $content)
    {
        $user_data = get_userdata($user_id);

        $search = array(
            '{{username}}',
            '{{email}}',
            '{{first_name}}',
            '{{last_name}}'
        );

        $replace = array(
            $user_data->user_login,
            $user_data->user_email,
            $user_data->first_name,
            $user_data->last_name
        );

        return str_replace($search, $replace, $content);
    }

    /**
     * Return the email address associated with a user ID
     *
     * @param int $user_id ID of user
     *
     * @return mixed string email address
     */
    public static function user_id_email($user_id)
    {
        return get_userdata($user_id)->user_email;
    }

    /**
     * Send Approval notification
     *
     * @param $user_id
     */
    public static function approve($user_id)
    {
        if (ppress_get_setting('account_approved_email_enabled', 'on') != 'on') return;

        /**
         * Notification Action that is fires when user is approved
         *
         * @param int $user_id ID pf user that is being approved
         */
        do_action('ppress_approval_notification', $user_id);

        $subject = ppress_get_setting('account_approved_email_subject', esc_html__('Your Account is Approved', 'profilepress-pro'), true);
        $message = ppress_get_setting('account_approved_email_content', ppress_user_moderation_msg_default('approved'), true);

        self::send_mail($user_id, $subject, $message);
    }

    /**
     * Send Rejection notification
     *
     * @param $user_id
     */
    public static function reject($user_id)
    {
        if (ppress_get_setting('account_rejected_email_enabled', 'on') != 'on') return;

        /**
         * Notification Action that is fires when user is approved
         *
         * @param int $user_id ID pf user that is being approved
         */
        do_action('ppress_rejection_notification', $user_id);

        $subject = ppress_get_setting('account_rejected_email_subject', esc_html__('Your Account Has Been Rejected', 'profilepress-pro'), true);
        $message = ppress_get_setting('account_rejected_email_content', ppress_user_moderation_msg_default('rejected'), true);

        self::send_mail($user_id, $subject, $message);
    }

    /**
     * Send account blocked notification
     *
     * @param $user_id
     */
    public static function block($user_id)
    {
        if (ppress_get_setting('account_blocked_email_enabled', 'on') != 'on') return;

        /**
         * Notification Action that is fires when user is blocked
         *
         * @param int $user_id ID pf user that is being blocked
         */
        do_action('ppress_blocked_notification', $user_id);

        $subject = ppress_get_setting('account_blocked_email_subject', esc_html__('Your Account is Blocked', 'profilepress-pro'), true);
        $message = ppress_get_setting('account_blocked_email_content', ppress_user_moderation_msg_default('blocked'), true);


        self::send_mail($user_id, $subject, $message);
    }

    /**
     * Send unblock notification
     *
     * @param $user_id
     */
    public static function unblock($user_id)
    {
        if (ppress_get_setting('account_unblocked_email_enabled', 'on') != 'on') return;

        /**
         * Notification Action that is fires when user is unblocked
         *
         * @param int $user_id ID pf user that is being unblocked
         */
        do_action('ppress_unblocked_notification', $user_id);

        $subject = ppress_get_setting('account_unblocked_email_subject', esc_html__('Your Account is Unblocked', 'profilepress-pro'), true);
        $message = ppress_get_setting('account_unblocked_email_content', ppress_user_moderation_msg_default('unblocked'), true);

        self::send_mail($user_id, $subject, $message);
    }

    /**
     * Send notification to users pending approval after signup
     *
     * @param $user_id
     */
    public static function pending($user_id)
    {
        if (ppress_get_setting('account_pending_review_email_enabled', 'on') != 'on') return;

        /**
         * Notification Action that is fires when user is pending approval.
         *
         * @param int $user_id ID pf user that is pending approval.
         */
        do_action('ppress_pending_notification', $user_id);

        $subject = ppress_get_setting('account_pending_review_email_subject', esc_html__('Account Awaiting Review', 'profilepress-pro'), true);
        $message = ppress_get_setting('account_pending_review_email_content', ppress_user_moderation_msg_default('pending'), true);

        self::send_mail($user_id, $subject, $message);
    }

    public static function parse_admin_notification($content, $user_id)
    {
        $user = get_userdata($user_id);

        $approval_url  = admin_url("users.php?action=ppress_approve_user&id=$user_id");
        $rejection_url = admin_url("users.php?action=ppress_reject_user&id=$user_id");


        // handle support for custom fields placeholder.
        preg_match_all('#({{[a-z_-]+}})#', $content, $matches);

        if (isset($matches[1]) && ! empty($matches[1])) {

            foreach ($matches[1] as $match) {
                $key = str_replace(['{', '}'], '', $match);

                if (isset($user->{$key})) {
                    $value = $user->{$key};

                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }

                    $content = str_replace($match, $value, $content);
                }
            }
        }

        $search = apply_filters(
            'ppress_pending_admin_notification_placeholder_search',
            ['{{username}}', '{{email}}', '{{first_name}}', '{{last_name}}', '{{approval_url}}', '{{rejection_url}}']
        );

        $replace = apply_filters(
            'ppress_pending_admin_notification_placeholder_replace',
            [$user->user_login, $user->user_email, $user->first_name, $user->last_name, $approval_url, $rejection_url]
        );

        return str_replace($search, $replace, $content);
    }

    /**
     * Send notification to admin when a user is pending approval.
     *
     * @param $user_id
     */
    public static function pending_admin_notification($user_id)
    {
        if (apply_filters('ppress_disable_pending_admin_notification', false) === true) return;

        if (ppress_get_setting('account_approval_admin_email_enabled', 'on') != 'on') return;

        $subject = ppress_get_setting('account_approval_admin_email_subject', esc_html__('Account Awaiting Approval', 'profilepress-pro'), true);
        $subject = self::parse_admin_notification($subject, $user_id);

        $message = ppress_get_setting('account_approval_admin_email_content', ppress_user_moderation_msg_default('admin_notification'), true);
        $message = self::parse_admin_notification($message, $user_id);

        $admin_email = apply_filters('ppress_pending_user_admin_notification_email', ppress_get_admin_notification_emails());

        ppress_send_email($admin_email, $subject, $message);
    }
}