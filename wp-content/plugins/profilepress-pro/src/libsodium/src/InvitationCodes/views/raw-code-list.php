<?php

use ProfilePress\Libsodium\InvitationCodes\Init;

$result = '';

$invite_codes = Init::get_all_invite_codes();

if ( ! empty($invite_codes)) {
    $result = implode(PHP_EOL, $invite_codes);
}
?>
<div class="postbox">
    <form method="post">
        <div class="inside">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="raw_list"><?php _e('Code List', 'profilepress-pro'); ?></label>
                    </th>
                    <td>
                        <textarea id="raw_list" rows="15"><?php echo $result ?></textarea>
                        <p class="description"><?php _e('List of all invite codes to make copy and paste easy for you.', 'profilepress-pro'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
    </form>
</div>