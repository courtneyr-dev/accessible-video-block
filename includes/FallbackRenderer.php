<?php
declare( strict_types=1 );

namespace CourtneyR\AccessibleVideoBlock;

final class FallbackRenderer {

	public static function render( array $attrs, string $inner_content ): string {
		$source_mode = $attrs['sourceMode'] ?? 'media';

		$body = '';
		switch ( $source_mode ) {
			case 'youtube':
				$body = self::youtube_link( $attrs );
				break;
			case 'vimeo':
				$body = self::vimeo_link( $attrs );
				break;
			case 'media':
			default:
				$body = self::native_media( $attrs );
				break;
		}

		$transcript = '';
		if ( trim( $inner_content ) !== '' ) {
			$transcript = sprintf(
				'<details class="avb-transcript"><summary>%s</summary><div class="avb-transcript__body">%s</div></details>',
				esc_html__( 'View transcript', 'accessible-video-block' ),
				$inner_content
			);
		}

		return sprintf(
			"<!-- accessible-video-block: Able Player plugin not active; rendering fallback -->\n" .
			'<figure class="wp-block-courtneyr-accessible-video is-fallback">%s%s</figure>',
			$body,
			$transcript
		);
	}

	private static function native_media( array $attrs ): string {
		$url  = (string) ( $attrs['mediaUrl'] ?? '' );
		$mime = (string) ( $attrs['mediaMime'] ?? '' );
		if ( $url === '' ) {
			return '';
		}
		$tag = strpos( $mime, 'audio/' ) === 0 ? 'audio' : 'video';
		return sprintf(
			'<%1$s controls src="%2$s">%3$s</%1$s>',
			$tag,
			esc_url( $url ),
			esc_html__( 'Your browser does not support this media element.', 'accessible-video-block' )
		);
	}

	private static function youtube_link( array $attrs ): string {
		$id = (string) ( $attrs['youtubeId'] ?? '' );
		if ( $id === '' ) {
			return '';
		}
		$url = sprintf( 'https://www.youtube.com/watch?v=%s', rawurlencode( $id ) );
		return sprintf(
			'<a class="avb-fallback-link" href="%s" rel="external noopener">%s</a>',
			esc_url( $url ),
			esc_html__( 'Watch on YouTube', 'accessible-video-block' )
		);
	}

	private static function vimeo_link( array $attrs ): string {
		$id = (string) ( $attrs['vimeoId'] ?? '' );
		if ( $id === '' ) {
			return '';
		}
		$url = sprintf( 'https://vimeo.com/%s', rawurlencode( $id ) );
		return sprintf(
			'<a class="avb-fallback-link" href="%s" rel="external noopener">%s</a>',
			esc_url( $url ),
			esc_html__( 'Watch on Vimeo', 'accessible-video-block' )
		);
	}
}
