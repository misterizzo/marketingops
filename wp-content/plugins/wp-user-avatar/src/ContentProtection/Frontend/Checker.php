<?php

namespace ProfilePress\Core\ContentProtection\Frontend;


use ProfilePress\Core\ContentProtection\ContentConditions;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;

class Checker
{
    public static function is_blocked($who_can_access = 'everyone', $roles = [], $wp_users = [], $membership_plans = [])
    {
        $function_args = func_get_args();

        if ('login' == $who_can_access) {

            if ( ! is_user_logged_in()) return self::fr(true, ...$function_args);

            if ( ! empty($membership_plans)) {

                $customer = CustomerFactory::fromUserId(get_current_user_id());

                $membership_plans = array_map('absint', $membership_plans);

                foreach ($membership_plans as $plan_id) {
                    if ($customer->has_active_subscription($plan_id)) return self::fr(false, ...$function_args);
                }
            }

            if ( ! empty($roles)) {

                $user = wp_get_current_user();

                $user_roles = $user->roles;

                if (is_array($roles) && in_array('administrator', $roles, true) && is_super_admin($user->ID)) {
                    return self::fr(false, ...$function_args);
                }

                if ( ! empty(array_intersect($roles, $user_roles))) return self::fr(false, ...$function_args);
            }

            if ( ! empty($wp_users)) {

                $users = array_map('absint', $wp_users);

                if (in_array(get_current_user_id(), $users)) return self::fr(false, ...$function_args);
            }

            if (empty($roles) && empty($wp_users) && empty($membership_plans)) return self::fr(false, ...$function_args);

            // returning true to make users and user role combined rule OR instead of AND
            return self::fr(true, ...$function_args);
        }

        if ('logout' == $who_can_access) {

            if (is_user_logged_in()) return self::fr(true, ...$function_args);
        }

        return self::fr(false, ...$function_args);
    }

    /**
     * @param $protection_rule
     * @param bool $is_redirect set to true if this is a redirect check and not post content check.
     *
     * @return bool
     */
    public static function content_match($protection_rule, $is_redirect = false)
    {
        $content_match = false;

        if (empty($protection_rule)) return $content_match;

        // All Groups Must Return True. Break if any is false and set $loadable to false.
        foreach ($protection_rule as $group => $conditions) {

            // Groups are false until a condition proves true.
            $group_check = false;

            // At least one group condition must be true. Break this loop if any condition is true.
            foreach ($conditions as $condition) {

                $match = self::check_condition($condition['condition'], ppress_var($condition, 'value', []), $is_redirect);

                // If any condition passes, set $group_check true and break.
                if ($match) {
                    $group_check = true;
                    break;
                }
            }

            // If any group of conditions doesn't pass, not loadable.
            if ( ! $group_check) {
                $content_match = false;
                break;
            } else {
                $content_match = true;
            }
        }

        return $content_match;
    }

    public static function check_condition($condition_id, $rule_saved_value, $is_redirect = false)
    {
        $condition = ContentConditions::get_instance()->get_condition($condition_id);

        if ( ! $condition) return false;

        return call_user_func($condition['callback'], $condition_id, $rule_saved_value, $is_redirect);
    }

    public static function fr($response, ...$filter_args)
    {
        return apply_filters('ppress_content_protection_is_blocked', $response, ...$filter_args);
    }
}