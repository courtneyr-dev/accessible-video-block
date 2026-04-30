<?php
declare( strict_types=1 );

namespace CourtneyR\AccessibleVideoBlock;

final class ShortcodeBuilder {

	/**
	 * Build an [ableplayer] shortcode string from an attribute map.
	 *
	 * Drop rules:
	 *   - null, empty string, and bool false are dropped (false is Able Player's
	 *     default for nearly every flag, so emitting it adds noise).
	 *   - Bool true emits the literal string "true".
	 *   - Integer 0 is preserved (volume=0 is meaningful).
	 *
	 * Quoting:
	 *   - Every value is wrapped in double quotes so multi-word values, pipe-separator
	 *     track values, and colons survive WP's shortcode parser unchanged.
	 *   - Inner double quotes are encoded as &quot; — shortcode_atts decodes correctly.
	 *
	 * @param array<string, scalar|null> $attrs Attribute name => value.
	 */
	public static function build( array $attrs ): string {
		$parts = [];
		foreach ( $attrs as $key => $value ) {
			if ( $value === null || $value === '' || $value === false ) {
				continue;
			}
			if ( $value === true ) {
				$value = 'true';
			}
			$escaped = str_replace( '"', '&quot;', (string) $value );
			$parts[] = sprintf( '%s="%s"', $key, $escaped );
		}
		return '[ableplayer' . ( $parts ? ' ' . implode( ' ', $parts ) : '' ) . ']';
	}
}
