<?php
/**
 * View: Left caret icon.
 *
 * @since 4.21.0
 * @version 4.21.0
 *
 * @var string[] $classes        Additional classes to add to the svg icon.
 * @var string   $label          The label for the icon.
 * @var bool     $is_aria_hidden Whether the icon is hidden from screen readers. Default false to show the icon.
 * @var Template $this           The template instance.
 *
 * @package LearnDash\Core
 */

use LearnDash\Core\Template\Template;

$svg_classes = [ 'ld-svgicon__left-caret' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}

if ( empty( $label ) ) {
	$label = __( 'Left caret icon', 'learndash' );
}

$this->template(
	'components/icons/icon/start',
	[
		'classes' => $svg_classes,
		'height'  => 14,
		'label'   => $label,
		'width'   => 8,
	],
);

?>

<path d="M5.62695 13.198C5.81787 13.3889 6.05237 13.4844 6.33044 13.4844C6.60852 13.4844 6.84302 13.3889 7.03394 13.198C7.14185 13.1067 7.21655 12.9988 7.25806 12.8743C7.29956 12.7498 7.32031 12.6252 7.32031 12.5007C7.32031 12.3679 7.29749 12.2434 7.25183 12.1272C7.20618 12.011 7.13354 11.9031 7.03394 11.8035L2.41455 7.18408L7.03394 2.52734C7.22485 2.33643 7.32031 2.10193 7.32031 1.82385C7.32031 1.54578 7.22485 1.31128 7.03394 1.12036C6.84302 0.929443 6.60852 0.833984 6.33044 0.833984C6.05237 0.833984 5.81787 0.929443 5.62695 1.12036L0.2854 6.47437C0.194092 6.56567 0.123535 6.67151 0.0737305 6.79187C0.0239258 6.91223 -0.000976562 7.03882 -0.000976562 7.17163C-0.000976562 7.29614 0.026001 7.42273 0.0799561 7.55139C0.133911 7.68005 0.202393 7.78589 0.2854 7.8689L5.62695 13.198Z" fill="currentColor"/>

<?php
$this->template( 'components/icons/icon/end' );
