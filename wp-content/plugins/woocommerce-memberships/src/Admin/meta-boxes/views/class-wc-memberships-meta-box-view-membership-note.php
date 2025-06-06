<?php
/**
 * WooCommerce Memberships
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Memberships to newer
 * versions in the future. If you wish to customize WooCommerce Memberships for your
 * needs please refer to https://docs.woocommerce.com/document/woocommerce-memberships/ for more information.
 *
 * @author    SkyVerge
 * @copyright Copyright (c) 2014-2025, SkyVerge, Inc. (info@skyverge.com)
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * View for a single membership note
 *
 * @since 1.7.0
 */
class WC_Memberships_Meta_Box_View_Membership_Note extends \WC_Memberships_Meta_Box_View  {


	/**
	 * HTML Output
	 *
	 * @since 1.7.0
	 * @param array $args
	 */
	public function output( $args = array() ) {

		$note         = isset( $args['note'] )         ? $args['note']         : null;
		$note_classes = isset( $args['note_classes'] ) ? $args['note_classes'] : array( 'note' );

		if ( is_object( $note ) ) :

			?>
			<li rel="<?php echo absint( $note->comment_ID ); ?>" class="<?php echo implode( ' ', array_map( 'sanitize_html_class', $note_classes ) ); ?>">

				<div class="note-content">
					<?php echo wpautop( wptexturize( wp_kses_post( $note->comment_content ) ) ); ?>
				</div>

				<p class="meta">
					<?php

					$abbr_start = '<abbr class="exact-date" title="' . esc_attr( $note->comment_date ) . '">';
					$abbr_end   = '</abbr>';

					/* translators: Placeholders: Date (%1$s) and time (%2$s) when a Membership Note was added */
					$note_meta = sprintf( $abbr_start . __( 'added on %1$s at %2$s', 'woocommerce-memberships' ) . $abbr_end,
						date_i18n( wc_date_format(), strtotime( $note->comment_date ) ),
						date_i18n( wc_time_format(), strtotime( $note->comment_date ) )
					);

					if ( $note->comment_author !== __( 'WooCommerce', 'woocommerce-memberships' ) ) {

						/* translators: Placeholders: %1$s - membership note published date and time; %2$s membership note published by - for example "On 1 October 2020 at 10:25am by John Doe" */
						$note_meta = sprintf( __( '%1$s by %2$s', 'woocommerce-memberships' ),
							$note_meta,
							$note->comment_author
						);
					}

					echo $note_meta;

					?>
					<a href="#" class="delete-note js-delete-note"><?php esc_html_e( 'Delete note', 'woocommerce-memberships' ); ?></a>
				</p>

			</li>
			<?php

		endif;
	}


}
