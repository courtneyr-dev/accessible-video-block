import { __ } from '@wordpress/i18n';
import {
	PanelBody,
	TextControl,
	Button,
	ButtonGroup,
	Notice,
} from '@wordpress/components';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { useState } from '@wordpress/element';

const YOUTUBE_RE = /(?:youtu\.be\/|youtube(?:-nocookie)?\.com\/(?:watch\?v=|embed\/|shorts\/))([A-Za-z0-9_-]{11})/;
const YOUTUBE_BARE = /^[A-Za-z0-9_-]{11}$/;
const VIMEO_RE = /vimeo\.com\/(?:video\/)?(\d+)/;

function extractYouTubeId( input ) {
	const trimmed = ( input || '' ).trim();
	if ( YOUTUBE_BARE.test( trimmed ) ) {
		return trimmed;
	}
	const match = trimmed.match( YOUTUBE_RE );
	return match ? match[ 1 ] : null;
}

function extractVimeoId( input ) {
	const trimmed = ( input || '' ).trim();
	if ( /^\d+$/.test( trimmed ) ) {
		return trimmed;
	}
	const match = trimmed.match( VIMEO_RE );
	return match ? match[ 1 ] : null;
}

export default function SourcePicker( { attributes, setAttributes, context = 'inspector', onCancel } ) {
	const { sourceMode } = attributes;
	const [ rawInput, setRawInput ] = useState(
		attributes.youtubeId || attributes.vimeoId || ''
	);
	const [ error, setError ] = useState( null );

	const switchTo = ( mode ) =>
		setAttributes( {
			sourceMode: mode,
			mediaId: undefined,
			mediaUrl: undefined,
			mediaMime: undefined,
			mediaTitle: undefined,
			youtubeId: undefined,
			vimeoId: undefined,
		} );

	const submitId = () => {
		setError( null );
		if ( sourceMode === 'youtube' ) {
			const id = extractYouTubeId( rawInput );
			if ( ! id ) {
				setError( __( 'Could not find an 11-character YouTube video ID in that input.', 'accessible-video-block' ) );
				return;
			}
			setAttributes( { youtubeId: id } );
		} else if ( sourceMode === 'vimeo' ) {
			const id = extractVimeoId( rawInput );
			if ( ! id ) {
				setError( __( 'Could not find a Vimeo numeric ID in that input.', 'accessible-video-block' ) );
				return;
			}
			setAttributes( { vimeoId: id } );
		}
	};

	const Wrapper = context === 'inspector' ? PanelBody : 'div';
	const wrapperProps = context === 'inspector' ? { title: __( 'Source', 'accessible-video-block' ), initialOpen: true } : {};

	return (
		<Wrapper { ...wrapperProps }>
			<ButtonGroup>
				<Button
					variant={ sourceMode === 'media' ? 'primary' : 'secondary' }
					onClick={ () => switchTo( 'media' ) }
				>
					{ __( 'Media library', 'accessible-video-block' ) }
				</Button>
				<Button
					variant={ sourceMode === 'youtube' ? 'primary' : 'secondary' }
					onClick={ () => switchTo( 'youtube' ) }
				>
					{ __( 'YouTube', 'accessible-video-block' ) }
				</Button>
				<Button
					variant={ sourceMode === 'vimeo' ? 'primary' : 'secondary' }
					onClick={ () => switchTo( 'vimeo' ) }
				>
					{ __( 'Vimeo', 'accessible-video-block' ) }
				</Button>
			</ButtonGroup>

			{ sourceMode === 'media' && (
				<div style={ { marginTop: '1em' } }>
					<MediaUploadCheck>
						<MediaUpload
							allowedTypes={ [ 'video', 'audio' ] }
							value={ attributes.mediaId }
							onSelect={ ( media ) =>
								setAttributes( {
									mediaId: media.id,
									mediaUrl: media.url,
									mediaMime: media.mime,
									mediaTitle: media.title,
								} )
							}
							render={ ( { open } ) => (
								<Button variant="secondary" onClick={ open }>
									{ attributes.mediaUrl
										? __( 'Replace media', 'accessible-video-block' )
										: __( 'Choose media', 'accessible-video-block' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					{ attributes.mediaTitle && (
						<p style={ { marginTop: '0.5em' } }>
							<em>{ attributes.mediaTitle }</em>
						</p>
					) }
				</div>
			) }

			{ ( sourceMode === 'youtube' || sourceMode === 'vimeo' ) && (
				<div style={ { marginTop: '1em' } }>
					<TextControl
						label={
							sourceMode === 'youtube'
								? __( 'YouTube URL or ID', 'accessible-video-block' )
								: __( 'Vimeo URL or ID', 'accessible-video-block' )
						}
						value={ rawInput }
						onChange={ setRawInput }
					/>
					<ButtonGroup>
						<Button variant="primary" onClick={ submitId }>
							{ __( 'Save', 'accessible-video-block' ) }
						</Button>
						{ onCancel && (
							<Button variant="tertiary" onClick={ onCancel }>
								{ __( 'Cancel', 'accessible-video-block' ) }
							</Button>
						) }
					</ButtonGroup>
					{ error && (
						<Notice status="warning" isDismissible={ false }>
							{ error }
						</Notice>
					) }
					{ ( attributes.youtubeId || attributes.vimeoId ) && (
						<p style={ { marginTop: '0.5em' } }>
							<strong>{ __( 'Saved ID:', 'accessible-video-block' ) }</strong>{ ' ' }
							<code>{ attributes.youtubeId || attributes.vimeoId }</code>
						</p>
					) }
				</div>
			) }
		</Wrapper>
	);
}
