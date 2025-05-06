<?php
/**
 * WordPress requirements module.
 *
 * @since 4.20.1
 *
 * @package LearnDash\Core
 */

namespace LearnDash\Core\Modules\Support\Requirements;

use StellarWP\Learndash\StellarWP\AdminNotices\AdminNotices;

/**
 * WordPress requirements module class.
 *
 * @since 4.20.1
 */
class WordPress {
	/**
	 * Register admin notices.
	 *
	 * @since 4.20.1
	 *
	 * @return void
	 */
	public function register_notices(): void {
		global $wp_version;

		if ( version_compare( $wp_version, '6.5', '>' ) ) {
			return;
		}

		AdminNotices::show(
			'learndash-support-policy-wp-version-update',
			function () {
				ob_start();
				?>
					<p>
						<strong>
							🔔 <?php esc_html_e( 'LearnDash WordPress Version Support Update', 'learndash' ); ?>
						</strong>
					</p>

					<p>
						<?php
						printf(
							// Translators: %1$s is the opening anchor tag, %2$s is the closing anchor tag, %3$s is the opening anchor tag, %4$s is the closing anchor tag.
							esc_html__( 'LearnDash is implementing a new WordPress Version Support Policy. Please update to WordPress 6.6 or higher by June 2025 to continue with seamless updates. %1$sLearn more about this change%2$s in our announcement article. %3$sSee All Technical Requirements%4$s.', 'learndash' ),
							'<a href="https://go.learndash.com/wpvupdate" target="_blank" rel="noreferrer noopener">',
							'</a>',
							'<a href="https://go.learndash.com/tecreq" target="_blank" rel="noreferrer noopener">',
							'</a>'
						);
						?>
					</p>
				<?php
				return ob_get_clean();
			}
		)
			->on(
				'plugins.php', // Plugins page.
				'update-core.php', // Updates page.
				'~(edit|admin)\.php\?(post_type|page)=.*?(learndash|lms|groups|ld|lms|sfwd)~i' // LearnDash admin pages.
			)
			->ifUserCan( LEARNDASH_ADMIN_CAPABILITY_CHECK )
			->dismissible();
	}
}
