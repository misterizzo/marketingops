<?php

namespace ProfilePress\Core\Integrations\TutorLMS;

use ProfilePress\Core\Admin\SettingsPages\AbstractSettingsPage;
use ProfilePress\Core\Classes\ExtensionManager as EM;
use ProfilePress\Core\Classes\FormRepository as FR;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionBillingFrequency;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\Repositories\PlanRepository;
use Tutor\Models\CourseModel;

class Init
{
    public static $instance_flag = false;

    public function __construct()
    {
        add_action('plugins_loaded', function () {

            add_filter('tutor_monetization_options', [$this, 'tutor_monetization_options'], 5);

            if (class_exists('\TUTOR\TUTOR')) {

                add_filter('ppress_settings_page_args', [$this, 'settings_page']);

                add_filter('ppress_admin_membership_plan_metabox_settings', [$this, 'plan_edit_screen']);

                add_action('ppress_order_completed', [$this, 'on_order_sub_success']);
                add_action('ppress_subscription_activated', [$this, 'on_order_sub_success']);
                add_action('ppress_subscription_enabled_trial', [$this, 'on_order_sub_success']);

                add_action('ppress_subscription_cancelled', [$this, 'on_subscription_cancelled']);
                add_action('ppress_subscription_expired', [$this, 'on_subscription_cancelled']);

                add_action('ppress_after_registration', [$this, 'after_user_registration'], 10, 4);

                add_filter('ppress_shortcode_builder_registration_meta', [$this, 'save_shortcode_builder_settings']);
                add_action('ppress_shortcode_builder_registration_screen_settings', [$this, 'shortcode_builder_settings']);
                add_filter('ppress_form_builder_meta_box_settings', [$this, 'dnd_builder_settings'], 99);

                add_filter('tutor/course/single/entry-box/free', [$this, 'course_checkout_link'], 10, 2);
                add_filter('tutor_course_loop_price', [$this, 'loop_course_checkout_link']);

                add_filter('tutor_student_register_url', [$this, 'override_student_register_url']);
                add_filter('tutor_instructor_register_url', [$this, 'override_instructor_register_url']);
            }
        });
    }

    public function tutor_monetization_options($arr)
    {
        $arr['profilepress'] = __('ProfilePress', 'wp-user-avatar');

        return $arr;
    }

    private function get_course_plan_ids($course_id)
    {
        $ids = [];

        $plans = PlanRepository::init()->retrieveAll();

        $course_id = (int)$course_id;

        foreach ($plans as $plan) {

            if ($plan->is_active()) {

                $courses = $plan->get_plan_extras('tutorlms_courses');

                $courses = is_array($courses) ? array_map('absint', $plan->get_plan_extras('tutorlms_courses')) : [];

                if (is_array($courses) && in_array($course_id, $courses, true)) {
                    $ids[] = $plan->get_id();
                }
            }
        }

        return ! empty($ids) ? $ids : false;
    }

    /**
     * Check if any of the customer active subscriptions can grant course access.
     *
     * @param $course_id
     *
     * @return bool
     */
    private function is_customer_plans_has_course_access($course_id)
    {
        $customer = CustomerFactory::fromUserId(get_current_user_id());

        $plan_ids = $this->get_course_plan_ids($course_id);

        $plan_checks = array_map(function ($plan_id) use ($customer) {

            if ( ! $customer->exists()) return false;

            return $customer->has_active_subscription($plan_id);

        }, $plan_ids);

        return in_array(true, $plan_checks, true);
    }

    public function loop_course_checkout_link($output)
    {
        if (get_tutor_option('monetize_by') == 'profilepress') {

            $course_id = get_the_ID();
            $user_id   = get_current_user_id();

            $is_enrolled          = tutor_utils()->is_enrolled(get_the_ID(), $user_id);
            $is_enabled           = tutor_utils()->has_user_course_content_access($user_id, $course_id);
            $can_user_edit_course = tutor_utils()->can_user_edit_course($user_id, get_the_ID());

            if ( ! $is_enrolled && ( ! $is_enabled || ! $can_user_edit_course)) {

                $plan_ids = $this->get_course_plan_ids($course_id);

                if ( ! empty($plan_ids)) {

                    $customer = CustomerFactory::fromUserId(get_current_user_id());

                    if ( ! $customer->exists() || ! $this->is_customer_plans_has_course_access($course_id)) {

                        $plan              = ppress_get_plan($plan_ids[0]);
                        $billing_frequency = $plan->is_recurring() ? ' ' . SubscriptionBillingFrequency::get_label($plan->billing_frequency) : '';

                        ob_start();
                        ?>
                        <div class="tutor-profilepress-purchase-wrap tutor-mb-12">

                    <span class="tutor-fs-6 tutor-fw-bold tutor-color-black">
                        <?php echo ppress_display_amount($plan->get_price()); ?>
                        <?php echo $billing_frequency; ?>
                    </span>
                        </div>
                        <div class="tutor-profilepress-message-wrapper tutor-justify-center tutor-align-center tutor-flex-column">
                            <a class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-btn-block" href="<?php echo esc_url($plan->get_checkout_url()); ?>">
                                <?php esc_html_e(apply_filters('ppress_tutorlms_checkout_button_text', 'Subscribe Now'), 'wp-user-avatar'); ?>
                            </a>
                        </div>
                        <?php
                        $output = ob_get_clean();
                    }
                }
            }
        }

        return $output;
    }

    public function course_checkout_link($output, $course_id)
    {
        if (get_tutor_option('monetize_by') == 'profilepress') {

            $plan_ids = $this->get_course_plan_ids($course_id);

            if ( ! empty($plan_ids)) {

                $customer = CustomerFactory::fromUserId(get_current_user_id());

                if ( ! $customer->exists() || ! $this->is_customer_plans_has_course_access($course_id)) {

                    $plan              = ppress_get_plan($plan_ids[0]);
                    $billing_frequency = $plan->is_recurring() ? ' ' . SubscriptionBillingFrequency::get_label($plan->billing_frequency) : '';

                    ob_start();
                    ?>
                    <div class="tutor-course-single-pricing">
                    <span class="tutor-fs-4 tutor-fw-bold tutor-color-black">
                        <?php echo ppress_display_amount($plan->get_price()); ?>
                        <?php echo $billing_frequency; ?>
                    </span>
                    </div>

                    <div class="tutor-course-single-btn-group">
                        <form class="tutor-enrol-course-form" method="get" action="<?php echo esc_url($plan->get_checkout_url()); ?>">
                            <input type="hidden" name="plan" value="<?php esc_html_e($plan_ids[0]); ?>">
                            <button type="submit" class="tutor-btn tutor-btn-primary tutor-btn-lg tutor-btn-block tutor-mt-24 tutor-enroll-course-button">
                                <?php esc_html_e('Subscribe Now', 'tutor'); ?>
                            </button>
                        </form>
                    </div>
                    <?php
                    $output = ob_get_clean();
                }
            }
        }

        return $output;
    }

    public function after_user_registration($form_id, $user_data, $user_id, $is_melange)
    {
        $global_tutorlms_courses = ppress_settings_by_key('tlms_courses', []);

        if (is_array($global_tutorlms_courses)) {
            $global_tutorlms_courses = array_filter($global_tutorlms_courses);
            if ( ! empty($global_tutorlms_courses)) {
                foreach ($global_tutorlms_courses as $course_id) {
                    tutor_utils()->do_enroll($course_id, 0, $user_id);
                }
            }
        }

        if ($is_melange === true || ! $form_id) return;

        $form_id = intval($form_id);

        if (FR::is_drag_drop($form_id, FR::REGISTRATION_TYPE)) {
            $tutorlms_courses = FR::get_dnd_metabox_setting('tutorlms_courses', $form_id, FR::REGISTRATION_TYPE);
        } else {
            $tutorlms_courses = FR::get_form_meta($form_id, FR::REGISTRATION_TYPE, 'tutorlms_courses');
        }

        if (is_array($tutorlms_courses)) {
            $tutorlms_courses = array_filter($tutorlms_courses);
            if ( ! empty($tutorlms_courses)) {
                foreach ($tutorlms_courses as $course_id) {
                    tutor_utils()->do_enroll($course_id, 0, $user_id);
                }
            }
        }
    }

    /**
     * @param OrderEntity|SubscriptionEntity $order_or_sub
     *
     * @return void
     */
    public function on_order_sub_success($order_or_sub)
    {
        if ($order_or_sub instanceof SubscriptionEntity) {
            $order = OrderFactory::fromId($order_or_sub->get_parent_order_id());
        } else {
            $order = $order_or_sub;
        }

        $plan = $order->get_plan();

        $courses = $plan->get_plan_extras('tutorlms_courses');

        $user_id = $order->get_customer()->user_id;

        if (is_array($courses)) {
            $courses = array_filter($courses);
            if ( ! empty($courses)) {
                foreach ($courses as $course_id) {
                    if ( ! tutor_utils()->is_enrolled($course_id, $user_id)) {
                        tutor_utils()->do_enroll($course_id, 0, $user_id);
                    }
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

        $plan = $subscription->get_plan();

        $user_id = $subscription->get_customer()->user_id;

        $courses = $plan->get_plan_extras('tutorlms_courses');

        if (is_array($courses)) {
            $courses = array_filter($courses);
            if ( ! empty($courses)) {
                foreach ($courses as $course_id) {
                    tutor_utils()->cancel_course_enrol($course_id, $user_id);
                }
            }
        }
    }

    private function get_courses($key_value = false)
    {
        $courses = CourseModel::get_courses();

        if ($key_value === false) return $courses;

        $options = array();

        foreach ($courses as $course) {
            $options[$course->ID] = $course->post_title;
        }

        return $options;
    }

    /**
     * @param array $args
     *
     * @return array
     */
    public function settings_page($args)
    {
        $args['pp_tutorlms_settings'] = [
            'tab_title'                        => esc_html__('Tutor LMS', 'wp-user-avatar'),
            'section_title'                    => esc_html__('Tutor LMS Settings', 'wp-user-avatar'),
            'dashicon'                         => 'dashicons-welcome-learn-more',
            'tlms_courses'                     => array(
                'type'        => 'select2',
                'options'     => $this->get_courses(true),
                'label'       => __('Courses', 'wp-user-avatar'),
                'description' => esc_attr__('Select the Tutor LMS courses to enroll users in after user registration.', 'wp-user-avatar'),
            ),
            'tlms_student_registration_url'    => [
                'type'        => 'custom_field_block',
                'label'       => esc_html__('Custom Student Registration Page', 'wp-user-avatar'),
                'data'        => AbstractSettingsPage::page_dropdown('tlms_student_registration_url'),
                'description' => sprintf(
                    esc_html__('Select a page with a custom ProfilePress registration form shortcode you wish to make a student registration page for Tutor LMS. %sLearn more%s', 'wp-user-avatar'),
                    '<a target="_blank" href="https://profilepress.com/article/custom-tutor-lms-student-registration-form/">', '</a>'
                )
            ],
            'tlms_instructor_registration_url' => [
                'type'        => 'custom_field_block',
                'label'       => esc_html__('Custom Instructor Registration Page', 'wp-user-avatar'),
                'data'        => AbstractSettingsPage::page_dropdown('tlms_instructor_registration_url'),
                'description' => sprintf(
                    esc_html__('Select a page with a ProfilePress registration form shortcode you wish to make an instructor registration page for Tutor LMS. %sLearn more%s', 'wp-user-avatar'),
                    '<a target="_blank" href="https://profilepress.com/article/custom-tutor-lms-instructor-registration-form/">', '</a>'
                )
            ],
        ];

        return $args;
    }

    public function plan_edit_screen($settings)
    {
        $settings['tutorlms'] = [
            'tab_title' => esc_html__('Tutor LMS', 'wp-user-avatar'),
            [
                'id'          => 'tutorlms_courses',
                'type'        => 'select2',
                'options'     => $this->get_courses(true),
                'label'       => esc_html__('Select Courses', 'wp-user-avatar'),
                'description' => esc_html__('Select the Tutor LMS courses to enroll users in that subscribe to this plan.', 'wp-user-avatar'),
                'priority'    => 5
            ]
        ];

        return $settings;
    }

    public function save_shortcode_builder_settings($settings)
    {
        $settings['tutorlms_courses'] = array_map('intval', $_POST['rfb_tutorlms_courses']);

        return $settings;
    }

    public function dnd_builder_settings($meta_box_settings)
    {
        $meta_box_settings['tutorlms'] = [
            'tab_title' => esc_html__('Tutor LMS', 'wp-user-avatar'),
            [
                'id'          => 'tutorlms_courses',
                'type'        => 'select2',
                'options'     => $this->get_courses(true),
                'label'       => esc_html__('Select Courses', 'wp-user-avatar'),
                'description' => esc_attr__('Select the Tutor LMS courses to enroll users in after registration through this form.', 'wp-user-avatar'),
                'priority'    => 5
            ]
        ];

        return $meta_box_settings;
    }

    public function shortcode_builder_settings($form_id)
    {
        $saved_courses = FR::get_form_meta($form_id, FR::REGISTRATION_TYPE, 'tutorlms_courses');
        if (empty($saved_courses)) $saved_courses = [];

        ?>
        <style>.select2-container {
                width: 100% !important;
            }</style>
        <h4 class="ppSCB-tab-content-header"><?= esc_html__('Tutor LMS', 'wp-user-avatar') ?></h4>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="tutorlms_courses"><?php esc_attr_e('Courses', 'wp-user-avatar'); ?></label>
                </th>
                <td>
                    <select class="ppselect2" name="rfb_tutorlms_courses[]" id="tutorlms_courses" multiple>
                        <?php foreach ($this->get_courses() as $course) : ?>
                            <option value="<?= esc_attr($course->ID) ?>"<?= in_array($course->ID, $saved_courses) ? ' selected' : ''; ?>><?= esc_attr($course->post_title) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_attr_e('Select the Tutor LMS courses to enroll users in after registration through this form', 'wp-user-avatar'); ?></p>
                </td>
            </tr>
        </table>
        <div class="ppSCB-clear-both"></div>
        <?php
    }

    public function override_student_register_url($url)
    {
        $page_id = ppress_settings_by_key('tlms_student_registration_url');

        if ( ! empty($page_id) && 'publish' == get_post_status($page_id)) {
            $url = get_permalink($page_id);
        }

        return $url;
    }

    public function override_instructor_register_url($url)
    {
        $page_id = ppress_settings_by_key('tlms_instructor_registration_url');

        if ( ! empty($page_id) && 'publish' == get_post_status($page_id)) {
            $url = get_permalink($page_id);
        }

        return $url;
    }

    /**
     * @return self|void
     */
    public static function get_instance()
    {
        self::$instance_flag = true;

        if ( ! defined('ProfilePress\Core\Classes\ExtensionManager::TUTORLMS')) return;

        if ( ! EM::is_enabled(EM::TUTORLMS)) return;

        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}
