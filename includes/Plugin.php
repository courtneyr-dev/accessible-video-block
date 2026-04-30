<?php
declare( strict_types=1 );

namespace CourtneyR\AccessibleVideoBlock;

final class Plugin {

	public function register(): void {
		( new DependencyGuard() )->register();
		add_action( 'init', [ $this, 'load_textdomain' ] );
		add_action( 'init', [ $this, 'register_block' ] );
	}

	public function load_textdomain(): void {
		load_plugin_textdomain(
			'accessible-video-block',
			false,
			dirname( plugin_basename( COURTNEYR_AVB_FILE ) ) . '/languages'
		);
	}

	public function register_block(): void {
		$build_dir = COURTNEYR_AVB_DIR . 'build';
		if ( ! is_dir( $build_dir ) ) {
			return;
		}
		register_block_type( $build_dir );
	}
}
