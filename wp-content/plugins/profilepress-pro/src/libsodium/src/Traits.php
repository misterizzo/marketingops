<?php

namespace ProfilePress\Libsodium;

use ProfilePress\Core\Membership\Repositories\PlanRepository;

trait Traits
{
    /**
     * JS files
     */
    public function email_subscription_js()
    {
        wp_enqueue_script('ppesp-subscribe', PROFILEPRESS_PRO_LIBSODIUM_ASSETS_URL . 'esp/subscribe.js', ['jquery', 'ppress-jbox'], false, true);

        wp_localize_script('ppesp-subscribe', 'ppress_esp_globals', [
            'sync_tool_label'  => esc_html__('Sync Tool', 'profilepress-pro'),
            'nonce'            => wp_create_nonce('ppesp-sync-tool'),
            'processing_label' => esc_html__('Processing...', 'profilepress-pro'),
            'sync_success'     => sprintf(
                esc_html__('Synchronization request has been processed. It will take a while to show up in %s.', 'profilepress-pro'),
                '%esp%'
            ),
            'sync_error'       => esc_html__('There was an error processing the sync request. Please try again.', 'profilepress-pro'),
        ]);
    }

    public function admin_js_script($prefix = 'mc')
    {
        if ( ! ppress_is_admin_page()) return;
        ?>
        <script>
            (function ($) {
                $(function () {
                    $('#<?=$prefix?>_checkout_checkbox').on('change', function () {
                        $('#<?=$prefix?>_checkout_checkbox_label_row').toggle(this.checked);
                    }).trigger('change');
                });
            })(jQuery);
        </script>
        <?php
    }

    protected function parse_select_options($options)
    {
        foreach ($options as $key => $label) {
            if (is_array($label)) {
                printf('<optgroup label="%s">', $key);
                foreach ($label as $index => $option) {
                    printf('<option value="%s">%s</option>', $index, $option);
                }
                echo '</optgroup>';
            } else {
                printf('<option value="%s">%s</option>', $key, $label);
            }
        }
    }

    public function sync_tool_html($esp, $email_list_options = [])
    {
        $user_role_label       = esc_html__('User Roles', 'profilepress-pro');
        $membership_plan_label = esc_html__('Membership Plans', 'profilepress-pro');

        $select_options = ['all' => esc_html__('All Users', 'profilepress-pro')];

        foreach (PlanRepository::init()->retrieveAll() as $plan) {
            $select_options[$membership_plan_label][$plan->id] = $plan->name;
        }

        foreach (wp_roles()->roles as $role_id => $role) {
            $select_options[$user_role_label][$role_id] = $role['name'];
        }

        ob_start();
        ?>
        <style>
            #ppesp-sync-tool-modal {
                width: 100%;
                max-width: 400px;
            }

            #ppesp-sync-tool-modal .jBox-content label {
                display: block;
            }

            #ppesp-sync-tool-modal .jBox-content input,
            #ppesp-sync-tool-modal .jBox-content select,
            #ppesp-sync-tool-modal .jBox-content textarea {
                width: 100%;
                max-width: 100%;
            }
        </style>
        <div id="ppesp-sync-tool-modal" style="display: none">
            <div class="ppesp-sync-tool-modal-content" data-pp-esp="<?= $esp ?>">
                <p>
                    <label for="ppesp-sync-role"><?= esc_html__('Select a user role or membership plan', 'profilepress-pro') ?></label>
                    <select name="ppesp-sync-role" id="ppesp-sync-role">
                        <?php $this->parse_select_options($select_options); ?>
                    </select>
                <p>
                <p>
                    <label for="ppesp-sync-list"><?= esc_html__('Select List', 'profilepress-pro') ?></label>
                    <select name="ppesp-sync-list" id="ppesp-sync-list">
                        <?php $this->parse_select_options($email_list_options); ?>
                    </select>
                    <span class="description"><?= esc_html__('Select an audience or list to synchronize the users with.', 'profilepress-pro'); ?></span>
                <p>
                    <input type="submit" class="button button-primary" id="ppesp-sync-users-now-btn" value="<?= esc_html__('Sync Users', 'profilepress-pro') ?>">
                </p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}