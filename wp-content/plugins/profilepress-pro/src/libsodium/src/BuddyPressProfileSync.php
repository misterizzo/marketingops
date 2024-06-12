<?php

namespace ProfilePress\Libsodium;

use BP_XProfile_Field;
use ProfilePress\Core\Base;
use ProfilePress\Core\Classes\ExtensionManager as EM;
use ProfilePress\Core\Classes\PROFILEPRESS_sql;

class BuddyPressProfileSync
{
    public static $instance_flag = false;

    public function __construct()
    {
        add_action('bp_loaded', function () {

            add_filter('ppress_edit_profile_field_settings', [$this, 'add_buddypress_sync_field']);

            add_filter('ppress_insert_custom_field_db', [$this, 'save_sync_status'], 10, 2);
            add_filter('ppress_update_custom_field_db', [$this, 'update_sync_status'], 10, 2);
            add_filter('ppress_delete_custom_field_db', [$this, 'delete_xprofile_field']);

            add_action('xprofile_profile_field_data_updated', [$this, 'update_pp_custom_field_meta_data'], 10, 2);
            add_action('ppress_after_custom_field_update', [$this, 'update_xprofile_field_data'], 10, 3);

            // delete PP custom field if corresponding xprofile field is deleted.
            add_action('xprofile_fields_deleted_field', [$this, 'delete_pp_custom_field']);
        });
    }

    /**
     * Return the user ID of the user profile being viewed.
     *
     * @return int
     */
    public function get_user_id()
    {
        $user_id = (int)get_current_user_id();
        // We'll need a user ID when not on self profile.
        if ( ! empty($_GET['user_id'])) {
            $user_id = absint($_GET['user_id']);
        }

        return $user_id;
    }

    /**
     * Callback to update ProfilePress custom field value.
     *
     * @param int $xprofile_field_id
     * @param mixed $value
     */
    public function update_pp_custom_field_meta_data($xprofile_field_id, $value)
    {
        $pp_custom_field_id = (int)get_option('xprofile_field_' . $xprofile_field_id . '_pp_field_id', '');

        // if this field isn't sync-ed with a PP custom field, bail.
        if ( ! self::is_sync_active($pp_custom_field_id)) return;

        $xprofile_field_type = BP_XProfile_Field::get_type($xprofile_field_id);

        if ($xprofile_field_type == 'datebox') {
            $value = date("F j, Y", strtotime($value));
        }

        $user_id = $this->get_user_id();

        $pp_custom_field_key = self::get_pp_field_key_from_id($pp_custom_field_id);

        update_user_meta($user_id, $pp_custom_field_key, $value);
    }

    /**
     * Callback to update xprofile field value.
     *
     * @param string $key
     * @param mixed $value
     * @param int $user_id
     */
    public function update_xprofile_field_data($key, $value, $user_id)
    {
        if ( ! class_exists('\BP_XProfile_Field')) return;

        $pp_custom_field_id = self::get_pp_field_id_from_key($key);

        // if this field isn't sync-ed with a PP custom field, bail.
        if ( ! self::is_sync_active($pp_custom_field_id)) return;

        $xprofile_field_id   = get_option('ppress_field' . $pp_custom_field_id . '_xprofile_field_id');
        $xprofile_field_type = BP_XProfile_Field::get_type($xprofile_field_id);

        if ($xprofile_field_type == 'datebox') {
            $value = date("Y-m-d H:i:s", strtotime($value));
        }

        // remove empty values
        if (is_array($value)) $value = array_filter($value);

        xprofile_set_field_data($xprofile_field_id, $user_id, $value);
    }

    /**
     * Get ProfilePress field key from it ID.
     *
     * @param int $pp_field_id
     *
     * @return null|string
     */
    public static function get_pp_field_key_from_id($pp_field_id)
    {
        global $wpdb;

        $table = Base::profile_fields_db_table();

        return $wpdb->get_var(
            $wpdb->prepare("SELECT field_key FROM $table WHERE id = %d", [$pp_field_id])
        );
    }

    /**
     * Get ProfilePress field ID from it key.
     *
     * @param string $pp_field_key
     *
     * @return null|string
     */
    public static function get_pp_field_id_from_key($pp_field_key)
    {
        global $wpdb;

        $table = Base::profile_fields_db_table();

        return $wpdb->get_var(
            $wpdb->prepare("SELECT id FROM $table WHERE field_key = %s", [$pp_field_key])
        );
    }

    /**
     * Settings page of sync activation.
     *
     * @param int|null $field_id
     */
    public function add_buddypress_sync_field($field_id = null)
    {
        $data = '';

        if ( ! empty($field_id)) $data = get_option('ppress_xprofile_sync_' . $field_id, '');
        ?>
        <tr>
            <th scope="row"><?php _e('BuddyPress Profile Sync', 'profilepress-pro'); ?></th>
            <td>
                <label for="activate_bp_xprofile_sync"><strong><?php _e('Activate Sync', 'profilepress-pro'); ?></strong></label>
                <input type="checkbox" id="activate_bp_xprofile_sync" name="activate_bp_xprofile_sync" value="yes" <?php checked($data, 'yes'); ?>>
                <p class="description"><?php _e('Check to sync this field with BuddyPress Extended Profile', 'profilepress-pro'); ?></p>
            </td>
        </tr>
        <?php
    }


    /**
     * Save sync state.
     *
     * @param int $pp_custom_field_id
     * @param array $POST
     */
    public function save_sync_status($pp_custom_field_id, $POST)
    {
        if ( ! empty($POST['activate_bp_xprofile_sync']) && $POST['activate_bp_xprofile_sync'] == 'yes') {
            $this->add_xprofile_field($POST, $pp_custom_field_id);
        }
    }


    /**
     * Update sync state.
     *
     * @param int $pp_custom_field_id
     * @param array $POST
     */
    public function update_sync_status($pp_custom_field_id, $POST)
    {
        if ( ! isset($POST['activate_bp_xprofile_sync']) || empty($POST['activate_bp_xprofile_sync'])) {
            $this->delete_xprofile_field($pp_custom_field_id);
        } else {
            $xprofile_field_id = get_option('ppress_field' . $pp_custom_field_id . '_xprofile_field_id', '');

            // if there is no xprofile field saved against this custom profilepress field, attempt to create one.
            // necessary for custom field that do not have sync activated on creation time.
            if (empty($xprofile_field_id)) {
                $this->add_xprofile_field($POST, $pp_custom_field_id);
            }
        }
    }

    /**
     * Add a new xProfile field to BuddyPress.
     *
     * @param array $POST posted data
     *
     * @return void
     */
    public function add_xprofile_field($POST, $pp_custom_field_id)
    {
        $name        = stripslashes(sanitize_text_field($POST['cpf_label_name']));
        $description = stripslashes(sanitize_text_field($POST['cpf_description']));
        // sync the field with the very first buddypress group ID. should they want it added to another xprofile group
        // they should use BP group UI to effect the change.
        $group_id = bp_xprofile_get_groups()[0]->id;

        // switch PP field naming convention to that of BuddyPress
        $pp_field = sanitize_text_field($POST['cpf_type']);

        switch ($pp_field) {
            case 'text':
                $field_type = 'textbox';
                break;
            case 'select':
                $field_type = 'selectbox';
                break;
            case 'radio':
                $field_type = 'radio';
                break;
            case 'date':
                $field_type = 'datebox';
                break;
            case 'textarea':
                $field_type = 'textarea';
                break;
        }

        // determine if field is multiselect select dropdown
        if ($pp_field == 'select' && ! empty($_POST['cpf_multi_select']) && $_POST['cpf_multi_select'] == 'yes') {
            $field_type = 'multiselectbox';
        }

        // Note: BuddyPress checkbox accept multi options by default.
        if ($pp_field == 'checkbox' && ! empty($_POST['cpf_multi_checkbox']) && $_POST['cpf_multi_checkbox'] == 'yes') {
            $field_type = 'checkbox';
        }

        // bail if no name or field type is found.
        if (empty($name) || empty($field_type)) return;

        $field              = new BP_XProfile_Field();
        $field->name        = $name;
        $field->description = $description;
        $field->group_id    = $group_id;
        $field->type        = $field_type;

        if ($field_type == 'selectbox' || $field_type == 'multiselectbox' || $field_type == 'radio' || $field_type == 'checkbox') {
            // BP_XProfile_Field::save() check for the below POSTed data for options of selectbox before saving to DB.
            $_POST["{$field_type}_option"] = array_map('trim', explode(',', sanitize_text_field($_POST['cpf_options'])));
        }

        $field->save();

        $inserted_id = $field->id;

        update_option('ppress_xprofile_sync_' . $pp_custom_field_id, 'yes');
        // used if we want to get the xprofile field of a corresponding PP custom field.
        update_option('ppress_field' . $pp_custom_field_id . '_xprofile_field_id', $inserted_id);

        // used if we want to get the PP custom field of a corresponding xprofile field.
        update_option('xprofile_field_' . $inserted_id . '_pp_field_id', $pp_custom_field_id);
    }

    /**
     * Delete created xprofile field of a pp custom field.
     *
     * @param int $pp_custom_field_id PP custom field ID.
     */
    public function delete_xprofile_field($pp_custom_field_id)
    {
        $field     = new BP_XProfile_Field();
        $field->id = get_option('ppress_field' . $pp_custom_field_id . '_xprofile_field_id');
        $field->delete();

        delete_option('ppress_xprofile_sync_' . $pp_custom_field_id);
        delete_option('ppress_field' . $pp_custom_field_id . '_xprofile_field_id');
    }

    /**
     * Delete PP custom field attached to an xprofile field.
     *
     * @param BP_XProfile_Field $xprofile_field_obj
     */
    public function delete_pp_custom_field($xprofile_field_obj)
    {
        $xprofile_field_id  = $xprofile_field_obj->id;
        $pp_custom_field_id = get_option('xprofile_field_' . $xprofile_field_id . '_pp_field_id', '');
        PROFILEPRESS_sql::delete_profile_custom_field($pp_custom_field_id);
        delete_option('xprofile_field_' . $xprofile_field_id . '_pp_field_id');
        delete_option('ppress_field' . $pp_custom_field_id . '_xprofile_field_id');
    }

    /**
     * Check if the sync is active.
     *
     * @param int $pp_field_id ID of ProfilePress custom field
     *
     * @return bool
     */
    public function is_sync_active($pp_field_id)
    {
        $status = get_option("ppress_xprofile_sync_$pp_field_id", '');

        return ! empty($status);
    }

    /**
     * @return self|void
     */
    public static function get_instance()
    {
        self::$instance_flag = true;

        if ( ! defined('ProfilePress\Core\Classes\ExtensionManager::BUDDYPRESS_SYNC')) return;

        if ( ! EM::is_enabled(EM::BUDDYPRESS_SYNC)) return;

        static $instance;

        if ( ! isset($instance)) {
            $instance = new self;
        }

        return $instance;
    }
}