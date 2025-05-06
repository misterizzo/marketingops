<?php
/**
 * View: Quiz complete icon.
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

$svg_classes = [ 'ld-svgicon__quiz-complete' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}

if ( empty( $label ) ) {
	$label = sprintf(
		// translators: %s: Quiz label.
		__( '%s complete icon', 'learndash' ),
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

<path fill-rule="evenodd" clip-rule="evenodd" d="M14.5683 12.9562C14.3151 12.6813 13.8954 12.6813 13.6423 12.9562L11.2105 15.5963L10.3577 14.6704C10.1046 14.3956 9.6849 14.3956 9.43173 14.6704C9.18942 14.9335 9.18942 15.3522 9.43173 15.6153L10.7475 17.0438C11.0007 17.3187 11.4204 17.3187 11.6735 17.0438L14.5683 13.901C14.8106 13.6379 14.8106 13.2192 14.5683 12.9562Z" fill="currentColor"/>
<path fill-rule="evenodd" clip-rule="evenodd" d="M11.169 4.80469C11.0584 5.29894 11 5.81294 11 6.34058C11 6.36114 11.0001 6.38169 11.0003 6.40221L11 6.40468L6.75992 6.40468L6.75 6.40473L6.75 13.2618L6.75 18.4046L6.75994 18.4047H17.2402L17.25 18.4046V13.3009C17.4964 13.3271 17.7466 13.3406 18 13.3406C18.3395 13.3406 18.6734 13.3164 19 13.2697L19 18.4046C19 18.4577 18.9971 18.5105 18.9914 18.5628C18.9517 18.9291 18.7746 19.2735 18.4874 19.5361C18.1593 19.8361 17.7141 20.0047 17.25 20.0047H6.75C6.28587 20.0047 5.84075 19.8361 5.51256 19.5361C5.18437 19.236 5 18.829 5 18.4047V6.40473C5 5.98039 5.18437 5.57337 5.51256 5.27331C5.84075 4.97326 6.28588 4.80469 6.75 4.80469H11.169Z" fill="currentColor"/>
<path fill-rule="evenodd" clip-rule="evenodd" d="M11.9266 9.82331L11.2105 10.6008L10.3577 9.67491C10.1046 9.40003 9.6849 9.40003 9.43173 9.67491C9.18942 9.93798 9.18942 10.3567 9.43173 10.6197L10.7475 12.0483C11.0007 12.3232 11.4204 12.3232 11.6735 12.0483L12.7092 10.9239C12.4145 10.5841 12.1521 10.2157 11.9266 9.82331Z" fill="currentColor"/>
<path fill-rule="evenodd" clip-rule="evenodd" d="M18 0.335938C14.6863 0.335938 12 3.02223 12 6.33594C12 9.64965 14.6863 12.3359 18 12.3359C21.3137 12.3359 24 9.64965 24 6.33594C24 3.02223 21.3137 0.335938 18 0.335938ZM20.0697 4.24562C20.3607 3.94938 20.8345 3.94938 21.1255 4.24562C21.4141 4.53933 21.4141 5.01367 21.1255 5.30738L17.5526 8.94411C17.2616 9.24035 16.7878 9.24035 16.4967 8.94411L14.8727 7.29105C14.5841 6.99734 14.5841 6.523 14.8727 6.22929C15.1637 5.93305 15.6375 5.93305 15.9286 6.22929L17.0247 7.34498L20.0697 4.24562Z" fill="currentColor"/>

<?php
$this->template( 'components/icons/icon/end' );
