<?php
/**
 * View: Right caret icon.
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

$svg_classes = [ 'ld-svgicon__right-caret' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}

if ( empty( $label ) ) {
	$label = __( 'Right caret icon', 'learndash' );
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

<path d="M2.01367 13.198C1.82275 13.3889 1.58826 13.4844 1.31018 13.4844C1.0321 13.4844 0.797607 13.3889 0.606689 13.198C0.498779 13.1067 0.424072 12.9988 0.382568 12.8743C0.341064 12.7498 0.320312 12.6252 0.320312 12.5007C0.320312 12.3679 0.34314 12.2434 0.388794 12.1272C0.434448 12.011 0.50708 11.9031 0.606689 11.8035L5.22607 7.18408L0.606689 2.52734C0.415771 2.33643 0.320312 2.10193 0.320312 1.82385C0.320312 1.54578 0.415771 1.31128 0.606689 1.12036C0.797607 0.929443 1.0321 0.833984 1.31018 0.833984C1.58826 0.833984 1.82275 0.929443 2.01367 1.12036L7.35522 6.47437C7.44653 6.56567 7.51709 6.67151 7.56689 6.79187C7.6167 6.91223 7.6416 7.03882 7.6416 7.17163C7.6416 7.29614 7.61462 7.42273 7.56067 7.55139C7.50671 7.68005 7.43823 7.78589 7.35522 7.8689L2.01367 13.198Z" fill="currentColor"/>

<?php
$this->template( 'components/icons/icon/end' );
