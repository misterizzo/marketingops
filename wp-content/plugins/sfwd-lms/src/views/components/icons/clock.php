<?php
/**
 * View: Clock Icon.
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

$svg_classes = [ 'ld-svgicon__clock' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}

if ( empty( $label ) ) {
	$label = __( 'Clock icon', 'learndash' );
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

<path fill-rule="evenodd" clip-rule="evenodd" d="M12 5.96918C8.56356 5.96918 5.77778 8.75497 5.77778 12.1914C5.77778 15.6278 8.56356 18.4136 12 18.4136C15.4364 18.4136 18.2222 15.6278 18.2222 12.1914C18.2222 8.75497 15.4364 5.96918 12 5.96918ZM4 12.1914C4 7.77313 7.58172 4.19141 12 4.19141C16.4183 4.19141 20 7.77313 20 12.1914C20 16.6097 16.4183 20.1914 12 20.1914C7.58172 20.1914 4 16.6097 4 12.1914Z" />
<path fill-rule="evenodd" clip-rule="evenodd" d="M12 7.03585C12.4909 7.03585 12.8889 7.43382 12.8889 7.92474V11.642L15.242 12.8186C15.6811 13.0381 15.859 13.5721 15.6395 14.0112C15.4199 14.4502 14.886 14.6282 14.4469 14.4087L11.6025 12.9865C11.3013 12.8359 11.1111 12.5281 11.1111 12.1914V7.92474C11.1111 7.43382 11.5091 7.03585 12 7.03585Z" />

<?php
$this->template( 'components/icons/icon/end' );
