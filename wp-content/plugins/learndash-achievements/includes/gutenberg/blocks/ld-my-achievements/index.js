const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InspectorControls, useBlockProps } = wp.blockEditor;
const { PanelBody, SelectControl, TextControl, ToggleControl } = wp.components;

import ServerSideRender from '@wordpress/server-side-render';

registerBlockType( 'learndash/ld-my-achievements', {
	title: __( 'LearnDash My Achievements', 'learndash-achievements' ),
	description: __(
		'Display a list of current user LearnDash achievements.',
		'learndash-achievements'
	),
	icon: 'grid-view',
	category: 'learndash-blocks',
	keywords: [
		'LearnDash',
		'learndash achievements',
		'my achievements',
		'achievements',
	],
	attributes: {
		preview_show: {
			type: 'boolean',
			default: true,
		},
		show_title: {
			type: 'boolean',
			default: false,
		},
		show_points: {
			type: 'boolean',
			default: false,
		},
		points_position: {
			type: 'string',
			default: 'after',
		},
		points_label: {
			type: 'string',
			default: __( 'Points', 'learndash-achievements' ),
		},
	},

	edit( props ) {
		const { attributes, setAttributes } = props;
		// eslint-disable-next-line react-hooks/rules-of-hooks
		const blockProps = useBlockProps();

		const inspectorControls = (
			<InspectorControls>
				<PanelBody
					title={ __( 'Settings', 'learndash-achievements' ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Show Title', 'learndash-achievements' ) }
						checked={ !! props.attributes.show_title }
						// eslint-disable-next-line camelcase
						onChange={ ( show_title ) =>
							// eslint-disable-next-line camelcase -- Kept for backward compatibility.
							setAttributes( { show_title } )
						}
					/>
					<ToggleControl
						label={ __( 'Show Points', 'learndash-achievements' ) }
						checked={ !! props.attributes.show_points }
						// eslint-disable-next-line camelcase
						onChange={ ( show_points ) =>
							// eslint-disable-next-line camelcase -- Kept for backward compatibility.
							setAttributes( { show_points } )
						}
					/>
					{
						// eslint-disable-next-line camelcase
						props.attributes.show_points && (
							<>
								<TextControl
									label={ __(
										'Points Label',
										'learndash-achievements'
									) }
									type="text"
									id="points_label"
									value={ props.attributes.points_label }
									// eslint-disable-next-line camelcase -- Kept for backward compatibility.
									onChange={ ( points_label ) =>
										// eslint-disable-next-line camelcase -- Kept for backward compatibility.
										setAttributes( { points_label } )
									}
								/>
								<SelectControl
									label={ __(
										'Points Position',
										'learndash-achievements'
									) }
									value={ props.attributes.points_position }
									// eslint-disable-next-line camelcase -- Kept for backward compatibility.
									onChange={ ( points_position ) =>
										// eslint-disable-next-line camelcase -- Kept for backward compatibility.
										setAttributes( { points_position } )
									}
									options={ [
										{
											value: 'before',
											label: __(
												'Before Points',
												'learndash-achievements'
											),
										},
										{
											value: 'after',
											label: __(
												'After Points',
												'learndash-achievements'
											),
										},
									] }
								/>
							</>
						)
					}
				</PanelBody>
				<PanelBody
					title={ __( 'Preview', 'learndash-achievements' ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Show Preview', 'learndash-achievements' ) }
						checked={ !! props.attributes.preview_show }
						// eslint-disable-next-line camelcase
						onChange={ ( preview_show ) =>
							// eslint-disable-next-line camelcase
							setAttributes( { preview_show } )
						}
					/>
				</PanelBody>
			</InspectorControls>
		);

		function doServersideRender( attrs ) {
			if ( attrs.preview_show === true ) {
				return (
					<div className={ 'learndash-block-inner' }>
						<ServerSideRender
							block="learndash/ld-my-achievements"
							attributes={ attrs }
						/>
					</div>
				);
			}
			return __(
				'[ld_my_achievements] shortcode output shown here',
				'learndash-achievements'
			);
		}

		return (
			<div { ...blockProps }>
				{ inspectorControls }
				{ doServersideRender( attributes ) }
			</div>
		);
	},

	save: () => {
		return null;
	},
} );
