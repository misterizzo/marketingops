<?php

namespace ProfilePress\Libsodium\SenseiLMS;

use ProfilePress\Core\Classes\ExtensionManager as EM;
use ProfilePress\Core\Classes\FormRepository as FR;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\Repositories\PlanRepository;
use Sensei_Pro_Student_Groups\Repositories\Group_Student_Repository;

class Init
{
    public static $instance_flag = false;

    /** @var callable Take course block render callback. */
    private $render_take_course;

    /** @var int Course ID */
    private $course_id;

    /** @var int Course plan ID */
    private $course_pp_plan_id;

    /** @var string Button HTML. */
    private $button;

    public function __construct()
    {
        add_action('plugins_loaded', function () {

            if (function_exists('Sensei')) {

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

                add_filter('sensei_block_type_args', [$this, 'extend_take_course_block'], 10, 2);
            }
        });
    }

    private function add_to_remove_from_group($group_id, $user_id, $remove_action = false)
    {
        if (class_exists('Sensei_Pro_Student_Groups\Repositories\Group_Student_Repository')) {

            global $wpdb;

            $instance = (new Group_Student_Repository($wpdb));

            if ($remove_action === false) {
                $instance->create(get_post($group_id), get_userdata($user_id));
            } else {
                $instance->delete(get_post($group_id), get_userdata($user_id));
            }
        }
    }

    public function after_user_registration($form_id, $user_data, $user_id, $is_melange)
    {
        $global_sensei_courses = ppress_settings_by_key('sensei_courses', []);
        $global_sensei_groups  = ppress_settings_by_key('sensei_groups', []);

        if (is_array($global_sensei_courses)) {
            $global_sensei_courses = array_filter($global_sensei_courses);
            if ( ! empty($global_sensei_courses)) {
                foreach ($global_sensei_courses as $course_id) {
                    \Sensei_Course_Enrolment::get_course_instance($course_id)->enrol($user_id);
                }
            }
        }

        if (is_array($global_sensei_groups)) {
            $global_sensei_groups = array_filter($global_sensei_groups);
            if ( ! empty($global_sensei_groups)) {
                foreach ($global_sensei_groups as $group_id) {
                    $this->add_to_remove_from_group($group_id, $user_id);
                }
            }
        }

        if ($is_melange === true || ! $form_id) return;

        $form_id = intval($form_id);

        if (FR::is_drag_drop($form_id, FR::REGISTRATION_TYPE)) {
            $sensei_courses = FR::get_dnd_metabox_setting('sensei_courses', $form_id, FR::REGISTRATION_TYPE);
            $sensei_groups  = FR::get_dnd_metabox_setting('sensei_groups', $form_id, FR::REGISTRATION_TYPE);
        } else {
            $sensei_courses = FR::get_form_meta($form_id, FR::REGISTRATION_TYPE, 'sensei_courses');
            $sensei_groups  = FR::get_form_meta($form_id, FR::REGISTRATION_TYPE, 'sensei_groups');
        }

        if (is_array($sensei_courses)) {
            $sensei_courses = array_filter($sensei_courses);
            if ( ! empty($sensei_courses)) {
                foreach ($sensei_courses as $course_id) {
                    \Sensei_Course_Enrolment::get_course_instance($course_id)->enrol($user_id);
                }
            }
        }

        if (is_array($sensei_groups)) {
            $sensei_groups = array_filter($sensei_groups);
            if ( ! empty($sensei_groups)) {
                foreach ($sensei_groups as $group_id) {
                    $this->add_to_remove_from_group($group_id, $user_id);
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

        $courses = $plan->get_plan_extras('sensei_courses');

        $groups = $plan->get_plan_extras('sensei_groups');

        if (is_array($courses)) {
            $courses = array_filter($courses);
            if ( ! empty($courses)) {
                foreach ($courses as $course_id) {
                    \Sensei_Course_Enrolment::get_course_instance($course_id)->enrol($user_id);
                }
            }
        }

        if (is_array($groups)) {
            $groups = array_filter($groups);
            if ( ! empty($groups)) {
                foreach ($groups as $group_id) {
                    $this->add_to_remove_from_group($group_id, $user_id);
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

        $courses = $plan->get_plan_extras('sensei_courses');

        $groups = $plan->get_plan_extras('sensei_groups');

        if (is_array($courses)) {
            $courses = array_filter($courses);
            if ( ! empty($courses)) {
                foreach ($courses as $course_id) {
                    \Sensei_Course_Enrolment::get_course_instance($course_id)->withdraw($user_id);
                }
            }
        }

        if (is_array($groups)) {
            $groups = array_filter($groups);
            if ( ! empty($groups)) {
                foreach ($groups as $group_id) {
                    $this->add_to_remove_from_group($group_id, $user_id, true);
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

    private function get_groups($key_value = false)
    {
        $result = get_posts([
            'post_type'      => 'group',
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
        $tabs_to_show = ppress_settings_by_key('sensei_myacc_display_profile', []);

        if ('true' == $tabs_to_show) {

            $tabs['sensei-courses'] = [
                'title'    => esc_html__('Courses', 'profilepress-pro'),
                'endpoint' => 'sensei-courses',
                'priority' => 38,
                'icon'     => 'school',
                'callback' => [__CLASS__, 'sensei_courses_myac_page']
            ];
        }

        return apply_filters('ppress_my_account_sensei_tabs', $tabs);
    }

    public static function sensei_courses_myac_page()
    {
        add_filter('sensei_shortcode_user_courses_display_course_toggle_actions', '__return_false');
        echo '<div id="ppress-sensei-profile">';
        echo do_shortcode('[sensei_user_courses]');
        echo '</div>';
    }

    /**
     * @param array $args
     *
     * @return array
     */
    public function settings_page($args)
    {
        $args['pp_sensei_settings'] = [
            'tab_title'                    => esc_html__('Sensei LMS', 'profilepress-pro'),
            'section_title'                => esc_html__('Sensei LMS Settings', 'profilepress-pro'),
            'dashicon'                     => 'dashicons-welcome-learn-more',
            'sensei_courses'               => [
                'type'        => 'select2',
                'options'     => $this->get_courses(true),
                'label'       => __('Courses', 'profilepress-pro'),
                'description' => esc_attr__('Select the Sensei courses to enroll users in after user registration.', 'profilepress-pro'),
            ],
            'sensei_groups'                => [
                'type'        => 'select2',
                'options'     => $this->get_groups(true),
                'label'       => __('Groups', 'profilepress-pro'),
                'description' => esc_attr__('Select the Sensei groups to add users after user registration.', 'profilepress-pro'),
            ],
            'sensei_myacc_display_profile' => [
                'type'           => 'checkbox',
                'checkbox_label' => esc_html__('Enable', 'profilepress-pro'),
                'label'          => __('Courses My Account Menu', 'profilepress-pro'),
                'description'    => sprintf(
                    __("Enable to add a Course menu to the My Account Page that will display enrolled courses of users in Sensei. %sLearn more%s", 'profilepress-pro'),
                    '<a target="_blank" href="https://profilepress.com/article/setting-up-sensei-lms-addon/">', '</a>'
                ),
            ]
        ];

        if ( ! class_exists('\Sensei_Pro_Student_Groups\Student_Groups')) {
            unset($args['pp_sensei_settings']['sensei_groups']);
        }

        return $args;
    }

    public function plan_edit_screen($settings)
    {
        $settings['sensei'] = [
            'tab_title' => esc_html__('Sensei LMS', 'profilepress-pro'),
            [
                'id'          => 'sensei_courses',
                'type'        => 'select2',
                'options'     => $this->get_courses(true),
                'label'       => esc_html__('Select Courses', 'profilepress-pro'),
                'description' => esc_html__('Select the Sensei courses to enroll users in that subscribe to this plan.', 'profilepress-pro'),
                'priority'    => 5
            ],
            [
                'id'          => 'sensei_groups',
                'type'        => 'select2',
                'options'     => $this->get_groups(true),
                'label'       => esc_html__('Select Groups', 'profilepress-pro'),
                'description' => esc_html__('Select the Sensei groups to add users that subscribe to this plan.', 'profilepress-pro'),
                'priority'    => 10
            ]
        ];

        if ( ! class_exists('\Sensei_Pro_Student_Groups\Student_Groups')) {
            unset($settings['sensei'][1]);
        }

        return $settings;
    }

    public function save_shortcode_builder_settings($settings)
    {
        $settings['sensei_courses'] = array_map('intval', $_POST['rfb_sensei_courses'] ?? []);

        $settings['sensei_groups'] = array_map('intval', $_POST['rfb_sensei_groups'] ?? []);

        return $settings;
    }

    public function dnd_builder_settings($meta_box_settings)
    {
        $meta_box_settings['sensei'] = [
            'tab_title' => esc_html__('Sensei LMS', 'profilepress-pro'),
            [
                'id'          => 'sensei_courses',
                'type'        => 'select2',
                'options'     => $this->get_courses(true),
                'label'       => esc_html__('Select Courses', 'profilepress-pro'),
                'description' => esc_attr__('Select the Sensei courses to enroll users in after registration through this form.', 'profilepress-pro'),
                'priority'    => 5
            ],
            [
                'id'          => 'sensei_groups',
                'type'        => 'select2',
                'options'     => $this->get_groups(true),
                'label'       => esc_html__('Select Groups', 'profilepress-pro'),
                'description' => esc_attr__('Select the Sensei groups to add users after registration through this form.', 'profilepress-pro'),
                'priority'    => 10
            ]
        ];

        if ( ! class_exists('\Sensei_Pro_Student_Groups\Student_Groups')) {
            unset($meta_box_settings['sensei'][1]);
        }

        return $meta_box_settings;
    }

    public function shortcode_builder_settings($form_id)
    {
        $saved_courses = FR::get_form_meta($form_id, FR::REGISTRATION_TYPE, 'sensei_courses');
        if (empty($saved_courses)) $saved_courses = [];

        $saved_groups = FR::get_form_meta($form_id, FR::REGISTRATION_TYPE, 'sensei_groups');
        if (empty($saved_groups)) $saved_groups = [];

        ?>
        <style>.select2-container {
                width: 100% !important;
            }</style>
        <h4 class="ppSCB-tab-content-header"><?= esc_html__('Sensei LMS', 'profilepress-pro') ?></h4>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="sensei_courses"><?php esc_attr_e('Courses', 'profilepress-pro'); ?></label>
                </th>
                <td>
                    <select class="ppselect2" name="rfb_sensei_courses[]" id="sensei_courses" multiple>
                        <?php foreach ($this->get_courses() as $course) : ?>
                            <option value="<?= $course->ID ?>"<?= in_array($course->ID, $saved_courses) ? ' selected' : ''; ?>><?= $course->post_title ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_attr_e('Select the Sensei courses to enroll users in after registration through this form', 'profilepress-pro'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="sensei_groups"><?php esc_attr_e('Groups', 'profilepress-pro'); ?></label>
                </th>
                <td>
                    <select class="ppselect2" name="rfb_sensei_groups[]" id="sensei_groups" multiple>
                        <?php foreach ($this->get_groups() as $group) : ?>
                            <option value="<?= $group->ID ?>"<?= in_array($group->ID, $saved_groups) ? ' selected' : ''; ?>><?= $group->post_title ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_attr_e('Select the Sensei groups to add users after registration through this form.', 'profilepress-pro'); ?></p>
                </td>
            </tr>
        </table>
        <div class="ppSCB-clear-both"></div>
        <?php
    }

    private function get_course_plan_id($course_id)
    {
        $plans = PlanRepository::init()->retrieveAll();

        $course_id = (int)$course_id;

        foreach ($plans as $plan) {

            if ($plan->is_active()) {

                $courses = $plan->get_plan_extras('sensei_courses');

                $courses = is_array($courses) ? array_map('absint', $plan->get_plan_extras('sensei_courses')) : [];

                if (is_array($courses) && in_array($course_id, $courses, true)) {
                    return $plan->get_id();
                }
            }
        }

        return false;
    }

    private function is_customer_subscribed_to_course_plan($course_pp_plan_id)
    {
        if ( ! empty($course_pp_plan_id)) {

            $customer = CustomerFactory::fromUserId(get_current_user_id());

            if ($customer->exists() && $customer->has_active_subscription($course_pp_plan_id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extend take course block.
     * Culled from sensei-pro/modules/wc-paid-courses/includes/blocks/class-block-purchase-course.php
     *
     * @param array $args Block settings.
     * @param string $name Block name.
     *
     * @return array
     */
    public function extend_take_course_block($args, $name)
    {
        if ('sensei-lms/button-take-course' === $name) {
            $this->render_take_course = $args['render_callback'];

            $args['render_callback'] = [$this, 'maybe_override_take_course_block'];
        }

        return $args;
    }

    /**
     * Render the purchase course block instead of Take course if the course is purchasable.
     *
     * @param array $attributes Block attributes.
     * @param string $content Block HTML.
     *
     * @return string Block output.
     */
    public function maybe_override_take_course_block($attributes, $content)
    {
        global $post;
        $this->course_id  = $post->ID;
        $this->button     = $content;
        $this->attributes = $attributes;

        $this->course_pp_plan_id = $this->get_course_plan_id($this->course_id);

        if ( ! $this->course_pp_plan_id || $this->is_customer_subscribed_to_course_plan($this->course_pp_plan_id)) {
            return call_user_func($this->render_take_course, $attributes, $content);
        }

        return $this->render_purchase_course_block();
    }

    private function render_purchase_course_block()
    {
        if (\Sensei_Course::is_user_enrolled($this->course_id, get_current_user_id())) {
            return '';
        }

        if ( ! \Sensei_Course::is_prerequisite_complete($this->course_id)) {
            Sensei()->notices->add_notice(Sensei()->course::get_course_prerequisite_message($this->course_id), 'info', 'sensei-take-course-prerequisite');

            return '';
        }

        return $this->wrap_in_sensei_wrapper($this->render_purchase_button());
    }

    private function render_purchase_button()
    {
        $plan = ppress_get_plan($this->course_pp_plan_id);

        $button = $this->render_button(
            esc_html__('Buy', 'profilepress-pro') . ' - ' . ppress_display_amount($plan->price)
        );

        return '
			<form action="' . esc_url($plan->get_checkout_url()) . '" method="get">
				<input type="hidden" name="plan" value="' . esc_attr($this->course_pp_plan_id) . '" />
			    ' . $button . '
			</form>';
    }

    /**
     * Wrap the html content in a sensei block wrapper div.
     *
     * @param string $content The html content.
     *
     * @return string Wrapped content.
     */
    private function wrap_in_sensei_wrapper($content)
    {
        $wrapper_attributes = get_block_wrapper_attributes(['class' => 'sensei-block-wrapper sensei-cta']);

        return '<div ' . $wrapper_attributes . '>' . $content . '</div>';
    }

    /**
     * Render button with given text content.
     *
     * @param string $text Button label.
     *
     * @return string Button HTML.
     */
    private function render_button($text)
    {
        $button = preg_replace(
            '|<button(.*)>(.*)</button>|i',
            '<button $1>abcxyz</button>',
            $this->button
        );

        // doing this because the $ sign in the price breaks regex
        $button = str_replace('abcxyz', $text, $button);

        $this->add_login_notice();

        return $button;
    }

    /**
     * Add a log in notice to the button.
     */
    private function add_login_notice()
    {
        if ( ! is_user_logged_in()) {

            Sensei()->notices->add_notice(
                sprintf(
                // translators: Placeholder is a link to log in.
                    __('Please %1$s to access your purchased courses.', 'profilepress-pro'),
                    '<a href="' . ppress_login_url() . '">' . __('log in', 'profilepress-pro') . '</a>'
                ),
                'info',
                'sensei-take-course-login'
            );
        }
    }

    /**
     * @return self|void
     */
    public static function get_instance()
    {
        self::$instance_flag = true;

        if ( ! defined('ProfilePress\Core\Classes\ExtensionManager::SENSEI_LMS')) return;

        if ( ! EM::is_enabled(EM::SENSEI_LMS)) return;

        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}