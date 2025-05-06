<?php
/**
 * View: Lock icon.
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

$svg_classes = [ 'ld-svgicon__lock' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}

if ( empty( $label ) ) {
	$label = __( 'Lock icon', 'learndash' );
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

<path fill-rule="evenodd" clip-rule="evenodd" d="M12.0799 6.00005C10.9028 6.00005 9.94865 6.95944 9.94865 8.14291V11.1429H15.9161C17.0726 11.1429 18 12.0905 18 13.2468V17.6104C18 18.7668 17.0726 19.7143 15.9161 19.7143H8.08388C6.92739 19.7143 6 18.7668 6 17.6104V13.2468C6 12.0905 6.92739 11.1429 8.08388 11.1429H8.24366V8.14291C8.24366 6.01267 9.9612 4.28577 12.0799 4.28577C14.1986 4.28577 15.9161 6.01267 15.9161 8.14291V8.64291C15.9161 9.1163 15.5344 9.50005 15.0636 9.50005C14.5928 9.50005 14.2111 9.1163 14.2111 8.64291V8.14291C14.2111 6.95944 13.2569 6.00005 12.0799 6.00005Z" fill="currentColor"/>

<?php
$this->template( 'components/icons/icon/end' );
