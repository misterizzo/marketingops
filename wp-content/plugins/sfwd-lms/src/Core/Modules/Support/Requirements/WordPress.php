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
use WP_Error;
use WP_Upgrader;

/**
 * WordPress requirements module class.
 *
 * @since 4.20.1
 */
class WordPress {
	/**
	 * Minimum required WordPress version.
	 *
	 * @since 4.21.5
	 *
	 * @var string
	 */
	private const MINIMUM_WORDPRESS_VERSION = '6.6';

	/**
	 * Register admin notices.
	 *
	 * @since 4.20.1
	 * @since 4.21.4 Added the final warning notice for WP < 6.6 and remove the previous one.
	 *
	 * @return void
	 */
	public function register_notices(): void {
		global $wp_version;

		if ( version_compare( $wp_version, '6.5', '>' ) ) {
			return;
		}

		AdminNotices::show(
			'learndash-support-policy-wp-version-update-6-6',
			function () {
				ob_start();
				?>
					<p>
						<strong>
							⚠️ <?php esc_html_e( 'Action Needed: Update WordPress for LearnDash Compatibility', 'learndash' ); ?>
						</strong>
					</p>

					<p>
						<?php
						printf(
							// Translators: %1$s: opening strong tag, %2$s: closing strong tag.
							esc_html__( 'Starting the first week of June 2025, LearnDash will require WordPress version %1$s6.6 or higher%2$s. Please update your WordPress site by then to maintain uninterrupted LearnDash functionality and updates.', 'learndash' ),
							'<strong>',
							'</strong>'
						);
						?>
					</p>

					<p>
						<?php
						printf(
							// Translators: %1$s: opening link tag, %2$s: closing link tag, %3$s: opening link tag, %4$s: closing link tag.
							esc_html__( '🔧 %1$sUpdate WordPress%2$s | 📄 %3$sView Details%4$s', 'learndash' ),
							'<a href="' . esc_url( admin_url( '/update-core.php' ) ) . '">',
							'</a>',
							'<a href="https://go.learndash.com/wpvupdate" target="_blank" rel="noopener noreferrer">',
							'</a>'
						);
						?>
					</p>
				<?php
				return ob_get_clean();
			}
		)
			->on(
				// Dashboard page.
				[
					'id' => 'dashboard',
				],
				'plugins.php', // Plugins page.
				'update-core.php', // Updates page.
				'~(edit|admin)\.php\?(post_type|page)=.*?(learndash|lms|groups|ld|lms|sfwd)~i' // LearnDash admin pages.
			)
			->ifUserCan( LEARNDASH_ADMIN_CAPABILITY_CHECK )
			->asWarning()
			->notDismissible();
	}

	/**
	 * Check the required WordPress version.
	 *
	 * @since 4.21.5
	 *
	 * @param bool                 $pre_download Whether to bail without returning the package. Default false.
	 * @param string               $package      The package file name.
	 * @param WP_Upgrader          $wp_upgrader  The WP_Upgrader instance.
	 * @param array<string, mixed> $hook_extra   Extra arguments passed to hooked filters.
	 *
	 * @return bool|WP_Error
	 */
	public function check_required_wp_version( $pre_download, $package, $wp_upgrader, $hook_extra ) {
		if (
			! isset( $hook_extra['plugin'] )
			|| $hook_extra['plugin'] !== LEARNDASH_LMS_PLUGIN_KEY
		) {
			return $pre_download;
		}

		$wp_version  = learndash_sanitize_version_string( get_bloginfo( 'version' ) );
		$min_version = learndash_sanitize_version_string( self::MINIMUM_WORDPRESS_VERSION );

		if ( ! version_compare( $wp_version, $min_version, '<' ) ) {
			return $pre_download;
		}

		return new WP_Error(
			'learndash_wp_version_error',
			sprintf(
				// Translators: %1$s: opening link tag, %2$s: closing link tag.
				__( 'Please update WordPress to at least version %1$s. %2$sSee all Technical Requirements%3$s.', 'learndash' ),
				self::MINIMUM_WORDPRESS_VERSION,
				'<a href="https://go.learndash.com/tecreq" target="_blank" rel="noopener noreferrer">',
				'</a>'
			)
		);
	}
}
