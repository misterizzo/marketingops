<?php
/**
 * View: Lesson complete icon.
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

$svg_classes = [ 'ld-svgicon__lesson-complete' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}

if ( empty( $label ) ) {
	$label = sprintf(
		// translators: %s: Lesson label.
		__( '%s complete icon', 'learndash' ),
		learndash_get_custom_label( 'lesson' )
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

<path fill-rule="evenodd" clip-rule="evenodd" d="M11.2538 8.21051H9.37524C8.89199 8.21051 8.50024 8.58753 8.50024 9.05262C8.50024 9.5177 8.89199 9.89472 9.37524 9.89472H11.9709C11.6623 9.37303 11.4194 8.80782 11.2538 8.21051ZM13.3619 11.5789H9.37524C8.89199 11.5789 8.50024 11.956 8.50024 12.421C8.50024 12.8861 8.89199 13.2631 9.37524 13.2631H14.6252C14.9531 13.2631 15.2389 13.0896 15.3888 12.8327C14.6404 12.5316 13.9561 12.105 13.3619 11.5789ZM17.25 13.2962V18.3158H6.75L6.75 5.68421H11.0299C11.084 5.09895 11.2101 4.53456 11.3992 4H6.75C6.28587 4 5.84075 4.17744 5.51256 4.49329C5.18438 4.80914 5 5.23753 5 5.68421V18.3158C5 18.7625 5.18437 19.1909 5.51256 19.5067C5.84075 19.8226 6.28587 20 6.75 20H17.25C17.7141 20 18.1592 19.8226 18.4874 19.5067C18.8156 19.1909 19 18.7625 19 18.3158V13.265C18.6734 13.3118 18.3395 13.3359 18 13.3359C17.7466 13.3359 17.4964 13.3225 17.25 13.2962ZM8.50024 15.7895C8.50024 15.3244 8.89199 14.9474 9.37524 14.9474H14.6252C15.1085 14.9474 15.5002 15.3244 15.5002 15.7895C15.5002 16.2545 15.1085 16.6316 14.6252 16.6316L9.37524 16.6316C8.89199 16.6316 8.50024 16.2545 8.50024 15.7895Z" fill="currentColor"/>
<path fill-rule="evenodd" clip-rule="evenodd" d="M18 0.335938C14.6863 0.335938 12 3.02223 12 6.33594C12 9.64965 14.6863 12.3359 18 12.3359C21.3137 12.3359 24 9.64965 24 6.33594C24 3.02223 21.3137 0.335938 18 0.335938ZM20.0697 4.24562C20.3607 3.94938 20.8345 3.94938 21.1255 4.24562C21.4141 4.53933 21.4141 5.01367 21.1255 5.30738L17.5526 8.94411C17.2616 9.24035 16.7878 9.24035 16.4967 8.94411L14.8727 7.29105C14.5841 6.99734 14.5841 6.523 14.8727 6.22929C15.1637 5.93305 15.6375 5.93305 15.9286 6.22929L17.0247 7.34498L20.0697 4.24562Z" fill="currentColor"/>

<?php
$this->template( 'components/icons/icon/end' );
