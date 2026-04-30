<?php
declare( strict_types=1 );

namespace CourtneyR\AccessibleVideoBlock;

final class DependencyGuard {

	private const ABLE_PLAYER_BASENAME = 'ableplayer/ableplayer.php';

	public function register(): void {
		add_action( 'admin_notices', [ $this, 'maybe_admin_notice' ] );
	}

	public function is_able_player_active(): bool {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return is_plugin_active( self::ABLE_PLAYER_BASENAME );
	}

	public function maybe_admin_notice(): void {
		if ( $this->is_able_player_active() ) {
			return;
		}
		$install_url = wp_nonce_url(
			self_admin_url( 'update.php?action=install-plugin&plugin=ableplayer' ),
			'install-plugin_ableplayer'
		);
		printf(
			'<div class="notice notice-warning"><p><strong>%s</strong> %s <a href="%s">%s</a></p></div>',
			esc_html__( 'Accessible Video Block:', 'accessible-video-block' ),
			esc_html__( 'requires the Able Player plugin to render its blocks. Until it is active, blocks will fall back to native HTML5 media.', 'accessible-video-block' ),
			esc_url( $install_url ),
			esc_html__( 'Install Able Player', 'accessible-video-block' )
		);
	}
}
