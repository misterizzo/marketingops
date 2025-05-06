<?php
/**
 * View: Quiz icon.
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

$svg_classes = [ 'ld-svgicon__quiz' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}

if ( empty( $label ) ) {
	$label = sprintf(
		// translators: %s: Quiz label.
		__( '%s icon', 'learndash' ),
		learndash_get_custom_label( 'quiz' )
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

<path fill-rule="evenodd" clip-rule="evenodd" d="M18.9914 18.555C18.9517 18.9213 18.7746 19.2657 18.4874 19.5282C18.1592 19.8283 17.7141 19.9969 17.25 19.9969H6.75C6.28587 19.9969 5.84075 19.8283 5.51256 19.5282C5.18437 19.2282 5 18.8212 5 18.3969V6.39692C5 5.97258 5.18437 5.56556 5.51256 5.2655C5.84075 4.96544 6.28587 4.79687 6.75 4.79688L17.25 4.79688C17.7141 4.79688 18.1592 4.96545 18.4874 5.2655C18.7655 5.5197 18.9403 5.85063 18.9872 6.20385C18.9957 6.26757 19 6.33202 19 6.39687L19 12.3969V18.3968M6.75 6.39692C6.75332 6.39692 6.75663 6.3969 6.75994 6.39687L17.2402 6.39687C17.2434 6.3969 17.2467 6.39692 17.25 6.39692C17.25 6.39692 17.25 10.0537 17.25 12.3969C17.25 14.74 17.25 18.3968 17.25 18.3968C17.2467 18.3968 17.2434 18.3968 17.2402 18.3969H6.75994C6.75663 18.3968 6.75332 18.3968 6.75 18.3968L6.75 12.7968L6.75 6.39692ZM19 18.3968C19 18.4499 18.9971 18.5027 18.9914 18.555Z" fill="currentColor"/>
<path fill-rule="evenodd" clip-rule="evenodd" d="M14.5683 7.95616C14.3151 7.68128 13.8954 7.68128 13.6423 7.95616L11.2105 10.5963L10.3577 9.67044C10.1046 9.39557 9.6849 9.39557 9.43173 9.67044C9.18942 9.93352 9.18942 10.3522 9.43173 10.6153L10.7475 12.0438C11.0007 12.3187 11.4204 12.3187 11.6735 12.0438L14.5683 8.90099C14.8106 8.63791 14.8106 8.21923 14.5683 7.95616Z" fill="currentColor"/>
<path fill-rule="evenodd" clip-rule="evenodd" d="M14.5683 12.9562C14.3151 12.6813 13.8954 12.6813 13.6423 12.9562L11.2105 15.5963L10.3577 14.6704C10.1046 14.3956 9.6849 14.3956 9.43173 14.6704C9.18942 14.9335 9.18942 15.3522 9.43173 15.6153L10.7475 17.0438C11.0007 17.3187 11.4204 17.3187 11.6735 17.0438L14.5683 13.901C14.8106 13.6379 14.8106 13.2192 14.5683 12.9562Z" fill="currentColor"/>

<?php
$this->template( 'components/icons/icon/end' );
