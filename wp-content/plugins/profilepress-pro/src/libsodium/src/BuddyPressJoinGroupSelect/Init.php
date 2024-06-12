<?php

namespace ProfilePress\Libsodium\BuddyPressJoinGroupSelect;

use ProfilePress\Core\Classes\ExtensionManager as EM;
use ProfilePress\Core\Classes\FormRepository;

class Init
{
    public static $instance_flag = false;

    public function __construct()
    {
        add_action('bp_loaded', function () {

            add_shortcode('pp-buddypress-groups', array($this, 'group_selection_factory'));
            add_filter('ppress_after_registration', array($this, 'add_user_to_select_group'), 10, 3);

            add_filter('ppress_reg_edit_profile_available_shortcodes', [$this, 'add_available_shortcode_popup'], 10, 2);

            if (isset($_GET['form-type']) && FormRepository::REGISTRATION_TYPE == $_GET['form-type']) {
                new RegistrationField();
            }

        }, 99);
    }

    public function add_available_shortcode_popup($shortcodes, $type)
    {
        if ('reg' == $type) {
            $shortcodes['pp-buddypress-groups'] = [
                'description' => esc_html__('Allow users select BuddyPress groups to join', 'profilepress-pro'),
                'shortcode'   => 'pp-buddypress-groups',
                'attributes'  => [
                    'type' => [
                        'label'   => esc_html__('Field Type', 'profilepress-pro'),
                        'field'   => 'select',
                        'options' => [
                            'checkbox' => esc_html__('Checkboxes', 'profilepress-pro'),
                            'select'   => esc_html__('Select Dropdown', 'profilepress-pro')
                        ]
                    ]
                ]
            ];
        }

        return $shortcodes;
    }

    /**
     * Factory method to output either select or checkbox list of group depending on type shortcode attribute.
     *
     * @param $atts
     *
     * @return string
     */
    public function group_selection_factory($atts)
    {
        $type = ! empty($atts['type']) ? esc_attr($atts['type']) : 'checkbox';

        $callback_method = "{$type}_list";

        return $this->{$callback_method}($atts);
    }

    /**
     * Checkbox of all BuddyPress groups.
     *
     * @param array $atts
     *
     * @return string
     */
    public function checkbox_list($atts)
    {
        $groups = $this->groups_array();

        $atts = shortcode_atts(
            array(
                'class' => '',
                'id'    => '',
            ),
            $atts
        );

        $input_class = esc_attr($atts['class']);
        $input_id    = esc_attr($atts['id']);

        $output = '';
        foreach ($groups as $group_id => $group_name) {
            $checked = isset($_POST['reg_buddypress_group']) && in_array($group_id, $_POST['reg_buddypress_group']) ? 'checked' : null;
            ob_start(); ?>
            <label class="pp-bp-groups-select" style="display: block">
                <input type="checkbox" name="reg_buddypress_group[]" value="<?php echo esc_attr($group_id); ?>" class="<?php echo esc_attr($input_class); ?>" <?php echo $checked; ?>>
                <span class="group_name"><?php echo esc_attr($group_name); ?></span> </label>
            <?php
            $output .= apply_filters('ppress_join_buddypress_checkbox', ob_get_clean(), $group_id, $group_name, $input_class, $input_id);
        }

        return $output;
    }

    /**
     * Multi-select of all BuddyPress groups.
     *
     * @param array $atts
     *
     * @return string
     */
    public function select_list($atts)
    {
        $groups = $this->groups_array();

        $atts = shortcode_atts(
            array(
                'class' => '',
                'id'    => ''
            ),
            $atts
        );

        $input_class = esc_attr($atts['class']);
        $input_id    = esc_attr($atts['id']);

        $select_class = esc_attr(apply_filters('ppress_buddypress_select_class', 'select_group_class'));
        $select_id    = esc_attr(apply_filters('ppress_buddypress_select_id', 'select_group_id'));

        $output = "<select name=\"reg_buddypress_group[]\" class=\"$select_class\" id=\"$select_id\" multiple>";
        foreach ($groups as $group_id => $group_name) :
            $selected = isset($_POST['reg_buddypress_group']) && in_array($group_id, $_POST['reg_buddypress_group']) ? 'selected' : null;
            $output   .= "<option value='$group_id' class='$input_class' $selected><span class=\"group_name\">$group_name</span></option>";
        endforeach;
        $output .= '</select>';
        $output .= "<script>
  jQuery(function($) {
    $( '.select_group_class' ).select2({width: '100%'});
  });
  </script>";

        return apply_filters('ppress_join_buddypress_select_option', $output, $atts, $input_class, $input_id);
    }

    /**
     * Array of group IDs and names.
     *
     * @return array
     */
    public function groups_array()
    {
        $f_args = apply_filters('ppress_buddypress_groups_get_groups_args', ['per_page' => 0]);

        $groups = apply_filters('ppress_buddypress_groups_get_groups', groups_get_groups($f_args));

        $groups = $groups['groups'];

        $arg = array();
        foreach ($groups as $group) {
            $arg[$group->id] = $group->name;
        }

        return $arg;
    }

    /**
     * Add registering user to BuddyPress group.
     *
     * @param int $form_id
     * @param array $user_data
     * @param int $user_id
     */
    public function add_user_to_select_group($form_id, $user_data, $user_id)
    {
        if (isset($_POST['reg_buddypress_group']) && ! empty($_POST['reg_buddypress_group'])) {
            // sanitize array
            $sanitized_data = array_map('absint', $_POST['reg_buddypress_group']);
            foreach ($sanitized_data as $group_id) {
                groups_join_group($group_id, $user_id);
            }
        }
    }

    /**
     * Singleton poop.
     *
     * @return self|void
     */
    public static function get_instance()
    {
        self::$instance_flag = true;

        if ( ! defined('ProfilePress\Core\Classes\ExtensionManager::JOIN_BUDDYPRESS_GROUPS')) return;

        if ( ! EM::is_enabled(EM::JOIN_BUDDYPRESS_GROUPS)) return;

        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}