<?php

$id = ppress_settings_by_key('replace_wc_my_account_edit_profile');

echo do_shortcode(sprintf('[profilepress-edit-profile id="%s"]', $id));