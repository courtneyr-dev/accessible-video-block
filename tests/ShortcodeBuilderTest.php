<?php
declare( strict_types=1 );

namespace CourtneyR\AccessibleVideoBlock\Tests;

use CourtneyR\AccessibleVideoBlock\ShortcodeBuilder;
use PHPUnit\Framework\TestCase;

final class ShortcodeBuilderTest extends TestCase {

	public function test_minimal_self_hosted_attachment_id(): void {
		$out = ShortcodeBuilder::build( [ 'id' => 42 ] );
		$this->assertSame( '[ableplayer id="42"]', $out );
	}

	public function test_youtube_id_with_nocookie(): void {
		$out = ShortcodeBuilder::build( [
			'youtube-id'       => 'dQw4w9WgXcQ',
			'youtube-nocookie' => true,
		] );
		$this->assertSame(
			'[ableplayer youtube-id="dQw4w9WgXcQ" youtube-nocookie="true"]',
			$out
		);
	}

	public function test_drops_empty_string_and_null_values(): void {
		$out = ShortcodeBuilder::build( [
			'id'      => 7,
			'heading' => '',
			'width'   => null,
		] );
		$this->assertSame( '[ableplayer id="7"]', $out );
	}

	public function test_emits_zero_volume(): void {
		$out = ShortcodeBuilder::build( [ 'id' => 1, 'volume' => 0 ] );
		$this->assertSame( '[ableplayer id="1" volume="0"]', $out );
	}

	public function test_quotes_values_with_spaces(): void {
		$out = ShortcodeBuilder::build( [ 'heading' => 'My Talk: An Intro' ] );
		$this->assertSame( '[ableplayer heading="My Talk: An Intro"]', $out );
	}

	public function test_escapes_double_quotes_in_values(): void {
		$out = ShortcodeBuilder::build( [ 'heading' => 'A "great" talk' ] );
		$this->assertSame( '[ableplayer heading="A &quot;great&quot; talk"]', $out );
	}

	public function test_pipe_separator_track_format_passes_through(): void {
		$out = ShortcodeBuilder::build( [
			'captions' => '123|en|English',
		] );
		$this->assertSame( '[ableplayer captions="123|en|English"]', $out );
	}

	public function test_boolean_true_becomes_string_true(): void {
		$out = ShortcodeBuilder::build( [ 'autoplay' => true ] );
		$this->assertSame( '[ableplayer autoplay="true"]', $out );
	}

	public function test_boolean_false_is_dropped(): void {
		$out = ShortcodeBuilder::build( [ 'id' => 1, 'autoplay' => false ] );
		$this->assertSame( '[ableplayer id="1"]', $out );
	}

	public function test_attribute_order_is_preserved(): void {
		$out = ShortcodeBuilder::build( [
			'id'      => 1,
			'heading' => 'Test',
			'volume'  => 5,
		] );
		$this->assertSame( '[ableplayer id="1" heading="Test" volume="5"]', $out );
	}

	public function test_empty_attrs_array_returns_bare_shortcode(): void {
		$this->assertSame( '[ableplayer]', ShortcodeBuilder::build( [] ) );
	}
}
