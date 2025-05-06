<?php
/**
 * View: Course icon.
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

$svg_classes = [ 'ld-svgicon__course' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}

if ( empty( $label ) ) {
	$label = sprintf(
		// translators: %s: Course label.
		__( '%s icon', 'learndash' ),
		learndash_get_custom_label( 'course' )
	);
}

$this->template(
	'components/icons/icon/start',
	[
		'classes' => $svg_classes,
		'height'  => 24,
		'label'   => $label,
		'width'   => 24,
	],
);
?>

<path fill-rule="evenodd" clip-rule="evenodd" d="M4.72724 5C4.3256 5 4 5.3134 4 5.7V16.2C4 16.5866 4.3256 16.9 4.72724 16.9H9.81792C10.2037 16.9 10.5736 17.0475 10.8464 17.3101C11.1192 17.5726 11.2724 17.9287 11.2724 18.3C11.2724 18.6866 11.598 19 11.9996 19C12.4013 19 12.7276 18.6866 12.7276 18.3C12.7276 17.9287 12.8808 17.5726 13.1536 17.3101C13.4264 17.0475 13.7963 16.9 14.1821 16.9H19.2728C19.6744 16.9 20 16.5866 20 16.2V5.7C20 5.3134 19.6744 5 19.2728 5H14.9093C13.9449 5 13.0201 5.36875 12.3381 6.02513C12.2156 6.14306 12.1027 6.26854 12 6.40043C11.8973 6.26854 11.7844 6.14306 11.6619 6.02513C10.9799 5.36875 10.0551 5 9.09068 5H4.72724ZM11.2724 8.5V15.8751C10.8339 15.6315 10.333 15.5 9.81792 15.5H5.45448V6.4H9.09068C9.66931 6.4 10.2242 6.62125 10.6334 7.01508C11.0425 7.4089 11.2724 7.94305 11.2724 8.5ZM14.1821 15.5C13.667 15.5 13.1661 15.6315 12.7276 15.8751V8.5C12.7276 7.94305 12.9575 7.4089 13.3666 7.01508C13.7758 6.62125 14.3307 6.4 14.9093 6.4H18.5455V15.5H14.1821Z" fill="currentColor"/>

<?php
$this->template( 'components/icons/icon/end' );
