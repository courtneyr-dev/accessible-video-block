<?php
declare( strict_types=1 );

namespace CourtneyR\AccessibleVideoBlock;

/**
 * Server-side defensive parsing of YouTube/Vimeo input.
 *
 * The editor-side regex already extracts the ID before saving, so this is
 * a second line of defense for content that was authored elsewhere or
 * imported. Returns null on anything that doesn't pattern-match.
 */
final class UrlParser {

	public static function youtube_id( string $input ): ?string {
		$input = trim( $input );
		if ( $input === '' ) {
			return null;
		}
		if ( preg_match( '/^[A-Za-z0-9_-]{11}$/', $input ) ) {
			return $input;
		}
		if ( preg_match( '#(?:youtu\.be/|youtube(?:-nocookie)?\.com/(?:watch\?v=|embed/|shorts/))([A-Za-z0-9_-]{11})#', $input, $m ) ) {
			return $m[1];
		}
		return null;
	}

	public static function vimeo_id( string $input ): ?string {
		$input = trim( $input );
		if ( $input === '' ) {
			return null;
		}
		if ( ctype_digit( $input ) ) {
			return $input;
		}
		if ( preg_match( '#vimeo\.com/(?:video/)?(\d+)#', $input, $m ) ) {
			return $m[1];
		}
		return null;
	}
}
