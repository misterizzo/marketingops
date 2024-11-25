<?php

namespace GtmEcommerceWoo\Lib\Util;

/**
 * Utility to work with settings and options WordPress API.
 */
class WpSettingsUtil {
	/** @var string */
	protected $snakeCaseNamespace;
	/** @var string */
	protected $spineCaseNamespace;
	/** @var array */
	protected $tabs = [];
	/** @var array */
	protected $sections = [];

	public function __construct( string $snakeCaseNamespace, string $spineCaseNamespace) {
		$this->snakeCaseNamespace = $snakeCaseNamespace;
		$this->spineCaseNamespace = $spineCaseNamespace;
	}

	public function getOption( $optionName) {
		return get_option($this->snakeCaseNamespace . '_' . $optionName);
	}

	public function deleteOption( $optionName) {
		return delete_option($this->snakeCaseNamespace . '_' . $optionName);
	}

	public function updateOption( $optionName, $optioValue) {
		return update_option($this->snakeCaseNamespace . '_' . $optionName, $optioValue);
	}

	public function registerSetting( $settingName) {
		return register_setting( $this->snakeCaseNamespace, $this->snakeCaseNamespace . '_' . $settingName );
	}

	public function addTab( $tabName, $tabTitle, $showSaveButton = true, $inactive = false) {
		$this->tabs[$tabName] = [
			'name' => $tabName,
			'title' => $tabTitle,
			'show_save_button' => $showSaveButton,
			'inactive' => $inactive
		];
	}

	public function addSettingsSection( $sectionName, $sectionTitle, $description, $tab, $extra = null): void {
		$this->sections[$sectionName] = [
			'name' => $sectionName,
			'tab' => $tab
		];
		$args = [
			'before_section' => '',
			'after_section' => '',
		];

		$grid = isset($extra['grid']) ? $extra['grid'] : null;
		$badge = isset($extra['badge']) ? $extra['badge'] : null;

		if ( 'start' === $grid || 'single' === $grid ) {
			$args['before_section'] = '<div class="metabox-holder"><div class="postbox-container" style="float: none; display: flex; flex-wrap:wrap;">';
		}
		if ( null !== $grid ) {
			$args['before_section'] .= '<div style="margin-left: 4%; width: 45%" class="postbox"><div class="inside">';
			$args['after_section'] = '</div></div>';
		}

		if ( 'end' === $grid || 'single' === $grid ) {
			$args['after_section'] .= '</div></div><br />';
		}

		$title = __( $sectionTitle, $this->spineCaseNamespace );
		if ($badge) {
			$title .= ' <code>' . strtoupper($badge) . '</code>';
		}

		add_settings_section(
			$this->snakeCaseNamespace . '_' . $sectionName,
			$title,
			static function( $args) use ( $description, $grid ) {
				?>
			  <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php echo wp_kses($description, SanitizationUtil::WP_KSES_ALLOWED_HTML, SanitizationUtil::WP_KSES_ALLOWED_PROTOCOLS); ?></p>
			  <?php
			},
			$this->snakeCaseNamespace . '_' . $tab,
			$args
		);
	}

	public function addSettingsField( $fieldName, $fieldTitle, $fieldCallback, $fieldSection, $fieldDescription = '', $extraAttrs = []) {
		$attrs = array_merge([
			'label_for'   => $this->snakeCaseNamespace . '_' . $fieldName,
			'description' => $fieldDescription,
		], $extraAttrs);
		$section = $this->sections[$fieldSection];
		register_setting( $this->snakeCaseNamespace . '_' . $section['tab'], $this->snakeCaseNamespace . '_' . $fieldName );
		add_settings_field(
			$this->snakeCaseNamespace . '_' . $fieldName, // As of WP 4.6 this value is used only internally.
			// Use $args' label_for to populate the id inside the callback.
			__( $fieldTitle, $this->spineCaseNamespace ),
			$fieldCallback,
			$this->snakeCaseNamespace . '_' . $section['tab'],
			$this->snakeCaseNamespace . '_' . $fieldSection,
			$attrs
		);
	}

	public function addSubmenuPage( $options, $title1, $title2, $capabilities) {
		$snakeCaseNamespace = $this->snakeCaseNamespace;
		$spineCaseNamespace = $this->spineCaseNamespace;
		$activeTab = isset( $_GET[ 'tab' ] ) ? sanitize_key($_GET[ 'tab' ]) : array_keys($this->tabs)[0];
		add_submenu_page(
			$options,
			$title1,
			$title2,
			$capabilities,
			$this->spineCaseNamespace,
			function() use ( $capabilities, $snakeCaseNamespace, $spineCaseNamespace, $activeTab) {
				// check user capabilities
				if ( ! current_user_can( $capabilities ) ) {
					return;
				}
				// show error/update messages
				settings_errors( $snakeCaseNamespace . '_messages' );
				?>
			  <div class="wrap">
				<div id="icon-themes" class="icon32"></div>
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

				<h2 class="nav-tab-wrapper">
					<?php foreach ($this->tabs as $tab) : ?>
					<?php
						$link = sprintf('?page=%s&tab=%s', $this->spineCaseNamespace, $tab['name']);
						if (true === @$tab['inactive']) {
							$link = '#';
						}
						?>
					<a
						href="<?php echo esc_url($link); ?>"
						class="nav-tab
						<?php if ($activeTab === $tab['name']) : ?>
							nav-tab-active
						<?php endif; ?>
					"><?php echo wp_kses($tab['title'], SanitizationUtil::WP_KSES_ALLOWED_HTML, SanitizationUtil::WP_KSES_ALLOWED_PROTOCOLS); ?></a>
					<?php endforeach; ?>
				</h2>

				<form action="options.php" method="post">
				  <?php
					// output security fields for the registered setting "wporg_options"
					settings_fields( $snakeCaseNamespace . '_' . $activeTab );
					// output setting sections and their fields
					// (sections are registered for "wporg", each field is registered to a specific section)
					do_settings_sections( $snakeCaseNamespace . '_' . $activeTab );
					// output save settings button
					if (false !== $this->tabs[$activeTab]['show_save_button']) {
						submit_button( __( 'Save Settings', $spineCaseNamespace ) );
					}
					?>
				</form>
			  </div>
				<?php
			}
		);
	}

	public function getSnakeCaseNamespace() {
		return $this->snakeCaseNamespace;
	}
}
