<?php

use ProfilePress\Core\Membership\Repositories\PlanRepository;

$plans = PlanRepository::init()->retrieveAll();

?>
<div class="postbox">
    <form method="post">
        <div class="inside">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="code_prefix"><?php _e('Code Prefix', 'profilepress-pro'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="code_prefix" name="code_prefix" class="regular-text code">
                        <p class="description"><?php _e('Prefix to add to generated invite codes.', 'profilepress-pro'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="code_quantity"><?php _e('Code Quantity', 'profilepress-pro'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="code_quantity" name="code_quantity" class="regular-text code"/>
                        <p class="description"><?php _e('The number of invite codes to generate.', 'profilepress-pro'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="usage_limit"><?php _e('Usage Limit', 'profilepress-pro'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="usage_limit" name="usage_limit" class="regular-text code">
                        <p class="description"><?php _e('The number of times invite codes can be used. Leave empty for unlimited usage.', 'profilepress-pro'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="expiry_date"><?php _e('Expiry Date', 'profilepress-pro'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="expiry_date" name="expiry_date" class="regular-text code ppress_datepicker"/>
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
                                    <option value="<?php echo $plan->get_id() ?>"><?php echo $plan->get_name() ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select a membership plan to subscribe users that use this invite code during registration.', 'profilepress-pro'); ?></p>
                    </td>
                </tr>
            </table>
            <p>
                <?php wp_nonce_field('ppress_invite_generate_code'); ?>
                <input class="button-primary" type="submit" name="generate_invite_code" value="<?php _e('Generate Codes', 'profilepress-pro'); ?>">
            </p>
        </div>
    </form>
</div>