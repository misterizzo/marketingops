/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 *
 * cspell:ignore freeserif .
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
	let {
		id = 0,
		backgroundImage = '',
		pageSize = 'LETTER',
		pageOrientation = 'L',
		font = '',
		useFont = false,
		pageHeight = 0,
		pageWidth = 0,
		containerWidth = 70,
		spacing = 1,
		rtl = false,
		viewPort = true
	} = attributes;

	const hasBackground = id !== 0
	//update post id
	const onSelectMedia = (imageObject) => {
		if (
			imageObject.type !== 'image'
			&& imageObject.type !== 'attachment'
		) {
			return;
		}

		setAttributes({
			id: imageObject.id,
			backgroundImage: imageObject.url,
		});

		setTimeout(function () {
			let height = jQuery('#certificate-builder-blocks').height();
			let width = jQuery('#certificate-builder-blocks').width();
			setAttributes({
				pageHeight: height,
				pageWidth: width
			});
		})
	}

	const ALLOWED_MEDIA_TYPES = ['image'];
	const PAGE_SIZES = [
		{label: __('Letter/USLetter (default)', 'learndash-certificate-builder'), value: 'LETTER'},
		{label: __('A4', 'learndash-certificate-builder'), value: 'A4'}
	];
	const PAGE_ORIENTATION = [
		{label: __('Landscape (default)', 'learndash-certificate-builder'), value: 'L'},
		{label: __('Portrait', 'learndash-certificate-builder'), value: 'P'}
	];
	let styles = {};
	if ('' !== backgroundImage) {
		styles.position = 'relative'
	}

	let useCoreFont = () => {
		return `#certificate-builder-inner-blocks, #certificate-builder-inner-blocks * {
		font-family: "freeserif", serif;
	}`
	}

	let info = Helper.getActiveFont(Object.entries(certificate_builder.fonts), font);
	if (font === '' || font === null) {
		setAttributes({
			font: info.currFont.key
		})
	}
	let fontDefine = Helper.buildFontStyle(certificate_builder.font_url, certificate_builder.fonts)
	let fontStyle = '';
	if (useFont === false) {
		fontStyle = useCoreFont();
	} else {
		fontStyle = `
	#certificate-builder-inner-blocks, #certificate-builder-inner-blocks * {
		font-family: "${info.currFont.key}", "freeserif", serif;
	}}
	`
	}

	const MyToggleControl = (
		<ToggleControl
			label={__("Use custom font", 'learndash-certificate-builder')}
			checked={useFont}
			onChange={(value) => {
				setAttributes({
					useFont: value
				})
			}}
		/>
	)
	const controls = (
		<>
			<BlockControls>
				{
					hasBackground && (
						<>
							<MediaReplaceFlow
								mediaId={id}
								mediaURL={backgroundImage}
								allowedTypes={ALLOWED_MEDIA_TYPES}
								accept="image/jpeg, image/png"
								onSelect={onSelectMedia}
							/>
						</>
					)
				}
			</BlockControls>
			<InspectorControls>
				{
					hasBackground && (
						<>
							<PanelBody title={__('Spacing', 'learndash-certificate-builder')}>
								<RangeControl label={__("Container size (%)", 'learndash-certificate-builder')}
											  value={containerWidth}
											  min={50}
											  max={100}
											  onChange={(newSize) => {
												  setAttributes({
													  containerWidth: newSize
												  })
											  }}
								/>
								<RangeControl label={__("Margin bottom (rem)", 'learndash-certificate-builder')}
											  value={spacing}
											  min={0}
											  max={10}
											  step={0.1}
											  onChange={(value) => {
												  setAttributes({
													  spacing: value
												  })
											  }}
								/>

							</PanelBody>
							<PanelBody title={__('Options', 'learndash-certificate-builder')}>
								<SelectControl label={__("PDF Page Size", 'learndash-certificate-builder')}
											   value={pageSize} options={PAGE_SIZES}
											   onChange={(size) => {
												   setAttributes({
													   pageSize: size
												   })
											   }}
								/>
								<SelectControl label={__("PDF Page Orientation", 'learndash-certificate-builder')}
											   value={pageOrientation}
											   options={PAGE_ORIENTATION}
											   onChange={(o) => {
												   setAttributes({
													   pageOrientation: o
												   })
											   }}/>
								<ToggleControl
									label={__("Fixed Viewport", 'learndash-certificate-builder')}
									checked={viewPort}
									onChange={(value) => {
										setAttributes({
											viewPort: value
										})
									}}
								/>
								<ToggleControl
									label={__("RTL", 'learndash-certificate-builder')}
									checked={rtl}
									onChange={(value) => {
										setAttributes({
											rtl: value
										})
									}}
								/>
							</PanelBody>
							<PanelBody title={__('Fonts', 'learndash-certificate-builder')}>
								{MyToggleControl}
								{useFont && (
									<SelectControl label={__("Font family", 'learndash-certificate-builder')}
												   value={font} options={info.list}
												   onChange={(o) => {
													   setAttributes({
														   font: o
													   })
												   }}/>
								)}
							</PanelBody>

						</>
					)
				}
			</InspectorControls>
		</>
	);
	if (!hasBackground) {
		return (
			<div>
				{controls}
				<div>
					<MediaPlaceholder allowedTypes={ALLOWED_MEDIA_TYPES}
									  multiple={false}
									  labels={{title: __('Certificate Background', 'learndash-certificate-builder')}}
									  onSelect={onSelectMedia}
									  accept="image/jpeg, image/png"
					/>
				</div>
			</div>
		)
	}
	let spaceStyle = `#certificate-builder-inner-blocks .wp-block:not(.wp-block-column) {
      margin-bottom: ${spacing}rem !important;
    }
    #certificate-builder-inner-blocks .wp-block:last-child {
        margin-bottom:0 !important;
    }
    `

	let sizeRatio = {
		'LETTER_L': [1056, 816],
		'LETTER_P': [816, 1056],
		'A4_L': [1122, 793],
		'A4_P': [793, 1122]
	};
	//we use full width and calculate height
	let key = pageSize + '_' + pageOrientation;
	let size = sizeRatio[key];
	let width = size[0]
	let height = size[1]
	styles.width = width + 'px';
	styles.height = height + 'px';
	if (viewPort === false) {
		styles.width = '100%';
		styles.height = 'auto';
	}
	let rtlStyle = '';
	if (rtl) {
		rtlStyle = `#certificate-builder-inner-blocks{
			direction: rtl;
		}`;

	}
	return (
		<div>
			<style>{spaceStyle}{fontDefine}{fontStyle}{rtlStyle}</style>
			{controls}
			<div id={"certificate-builder-blocks"} className={className}>
				<div style={styles}>
					<img src={backgroundImage} style={{opacity: 1, width: '100%', height: 'auto'}}/>
					<div id={'certificate-builder-inner-blocks'} style={{
						position: 'absolute',
						top: 0,
						width: '100%',
						height: 'auto',
					}}>
						<div style={{
							width: containerWidth + '%',
							margin: 'auto',
						}}>
							<InnerBlocks templateLock={false} allowedBlocks={certificate_builder.allowed_blocks}/>
						</div>
					</div>
				</div>
			</div>
		</div>
	)
}
