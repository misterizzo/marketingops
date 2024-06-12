<?php

use ProfilePress\Core\Admin\SettingsPages\Membership\GroupsPage\GroupWPListTable;

?>
<div class="submitbox" id="submitpost">

    <div id="major-publishing-actions">
        <div id="delete-action">
            <?php if (ppressGET_var('ppress_group_action') == 'edit') : ?>
                <a class="submitdelete deletion pp-confirm-delete" href="<?= GroupWPListTable::delete_group_url(absint($_GET['id'])); ?>">
                    <?= esc_html__('Delete', 'wp-user-avatar') ?>
                </a>
            <?php endif; ?>
        </div>

        <div id="publishing-action">
            <input type="submit" name="ppress_save_group" class="button button-primary button-large" value="<?= esc_html__('Save Group', 'wp-user-avatar') ?>">
        </div>
        <div class="clear"></div>
    </div>

</div>