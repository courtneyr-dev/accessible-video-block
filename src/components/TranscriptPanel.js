import { __ } from '@wordpress/i18n';
import {
	PanelBody,
	RadioControl,
	Notice,
	SelectControl,
} from '@wordpress/components';

export default function TranscriptPanel( { attributes, setAttributes } ) {
	const { transcriptMode = 'innerblocks' } = attributes;

	const vttSourceOptions = [
		{ label: __( 'Captions', 'accessible-video-block' ), value: 'captions' },
		{ label: __( 'First subtitle track', 'accessible-video-block' ), value: 'subtitle:0' },
		{ label: __( 'Chapters', 'accessible-video-block' ), value: 'chapters' },
	];

	return (
		<PanelBody title={ __( 'Transcript', 'accessible-video-block' ) } initialOpen={ true }>
			<RadioControl
				label={ __( 'Transcript source', 'accessible-video-block' ) }
				selected={ transcriptMode }
				options={ [
					{
						label: __( 'Inline rich text (default, JS-free)', 'accessible-video-block' ),
						value: 'innerblocks',
					},
					{
						label: __( 'Generate from a VTT track (JS-free)', 'accessible-video-block' ),
						value: 'vtt',
					},
					{
						label: __( "Able Player's built-in transcript (requires JS)", 'accessible-video-block' ),
						value: 'native',
					},
				] }
				onChange={ ( transcriptMode ) => setAttributes( { transcriptMode } ) }
			/>

			{ transcriptMode === 'vtt' && (
				<>
					<SelectControl
						label={ __( 'Generate from', 'accessible-video-block' ) }
						value={ attributes.transcriptVttSource || 'captions' }
						options={ vttSourceOptions }
						onChange={ ( transcriptVttSource ) => setAttributes( { transcriptVttSource } ) }
					/>
					<Notice status="info" isDismissible={ false }>
						{ __(
							'The transcript is parsed and cached server-side, so it stays readable with JavaScript disabled.',
							'accessible-video-block'
						) }
					</Notice>
				</>
			) }

			{ transcriptMode === 'native' && (
				<Notice status="warning" isDismissible={ false }>
					{ __(
						"Able Player's built-in transcript requires JavaScript to render. Use this when chapter navigation matters more than no-JS readability.",
						'accessible-video-block'
					) }
				</Notice>
			) }
		</PanelBody>
	);
}
