<?php
declare( strict_types=1 );

namespace CourtneyR\AccessibleVideoBlock;

final class BlockRenderer {

	/**
	 * Able Player attribute defaults we drop from output to keep shortcodes lean.
	 * Every entry here matches Able Player's documented default — if the block
	 * attribute equals it, we don't emit it.
	 */
	private const ABLE_PLAYER_DEFAULTS = [
		'volume'      => 7,
		'speed'       => 'animals',
		'playsinline' => true,
	];

	public static function render( array $attrs, string $inner_content, $block ): string {
		$guard = new DependencyGuard();
		if ( ! $guard->is_able_player_active() ) {
			return FallbackRenderer::render( $attrs, $inner_content );
		}

		$shortcode_attrs = self::map_attrs( $attrs, $block );
		$shortcode       = ShortcodeBuilder::build( $shortcode_attrs );
		$player_html     = do_shortcode( $shortcode );

		$post_id         = self::resolve_post_id( $block );
		$transcript_html = TranscriptRenderer::render( $attrs, $inner_content, $post_id );

		return sprintf(
			'<figure class="wp-block-courtneyr-accessible-video">%s%s</figure>',
			$player_html,
			$transcript_html
		);
	}

	private static function resolve_post_id( $block ): int {
		if ( is_object( $block ) && isset( $block->context['postId'] ) ) {
			return (int) $block->context['postId'];
		}
		return (int) ( get_the_ID() ?: 0 );
	}

	/**
	 * Convert block attributes to an Able Player shortcode attribute map.
	 */
	private static function map_attrs( array $attrs, $block ): array {
		$source_mode = $attrs['sourceMode'] ?? 'media';
		$out         = [];

		switch ( $source_mode ) {
			case 'youtube':
				$id = UrlParser::youtube_id( (string) ( $attrs['youtubeId'] ?? '' ) );
				if ( $id !== null ) {
					$out['youtube-id'] = $id;
					if ( ! empty( $attrs['youtubeNoCookie'] ) ) {
						$out['youtube-nocookie'] = true;
					}
					if ( ! empty( $attrs['youtubeDescId'] ) ) {
						$desc = UrlParser::youtube_id( (string) $attrs['youtubeDescId'] );
						if ( $desc !== null ) {
							$out['youtube-desc-id'] = $desc;
						}
					}
					if ( ! empty( $attrs['youtubeSignSrc'] ) ) {
						$out['youtube-sign-src'] = esc_url_raw( (string) $attrs['youtubeSignSrc'] );
					}
				}
				break;

			case 'vimeo':
				$id = UrlParser::vimeo_id( (string) ( $attrs['vimeoId'] ?? '' ) );
				if ( $id !== null ) {
					$out['vimeo-id'] = $id;
					if ( ! empty( $attrs['vimeoDescId'] ) ) {
						$desc = UrlParser::vimeo_id( (string) $attrs['vimeoDescId'] );
						if ( $desc !== null ) {
							$out['vimeo-desc-id'] = $desc;
						}
					}
				}
				break;

			case 'media':
			default:
				if ( ! empty( $attrs['mediaId'] ) ) {
					$out['id'] = (int) $attrs['mediaId'];
				}
				break;
		}

		// Tracks (pipe-separator format: id-or-url|lang|label).
		if ( ! empty( $attrs['captions'] ) && is_array( $attrs['captions'] ) ) {
			$pipe = self::track_to_pipe_string( $attrs['captions'] );
			if ( $pipe !== '' ) {
				$out['captions'] = $pipe;
			}
		}
		if ( ! empty( $attrs['chapters'] ) && is_array( $attrs['chapters'] ) ) {
			$pipe = self::track_to_pipe_string( $attrs['chapters'] );
			if ( $pipe !== '' ) {
				$out['chapters'] = $pipe;
			}
		}
		if ( ! empty( $attrs['descriptions'] ) && is_array( $attrs['descriptions'] ) ) {
			$pipe = self::track_to_pipe_string( $attrs['descriptions'] );
			if ( $pipe !== '' ) {
				$out['descriptions'] = $pipe;
			}
		}
		// Subtitles: ship the first one only for v0.1; document that multi-lang
		// support depends on Able Player's shortcode behavior (see plan Phase 5).
		if ( ! empty( $attrs['subtitles'] ) && is_array( $attrs['subtitles'] ) ) {
			$first = reset( $attrs['subtitles'] );
			if ( is_array( $first ) ) {
				$pipe = self::track_to_pipe_string( $first );
				if ( $pipe !== '' ) {
					$out['subtitles'] = $pipe;
				}
			}
		}

		// Player options — emit only when not equal to the documented default.
		$option_map = [
			'autoplay'     => 'autoplay',
			'loop'         => 'loop',
			'playsinline'  => 'playsinline',
			'hidecontrols' => 'hidecontrols',
			'nowplaying'   => 'nowplaying',
			'speed'        => 'speed',
			'volume'       => 'volume',
			'start'        => 'start',
			'seekinterval' => 'seekinterval',
			'heading'      => 'heading',
			'width'        => 'width',
			'height'       => 'height',
		];
		foreach ( $option_map as $block_key => $sc_key ) {
			if ( ! array_key_exists( $block_key, $attrs ) ) {
				continue;
			}
			$value = $attrs[ $block_key ];
			if ( array_key_exists( $sc_key, self::ABLE_PLAYER_DEFAULTS )
				&& $value === self::ABLE_PLAYER_DEFAULTS[ $sc_key ] ) {
				continue;
			}
			$out[ $sc_key ] = $value;
		}

		// Poster image (separate handling — we want the URL not the ID).
		if ( ! empty( $attrs['posterId'] ) ) {
			$poster = wp_get_attachment_image_url( (int) $attrs['posterId'], 'full' );
			if ( is_string( $poster ) && $poster !== '' ) {
				$out['poster'] = $poster;
			}
		} elseif ( ! empty( $attrs['posterUrl'] ) ) {
			$out['poster'] = esc_url_raw( (string) $attrs['posterUrl'] );
		}

		// Native transcript mode hooks Able Player's transcript-div feature.
		if ( ( $attrs['transcriptMode'] ?? '' ) === 'native' ) {
			$anchor = self::resolve_anchor( $attrs );
			$out['transcript-div'] = 'avb-transcript-' . $anchor;
		}

		return $out;
	}

	private static function track_to_pipe_string( array $track ): string {
		$value = '';
		if ( ! empty( $track['id'] ) ) {
			$value = (string) (int) $track['id'];
		} elseif ( ! empty( $track['url'] ) ) {
			$value = esc_url_raw( trim( (string) $track['url'] ) );
		}
		if ( $value === '' ) {
			return '';
		}
		$lang  = sanitize_text_field( (string) ( $track['lang']  ?? '' ) );
		$label = sanitize_text_field( (string) ( $track['label'] ?? '' ) );
		return $value . '|' . $lang . '|' . $label;
	}

	private static function resolve_anchor( array $attrs ): string {
		if ( ! empty( $attrs['anchor'] ) ) {
			return sanitize_html_class( (string) $attrs['anchor'] );
		}
		return substr( md5( wp_json_encode( $attrs ) ), 0, 8 );
	}
}
