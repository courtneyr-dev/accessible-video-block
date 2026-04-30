<?php
declare( strict_types=1 );

namespace CourtneyR\AccessibleVideoBlock;

/**
 * Two-tier cache for VTT-derived transcripts.
 *
 * Primary store: WordPress object cache (Object Cache Pro on this site).
 * Fallback: post meta on the post the block was rendered on.
 *
 * Cache key incorporates the source URL plus a freshness signal (attachment
 * modified time, or external Last-Modified). When the source changes the key
 * changes and stale entries fall out naturally; we don't need explicit eviction.
 */
final class TranscriptCache {

	private const GROUP    = 'accessible-video-block';
	private const META_KEY = '_avb_transcript_cache';

	/** @return array<int, string>|null */
	public static function get( string $url, int $post_id ): ?array {
		$key = self::cache_key( $url );

		$cached = wp_cache_get( $key, self::GROUP );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		if ( $post_id > 0 ) {
			$bag = get_post_meta( $post_id, self::META_KEY, true );
			if ( is_array( $bag ) && isset( $bag[ $key ] ) && is_array( $bag[ $key ] ) ) {
				wp_cache_set( $key, $bag[ $key ], self::GROUP );
				return $bag[ $key ];
			}
		}

		return null;
	}

	/** @param array<int, string> $paragraphs */
	public static function set( string $url, int $post_id, array $paragraphs ): void {
		$key = self::cache_key( $url );
		wp_cache_set( $key, $paragraphs, self::GROUP );

		if ( $post_id > 0 ) {
			$bag = get_post_meta( $post_id, self::META_KEY, true );
			if ( ! is_array( $bag ) ) {
				$bag = [];
			}
			$bag[ $key ] = $paragraphs;
			update_post_meta( $post_id, self::META_KEY, $bag );
		}
	}

	private static function cache_key( string $url ): string {
		$freshness = self::freshness_signal( $url );
		return 'avb_vtt_' . md5( $url . '|' . $freshness );
	}

	private static function freshness_signal( string $url ): string {
		$attachment_id = attachment_url_to_postid( $url );
		if ( $attachment_id > 0 ) {
			return (string) get_post_modified_time( 'U', true, $attachment_id );
		}
		$head = wp_safe_remote_head( $url, [ 'timeout' => 3 ] );
		if ( is_wp_error( $head ) ) {
			return '';
		}
		$etag          = wp_remote_retrieve_header( $head, 'etag' );
		$last_modified = wp_remote_retrieve_header( $head, 'last-modified' );
		return (string) ( $etag !== '' ? $etag : $last_modified );
	}
}
