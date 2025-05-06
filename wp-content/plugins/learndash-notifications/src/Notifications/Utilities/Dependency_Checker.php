<?php
/**
 * Plugin dependency checker class file.
 *
 * @package LearnDash\Notifications
 */

namespace LearnDash\Notifications\Utilities;

/**
 * Plugin dependency checker class.
 *
 * @since 1.6.3
 *
 * @phpstan-type Plugin array{
 *   class?: string,
 *   label?: string,
 *   min_version?: string,
 *   version_constant?: string
 * }
 *
 * @phpstan-type Plugins array<string, Plugin>
 */
class Dependency_Checker {
	/**
	 * Instance of our class.
	 *
	 * @since 1.6.3
	 *
	 * @var Dependency_Checker
	 */
	private static $instance;

	/**
	 * The displayed message shown to the user on admin pages.
	 *
	 * @since 1.6.3
	 *
	 * @var string
	 */
	private $admin_notice_message = '';

	/**
	 * The array of plugins to check Should be key => label pair. The label can be anything to display.
	 *
	 * @since 1.6.3
	 *
	 * @var Plugins
	 */
	private $plugins_to_check = [];

	/**
	 * Array to hold the inactive plugins. This is populated during the plugins_loaded action via the function call to check_inactive_plugin_dependency().
	 *
	 * @var Plugins
	 */
	private $plugins_inactive = [];

	/**
	 * Constructor.
	 *
	 * @since 1.6.3
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ], 1 );
	}

	/**
	 * Returns the instance of this class or new one.
	 *
	 * @since 1.6.3
	 *
	 * @return Dependency_Checker
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Check if required plugins are active. Default false.
	 *
	 * @since 1.6.3
	 *
	 * @return bool True if required plugin are active. False otherwise.
	 */
	public function check_dependency_results() {
		if ( empty( $this->plugins_inactive ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Callback function for the plugins_loaded action.
	 *
	 * @since 1.6.3
	 *
	 * @return void
	 */
	public function plugins_loaded() {
		$this->check_inactive_plugin_dependency();
	}

	/**
	 * Function called during the plugins_loaded process to check if required plugins are present and active. Handles regular and Multisite checks.
	 *
	 * @since 1.6.3
	 *
	 * @param bool $set_admin_notice Whether to set the Admin Notice or not. Default true.
	 *
	 * @return Plugins Inactive but required plugins.
	 */
	public function check_inactive_plugin_dependency( $set_admin_notice = true ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! empty( $this->plugins_to_check ) ) {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			foreach ( $this->plugins_to_check as $plugin_key => $plugin_data ) {
				// Check if plugin is inactive directly.
				if ( ! is_plugin_active( $plugin_key ) ) {
					if ( is_multisite() ) {
						if ( ! is_plugin_active_for_network( $plugin_key ) ) {
							$this->plugins_inactive[ $plugin_key ] = $plugin_data;
						}
					} else {
						$this->plugins_inactive[ $plugin_key ] = $plugin_data;
					}
				} elseif (
						isset( $plugin_data['class'] )
						&& ! empty( $plugin_data['class'] )
						&& ! class_exists( $plugin_data['class'] )
					) {
						$this->plugins_inactive[ $plugin_key ] = $plugin_data;
				}

				// Version checks if plugin is active.
				if (
					! isset( $this->plugins_inactive[ $plugin_key ] )
					&& isset( $plugin_data['min_version'] )
					&& ! empty( $plugin_data['min_version'] )
				) {
					if (
						isset( $plugin_data['version_constant'] )
						&& defined( $plugin_data['version_constant'] )
					) {
						/**
						 * Plugin version pulled from its constant.
						 *
						 * @var string
						 */
						$plugin_version = constant( $plugin_data['version_constant'] );

						// Check against the Version Constant if we know it.
						if ( version_compare( $plugin_version, $plugin_data['min_version'], '<' ) ) {
							$this->plugins_inactive[ $plugin_key ] = $plugin_data;
						}
					} else {
						// Otherwise attempt to parse the plugin header.
						if (
							defined( 'WP_PLUGIN_DIR' )
							&& file_exists( trailingslashit( wp_normalize_path( WP_PLUGIN_DIR ) ) . $plugin_key )
						) {
							$plugin_header = get_plugin_data( trailingslashit( wp_normalize_path( WP_PLUGIN_DIR ) ) . $plugin_key );

							if ( version_compare( $plugin_header['Version'], $plugin_data['min_version'], '<' ) ) {
								$this->plugins_inactive[ $plugin_key ] = $plugin_data;
							}
						}
					}
				}
			}

			if (
				! empty( $this->plugins_inactive )
				&& $set_admin_notice
			) {
				add_action( 'admin_notices', [ $this, 'notify_required' ] );
			}
		}

		return $this->plugins_inactive; }

	/**
	 * Function to set custom admin notice message.
	 *
	 * @param string $message Message.
	 *
	 * @since 1.6.3
	 * @return void
	 */
	public function set_message( $message = '' ) {
		if ( ! empty( $message ) ) {
			$this->admin_notice_message = $message;
		}
	}

	/**
	 * Set plugin required dependencies.
	 *
	 * @since 1.6.3
	 *
	 * @param Plugins $plugins Array of plugins to check.
	 *
	 * @return void
	 */
	public function set_dependencies( $plugins = [] ) {
		if ( is_array( $plugins ) ) {
			$this->plugins_to_check = $plugins;
		}
	}

	/**
	 * Notify user that missing plugins are required.
	 *
	 * @since 1.6.3
	 *
	 * @return void
	 */
	public function notify_required(): void {
		if (
			( ! empty( $this->admin_notice_message ) )
			&& ( ! empty( $this->plugins_inactive ) )
		) {
			$required_plugins = [];

			foreach ( $this->plugins_inactive as $plugin_data ) {
				$required_plugin = $plugin_data['label'] ?? '';

				if ( ( isset( $plugin_data['min_version'] ) ) && ( ! empty( $plugin_data['min_version'] ) ) ) {
					$required_plugin .= ' v' . $plugin_data['min_version'];
				}

				$required_plugins[] = $required_plugin;
			}

			$admin_notice_message = sprintf(
				$this->admin_notice_message . '<ul style="list-style-type: disc; margin-left: 1.5em;">%s</ul>',
				implode(
					"\n",
					array_map(
						function ( $required_plugin ) {
							return "<li>{$required_plugin}</li>";
						},
						$required_plugins
					)
				)
			);

			?>
			<div class="notice notice-error ld-notice-error is-dismissible">
				<p><?php echo wp_kses_post( $admin_notice_message ); ?></p>
			</div>
			<?php
		}
	}
}
