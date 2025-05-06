const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InspectorControls, useBlockProps } = wp.blockEditor;
const { PanelBody, TextControl, ToggleControl } = wp.components;

import ServerSideRender from '@wordpress/server-side-render';

registerBlockType( 'learndash/ld-achievements-leaderboard', {
	title: __( 'LearnDash Achievements Leaderboard', 'learndash-achievements' ),
	description: __(
		'Display LearnDash achievements leaderboard.',
		'learndash-achievements'
	),
	icon: 'editor-ol',
	category: 'learndash-blocks',
	keywords: [
		'LearnDash',
		'learndash-achievements',
		'achievements',
		'leaderboard',
	],
	attributes: {
		number: {
			type: 'integer',
			default: 10,
		},
		show_points: {
			type: 'boolean',
			default: false,
		},
		preview_show: {
			type: 'boolean',
			default: true,
		},
	},

	edit( props ) {
		const { attributes, setAttributes } = props;
		// eslint-disable-next-line react-hooks/rules-of-hooks
		const blockProps = useBlockProps();

		const inspectorControls = (
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'learndash-achievements' ) }>
					<TextControl
						label={ __( 'Number', 'learndash-achievements' ) }
						help={ __(
							'Number of users that will be displayed in leaderboard.',
							'learndash-achievements'
						) }
						value={ props.attributes.number || '' }
						type={ 'number' }
						onChange={ ( number ) => setAttributes( { number } ) }
					/>
					<ToggleControl
						label={ __( 'Show Points', 'learndash-achievements' ) }
						checked={ !! props.attributes.show_points }
						// eslint-disable-next-line camelcase
						onChange={ ( show_points ) =>
							// eslint-disable-next-line camelcase
							setAttributes( { show_points } )
						}
					/>
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
							block="learndash/ld-achievements-leaderboard"
							attributes={ attrs }
						/>
					</div>
				);
			}
			return __(
				'[ld_achievements_leaderboard] shortcode output shown here',
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
