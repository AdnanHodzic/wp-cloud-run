import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, Button, ToggleControl } from '@wordpress/components';
import { createHigherOrderComponent } from '@wordpress/compose';
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

import './blocks.scss';

const withLightboxPanelControls = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		const { name } = props;
		const isImageOrGalleryBlock = 'core/image' === name || 'core/gallery' === name;

		// lightpress object passed via wp_localize_script
		const activeLightbox = lightpress.activeLightbox;
		const isProLightbox = 'Pro Lightbox' === activeLightbox;
		const isProUser = lightpress.isProUser;
		const settingsUrl = lightpress.settingsUrl;
		const lightboxPanelOpen = lightpress.lightboxPanelOpen === '1' ? true : false;

		if ( ! isImageOrGalleryBlock ) {
			return <BlockEdit { ...props } />
		}

		return (
			<>
				<BlockEdit key="edit" { ...props } />
				<InspectorControls>
					<PanelBody className='lightpress-settings' title={ __( 'Lightbox' ) } initialOpen={ lightboxPanelOpen }>
						<p>
							{ __( 'You are using: ' ) }
							<span className='lightpress-active-lightbox'>{ activeLightbox }</span>
							{ '.' }
						</p>
						{
							! isProUser && (
								<>
									<ToggleControl
										label="Use Pro Lightbox?"
										checked={ false }
										disabled
									/>
									<p className="lightpress-upgrade-notice">{ __( 'Upgrade to enable Pro Lightbox.') }</p>
									<div className="lightpress-button-container">
										<Button
											variant="primary"
											className="lightpress-button"
											href='https://lightpress.io/pro-lightbox'
											target='_blank'
										>
											{ __( 'See Demos' ) }
										</Button>
										{ ' ' }
										<Button
											variant="primary"
											className="lightpress-button"
											href='https://lightpress.io/pro-lightbox/pricing'
											target='_blank'
										>
											{ __( 'Upgrade' ) }
										</Button>
									</div>
								</>
							)
						}
						{
							isProUser && ! isProLightbox && (
								<p>{ __( 'Notice: You have an active LightPress Pro license and can use the Pro Lightbox!') }</p>
							)
						}
						<div className="lightpress-settings-link">
							<Button
								variant="link"
								href={ settingsUrl }
								target='_blank'
							>
								{ __( 'View Lightbox Settings' ) }
							</Button>
						</div>		
					</PanelBody>
				</InspectorControls>
			</>
		);
	};
}, 'withLightboxPanelControls' );

addFilter(
	'editor.BlockEdit',
	'lightpress/lightbox-panel',
	withLightboxPanelControls
);
