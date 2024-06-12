<?php

namespace ProfilePress\Core\ContentProtection;

use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Repositories\PlanRepository;

class ConditionalBlocksIntegration
{
    public function __construct()
    {
        add_action('plugins_loaded', [$this, 'init'], 20);
    }

    public function init()
    {
        if (defined('CONDITIONAL_BLOCKS_PATH')) {
            add_filter('conditional_blocks_register_condition_categories', [$this, 'condition_category'], 1);
            add_filter('conditional_blocks_register_condition_types', [$this, 'conditions'], 1);
            add_filter('conditional_blocks_register_check_ppress_subscribed_membership_plan', [$this, 'visibility_check'], 10, 2);
        }
    }

    public function condition_category($categories)
    {
        $categories[] = array(
            'value' => 'profilepress',
            'label' => 'ProfilePress',
        );

        return $categories;

    }

    public function conditions($conditions)
    {

        // Add a new condition to your group.
        $conditions[] = array(
            'type'        => 'ppress_subscribed_membership_plan', // Important: The type identities the condition and should NOT be changed.
            'label'       => 'Active Membership Plans',
            'description' => esc_html__('The selected block will only be visible to users with an active subscription to the chosen membership plans below.', 'wp-user-avatar'),
            'category'    => 'profilepress',
            'fields'      => [
                [
                    'key'        => 'profilepress_membership_plans',
                    'type'       => 'select',
                    'attributes' => [
                        'multiple' => true
                    ],
                    'options'    => (function () {
                        $plans   = PlanRepository::init()->retrieveAll();
                        $options = [];
                        if (is_array($plans) && ! empty($plans)) {
                            foreach ($plans as $plan) {
                                $options[] = [
                                    'value' => $plan->get_id(),
                                    'label' => $plan->get_name(),
                                ];
                            }
                        }

                        return $options;
                    })()
                ],
                [
                    'key'  => 'blockAction', // Default key.
                    'type' => 'blockAction', // Reuse the common field for choosing the Block Action.
                ],
            ],
        );

        return $conditions;
    }

    /**
     * @param bool $should_block_render if condition passed validation.
     * @param array $condition contains the configured conditions with keys/values.
     *
     * @return bool $should_block_render - defaults to false.
     */
    function visibility_check($should_block_render, $condition)
    {
        $has_match = false;

        if (is_user_logged_in()) {
            if ( ! empty($condition['profilepress_membership_plans']) && is_array($condition['profilepress_membership_plans'])) {
                foreach ($condition['profilepress_membership_plans'] as $plan) {
                    if (CustomerFactory::fromUserId(get_current_user_id())->has_active_subscription(intval($plan['value']))) {
                        $has_match = true;
                    }
                }
            }
        }

        // Reuse the Block Action from other conditions.
        $block_action = ! empty($condition['blockAction']) ? $condition['blockAction'] : 'showBlock';

        if ($has_match && $block_action === 'showBlock') {
            $should_block_render = true;
        } elseif ( ! $has_match && $block_action === 'hideBlock') {
            $should_block_render = true;
        }

        return $should_block_render;
    }


    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}
