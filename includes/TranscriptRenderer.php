<?php
declare( strict_types=1 );

namespace CourtneyR\AccessibleVideoBlock;

final class TranscriptRenderer {

	public static function render( array $attrs, string $inner_content, int $post_id ): string {
		$mode = $attrs['transcriptMode'] ?? 'innerblocks';

		switch ( $mode ) {
			case 'innerblocks':
				return self::render_innerblocks( $inner_content );

			case 'vtt':
				return self::render_from_vtt( $attrs, $post_id );

			case 'native':
				return self::render_native_target( $attrs );
		}

		return '';
	}

	private static function render_innerblocks( string $inner_content ): string {
		if ( trim( $inner_content ) === '' ) {
			return '';
		}
		return self::wrap( $inner_content );
	}

	private static function render_from_vtt( array $attrs, int $post_id ): string {
		$url = self::resolve_vtt_url( $attrs );
		if ( $url === null ) {
			return '';
		}
		$paragraphs = TranscriptCache::get( $url, $post_id );
		if ( $paragraphs === null ) {
			$vtt = self::fetch_vtt( $url );
			if ( $vtt === null ) {
				return '';
			}
			$paragraphs = VttToProse::parse( $vtt );
			TranscriptCache::set( $url, $post_id, $paragraphs );
		}
		if ( $paragraphs === [] ) {
			return '';
		}

		$body = '';
		foreach ( $paragraphs as $paragraph ) {
			$body .= '<p>' . wp_kses_post( $paragraph ) . '</p>';
		}
		return self::wrap( $body );
	}

	private static function render_native_target( array $attrs ): string {
		$anchor = ! empty( $attrs['anchor'] )
			? sanitize_html_class( (string) $attrs['anchor'] )
			: substr( md5( (string) wp_json_encode( $attrs ) ), 0, 8 );
		return sprintf(
			'<div id="%s" class="avb-transcript avb-transcript--native"></div>',
			esc_attr( 'avb-transcript-' . $anchor )
		);
	}

	private static function resolve_vtt_url( array $attrs ): ?string {
		$source = $attrs['transcriptVttSource'] ?? 'captions';
		$track  = null;

		if ( $source === 'captions' ) {
			$track = $attrs['captions'] ?? null;
		} elseif ( $source === 'chapters' ) {
			$track = $attrs['chapters'] ?? null;
		} elseif ( strpos( (string) $source, 'subtitle:' ) === 0 ) {
			$index = (int) substr( (string) $source, strlen( 'subtitle:' ) );
			$track = $attrs['subtitles'][ $index ] ?? null;
		}

		if ( ! is_array( $track ) ) {
			return null;
		}
		if ( ! empty( $track['id'] ) ) {
			$url = wp_get_attachment_url( (int) $track['id'] );
			return is_string( $url ) ? $url : null;
		}
		if ( ! empty( $track['url'] ) ) {
			return esc_url_raw( (string) $track['url'] );
		}
		return null;
	}

	private static function fetch_vtt( string $url ): ?string {
		$response = wp_safe_remote_get(
			$url,
			[
				'timeout'    => 5,
				'user-agent' => 'AccessibleVideoBlock/' . COURTNEYR_AVB_VERSION,
			]
		);
		if ( is_wp_error( $response ) ) {
			return null;
		}
		$code = (int) wp_remote_retrieve_response_code( $response );
		if ( $code < 200 || $code >= 300 ) {
			return null;
		}
		$body = wp_remote_retrieve_body( $response );
		return is_string( $body ) && $body !== '' ? $body : null;
	}

	private static function wrap( string $body ): string {
		return sprintf(
			'<details class="avb-transcript"><summary>%s</summary><div class="avb-transcript__body">%s</div></details>',
			esc_html__( 'View transcript', 'accessible-video-block' ),
			$body
		);
	}
}
