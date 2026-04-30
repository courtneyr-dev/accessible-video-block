<?php
declare( strict_types=1 );

namespace CourtneyR\AccessibleVideoBlock;

/**
 * Convert a WebVTT track into reader-friendly paragraphs.
 *
 * The hard part isn't parsing — it's deciding where one paragraph ends and the next begins.
 * VTT cues are timing-driven, not paragraph-driven, so we have to invent paragraph breaks.
 *
 * Two cases:
 *   1. Speaker tags (<v Name>) present → each speaker change starts a new paragraph.
 *   2. No speaker tags → we group cues until the accumulated text ends in a sentence
 *      terminator (. ! ?) AND the next cue's first non-whitespace character is uppercase
 *      (or a digit, or a quote). This catches most real-world cases without over-splitting
 *      on abbreviations like "Dr." because the abbreviation usually isn't immediately
 *      followed by a capital-letter cue boundary.
 *
 * Known limitations:
 *   - Two-speaker overlap (rare in real captions) is not handled — we treat each cue
 *     atomically.
 *   - Mid-cue speaker changes (`<v Joe>...</v><v Sue>...`) are not split.
 *   - Sentences ending mid-cue are not broken into multiple paragraphs even if the next
 *     cue's text technically starts a new thought without ending the previous one.
 *
 * The collapsing rule lives in {@see should_start_new_paragraph()} so it's easy to revise.
 */
final class VttToProse {

	/** @return array<int, string> Paragraphs, in order, ready to wrap in <p>. */
	public static function parse( string $vtt ): array {
		$cues = self::extract_cues( $vtt );
		if ( $cues === [] ) {
			return [];
		}

		$paragraphs      = [];
		$current_text    = '';
		$current_speaker = null;

		foreach ( $cues as $cue ) {
			$speaker = $cue['speaker'];
			$text    = $cue['text'];

			$start_new = self::should_start_new_paragraph(
				$current_speaker,
				$speaker,
				$current_text,
				$text
			);

			if ( $start_new && $current_text !== '' ) {
				$paragraphs[]    = self::format_paragraph( $current_speaker, $current_text );
				$current_text    = '';
				$current_speaker = null;
			}

			if ( $current_speaker === null ) {
				$current_speaker = $speaker;
			}
			$current_text = trim( $current_text === '' ? $text : $current_text . ' ' . $text );
		}

		if ( $current_text !== '' ) {
			$paragraphs[] = self::format_paragraph( $current_speaker, $current_text );
		}

		return $paragraphs;
	}

	/**
	 * Tokenize a VTT file into ordered cues with optional speaker.
	 *
	 * @return array<int, array{speaker: ?string, text: string}>
	 */
	private static function extract_cues( string $vtt ): array {
		$vtt = trim( $vtt );
		if ( $vtt === '' || strpos( $vtt, 'WEBVTT' ) !== 0 ) {
			return [];
		}

		// Normalize line endings, drop the WEBVTT header block.
		$vtt    = preg_replace( "/\r\n?/", "\n", $vtt ) ?? $vtt;
		$blocks = preg_split( "/\n\s*\n/", $vtt ) ?: [];
		array_shift( $blocks ); // header

		$cues = [];
		foreach ( $blocks as $block ) {
			$block = trim( $block );
			if ( $block === '' || stripos( $block, 'NOTE' ) === 0 || stripos( $block, 'STYLE' ) === 0 || stripos( $block, 'REGION' ) === 0 ) {
				continue;
			}
			$lines = explode( "\n", $block );
			// Skip the cue identifier if the first line isn't a timestamp.
			if ( strpos( $lines[0], '-->' ) === false ) {
				array_shift( $lines );
			}
			if ( ! isset( $lines[0] ) || strpos( $lines[0], '-->' ) === false ) {
				continue;
			}
			array_shift( $lines ); // drop the timing line itself
			$raw = trim( implode( ' ', $lines ) );
			if ( $raw === '' ) {
				continue;
			}

			$speaker = null;
			if ( preg_match( '/^<v(?:\.[\w.-]+)?\s+([^>]+)>(.*)$/s', $raw, $m ) ) {
				$speaker = trim( $m[1] );
				$raw     = $m[2];
			}

			// Strip any remaining inline tags (<i>, <b>, <c.classname>, </v>, etc.)
			$text = preg_replace( '/<\/?[^>]+>/', '', $raw ) ?? $raw;
			$text = trim( preg_replace( '/\s+/', ' ', $text ) ?? $text );
			if ( $text === '' ) {
				continue;
			}

			$cues[] = [ 'speaker' => $speaker, 'text' => $text ];
		}

		return $cues;
	}

	private static function format_paragraph( ?string $speaker, string $text ): string {
		if ( $speaker !== null && $speaker !== '' ) {
			return sprintf( '<strong>%s:</strong> %s', $speaker, $text );
		}
		return $text;
	}

	/**
	 * Decide whether the next cue should start a new paragraph.
	 *
	 * **This is the heuristic the spec asked us to document.** Revise here freely.
	 *
	 * Rule, applied in order:
	 *   1. If the next cue has a different speaker than the current paragraph
	 *      (and both are non-null) → new paragraph.
	 *   2. If both cues have the same speaker → continue (collapse).
	 *      The spec is explicit: "Collapse consecutive cues from the same speaker
	 *      into single paragraphs."
	 *   3. If neither cue has a speaker → break only when the accumulated text
	 *      ends in `.`, `!`, or `?` AND the next cue's first non-whitespace
	 *      character is uppercase, a digit, or an opening quote/parenthesis.
	 *      This avoids splitting on abbreviations like "Dr. Smith" because in
	 *      practice such abbreviations are rarely cue boundaries.
	 *   4. If one cue has a speaker and the other doesn't → treat as a break.
	 *      (This shouldn't happen in well-formed VTT, but it's defensive.)
	 *
	 * The current rule favors faithful collapsing for speaker-tagged tracks and
	 * conservative paragraph breaks for plain caption tracks. Tradeoff: very long
	 * monologues by a single speaker become one big paragraph. If you'd rather
	 * cap paragraph length (say, break every ~3 sentences inside a single-speaker
	 * run), that's a one-line change — add a sentence-counter to the rule below.
	 */
	private static function should_start_new_paragraph(
		?string $current_speaker,
		?string $next_speaker,
		string $current_text,
		string $next_text
	): bool {
		if ( $current_text === '' ) {
			return false;
		}
		if ( $current_speaker !== null && $next_speaker !== null ) {
			return $current_speaker !== $next_speaker;
		}
		if ( $current_speaker === null && $next_speaker === null ) {
			$last_char  = substr( rtrim( $current_text ), -1 );
			$first_char = ltrim( $next_text )[0] ?? '';
			$ends_sentence  = in_array( $last_char, [ '.', '!', '?' ], true );
			$starts_new_thought = $first_char !== '' && (
				ctype_upper( $first_char )
				|| ctype_digit( $first_char )
				|| in_array( $first_char, [ '"', "'", '“', '‘', '(', '[' ], true )
			);
			return $ends_sentence && $starts_new_thought;
		}
		// Mixed (one has speaker, other doesn't) — defensive break.
		return true;
	}
}
