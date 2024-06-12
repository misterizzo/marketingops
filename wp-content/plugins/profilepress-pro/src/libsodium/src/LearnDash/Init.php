<?php

namespace ProfilePress\Libsodium\LearnDash;

use ProfilePress\Core\Classes\ExtensionManager as EM;
use ProfilePress\Core\Classes\FormRepository as FR;
use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;

class Init
{
    public static $instance_flag = false;

    public function __construct()
    {
        add_action('learndash_loaded', function () {

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
        });
    }

    public function after_user_registration($form_id, $user_data, $user_id, $is_melange)
    {
        $global_learndash_courses = ppress_settings_by_key('ldi_courses', []);
        $global_learndash_groups  = ppress_settings_by_key('ldi_groups', []);

        if (is_array($global_learndash_courses)) {
            $global_learndash_courses = array_filter($global_learndash_courses);
            if ( ! empty($global_learndash_courses)) {
                foreach ($global_learndash_courses as $course_id) {
                    ld_update_course_access($user_id, $course_id);
                }
            }
        }

        if (is_array($global_learndash_groups)) {
            $global_learndash_groups = array_filter($global_learndash_groups);
            if ( ! empty($global_learndash_groups)) {
                foreach ($global_learndash_groups as $group_id) {
                    ld_update_group_access($user_id, $group_id);
                }
            }
        }

        if ($is_melange === true || ! $form_id) return;

        $form_id = intval($form_id);

        if (FR::is_drag_drop($form_id, FR::REGISTRATION_TYPE)) {
            $learndash_courses = FR::get_dnd_metabox_setting('learndash_courses', $form_id, FR::REGISTRATION_TYPE);
            $learndash_groups  = FR::get_dnd_metabox_setting('learndash_groups', $form_id, FR::REGISTRATION_TYPE);
        } else {
            $learndash_courses = FR::get_form_meta($form_id, FR::REGISTRATION_TYPE, 'learndash_courses');
            $learndash_groups  = FR::get_form_meta($form_id, FR::REGISTRATION_TYPE, 'learndash_groups');
        }

        if (is_array($learndash_courses)) {
            $learndash_courses = array_filter($learndash_courses);
            if ( ! empty($learndash_courses)) {
                foreach ($learndash_courses as $course_id) {
                    ld_update_course_access($user_id, $course_id);
                }
            }
        }

        if (is_array($learndash_groups)) {
            $learndash_groups = array_filter($learndash_groups);
            if ( ! empty($learndash_groups)) {
                foreach ($learndash_groups as $group_id) {
                    ld_update_group_access($user_id, $group_id);
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

        $courses = $plan->get_plan_extras('learndash_courses');

        $groups = $plan->get_plan_extras('learndash_groups');

        if (is_array($courses)) {
            $courses = array_filter($courses);
            if ( ! empty($courses)) {
                foreach ($courses as $course_id) {
                    ld_update_course_access($user_id, $course_id);
                }
            }
        }

        if (is_array($groups)) {
            $groups = array_filter($groups);
            if ( ! empty($groups)) {
                foreach ($groups as $group_id) {
                    ld_update_group_access($user_id, $group_id);
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

        $courses = $plan->get_plan_extras('learndash_courses');

        $groups = $plan->get_plan_extras('learndash_groups');

        if (is_array($courses)) {
            $courses = array_filter($courses);
            if ( ! empty($courses)) {
                foreach ($courses as $course_id) {
                    ld_update_course_access($user_id, $course_id, true);
                }
            }
        }

        if (is_array($groups)) {
            $groups = array_filter($groups);
            if ( ! empty($groups)) {
                foreach ($groups as $group_id) {
                    ld_update_group_access($user_id, $group_id, true);
                }
            }
        }
    }

    private function get_courses($key_value = false)
    {
        $result = get_posts([
            'post_type'      => 'sfwd-courses',
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

    private function get_groups($key_value = false)
    {
        $result = get_posts([
            'post_type'      => 'groups',
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
        $tabs_to_show = ppress_settings_by_key('ldi_myacc_display_profile', []);

        if ('true' == $tabs_to_show) {

            $tabs['ld-courses'] = [
                'title'    => esc_html__('Courses', 'profilepress-pro'),
                'endpoint' => 'ld-courses',
                'priority' => 38,
                'icon'     => 'school',
                'callback' => [__CLASS__, 'ld_courses_myac_page']
            ];
        }

        return apply_filters('ppress_my_account_learndash_tabs', $tabs);
    }

    public static function ld_courses_myac_page()
    {
        echo '<div id="ppress-learndash-profile">';
        echo do_shortcode('[ld_profile show_header="no"]');
        echo '</div>';
    }

    /**
     * @param array $args
     *
     * @return array
     */
    public function settings_page($args)
    {
        $args['pp_ld_settings'] = [
            'tab_title'                 => esc_html__('LearnDash', 'profilepress-pro'),
            'section_title'             => esc_html__('LearnDash Settings', 'profilepress-pro'),
            'dashicon'                  => 'dashicons-welcome-learn-more',
            'ldi_courses'               => array(
                'type'        => 'select2',
                'options'     => $this->get_courses(true),
                'label'       => __('Courses', 'profilepress-pro'),
                'description' => esc_attr__('Select the LearnDash courses to enroll users in after user registration.', 'profilepress-pro'),
            ),
            'ldi_groups'                => array(
                'type'        => 'select2',
                'options'     => $this->get_groups(true),
                'label'       => __('Groups', 'profilepress-pro'),
                'description' => esc_attr__('Select the LearnDash courses to enroll users in after user registration.', 'profilepress-pro'),
            ),
            'ldi_myacc_display_profile' => array(
                'type'           => 'checkbox',
                'checkbox_label' => esc_html__('Enable', 'profilepress-pro'),
                'label'          => __('Courses My Account Menu', 'profilepress-pro'),
                'description'    => sprintf(
                    __("Enable to add a Course menu to the My Account Page that will display enrolled courses of users in LearnDash. %sLearn more%s", 'profilepress-pro'),
                    '<a target="_blank" href="https://profilepress.com/article/setting-up-learndash-addon/">', '</a>'
                ),
            ),
        ];

        return $args;
    }

    public function plan_edit_screen($settings)
    {
        $settings['learndash'] = [
            'tab_title' => esc_html__('LearnDash', 'profilepress-pro'),
            [
                'id'          => 'learndash_courses',
                'type'        => 'select2',
                'options'     => $this->get_courses(true),
                'label'       => esc_html__('Select Courses', 'profilepress-pro'),
                'description' => esc_html__('Select the LearnDash courses to enroll users in that subscribe to this plan.', 'profilepress-pro'),
                'priority'    => 5
            ],
            [
                'id'          => 'learndash_groups',
                'type'        => 'select2',
                'options'     => $this->get_groups(true),
                'label'       => esc_html__('Select Groups', 'profilepress-pro'),
                'description' => esc_html__('Select the LearnDash groups to enroll users in that subscribe to this plan.', 'profilepress-pro'),
                'priority'    => 10
            ]
        ];

        return $settings;
    }

    public function save_shortcode_builder_settings($settings)
    {
        $settings['learndash_courses'] = array_map('intval', $_POST['rfb_learndash_courses'] ?? []);
        $settings['learndash_groups']  = array_map('intval', $_POST['rfb_learndash_groups'] ?? []);

        return $settings;
    }

    public function dnd_builder_settings($meta_box_settings)
    {
        $meta_box_settings['learndash'] = [
            'tab_title' => esc_html__('LearnDash', 'profilepress-pro'),
            [
                'id'          => 'learndash_courses',
                'type'        => 'select2',
                'options'     => $this->get_courses(true),
                'label'       => esc_html__('Select Courses', 'profilepress-pro'),
                'description' => esc_attr__('Select the LearnDash courses to enroll users in after registration through this form.', 'profilepress-pro'),
                'priority'    => 5
            ],
            [
                'id'          => 'learndash_groups',
                'type'        => 'select2',
                'options'     => $this->get_groups(true),
                'label'       => esc_html__('Select Groups', 'profilepress-pro'),
                'description' => esc_attr__('Select the LearnDash groups to enroll users in after registration through this form.', 'profilepress-pro'),
                'priority'    => 10
            ]
        ];

        return $meta_box_settings;
    }

    public function shortcode_builder_settings($form_id)
    {
        $saved_courses = FR::get_form_meta($form_id, FR::REGISTRATION_TYPE, 'learndash_courses');
        if (empty($saved_courses)) $saved_courses = [];

        $saved_groups = FR::get_form_meta($form_id, FR::REGISTRATION_TYPE, 'learndash_groups');
        if (empty($saved_groups)) $saved_groups = [];

        ?>
        <style>.select2-container {
                width: 100% !important;
            }</style>
        <h4 class="ppSCB-tab-content-header"><?= esc_html__('LearnDash', 'profilepress-pro') ?></h4>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="learndash_courses"><?php esc_attr_e('Courses', 'profilepress-pro'); ?></label>
                </th>
                <td>
                    <select class="ppselect2" name="rfb_learndash_courses[]" id="learndash_courses" multiple>
                        <?php foreach ($this->get_courses() as $course) : ?>
                            <option value="<?= $course->ID ?>"<?= in_array($course->ID, $saved_courses) ? ' selected' : ''; ?>><?= $course->post_title ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_attr_e('Select the LearnDash courses to enroll users in after registration through this form', 'profilepress-pro'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="learndash_groups"><?php esc_attr_e('Groups', 'profilepress-pro'); ?></label>
                </th>
                <td>
                    <select class="ppselect2" name="rfb_learndash_groups[]" id="learndash_groups" multiple>
                        <?php foreach ($this->get_groups() as $group) : ?>
                            <option value="<?= $group->ID ?>"<?= in_array($group->ID, $saved_groups) ? ' selected' : ''; ?>><?= $group->post_title ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_attr_e('Select the LearnDash groups to enroll users in after registration through this form.', 'profilepress-pro'); ?></p>
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

        if ( ! defined('ProfilePress\Core\Classes\ExtensionManager::LEARNDASH')) return;

        if ( ! EM::is_enabled(EM::LEARNDASH)) return;

        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}