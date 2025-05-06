/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/#registering-a-block
 */
import {registerBlockType} from '@wordpress/blocks';

/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import {__} from '@wordpress/i18n';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './style.scss';

/**
 * Internal dependencies
 */
import Edit from './edit';
import save from './save';

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/#registering-a-block
 */
registerBlockType('learndash/ld-certificate-builder', {
	/**
	 * This is the display title for your block, which can be translated with `i18n` functions.
	 * The block inserter will show this name.
	 */
	title: __('LearnDash Certificate Builder', 'learndash-certificate-builder'),

	/**
	 * This is a short description for your block, can be translated with `i18n` functions.
	 * It will be shown in the Block Tab in the Settings Sidebar.
	 */
	description: __(
		'LearnDash certificate builder',
		'learnDash-certificate-builder'
	),

	/**
	 * Blocks are grouped into categories to help users browse and discover them.
	 * The categories provided by core are `common`, `embed`, `formatting`, `layout` and `widgets`.
	 */
	category: 'learndash-blocks',

	/**
	 * An icon property should be specified to make it easier to identify a block.
	 * These can be any of WordPress’ Dashicons, or a custom svg element.
	 */
	icon: 'welcome-learn-more',

	/**
	 * Optional block extended support features.
	 */
	supports: {
		// Removes support for an HTML mode.
		html: false,
		align: ['full']
	},
	multiple: false,
	attributes: {
		id: {
			type: 'int',
			default: 0
		},
		post_id: {
			type: 'int',
			default: 0
		},
		backgroundImage: {
			type: 'string',
			default: ''
		},
		font: {
			type: 'string',
			default: ''
		},
		useFont: {
			type: 'boolean',
			default: false
		},
		pageSize: {
			type: 'string',
			default: 'LETTER',
		},
		pageOrientation: {
			type: 'string',
			default: 'L'
		},
		align: {
			type: 'string',
			default: 'full'
		},
		pageHeight: {
			type: 'int',
			default: 0
		},
		pageWidth: {
			type: 'int',
			default: 0
		},
		containerWidth: {
			type: 'int',
			default: 70
		},
		spacing: {
			type: 'number',
			default: 1
		},
		rtl: {
			type: 'boolean',
			default: false
		},
		viewPort: {
			type: 'boolean',
			default: true
		}
	},
	/**
	 * @see ./edit.js
	 */
	edit: Edit,

	/**
	 * @see ./save.js
	 */
	save: save,
});
