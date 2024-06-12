<?php
// if ajax post request is received return the parsed shortcode
use ProfilePress\Core\ShortcodeParser\Builder\FrontendProfileBuilder;

if ( ! isset($_POST['builder_structure']) || empty($_POST['builder_structure'])) {
    return;
}
$builder_structure = stripslashes($_POST['builder_structure']);
$builder_css       = stripslashes($_POST['builder_css']);
?>

<head>
    <script type='text/javascript'>var pp_ajax_form = {"disable_ajax_form": "false"};</script>

    <link rel="stylesheet" type="text/css" href="<?= PPRESS_ASSETS_URL . '/css/frontend.min.css' ?>">
    <link rel="stylesheet" type="text/css" href="<?= PPRESS_ASSETS_URL . '/select2/select2.min.css' ?>">
    <script type="text/javascript" src="<?= includes_url('js/jquery/jquery.js'); ?>"></script>
    <script type="text/javascript" src="<?= PPRESS_ASSETS_URL . '/js/frontend.min.js'; ?>"></script>
    <script type="text/javascript" src="<?= PPRESS_ASSETS_URL . '/select2/select2.min.js'; ?>"></script>
    <?php if (class_exists('ProfilePress\Libsodium\Recaptcha\Recaptcha')) : ?>
        <script type="text/javascript" src="<?= \ProfilePress\Libsodium\Recaptcha\Recaptcha::enqueue_script(true); ?>"></script>
    <?php endif; ?>

    <style id="preview-css" type="text/css"><?= $builder_css ?></style>
</head>
<body>
<?php
FrontendProfileBuilder::get_instance();
echo do_shortcode($builder_structure);
?>
</body>