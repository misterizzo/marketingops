<?php

use ProfilePress\Core\Membership\Repositories\PlanRepository;
use ProfilePress\Libsodium\InvitationCodes\InviteCodeEntity;

$id = absint(ppressGET_var('id'));

$plans = PlanRepository::init()->retrieveAll();

$invite_code = new InviteCodeEntity($id);

?>
<div class="postbox">
    <form method="post">
        <div class="inside">
            <table class="form-table">
                <tr>
                    <?php if ( ! $invite_code->exists()): ?>
                        <th scope="row">
                            <label for="invite_codes"><?php _e('Invite Codes', 'profilepress-pro'); ?></label>
                        </th>
                        <td>
                            <textarea required id="invite_codes" name="invite_codes" rows="10" class="large-text code"></textarea>
                            <p class="description"><?php _e('Enter invite-codes, one per line.', 'profilepress-pro'); ?></p>
                        </td>
                    <?php else : ?>
                        <th scope="row">
                            <label for="invite_code"><?php _e('Invite Code', 'profilepress-pro'); ?></label>
                        </th>
                        <td>
                            <input disabled type="text" required id="invite_code" class="regular-text code" value="<?php echo $invite_code->invite_code ?>">
                            <input type="hidden" name="invite_code" value="<?php echo $invite_code->invite_code ?>">
                        </td>
                    <?php endif; ?>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="usage_limit"><?php _e('Usage Limit', 'profilepress-pro'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="usage_limit" name="usage_limit" class="regular-text code" value="<?php echo $invite_code->usage_limit ?>"/>
                        <p class="description"><?php _e('The number of times invite codes can be used. Leave empty for unlimited usage.', 'profilepress-pro'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="expiry_date"><?php _e('Expiry Date', 'profilepress-pro'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="expiry_date" name="expiry_date" class="regular-text code ppress_datepicker" value="<?php echo $invite_code->expiry_date ?>"/>
                        <p class="description"><?php _e('Set the date the invite code will expire. Leave empty for no expiry date', 'profilepress-pro'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="membership_plan"><?php _e('Membership Plan', 'profilepress-pro'); ?></label></th>
                    <td>
                        <select id="membership_plan" name="membership_plan">
                            <option value="">&mdash;&mdash;&mdash;</option>
                            <?php if (is_array($plans) && ! empty($plans)) : ?>
                                <?php foreach ($plans as $plan) : ?>
                                    <option value="<?php echo $plan->get_id() ?>" <?php selected($plan->get_id(), $invite_code->membership_plan) ?>><?php echo $plan->get_name() ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select a membership plan to subscribe users that use this invite code during registration.', 'profilepress-pro'); ?></p>
                    </td>
                </tr>
            </table>
            <p>
                <?php wp_nonce_field('ppress_save_invite_code'); ?>
                <input class="button-primary" type="submit" name="save_invite_code" value="<?php _e('Save Changes', 'profilepress-pro'); ?>">
            </p>
        </div>
    </form>
</div>