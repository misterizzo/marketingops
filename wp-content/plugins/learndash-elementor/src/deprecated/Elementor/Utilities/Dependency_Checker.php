<?php
/**
 * Deprecated plugin dependency checker class file.
 *
 * @deprecated 1.0.9
 *
 * @package LearnDash\Elementor\Deprecated
 */

namespace LearnDash\Elementor\Utilities;

_deprecated_file(
	__FILE__,
	'1.0.9',
	esc_html( LEARNDASH_ELEMENTOR_PLUGIN_DIR . 'src/App/Dependency_Checker.php' )
);

/**
 * Plugin dependency checker class.
 *
 * @since 1.0.5
 * @deprecated 1.0.9
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
class Dependency_Checker extends \LearnDash\Elementor\Dependency_Checker {}
