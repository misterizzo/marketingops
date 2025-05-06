<?php
/**
 * View: Up caret icon.
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

$svg_classes = [ 'ld-svgicon__up-caret' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}

if ( empty( $label ) ) {
	$label = __( 'Up caret icon', 'learndash' );
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

<path fill-rule="evenodd" clip-rule="evenodd" d="M19.665 17.6338C19.253 18.0844 18.6048 18.119 18.1564 17.7377L18.0488 17.6338L11.9998 11.0186L5.95073 17.6338C5.53875 18.0844 4.89049 18.119 4.44216 17.7377L4.33449 17.6338C3.92251 17.1832 3.89082 16.4741 4.23942 15.9838L4.33449 15.866L11.1916 8.36599C11.6036 7.91539 12.2519 7.88073 12.7002 8.26201L12.8079 8.36599L19.665 15.866C20.1113 16.3542 20.1113 17.1456 19.665 17.6338Z" fill="currentColor"/>

<?php
$this->template( 'components/icons/icon/end' );
