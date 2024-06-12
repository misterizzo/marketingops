<?php

namespace Leadin;

require_once LEADIN_PLUGIN_DIR . '/public/includes/api-loader.php';

use \Leadin\AssetsManager;
use \Leadin\PageHooks;
use \Leadin\admin\LeadinAdmin;
use Leadin\admin\widgets\ElementorForm;
use Leadin\admin\widgets\ElementorMeeting;
use Leadin\admin\widgets\ElementorFormSelect;
use Leadin\admin\widgets\ElementorMeetingSelect;

/**
 * Main class of the plugin.
 */
class Leadin {
	/**
	 * Plugin's constructor. Everything starts here.
	 */
	public function __construct() {
		new PageHooks();
		add_action( 'elementor/elements/categories_registered', array( $this, 'add_elementor_widget_categories' ) );
		add_action( 'elementor/controls/register', array( $this, 'register_hsselectors_control' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_elementor_widgets' ) );
		if ( is_admin() ) {
			new LeadinAdmin();
		}
	}

	/**
	 * Register widgets for Elementor.
	 *
	 * @param object $elements_manager elementor widget manager.
	 */
	public function add_elementor_widget_categories( $elements_manager ) {
		$elements_manager->add_category(
			'hubspot',
			array(
				'title' => esc_html__( 'Hubspot', 'leadin' ),
				'icon'  => 'fa fa-plug',
			)
		);
	}

	/**
	 * Register widgets for Elementor.
	 *
	 * @param object $widgets_manager elementor widget manager.
	 */
	public function register_elementor_widgets( $widgets_manager ) {
		$widgets_manager->register( new ElementorForm() );
		$widgets_manager->register( new ElementorMeeting() );
	}

	/**
	 * Register controls for elementor widget
	 *
	 * @param object $controls_manager elementor controls manager.
	 */
	public function register_hsselectors_control( $controls_manager ) {
		$controls_manager->register( new ElementorFormSelect() );
		$controls_manager->register( new ElementorMeetingSelect() );
	}

}

