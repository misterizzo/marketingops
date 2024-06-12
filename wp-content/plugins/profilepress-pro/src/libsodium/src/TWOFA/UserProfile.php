<?php

namespace ProfilePress\Libsodium\TWOFA;

class UserProfile
{
    const NOTICES_META_KEY = '_ppress_2fa_notices';

    public function __construct()
    {
        add_action('show_user_profile', [$this, 'wp_user_profile_2fa'], -99);
        add_action('edit_user_profile', [$this, 'wp_user_profile_2fa'], -99);
        add_action('admin_init', [$this, 'wp_user_profile_save_2fa']);
        add_action('admin_init', [$this, 'wp_user_profile_disable_2fa']);
    }

    public function admin_notices($user_id)
    {
        $notices = get_user_meta($user_id, self::NOTICES_META_KEY, true);

        if ( ! empty($notices)) {

            delete_user_meta($user_id, self::NOTICES_META_KEY);

            foreach ($notices as $message) { ?>
                <div class="pp2fa-up-notice">
                    <p><?php echo esc_html($message); ?></p>
                </div>
                <?php
            }
        }
    }

    public function wp_user_profile_2fa($user)
    {
        if ( ! isset($user->ID)) return;

        if ( ! Common::can_configure_2fa($user->ID)) return;

        ?>
        <table class="form-table" id="ppress-2fa">
            <tr>
                <th>
                    <label for="ppress-2fa"><?php _e('Two-Factor Authentication', 'profilepress-pro'); ?></label>
                </th>
                <td>
                    <?php if (IS_PROFILE_PAGE): // prevents admin for setting 2fa for users ?>
                        <?php $this->admin_notices($user->ID); ?>
                        <?php echo AbstractClass::twofa_setup_page_content($user->ID); ?>
                    <?php else : ?>

                        <?php if (Common::has_2fa_configured($user->ID)) : ?>

                            <button type="button" id="ppress-2fa-reset" class="button">
                                <?php _e('Disable 2-Factor Authentication', 'profilepress-pro'); ?>
                            </button>
                            <p class="description"><?php _e('When you disable the user\'s 2FA configuration, they can log back in with just their username and password.', 'profilepress-pro'); ?></p>

                        <?php else: ?>
                            <p><?php esc_html_e('2FA has not be configured by this user.', 'profilepress-pro'); ?></p>
                        <?php endif; ?>

                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <script type="text/javascript">
            (function ($) {
                $('#ppress-2fa-reset').on('click', function (e) {

                    e.preventDefault();

                    if (confirm('<?php esc_html_e('Are you sure you want to do this?', 'profilepress-pro')?>')) {
                        var data,
                            btn = $(this),
                            btnText = btn.text();

                        btn.attr('disabled', 'disabled')
                            .text('<?php esc_html_e('Resetting...', 'profilepress-pro')?>');

                        data = {
                            action: 'ppress_2fa_reset_user',
                            user_id: <?php echo $user->ID;?>,
                            pp2fa_nonce: '<?= wp_create_nonce('ppress_2fa_reset_user') ?>'
                        };

                        $.post(ajaxurl, data, function (response) {
                            if ('success' in response && response.success === true) {
                                btn.text(response.data);
                                alert(response.data);
                            } else {
                                btn.removeAttr('disabled');
                                btn.text(btnText);
                            }
                        });
                    }
                });
            })(jQuery);
        </script>
        <?php
    }

    public function wp_user_profile_save_2fa()
    {
        $user_id = get_current_user_id();

        if ( ! current_user_can('edit_user', $user_id)) return;

        if ( ! isset($_POST['ppress_2fa_secret']) || ! isset($_POST['ppmyac_2fa_authcode_submit'])) return;

        check_admin_referer('ppmyac_2fa_complete_setup', 'ppmyac_2fa_complete_setup_nonce');

        $notices = [];
        $error   = '';

        $secret_code          = sanitize_text_field($_POST['ppress_2fa_secret']);
        $activation_auth_code = (string)absint($_POST['ppmyac_2fa_authcode']);

        if ( ! Common::verify_auth_code($activation_auth_code, $secret_code)) {
            $error = Common::invalid_2fa_code_error();
        } elseif ( ! Common::set_user_2fa_secret(get_current_user_id(), $secret_code)) {
            $error = Common::unable_to_save_2fa_error();
        }

        if ( ! empty($error)) $notices[] = $error;

        if ( ! empty($notices)) {
            update_user_meta($user_id, self::NOTICES_META_KEY, $notices);
        }

        wp_safe_redirect(get_edit_profile_url($user_id) . '#ppress-2fa');
        exit;
    }

    public function wp_user_profile_disable_2fa()
    {
        $user_id = get_current_user_id();

        if ( ! current_user_can('edit_user', $user_id)) return;

        if (empty($_GET['ppress-2fa-reset'])) return;

        check_admin_referer('ppmyac_disable_2fa', 'ppress-2fa-reset');

        Common::disable_2fa($user_id);

        wp_safe_redirect(get_edit_profile_url($user_id) . '#ppress-2fa');
        exit;
    }

    /**
     * @return self
     */
    public static function get_instance()
    {
        static $instance;

        if ( ! isset($instance)) {
            $instance = new self;
        }

        return $instance;
    }
}