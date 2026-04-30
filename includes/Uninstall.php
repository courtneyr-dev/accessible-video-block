<?php
declare( strict_types=1 );

namespace CourtneyR\AccessibleVideoBlock;

final class Uninstall {

	public static function run(): void {
		global $wpdb;
		// Sweep transcript cache post meta. We never wrote options or rows to
		// custom tables, so this is the only thing to clean up.
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s",
				'_avb_transcript_cache'
			)
		);
	}
}
