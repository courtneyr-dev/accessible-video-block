import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	InspectorControls,
	InnerBlocks,
} from '@wordpress/block-editor';
import { TabPanel, Card, CardBody, Notice } from '@wordpress/components';
import SourcePicker from './components/SourcePicker';
import TracksPanel from './components/TracksPanel';
import PlayerOptionsPanel from './components/PlayerOptionsPanel';
import TranscriptPanel from './components/TranscriptPanel';
import EmptyStatePlaceholder from './components/EmptyStatePlaceholder';
import './editor.scss';

const TRANSCRIPT_TEMPLATE = [
	[ 'core/paragraph', { placeholder: __( 'Paste your transcript here. Headings, lists, and paragraphs are supported.', 'accessible-video-block' ) } ],
];

const ALLOWED_INNER_BLOCKS = [ 'core/paragraph', 'core/heading', 'core/list', 'core/quote' ];

export default function Edit( { attributes, setAttributes, clientId } ) {
	const blockProps = useBlockProps( { className: 'avb-editor' } );
	const hasSource =
		( attributes.sourceMode === 'media' && attributes.mediaUrl ) ||
		( attributes.sourceMode === 'youtube' && attributes.youtubeId ) ||
		( attributes.sourceMode === 'vimeo' && attributes.vimeoId );

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<TabPanel
					className="avb-inspector-tabs"
					tabs={ [
						{ name: 'source', title: __( 'Source', 'accessible-video-block' ) },
						{ name: 'tracks', title: __( 'Tracks', 'accessible-video-block' ) },
						{ name: 'transcript', title: __( 'Transcript', 'accessible-video-block' ) },
						{ name: 'playback', title: __( 'Playback', 'accessible-video-block' ) },
					] }
				>
					{ ( tab ) => {
						if ( tab.name === 'source' ) {
							return (
								<SourcePicker
									attributes={ attributes }
									setAttributes={ setAttributes }
									context="inspector"
								/>
							);
						}
						if ( tab.name === 'tracks' ) {
							return (
								<TracksPanel
									attributes={ attributes }
									setAttributes={ setAttributes }
								/>
							);
						}
						if ( tab.name === 'transcript' ) {
							return (
								<TranscriptPanel
									attributes={ attributes }
									setAttributes={ setAttributes }
								/>
							);
						}
						return (
							<PlayerOptionsPanel
								attributes={ attributes }
								setAttributes={ setAttributes }
							/>
						);
					} }
				</TabPanel>
			</InspectorControls>

			{ ! hasSource ? (
				<EmptyStatePlaceholder
					attributes={ attributes }
					setAttributes={ setAttributes }
				/>
			) : (
				<Card>
					<CardBody>
						<SourceSummary attributes={ attributes } />
						<Notice status="info" isDismissible={ false }>
							{ __(
								'Player preview is shown on the front end. The editor displays a static summary to keep load fast.',
								'accessible-video-block'
							) }
						</Notice>
					</CardBody>
				</Card>
			) }

			{ attributes.transcriptMode === 'innerblocks' && (
				<details className="avb-transcript avb-transcript--editor" open>
					<summary>{ __( 'Transcript', 'accessible-video-block' ) }</summary>
					<div className="avb-transcript__body">
						<InnerBlocks
							allowedBlocks={ ALLOWED_INNER_BLOCKS }
							template={ TRANSCRIPT_TEMPLATE }
							templateLock={ false }
						/>
					</div>
				</details>
			) }
		</div>
	);
}

function SourceSummary( { attributes } ) {
	const { sourceMode, mediaTitle, mediaUrl, youtubeId, vimeoId } = attributes;
	if ( sourceMode === 'youtube' ) {
		return (
			<p>
				<strong>{ __( 'YouTube:', 'accessible-video-block' ) }</strong> { youtubeId }
			</p>
		);
	}
	if ( sourceMode === 'vimeo' ) {
		return (
			<p>
				<strong>{ __( 'Vimeo:', 'accessible-video-block' ) }</strong> { vimeoId }
			</p>
		);
	}
	return (
		<p>
			<strong>{ __( 'Media:', 'accessible-video-block' ) }</strong>{ ' ' }
			{ mediaTitle || mediaUrl }
		</p>
	);
}
