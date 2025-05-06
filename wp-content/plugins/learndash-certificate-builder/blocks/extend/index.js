/**
 * WordPress Dependencies
 */
import {InspectorControls} from '@wordpress/block-editor';
import {__} from '@wordpress/i18n';

const {addFilter} = wp.hooks;
import {BlockEdit} from "@wordpress/block-editor";
import {BlockControls, PanelColorSettings} from '@wordpress/block-editor';
import {
	ToolbarGroup,
	ToolbarButton,
	FontSizePicker,
	PanelBody,
	ToggleControl,
	SelectControl
} from '@wordpress/components';

const {Fragment} = wp.element;
const {createHigherOrderComponent} = wp.compose;
import {alignCenter, alignLeft, alignRight, formatBold, formatItalic, textColor} from '@wordpress/icons';
import * as Helper from "../helper/font";
import domReady from '@wordpress/dom-ready';

let publishSidebarEnabled = false;
domReady(function () {
	wp.plugins.unregisterPlugin('block-directory');
	wp.plugins.unregisterPlugin('edit-post');
	wp.data.dispatch('core/edit-post').removeEditorPanel('post-excerpt');
	wp.data.dispatch('core/edit-post').removeEditorPanel('post-link');
	wp.data.dispatch('core/edit-post').removeEditorPanel('post-status');
	wp.data.dispatch('core/edit-post').removeEditorPanel('featured-image');
	if (wp.data.select('core/editor').isPublishSidebarEnabled()) {
		publishSidebarEnabled = true;
		wp.data.dispatch('core/editor').disablePublishSidebar()
	}
});

window.onbeforeunload = function () {
	if (publishSidebarEnabled === true) {
		wp.data.dispatch('core/editor').enablePublishSidebar()
	}
}

/**
 * Going to extend the font, align, and format
 * @param settings
 * @param name
 * @returns {*}
 */
function addAttributes(settings, name) {
	let ALLOWED_BLOCKS = ['learndash/ld-usermeta', 'learndash/ld-courseinfo', 'learndash/ld-groupinfo', 'learndash/ld-quizinfo', 'core/heading'];
	if (ALLOWED_BLOCKS.indexOf(name) > -1) {
		let attributes = {
			font: {
				type: 'string',
				default: '',
			},
			useFont: {
				type: 'boolean',
				default: false
			},
		}
		if ('core/heading' !== name) {
			attributes = Object.assign(attributes, {
				fontSize: {
					type: 'string',
					default: "20px"
				},
				textAlign: {
					type: 'string',
					default: ''
				},
				fontStyle: {
					type: 'string',
					default: ''
				},
				fontWeight: {
					type: 'string',
					default: ''
				},
				textTransform: {
					type: 'string',
					default: ''
				},
				textColor: {
					type: 'string'
				},
				backgroundColor: {
					type: 'string'
				},
			})
		}
		settings.attributes = Object.assign(settings.attributes, attributes);
	}
	return settings;
}

const withBlockControls = createHigherOrderComponent(BlockEdit => {
	return (props) => {
		let ALLOWED_BLOCKS = ['learndash/ld-usermeta', 'learndash/ld-courseinfo', 'learndash/ld-groupinfo', 'learndash/ld-quizinfo'];
		let ALLOWED_CORE_BLOCKS = ['core/heading'];
		let style = '';
		let isCore = false;

		if (ALLOWED_CORE_BLOCKS.indexOf(props.name) > -1 || ALLOWED_BLOCKS.indexOf(props.name) > -1) {
			const {attributes, setAttributes, className} = props;
			if (ALLOWED_CORE_BLOCKS.indexOf(props.name) > -1) {
				isCore = true;
			}
			if (isCore && attributes.useFont) {
				style = `font-family: ${attributes.font};`;
			} else {
				style = `
            font-style: ${attributes.fontStyle};
            font-weight: ${attributes.fontWeight};
            font-size: ${attributes.fontSize};
            text-transform: ${attributes.textTransform};
            `;
				if (attributes.useFont) {
					style += `font-family: ${attributes.font};`;
				}
			}
			if (attributes.textAlign !== 'left') {
				style += ` text-align: ${attributes.textAlign};`
			}
			if (attributes.textColor) {
				style += `color: ${attributes.textColor};`
			}
			if (attributes.backgroundColor) {
				style += `background-color: ${attributes.backgroundColor};`
			}

			let info = Helper.getActiveFont(Object.entries(certificate_builder.fonts), attributes.font);
			let id = 'block-' + props.clientId;
			style = `#${id}, #${id} *
            {
                ${style}
            }`;
			if (attributes.font === '' || attributes.font === null) {
				setTimeout(function () {
					setAttributes({
						font: info.currFont.key
					})
				})
			}
			let toggleLabel = __('Use custom font', 'learndash-certificate-builder');
			const MyToggleControl = (
				<ToggleControl
					label={toggleLabel}
					checked={attributes.useFont}
					onChange={(value) => {
						setAttributes({
							useFont: value
						})
					}}
				/>
			)
			return (
				<Fragment>
					<style>{style}</style>
					<BlockEdit {...props}/>
					<InspectorControls>
						{
							isCore === false && (
								<>
									<PanelBody title={__("Font size (px)", 'learndash-certificate-builder')}>
										<FontSizePicker
											fontSizes={[]}
											value={attributes.fontSize}
											fallbackFontSize={20}
											onChange={(newFontSize) => {
												setAttributes({
													fontSize: newFontSize
												})
											}}
										/>
									</PanelBody>
									<PanelColorSettings
										title={__('Color Settings')}
										colorSettings={
											[
												{
													colors: certificate_builder.colors[0], // here you can pass custom colors
													value: attributes.textColor,
													label: __('Text color', 'learndash-certificate-builder'),
													onChange: (color) => {
														setAttributes({
															textColor: color
														})
													}
												},
												{
													colors: certificate_builder.colors[0], // here you can pass custom colors
													value: attributes.backgroundColor,
													label: __('Background color', 'learndash-certificate-builder'),
													onChange: (color) => {
														setAttributes({
															backgroundColor: color
														})
													}
												},
											]
										}
									>

									</PanelColorSettings>
								</>
							)
						}
						<PanelBody title={__('Fonts', 'learndash-certificate-builder')}>
							{MyToggleControl}
							{attributes.useFont && (
								<SelectControl label={__("Font family", 'learndash-certificate-builder')}
											   value={attributes.font} options={info.list}
											   onChange={(o) => {
												   setAttributes({
													   font: o
												   })
											   }}/>
							)}
						</PanelBody>
					</InspectorControls>
					<BlockControls>
						{
							isCore === false && (
								<>
									<ToolbarGroup>
										<ToolbarButton
											icon={alignLeft}
											label={__("Left", 'learndash-certificate-builder')}
											isPressed={attributes.textAlign === 'left'}
											onClick={() => setAttributes({
												textAlign: 'left'
											})}
										/>
										<ToolbarButton
											icon={alignCenter}
											label={__("Center", 'learndash-certificate-builder')}
											isPressed={attributes.textAlign === 'center'}
											onClick={() => setAttributes({
												textAlign: 'center'
											})}
										/>
										<ToolbarButton
											icon={alignRight}
											label={__("Right", 'learndash-certificate-builder')}
											isPressed={attributes.textAlign === 'right'}
											onClick={() => setAttributes({
												textAlign: 'right'
											})}
										/>
									</ToolbarGroup>
									<ToolbarGroup>
										<ToolbarButton
											icon={formatBold}
											label={__("Bold", 'learndash-certificate-builder')}
											isPressed={attributes.fontWeight === 'bold'}
											onClick={() => {
												if (attributes.fontWeight === 'bold') {
													setAttributes({
														fontWeight: ''
													})
												} else {
													setAttributes({
														fontWeight: 'bold'
													})
												}
											}}
										/>
										<ToolbarButton
											icon={formatItalic}
											label={__("Italic", 'learndash-certificate-builder')}
											isPressed={attributes.fontStyle === 'italic'}
											onClick={() => {
												if (attributes.fontStyle === 'italic') {
													setAttributes({
														fontStyle: ''
													})
												} else {
													setAttributes({
														fontStyle: 'italic'
													})
												}
											}}
										/>
									</ToolbarGroup>
									<ToolbarGroup>
										<ToolbarButton
											icon={textColor}
											label={__("Uppercase", 'learndash-certificate-builder')}
											isPressed={attributes.textTransform === 'uppercase'}
											onClick={() => {
												if (attributes.textTransform === 'uppercase') {
													setAttributes({
														textTransform: ''
													})
												} else {
													setAttributes({
														textTransform: 'uppercase'
													})
												}
											}}
										/>
									</ToolbarGroup>
								</>
							)
						}
					</BlockControls>
				</Fragment>)
		}
		return (<Fragment>
			<BlockEdit {...props} />
		</Fragment>)
	}
}, "withBlockControls")
addFilter(
	'blocks.registerBlockType',
	'leanrdash/learndash-certificate-builder/custom-attributes',
	addAttributes
);

addFilter('editor.BlockEdit',
	'leanrdash/learndash-certificate-builder/custom-block-controls',
	withBlockControls
);
