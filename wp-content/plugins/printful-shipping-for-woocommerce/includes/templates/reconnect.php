<?php
/**
 * @var string $reconnect_url
 */
?>

<div class="printful-setting-group">

    <h2><?php echo esc_html__('Connection', 'printful'); ?></h2>

    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">
                    <?php echo esc_html__('Reconnect your store', 'printful'); ?>

                </th>
                <td>
                    <a href="<?= esc_url($reconnect_url) ?>" class="button button-primary"><?= esc_html__('Reconnect', 'printful') ?></a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
