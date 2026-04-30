import { __ } from '@wordpress/i18n';
import { Placeholder, Button, Flex, FlexItem } from '@wordpress/components';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { useState } from '@wordpress/element';
import SourcePicker from './SourcePicker';

export default function EmptyStatePlaceholder( { attributes, setAttributes } ) {
	const [ mode, setMode ] = useState( null );

	if ( mode === 'youtube' || mode === 'vimeo' ) {
		return (
			<Placeholder
				icon="format-video"
				label={
					mode === 'youtube'
						? __( 'YouTube video', 'accessible-video-block' )
						: __( 'Vimeo video', 'accessible-video-block' )
				}
				instructions={ __( 'Paste a URL or video ID.', 'accessible-video-block' ) }
			>
				<SourcePicker
					attributes={ { ...attributes, sourceMode: mode } }
					setAttributes={ setAttributes }
					context="placeholder"
					onCancel={ () => setMode( null ) }
				/>
			</Placeholder>
		);
	}

	return (
		<Placeholder
			icon="format-video"
			label={ __( 'Accessible Video', 'accessible-video-block' ) }
			instructions={ __(
				'Choose a source. Requires the Able Player plugin to render on the front end.',
				'accessible-video-block'
			) }
		>
			<Flex gap={ 2 } wrap>
				<FlexItem>
					<MediaUploadCheck>
						<MediaUpload
							allowedTypes={ [ 'video', 'audio' ] }
							onSelect={ ( media ) =>
								setAttributes( {
									sourceMode: 'media',
									mediaId: media.id,
									mediaUrl: media.url,
									mediaMime: media.mime,
									mediaTitle: media.title,
								} )
							}
							render={ ( { open } ) => (
								<Button variant="primary" onClick={ open }>
									{ __( 'Media library', 'accessible-video-block' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
				</FlexItem>
				<FlexItem>
					<Button variant="secondary" onClick={ () => setMode( 'youtube' ) }>
						{ __( 'YouTube', 'accessible-video-block' ) }
					</Button>
				</FlexItem>
				<FlexItem>
					<Button variant="secondary" onClick={ () => setMode( 'vimeo' ) }>
						{ __( 'Vimeo', 'accessible-video-block' ) }
					</Button>
				</FlexItem>
			</Flex>
		</Placeholder>
	);
}
