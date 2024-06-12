<?php

namespace ProfilePress\Libsodium;

use ProfilePress\Core\Classes\ExtensionManager as EM;
use ProfilePress\Core\Classes\FormRepository as FR;
use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;

class LifterLMS
{
    public static $instance_flag = false;

    public function __construct()
    {
        add_action('plugins_loaded', function () {

            if (class_exists('\LifterLMS')) {
                add_filter('ppress_settings_page_args', [$this, 'settings_page']);

                add_filter('ppress_admin_membership_plan_metabox_settings', [$this, 'plan_edit_screen']);
                add_action('ppress_order_completed', [$this, 'on_order_completed']);
                add_action('ppress_subscription_cancelled', [$this, 'on_subscription_cancelled']);
                add_action('ppress_subscription_expired', [$this, 'on_subscription_cancelled']);

                add_action('ppress_after_registration', [$this, 'after_user_registration'], 10, 4);

                add_filter('ppress_shortcode_builder_registration_meta', [$this, 'save_shortcode_builder_settings']);
                add_action('ppress_shortcode_builder_registration_screen_settings', [$this, 'shortcode_builder_settings']);
                add_filter('ppress_form_builder_meta_box_settings', [$this, 'dnd_builder_settings'], 99);

                add_filter('ppress_myaccount_tabs', [$this, 'myaccount_tabs']);
            }
        });
    }

    public function after_user_registration($form_id, $user_data, $user_id, $is_melange)
    {
        $global_lifterlms_courses     = ppress_settings_by_key('lfi_courses', []);
        $global_lifterlms_memberships = ppress_settings_by_key('lfi_memberships', []);

        if (is_array($global_lifterlms_courses)) {
            $global_lifterlms_courses = array_filter($global_lifterlms_courses);
            if ( ! empty($global_lifterlms_courses)) {
                foreach ($global_lifterlms_courses as $course_id) {
                    llms_enroll_student($user_id, $course_id);
                }
            }
        }

        if (is_array($global_lifterlms_memberships)) {
            $global_lifterlms_memberships = array_filter($global_lifterlms_memberships);
            if ( ! empty($global_lifterlms_memberships)) {
                foreach ($global_lifterlms_memberships as $membership_id) {
                    llms_enroll_student($user_id, $membership_id);
                }
            }
        }

        if ($is_melange === true || ! $form_id) return;

        $form_id = intval($form_id);

        if (FR::is_drag_drop($form_id, FR::REGISTRATION_TYPE)) {
            $lifterlms_courses     = FR::get_dnd_metabox_setting('lifterlms_courses', $form_id, FR::REGISTRATION_TYPE);
            $lifterlms_memberships = FR::get_dnd_metabox_setting('lifterlms_memberships', $form_id, FR::REGISTRATION_TYPE);
        } else {
            $lifterlms_courses     = FR::get_form_meta($form_id, FR::REGISTRATION_TYPE, 'lifterlms_courses');
            $lifterlms_memberships = FR::get_form_meta($form_id, FR::REGISTRATION_TYPE, 'lifterlms_memberships');
        }

        if (is_array($lifterlms_courses)) {
            $lifterlms_courses = array_filter($lifterlms_courses);
            if ( ! empty($lifterlms_courses)) {
                foreach ($lifterlms_courses as $course_id) {
                    llms_enroll_student($user_id, $course_id);
                }
            }
        }

        if (is_array($lifterlms_memberships)) {
            $lifterlms_memberships = array_filter($lifterlms_memberships);
            if ( ! empty($lifterlms_memberships)) {
                foreach ($lifterlms_memberships as $membership_id) {
                    llms_enroll_student($user_id, $membership_id);
                }
            }
        }
    }

    /**
     * @param OrderEntity $order
     *
     * @return void
     */
    public function on_order_completed($order)
    {
        $user_id = $order->get_customer()->get_user_id();

        $plan = $order->get_plan();

        $courses = $plan->get_plan_extras('lifterlms_courses');

        $memberships = $plan->get_plan_extras('lifterlms_memberships');

        if (is_array($courses)) {
            $courses = array_filter($courses);
            if ( ! empty($courses)) {
                foreach ($courses as $course_id) {
                    llms_enroll_student($user_id, $course_id, 'ProfilePress');
                }
            }
        }

        if (is_array($memberships)) {
            $memberships = array_filter($memberships);
            if ( ! empty($memberships)) {
                foreach ($memberships as $membership_id) {
                    llms_enroll_student($user_id, $membership_id, 'ProfilePress');
                }
            }
        }
    }

    /**
     * @param SubscriptionEntity $subscription
     *
     * @return void
     */
    public function on_subscription_cancelled($subscription)
    {
        // doing SubscriptionFactory call because we want to re-fetch from DB.
        if (SubscriptionFactory::fromId($subscription->id)->is_active()) return;

        $user_id = $subscription->get_customer()->get_user_id();

        $plan = $subscription->get_plan();

        $courses = $plan->get_plan_extras('lifterlms_courses');

        $memberships = $plan->get_plan_extras('lifterlms_memberships');

        if (is_array($courses)) {
            $courses = array_filter($courses);
            if ( ! empty($courses)) {
                foreach ($courses as $course_id) {
                    llms_unenroll_student($user_id, $course_id);
                }
            }
        }

        if (is_array($memberships)) {
            $memberships = array_filter($memberships);
            if ( ! empty($memberships)) {
                foreach ($memberships as $membership_id) {
                    llms_unenroll_student($user_id, $membership_id);
                }
            }
        }
    }

    private function get_courses($key_value = false)
    {
        $result = get_posts([
            'post_type'      => 'course',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'post_title',
            'order'          => 'ASC',
        ]);

        if ($key_value === false) return $result;

        $output = [];

        foreach ($result as $course) {
            $output[$course->ID] = $course->post_title;
        }

        return $output;
    }

    private function get_memberships($key_value = false)
    {
        $result = get_posts([
            'post_type'      => 'llms_membership',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'post_title',
            'order'          => 'ASC',
        ]);

        if ($key_value === false) return $result;

        $output = [];

        foreach ($result as $course) {
            $output[$course->ID] = $course->post_title;
        }

        return $output;
    }

    public function myaccount_tabs($tabs)
    {
        $tabs_to_show = ppress_settings_by_key('lfi_myacc_display_profile', []);

        if ('true' == $tabs_to_show) {

            $tabs['lfi-courses'] = [
                'title'    => esc_html__('Courses', 'profilepress-pro'),
                'endpoint' => 'llms-courses',
                'priority' => 38,
                'icon'     => 'school',
                'callback' => [__CLASS__, 'ld_courses_myac_page']
            ];
        }

        return apply_filters('ppress_my_account_lifterlms_tabs', $tabs);
    }

    public static function ld_courses_myac_page()
    {
        echo '<div id="ppress-lifterlms-profile">';
        lifterlms_template_student_dashboard_my_courses();
        echo '</div>';
    }

    /**
     * @param array $args
     *
     * @return array
     */
    public function settings_page($args)
    {
        $args['pp_lifterlms_settings'] = [
            'tab_title'                 => esc_html__('LifterLMS', 'profilepress-pro'),
            'section_title'             => esc_html__('LifterLMS Settings', 'profilepress-pro'),
            'dashicon'                  => 'dashicons-welcome-learn-more',
            'lfi_courses'               => array(
                'type'        => 'select2',
                'options'     => $this->get_courses(true),
                'label'       => __('Courses', 'profilepress-pro'),
                'description' => esc_attr__('Select the LifterLMS courses to enroll users in after user registration.', 'profilepress-pro'),
            ),
            'lfi_memberships'           => array(
                'type'        => 'select2',
                'options'     => $this->get_memberships(true),
                'label'       => __('Memberships', 'profilepress-pro'),
                'description' => esc_attr__('Select the LifterLMS memberships to enroll users in after user registration.', 'profilepress-pro'),
            ),
            'lfi_myacc_display_profile' => array(
                'type'           => 'checkbox',
                'checkbox_label' => esc_html__('Enable', 'profilepress-pro'),
                'label'          => __('Courses My Account Menu', 'profilepress-pro'),
                'description'    => sprintf(
                    __("Enable to add a Course menu to the My Account Page that will display enrolled courses of users in LifterLMS. %sLearn more%s", 'profilepress-pro'),
                    '<a target="_blank" href="https://profilepress.com/article/setting-up-lifterlms-addon/">', '</a>'
                ),
            ),
        ];

        return $args;
    }

    public function plan_edit_screen($settings)
    {
        $settings['lifterlms'] = [
            'tab_title' => esc_html__('LifterLMS', 'profilepress-pro'),
            [
                'id'          => 'lifterlms_courses',
                'type'        => 'select2',
                'options'     => $this->get_courses(true),
                'label'       => esc_html__('Select Courses', 'profilepress-pro'),
                'description' => esc_html__('Select the LifterLMS courses to enroll users in that subscribe to this plan.', 'profilepress-pro'),
                'priority'    => 5
            ],
            [
                'id'          => 'lifterlms_memberships',
                'type'        => 'select2',
                'options'     => $this->get_memberships(true),
                'label'       => esc_html__('Select Memberships', 'profilepress-pro'),
                'description' => esc_html__('Select the LifterLMS memberships to enroll users in that subscribe to this plan.', 'profilepress-pro'),
                'priority'    => 10
            ]
        ];

        return $settings;
    }

    public function save_shortcode_builder_settings($settings)
    {
        $settings['lifterlms_courses']     = array_map('intval', $_POST['rfb_lifterlms_courses'] ?? []);
        $settings['lifterlms_memberships'] = array_map('intval', $_POST['rfb_lifterlms_memberships'] ?? []);

        return $settings;
    }

    public function dnd_builder_settings($meta_box_settings)
    {
        $meta_box_settings['lifterlms'] = [
            'tab_title' => esc_html__('LifterLMS', 'profilepress-pro'),
            [
                'id'          => 'lifterlms_courses',
                'type'        => 'select2',
                'options'     => $this->get_courses(true),
                'label'       => esc_html__('Select Courses', 'profilepress-pro'),
                'description' => esc_attr__('Select the LifterLMS courses to enroll users in after registration through this form.', 'profilepress-pro'),
                'priority'    => 5
            ],
            [
                'id'          => 'lifterlms_memberships',
                'type'        => 'select2',
                'options'     => $this->get_memberships(true),
                'label'       => esc_html__('Select Memberships', 'profilepress-pro'),
                'description' => esc_attr__('Select the LifterLMS memberships to enroll users in after registration through this form.', 'profilepress-pro'),
                'priority'    => 10
            ]
        ];

        return $meta_box_settings;
    }

    public function shortcode_builder_settings($form_id)
    {
        $saved_courses = FR::get_form_meta($form_id, FR::REGISTRATION_TYPE, 'lifterlms_courses');
        if (empty($saved_courses)) $saved_courses = [];

        $saved_memberships = FR::get_form_meta($form_id, FR::REGISTRATION_TYPE, 'lifterlms_memberships');
        if (empty($saved_memberships)) $saved_memberships = [];

        ?>
        <style>.select2-container {
                width: 100% !important;
            }</style>
        <h4 class="ppSCB-tab-content-header"><?= esc_html__('LifterLMS', 'profilepress-pro') ?></h4>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="lifterlms_courses"><?php esc_attr_e('Courses', 'profilepress-pro'); ?></label>
                </th>
                <td>
                    <select class="ppselect2" name="rfb_lifterlms_courses[]" id="lifterlms_courses" multiple>
                        <?php foreach ($this->get_courses() as $course) : ?>
                            <option value="<?= $course->ID ?>"<?= in_array($course->ID, $saved_courses) ? ' selected' : ''; ?>><?= $course->post_title ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_attr_e('Select the LifterLMS courses to enroll users in after registration through this form', 'profilepress-pro'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="lifterlms_memberships"><?php esc_attr_e('Memberships', 'profilepress-pro'); ?></label>
                </th>
                <td>
                    <select class="ppselect2" name="rfb_lifterlms_memberships[]" id="lifterlms_memberships" multiple>
                        <?php foreach ($this->get_memberships() as $membership) : ?>
                            <option value="<?= $membership->ID ?>"<?= in_array($membership->ID, $saved_memberships) ? ' selected' : ''; ?>><?= $membership->post_title ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_attr_e('Select the LifterLMS memberships to enroll users in after registration through this form.', 'profilepress-pro'); ?></p>
                </td>
            </tr>
        </table>
        <div class="ppSCB-clear-both"></div>
        <?php
    }

    /**
     * @return self|void
     */
    public static function get_instance()
    {
        self::$instance_flag = true;

        if ( ! defined('ProfilePress\Core\Classes\ExtensionManager::LIFTERLMS')) return;

        if ( ! EM::is_enabled(EM::LIFTERLMS)) return;

        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}