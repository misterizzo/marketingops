<?php

use ProfilePress\Core\Classes\PROFILEPRESS_sql as PROFILEPRESS_sql;

?>
<form method="post">
    <div class="inside">
        <table class="widefat striped" id="pp_contact_info">
            <thead>
            <tr>
                <th class="custom-field-anchor"><span class="dashicons dashicons-menu"></span></th>
                <th> <?php _e('Field Label', 'profilepress-pro'); ?></th>
                <th> <?php _e('Field Key', 'profilepress-pro'); ?></th>
                <th> <?php _e('Action', 'profilepress-pro'); ?></th>
            </tr>
            </thead>

            <tfoot>
            <tr>
                <th class="custom-field-anchor"><span class="dashicons dashicons-menu"></span></th>
                <th> <?php _e('Field Label', 'profilepress-pro'); ?></th>
                <th> <?php _e('Field Key', 'profilepress-pro'); ?></th>
                <th> <?php _e('Action', 'profilepress-pro'); ?></th>
            </tr>
            </tfoot>

            <tbody>
            <?php
            $contact_info_in_db = PROFILEPRESS_sql::get_contact_info_fields();

            if (empty($contact_info_in_db)) {
                echo '<tr><td></td><td>' . esc_html__('No contact information was found', 'profilepress-pro') . '</td><td></td><td></td></tr>';
            } else {

                foreach ($contact_info_in_db as $key => $value) {
                    $key   = esc_html($key);
                    $value = esc_html($value);
                    echo "<tr id='$key'>";
                    echo '<td class="custom-field-anchor"><span class="dashicons dashicons-menu"></span></td>';
                    echo "<td>$value</td>";
                    echo "<td>$key</td>";
                    echo '<td>';
                    printf(
                        '<a href="%s">%s</a>',
                        PPRESS_CONTACT_INFO_SETTINGS_PAGE . '&edit=' . $key, esc_html__('Edit', 'profilepress-pro')
                    );

                    echo ' | ';
                    printf(
                        '<a style="color: #a00" class="pp-confirm-delete" href="%s">%s</a>',
                        wp_nonce_url(PPRESS_CONTACT_INFO_SETTINGS_PAGE . '&delete=' . $key, 'pp-delete-contact-info'), esc_html__('Delete', 'profilepress-pro')
                    );

                    echo '</td>';
                    echo '</tr>';
                }
            }
            ?>
            </tbody>
        </table>
    </div>
</form>