<?php

$redirect_current_page = ppress_get_current_url_query_string();

$pp_login_form_id = ppress_settings_by_key('replace_wc_checkout_login');

$shortcode = sprintf('profilepress-login id="%s"', $pp_login_form_id);

if (strpos($pp_login_form_id, 'melange_') !== false) {
    $shortcode = sprintf('profilepress-melange id="%s"', str_replace('melange_', '', $pp_login_form_id));
}

echo '<div class="pp_wc_login" style="display:none;margin-bottom:10px">';
echo do_shortcode(sprintf('[%s redirect="%s"]', $shortcode, $redirect_current_page));
echo '</div>';