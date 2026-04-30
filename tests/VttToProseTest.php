<?php
declare( strict_types=1 );

namespace CourtneyR\AccessibleVideoBlock\Tests;

use CourtneyR\AccessibleVideoBlock\VttToProse;
use PHPUnit\Framework\TestCase;

final class VttToProseTest extends TestCase {

	public function test_single_speaker_collapses_all_cues_into_one_paragraph(): void {
		// Spec: "Collapse consecutive cues from the same speaker into single paragraphs."
		// All three cues are <v Courtney>, so they merge into ONE paragraph.
		$vtt        = (string) file_get_contents( __DIR__ . '/fixtures/single-speaker.vtt' );
		$paragraphs = VttToProse::parse( $vtt );

		$this->assertCount( 1, $paragraphs, 'All same-speaker cues collapse to one paragraph.' );
		$this->assertStringStartsWith( '<strong>Courtney:</strong>', $paragraphs[0] );
		$this->assertStringContainsString( 'Welcome to the show.', $paragraphs[0] );
		$this->assertStringContainsString( 'open web.', $paragraphs[0] );
		$this->assertStringContainsString( "Let's start with what Able Player gets right.", $paragraphs[0] );
	}

	public function test_speaker_change_starts_new_paragraph_with_label(): void {
		$vtt        = (string) file_get_contents( __DIR__ . '/fixtures/multi-speaker.vtt' );
		$paragraphs = VttToProse::parse( $vtt );

		$this->assertCount( 3, $paragraphs, 'Expected three paragraphs (one per speaker run).' );
		$this->assertStringStartsWith( '<strong>Courtney:</strong>', $paragraphs[0] );
		$this->assertStringStartsWith( '<strong>Joe:</strong>', $paragraphs[1] );
		$this->assertStringStartsWith( '<strong>Courtney:</strong>', $paragraphs[2] );
		$this->assertStringContainsString( "I'd nudge the cue collapsing", $paragraphs[1] );
	}

	public function test_no_speakers_breaks_on_sentence_terminator_followed_by_capital(): void {
		// Spec: "group cues into paragraphs on natural sentence breaks
		// (periods/question marks/exclamation points followed by capital letters)."
		// Each cue in this fixture ends with a sentence terminator and the next
		// starts with a capital, so each cue becomes its own paragraph.
		$vtt        = (string) file_get_contents( __DIR__ . '/fixtures/no-speakers.vtt' );
		$paragraphs = VttToProse::parse( $vtt );

		$this->assertCount( 3, $paragraphs );
		$this->assertSame( 'Open source accessibility is a moving target.', $paragraphs[0] );
		$this->assertSame( 'Tools like Able Player help.', $paragraphs[1] );
		$this->assertSame( "But the work is never done. There's always one more cue style to handle.", $paragraphs[2] );
	}

	public function test_no_speakers_joins_cues_when_first_does_not_end_a_sentence(): void {
		$vtt = "WEBVTT\n\n00:00:01.000 --> 00:00:03.000\nThis sentence spans\n\n00:00:03.500 --> 00:00:05.000\nacross two cues.\n";
		$paragraphs = VttToProse::parse( $vtt );
		$this->assertSame( [ 'This sentence spans across two cues.' ], $paragraphs );
	}

	public function test_strips_cue_settings_and_inline_html(): void {
		$vtt        = "WEBVTT\n\n00:00:01.000 --> 00:00:03.000 align:start\n<i>Hello</i> world.\n";
		$paragraphs = VttToProse::parse( $vtt );
		$this->assertSame( [ 'Hello world.' ], $paragraphs );
	}

	public function test_returns_empty_array_for_invalid_vtt(): void {
		$this->assertSame( [], VttToProse::parse( '' ) );
		$this->assertSame( [], VttToProse::parse( 'not a vtt' ) );
	}

	public function test_skips_cue_identifiers(): void {
		// The "cue-1" and "cue-2" lines before each timing block must be ignored.
		// Cue text doesn't end in a sentence terminator, so cues join into one paragraph.
		$vtt = "WEBVTT\n\ncue-1\n00:00:01.000 --> 00:00:03.000\nFirst part\n\ncue-2\n00:00:03.500 --> 00:00:05.000\nsecond part.\n";
		$paragraphs = VttToProse::parse( $vtt );
		$this->assertSame( [ 'First part second part.' ], $paragraphs );
	}
}
