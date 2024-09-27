<?php

namespace Leadin\admin;

use Leadin\AssetsManager;
use Leadin\data\User;
use Leadin\admin\Connection;
use Leadin\data\User_Metadata;
use Leadin\admin\MenuConstants;
use Leadin\admin\Gutenberg;
use Leadin\admin\NoticeManager;
use Leadin\admin\PluginActionsManager;
use Leadin\admin\DeactivationForm;
use Leadin\admin\Links;
use Leadin\admin\ContentEmbedInstaller;
use Leadin\auth\OAuth;
use Leadin\admin\utils\Background;
use Leadin\utils\QueryParameters;
use Leadin\utils\Versions;
use Leadin\includes\utils as utils;

use Leadin\data\Portal_Options;
use Leadin\data\Filters;

/**
 * Class responsible for initializing the admin side of the plugin.
 */
class LeadinAdmin {

	const REDIRECT_TRANSIENT = 'leadin_redirect_after_activation';

	/**
	 * Class constructor, adds all the hooks and instantiate the APIs.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_languages' ), 14 );
		add_action( 'admin_init', array( $this, 'redirect_after_activation' ) );
		add_action( 'admin_init', array( $this, 'store_activation_time' ) );
		add_action( 'admin_init', array( $this, 'authorize' ) );
		add_action( 'admin_init', array( $this, 'check_review_requested' ) );
		add_action( 'admin_menu', array( $this, 'build_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		register_activation_hook( LEADIN_BASE_PATH, array( $this, 'do_activate_action' ) );

		/**
		 * The following hooks are public APIs.
		 */
		add_action( 'leadin_redirect', array( $this, 'set_redirect_transient' ) );
		add_action( 'leadin_activate', array( $this, 'do_redirect_action' ), 100 );

		new PluginActionsManager();
		new DeactivationForm();
		new NoticeManager();
		new Gutenberg();
		new ContentEmbedInstaller();
		add_action( 'elementor/documents/register_controls', array( $this, 'register_document_controls' ) );
	}

	/**
	 * Register additional document controls.
	 *
	 * @param \Elementor\Core\DocumentTypes\PageBase $document The PageBase document instance.
	 */
	public function register_document_controls( $document ) {
		if ( ! $document instanceof \Elementor\Core\DocumentTypes\PageBase || ! $document::get_property( 'has_elements' ) ) {
			return;
		}

		$document->start_controls_section(
			'hubspot',
			array(
				'label' => esc_html__( 'Hubspot', 'leadin' ),
				'tab'   => \Elementor\Controls_Manager::TAB_SETTINGS,
			)
		);

		$document->add_control(
			'content_type',
			array(
				'label'       => esc_html__( 'Select the content type HubSpot Analytics uses to track this page', 'leadin' ),
				'label_block' => true,
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => array(
					''                  => esc_html__( 'Detect Automatically', 'leadin' ),
					'blog-post'         => esc_html__( 'Blog Post', 'leadin' ),
					'knowledge-article' => esc_html__( 'Knowledge Article', 'leadin' ),
					'landing-page'      => esc_html__( 'Landing Page', 'leadin' ),
					'listing-page'      => esc_html__( 'Listing Page', 'leadin' ),
					'standard-page'     => esc_html__( 'Standard Page', 'leadin' ),
				),
				'default'     => '',
			)
		);

		$document->end_controls_section();
	}



	/**
	 * Load the .mo language files.
	 */
	public function load_languages() {
		load_plugin_textdomain( 'leadin', false, '/leadin/languages' );
	}

	/**
	 * Handler called on plugin activation.
	 */
	public function do_activate_action() {
		\do_action( 'leadin_activate' );
	}

	/**
	 * Handler for the leadin_activate action.
	 */
	public function do_redirect_action() {
		\do_action( 'leadin_redirect' );
	}

	/**
	 * Set transient after activating the plugin.
	 */
	public function set_redirect_transient() {
		set_transient( self::REDIRECT_TRANSIENT, true, 60 );
	}

	/**
	 * Redirect to the dashboard after activation.
	 */
	public function redirect_after_activation() {
		if ( get_transient( self::REDIRECT_TRANSIENT ) ) {
			delete_transient( self::REDIRECT_TRANSIENT );
			wp_safe_redirect( admin_url( 'admin.php?page=leadin' ) );
			exit;
		}
	}

	/**
	 * Connect/disconnect the plugin
	 */
	public function authorize() {
		if ( User::is_admin() ) {
			if ( Connection::is_connection_requested() ) {
				$redirect_params = array();
				Connection::oauth_connect();
				$redirect_params['leadin_just_connected'] = 1;

				if ( Connection::is_new_portal() ) {
					$redirect_params['is_new_portal'] = 1;
				}
				Routing::redirect( MenuConstants::USER_GUIDE, $redirect_params );
			} elseif ( Connection::is_disconnection_requested() ) {
				Connection::disconnect();
				Routing::redirect( MenuConstants::ROOT );
			}
		}
	}

	/**
	 * Check if query parameter for review is present on the request
	 * then add user metadata to persist review time
	 * Redirects if value is equal to true
	 */
	public function check_review_requested() {
		if ( Connection::is_connected() && Routing::has_review_request() ) {
			User_Metadata::set_skip_review( time() );
			if ( Routing::is_review_request() ) {
				header( 'Location: https://survey.hsforms.com/1ILAEu_k_Ttiy344dpHM--w1h' );
				exit();
			}
		}
	}

	/**
	 * Store activation time in a WP option.
	 */
	public function store_activation_time() {
		if ( empty( Portal_Options::get_activation_time() ) ) {
			Portal_Options::set_activation_time();
		}
	}

	/**
	 * Adds scripts for the admin section.
	 */
	public function enqueue_scripts() {
		AssetsManager::register_assets();
		AssetsManager::enqueue_admin_assets();
		if ( get_current_screen()->id === 'plugins' ) {
			AssetsManager::enqueue_feedback_assets();
		}
	}

	/**
	 * Adds Leadin menu to admin sidebar
	 */
	public function build_menu() {
		if ( Connection::is_connected() ) {
			add_menu_page( __( 'HubSpot', 'leadin' ), __( 'HubSpot', 'leadin' ), Filters::apply_view_plugin_menu_capability_filters(), MenuConstants::ROOT, array( $this, 'build_integrated_app' ), 'dashicons-sprocket', '25.100713' );

			add_submenu_page( MenuConstants::ROOT, __( 'User Guide', 'leadin' ), __( 'User Guide', 'leadin' ), Filters::apply_view_plugin_menu_capability_filters(), MenuConstants::USER_GUIDE, array( $this, 'build_integrated_app' ) );
			add_submenu_page( MenuConstants::ROOT, __( 'Forms', 'leadin' ), __( 'Forms', 'leadin' ), Filters::apply_view_plugin_menu_capability_filters(), MenuConstants::FORMS, array( $this, 'build_integrated_app' ) );
			add_submenu_page( MenuConstants::ROOT, __( 'Live Chat', 'leadin' ), __( 'Live Chat', 'leadin' ), Filters::apply_view_plugin_menu_capability_filters(), MenuConstants::CHATFLOWS, array( $this, 'build_integrated_app' ) );

			add_submenu_page( MenuConstants::ROOT, __( 'Contacts', 'leadin' ), self::make_external_link( Links::get_iframe_src( MenuConstants::CONTACTS ), __( 'Contacts', 'leadin' ), 'leadin_contacts_link' ), Filters::apply_view_plugin_menu_capability_filters(), MenuConstants::CONTACTS, array( $this, 'build_app' ) );
			add_submenu_page( MenuConstants::ROOT, __( 'Email', 'leadin' ), self::make_external_link( Links::get_iframe_src( MenuConstants::EMAIL ), __( 'Email', 'leadin' ), 'leadin_email_link' ), Filters::apply_view_plugin_menu_capability_filters(), MenuConstants::EMAIL, array( $this, 'build_app' ) );
			add_submenu_page( MenuConstants::ROOT, __( 'Lists', 'leadin' ), self::make_external_link( Links::get_iframe_src( MenuConstants::LISTS ), __( 'Lists', 'leadin' ), 'leadin_lists_link' ), Filters::apply_view_plugin_menu_capability_filters(), MenuConstants::LISTS, array( $this, 'build_app' ) );
			add_submenu_page( MenuConstants::ROOT, __( 'Reporting', 'leadin' ), self::make_external_link( Links::get_iframe_src( MenuConstants::REPORTING ), __( 'Reporting', 'leadin' ), 'leadin_reporting_link' ), Filters::apply_view_plugin_menu_capability_filters(), MenuConstants::REPORTING, array( $this, 'build_app' ) );

			add_submenu_page( MenuConstants::ROOT, __( 'Settings', 'leadin' ), __( 'Settings', 'leadin' ), Filters::apply_view_plugin_menu_capability_filters(), MenuConstants::SETTINGS, array( $this, 'build_integrated_app' ) );

			add_submenu_page( MenuConstants::ROOT, __( 'Upgrade', 'leadin' ), self::make_external_link( Links::get_iframe_src( MenuConstants::PRICING ), __( 'Upgrade', 'leadin' ), 'leadin_pricing_link' ), Filters::apply_view_plugin_menu_capability_filters(), MenuConstants::PRICING, array( $this, 'build_app' ) );

			remove_submenu_page( MenuConstants::ROOT, MenuConstants::ROOT );
		} else {
			$notification_icon = ' <span class="update-plugins count-1"><span class="plugin-count">!</span></span>';
			add_menu_page( __( 'HubSpot', 'leadin' ), __( 'HubSpot', 'leadin' ) . $notification_icon, Filters::apply_connect_plugin_capability_filters(), MenuConstants::ROOT, array( $this, 'build_integrated_app' ), 'dashicons-sprocket', '25.100713' );
		}
	}


	/**
	 * Wraps external link.
	 *
	 * @param String $href    link destination.
	 * @param String $content link content.
	 * @param String $class   class for ATs.
	 */
	public function make_external_link( $href, $content, $class ) {
		return "<a href=\"$href\" class=\"external_link $class\" target=\"_blank\" onclick=\"blur()\">$content</a>";
	}

	/**
	 * Renders the leadin admin page.
	 */
	public function build_app() {
		AssetsManager::enqueue_bridge_assets();
		self::render_app();
	}

	/**
	 * Renders the integrated forms app.
	 */
	public function build_integrated_app() {
		AssetsManager::enqueue_integrated_app_assets();
		self::render_app();
	}

	/**
	 * Render app container
	 */
	public function render_app() {
		$error_message = '';

		if ( Versions::is_php_version_not_supported() ) {
			$error_message = sprintf(
				/* translators: %1$s: Plugin current version %2$s: PHP required version */
				__( 'HubSpot All-In-One Marketing %1$s requires PHP %2$s or higher Please upgrade WordPress first', 'leadin' ),
				LEADIN_PLUGIN_VERSION,
				LEADIN_REQUIRED_PHP_VERSION
			);
		} elseif ( Versions::is_wp_version_not_supported() ) {
			$error_message = sprintf(
				/* translators: %1$s: Plugin current version %2$s: WordPress required version */
				__( 'HubSpot All-In-One Marketing %1$s requires WordPress %2$s or higher Please upgrade WordPress first', 'leadin' ),
				LEADIN_PLUGIN_VERSION,
				LEADIN_REQUIRED_WP_VERSION
			);
		}

		if ( $error_message ) {
			?>
				<div class='notice notice-warning'>
					<p>
						<?php echo esc_html( $error_message ); ?>
					</p>
				</div>
			<?php
		} else {
			?>
				<div id="leadin-iframe-fallback-container"></div>
				<div id="leadin-iframe-container"></div>
			<?php
		}
	}

}
