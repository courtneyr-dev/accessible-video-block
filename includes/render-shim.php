<?php
declare( strict_types=1 );

/**
 * Render shim referenced by block.json's "render" key.
 *
 * @var array<string, mixed> $attributes
 * @var string               $content
 * @var \WP_Block            $block
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo \CourtneyR\AccessibleVideoBlock\BlockRenderer::render(
	$attributes ?? [],
	$content ?? '',
	$block ?? null
);
