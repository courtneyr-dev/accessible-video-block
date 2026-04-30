import { __ } from '@wordpress/i18n';
import {
	PanelBody,
	ToggleControl,
	TextControl,
	SelectControl,
	RangeControl,
	Button,
} from '@wordpress/components';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';

export default function PlayerOptionsPanel( { attributes, setAttributes } ) {
	return (
		<>
			<PanelBody title={ __( 'Privacy & playback', 'accessible-video-block' ) }>
				<ToggleControl
					label={ __( 'Use youtube-nocookie.com (privacy mode)', 'accessible-video-block' ) }
					checked={ !! attributes.youtubeNoCookie }
					onChange={ ( youtubeNoCookie ) => setAttributes( { youtubeNoCookie } ) }
				/>
				<ToggleControl
					label={ __( 'Autoplay', 'accessible-video-block' ) }
					checked={ !! attributes.autoplay }
					onChange={ ( autoplay ) => setAttributes( { autoplay } ) }
				/>
				<ToggleControl
					label={ __( 'Loop', 'accessible-video-block' ) }
					checked={ !! attributes.loop }
					onChange={ ( loop ) => setAttributes( { loop } ) }
				/>
				<ToggleControl
					label={ __( 'Plays inline (mobile)', 'accessible-video-block' ) }
					checked={ !! attributes.playsinline }
					onChange={ ( playsinline ) => setAttributes( { playsinline } ) }
				/>
				<ToggleControl
					label={ __( 'Hide native controls', 'accessible-video-block' ) }
					checked={ !! attributes.hidecontrols }
					onChange={ ( hidecontrols ) => setAttributes( { hidecontrols } ) }
				/>
				<ToggleControl
					label={ __( 'Show "Now playing" label', 'accessible-video-block' ) }
					checked={ !! attributes.nowplaying }
					onChange={ ( nowplaying ) => setAttributes( { nowplaying } ) }
				/>
			</PanelBody>

			<PanelBody title={ __( 'Display', 'accessible-video-block' ) } initialOpen={ false }>
				<TextControl
					label={ __( 'Heading (visible above player)', 'accessible-video-block' ) }
					value={ attributes.heading || '' }
					onChange={ ( heading ) => setAttributes( { heading } ) }
				/>
				<TextControl
					label={ __( 'Width', 'accessible-video-block' ) }
					value={ attributes.width || '' }
					onChange={ ( width ) => setAttributes( { width } ) }
					help={ __( 'Leave empty for responsive.', 'accessible-video-block' ) }
				/>
				<TextControl
					label={ __( 'Height', 'accessible-video-block' ) }
					value={ attributes.height || '' }
					onChange={ ( height ) => setAttributes( { height } ) }
				/>
				<MediaUploadCheck>
					<MediaUpload
						allowedTypes={ [ 'image' ] }
						value={ attributes.posterId }
						onSelect={ ( media ) =>
							setAttributes( { posterId: media.id, posterUrl: media.url } )
						}
						render={ ( { open } ) => (
							<Button variant="secondary" onClick={ open }>
								{ attributes.posterUrl
									? __( 'Replace poster', 'accessible-video-block' )
									: __( 'Choose poster image', 'accessible-video-block' ) }
							</Button>
						) }
					/>
				</MediaUploadCheck>
			</PanelBody>

			<PanelBody title={ __( 'Behavior', 'accessible-video-block' ) } initialOpen={ false }>
				<SelectControl
					label={ __( 'Speed control style', 'accessible-video-block' ) }
					value={ attributes.speed || 'animals' }
					options={ [
						{ label: __( 'Animals (default)', 'accessible-video-block' ), value: 'animals' },
						{ label: __( 'Arrows', 'accessible-video-block' ), value: 'arrows' },
					] }
					onChange={ ( speed ) => setAttributes( { speed } ) }
				/>
				<RangeControl
					label={ __( 'Volume (0–10)', 'accessible-video-block' ) }
					value={ attributes.volume ?? 7 }
					onChange={ ( volume ) => setAttributes( { volume } ) }
					min={ 0 }
					max={ 10 }
				/>
				<TextControl
					label={ __( 'Start time (seconds)', 'accessible-video-block' ) }
					type="number"
					value={ attributes.start ?? '' }
					onChange={ ( v ) => setAttributes( { start: v === '' ? undefined : Number( v ) } ) }
				/>
				<TextControl
					label={ __( 'Seek interval (seconds)', 'accessible-video-block' ) }
					type="number"
					value={ attributes.seekinterval ?? '' }
					onChange={ ( v ) => setAttributes( { seekinterval: v === '' ? undefined : Number( v ) } ) }
					help={ __( 'Leave empty for the Able Player default.', 'accessible-video-block' ) }
				/>
			</PanelBody>
		</>
	);
}
