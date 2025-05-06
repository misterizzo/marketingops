/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import {__} from '@wordpress/i18n';
/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-block-editor/#useBlockProps
 */
import {
	InnerBlocks,
	InspectorControls,
	BlockControls,
	MediaPlaceholder,
	MediaReplaceFlow,
} from '@wordpress/block-editor';
import {SelectControl, PanelBody, ToggleControl, RangeControl} from '@wordpress/components'
import * as Helper from '../helper/font'
/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @return {JSX.Element} Element to render.
 */
export default function Edit(props) {
	const {attributes, setAttributes, className} = props;

	const ALLOWED_BLOCKS = [
		'learndash/ld-courseinfo',
		'learndash/ld-usermeta',
		'learndash/ld-groupinfo',
	];

	return (
		<div className={"ld-certificate-builder-row"}>
			<InnerBlocks templateLock={false} allowedBlocks={ALLOWED_BLOCKS}/>
		</div>
	)
}
