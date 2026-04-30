import { __ } from '@wordpress/i18n';
import {
	PanelBody,
	TextControl,
	Button,
	Flex,
	FlexItem,
	BaseControl,
	__experimentalVStack as VStack,
} from '@wordpress/components';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';

const VTT_TYPES = [ 'text/vtt' ];

function isVttUrl( url ) {
	return typeof url === 'string' && /\.vtt(\?|$)/i.test( url );
}

function TrackRow( { label, track, onChange, onRemove, allowRemove = false } ) {
	const t = track || {};
	return (
		<BaseControl __nextHasNoMarginBottom label={ label }>
			<MediaUploadCheck>
				<MediaUpload
					allowedTypes={ VTT_TYPES }
					value={ t.id }
					onSelect={ ( media ) =>
						onChange( { ...t, id: media.id, url: media.url } )
					}
					render={ ( { open } ) => (
						<Button variant="secondary" onClick={ open }>
							{ t.id || t.url
								? __( 'Replace VTT', 'accessible-video-block' )
								: __( 'Choose VTT', 'accessible-video-block' ) }
						</Button>
					) }
				/>
			</MediaUploadCheck>
			<TextControl
				__nextHasNoMarginBottom
				label={ __( 'External URL (optional)', 'accessible-video-block' ) }
				value={ t.url || '' }
				onChange={ ( url ) => onChange( { ...t, url, id: undefined } ) }
				help={
					t.url && ! isVttUrl( t.url )
						? __( 'Warning: URL does not end in .vtt.', 'accessible-video-block' )
						: undefined
				}
			/>
			<Flex gap={ 2 }>
				<FlexItem>
					<TextControl
						__nextHasNoMarginBottom
						label={ __( 'Language code', 'accessible-video-block' ) }
						value={ t.lang || '' }
						onChange={ ( lang ) => onChange( { ...t, lang } ) }
						placeholder="en"
					/>
				</FlexItem>
				<FlexItem>
					<TextControl
						__nextHasNoMarginBottom
						label={ __( 'Label', 'accessible-video-block' ) }
						value={ t.label || '' }
						onChange={ ( label ) => onChange( { ...t, label } ) }
						placeholder={ __( 'English', 'accessible-video-block' ) }
					/>
				</FlexItem>
			</Flex>
			{ allowRemove && (
				<Button variant="tertiary" isDestructive onClick={ onRemove }>
					{ __( 'Remove track', 'accessible-video-block' ) }
				</Button>
			) }
		</BaseControl>
	);
}

export default function TracksPanel( { attributes, setAttributes } ) {
	const subtitles = attributes.subtitles || [];

	const updateSubtitle = ( index, next ) => {
		const updated = [ ...subtitles ];
		updated[ index ] = next;
		setAttributes( { subtitles: updated } );
	};

	const removeSubtitle = ( index ) => {
		const updated = subtitles.filter( ( _, i ) => i !== index );
		setAttributes( { subtitles: updated } );
	};

	const addSubtitle = () =>
		setAttributes( { subtitles: [ ...subtitles, {} ] } );

	return (
		<>
			<PanelBody title={ __( 'Captions', 'accessible-video-block' ) }>
				<TrackRow
					label={ __( 'Captions track', 'accessible-video-block' ) }
					track={ attributes.captions }
					onChange={ ( captions ) => setAttributes( { captions } ) }
				/>
			</PanelBody>

			<PanelBody title={ __( 'Subtitles', 'accessible-video-block' ) } initialOpen={ false }>
				<VStack spacing={ 4 }>
					{ subtitles.map( ( track, index ) => (
						<TrackRow
							key={ index }
							label={ `${ __( 'Subtitle track', 'accessible-video-block' ) } ${ index + 1 }` }
							track={ track }
							onChange={ ( next ) => updateSubtitle( index, next ) }
							onRemove={ () => removeSubtitle( index ) }
							allowRemove
						/>
					) ) }
					<Button variant="secondary" onClick={ addSubtitle }>
						{ __( 'Add subtitle track', 'accessible-video-block' ) }
					</Button>
				</VStack>
			</PanelBody>

			<PanelBody title={ __( 'Chapters', 'accessible-video-block' ) } initialOpen={ false }>
				<TrackRow
					label={ __( 'Chapters track', 'accessible-video-block' ) }
					track={ attributes.chapters }
					onChange={ ( chapters ) => setAttributes( { chapters } ) }
				/>
			</PanelBody>

			<PanelBody title={ __( 'Audio descriptions', 'accessible-video-block' ) } initialOpen={ false }>
				<TrackRow
					label={ __( 'Descriptions track (VTT)', 'accessible-video-block' ) }
					track={ attributes.descriptions }
					onChange={ ( descriptions ) => setAttributes( { descriptions } ) }
				/>
				<TextControl
					label={ __( 'YouTube described version ID', 'accessible-video-block' ) }
					value={ attributes.youtubeDescId || '' }
					onChange={ ( youtubeDescId ) => setAttributes( { youtubeDescId } ) }
					help={ __( 'Optional alternate YouTube video with audio description.', 'accessible-video-block' ) }
				/>
				<TextControl
					label={ __( 'Vimeo described version ID', 'accessible-video-block' ) }
					value={ attributes.vimeoDescId || '' }
					onChange={ ( vimeoDescId ) => setAttributes( { vimeoDescId } ) }
				/>
				<TextControl
					label={ __( 'YouTube sign-language video URL', 'accessible-video-block' ) }
					value={ attributes.youtubeSignSrc || '' }
					onChange={ ( youtubeSignSrc ) => setAttributes( { youtubeSignSrc } ) }
				/>
			</PanelBody>
		</>
	);
}
