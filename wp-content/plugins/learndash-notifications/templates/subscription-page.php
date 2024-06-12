<?php
add_action( 'wp_footer', function() {
    ?>
    <script type="text/javascript">
        jQuery( document ).ready( function( $ ) {
            $( '.select-all' ).on( 'click', function() {
                if ( $( this ) .is( ':checked' ) ) {
                    $( 'input[type="checkbox"]' ).prop( 'checked', true );
                } else {
                    $( 'input[type="checkbox"]' ).prop( 'checked', false );
                }
            } );
        });
    </script>
    <?php
} );

get_header();

$triggers      = learndash_notifications_get_triggers();
$subscriptions = get_user_meta( get_current_user_id(), 'learndash_notifications_subscription', true );
?>

<style scoped="scoped">
    .primary {
        margin-bottom: 30px;
        max-width: 600px;
        margin: 0 auto;
    }

    h1 {
        text-align: center;
        margin-bottom: 40px;
    }

    .message {
        color: #fff;
        padding: 5px 15px;
        margin-bottom: 20px;
    }

    .message.success {
        background-color: green;
    }

    .message.fail {
        background-color: red;
    }

    .triggers {
        display: flex;
        flex-direction: column;
    }

    .item {
        display: flex;
        flex-direction: row;
    }

    .item.alternate {
        background-color: #f5f5f5;
    }

    .item.submit {
        margin-top: 20px;
    }

    .header {
        font-weight: bold;
    }

    .child {
        width: 80%;
        padding: 5px 10px;
    }

    .child.cb {
        width: 200px;
        text-align: center; 
    }

    @media screen {
        
    }
</style>

<main class="learndash-notifications primary">
    <h1><?php _e( 'LearnDash Notifications Subscription', 'learndash-notifications' ) ?></h1>
    <?php
    if ( isset( $_GET['message'] ) ) {
        switch ( $_GET['message'] ) {
            case 'success':
                $class = 'success';
                $message = __( 'Your notification settings has been successfully saved.', 'learndash-notifications' );
                break;

            case 'fail':
                $class = 'fail';
                $message = __( 'There is something wrong. Please try again later.', 'learndash-notifications' );
                break;
            
            default:
                $message = false;
                break;
        }

        if ( $message ) {
            ?>
            <div class="message <?php echo esc_attr( $class ); ?>"><?php echo $message; ?></div>
            <?php
        }
    }
    ?>
    <div class="triggers">
        <form action="" method="POST">
            <input type="hidden" name="action" value="learndash_notifications_subscription">
            <input type="hidden" name="user_id" value="<?php echo esc_attr( get_current_user_id() ) ?>">
            <?php wp_nonce_field( 'learndash_notifications_subscription', 'ld_nonce' ) ?>
            <div class="item">
                <div class="header child"><?php _e( 'Triggers', 'learndash-notifications' ) ?></div>
                <div class="header cb child">
                    <div>
                        <?php _e( 'Enabled', 'learndash-notifications' ) ?>
                    </div>
                    <div class="input">
                        <input type="checkbox" name="select-all" class="select-all" title="<?php esc_attr_e( 'Select All', 'learndash-notifications' ) ?>">
                    </div>
                </div>
            </div>
            <?php $count = 0; ?>
            <?php foreach ( $triggers as $key => $label ) : ?>
                <?php $count++; ?>
                <?php $checked = ! isset( $subscriptions[ $key ] ) || $subscriptions[ $key ] ? 'checked="checked"' : ''; ?>
                <div class="item <?php if ( $count % 2 ) echo 'alternate'; ?>">
                    <div class="label child"><?php echo esc_textarea( $label ) ?></div>
                    <div class="cb child"><input type="checkbox" name="<?php echo esc_attr( $key ) ?>" value="1" <?php echo $checked ?>></div>
                </div>
            <?php endforeach; ?>
            <div class="item submit">
                <div class="child"></div>
                <div class="child cb">
                    <input class="submit" type="submit" value="<?php esc_attr_e( 'Save Changes', 'learndash-notifications' ) ?>">
                </div>
            </div>
        </form>
    </div>
</main>

<?php
get_footer();