=== Accessible Video Block ===
Contributors: courtneyr
Tags: video, audio, accessibility, transcript, captions, able-player, gutenberg, block
Requires at least: 6.5
Tested up to: 6.7
Requires PHP: 7.4
Requires Plugins: ableplayer
Stable tag: 0.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A Gutenberg block that renders Joe Dolson's Able Player from a structured editor UI, with a JS-free inline transcript wrapper.

== Description ==

Accessible Video Block provides a single Gutenberg block that wraps Joe Dolson's [Able Player](https://wordpress.org/plugins/ableplayer/) plugin behind a structured editor UI. Pick a self-hosted media file, a YouTube ID, or a Vimeo ID; attach captions, subtitles, chapters, and audio descriptions; choose a transcript mode; and the plugin emits the `[ableplayer]` shortcode with the right attributes server-side.

The Able Player plugin is a hard runtime dependency. Without it, the block falls back to native HTML5 video/audio or a "Watch on YouTube/Vimeo" link so content isn't lost.

= Features =

* Three source modes: media library, YouTube, Vimeo.
* Track support: captions, subtitles (multiple languages), chapters, audio descriptions, sign-language video.
* Three transcript modes:
  * Inline rich text via inner blocks (default, JS-free).
  * Server-derived from a VTT track, with paragraphs grouped by speaker or sentence (JS-free).
  * Able Player's built-in transcript pane (requires JS, supports chapter navigation).
* Privacy: defaults to youtube-nocookie.com.
* No Able Player vendor assets bundled. No shortcode registered. No telemetry. No external API calls.

= Why this exists =

If you're already running Joe Dolson's Able Player plugin and prefer block editing to hand-written shortcodes, this fills the gap. It does not replace, fork, or modify Able Player.

== Installation ==

1. Install and activate Joe Dolson's [Able Player plugin](https://wordpress.org/plugins/ableplayer/) (required).
2. Install Accessible Video Block.
3. WordPress 6.5+ enforces the dependency: activation will fail without Able Player active first.
4. Insert the "Accessible Video" block from the inserter under Media.

== Frequently Asked Questions ==

= Does this bundle Able Player? =

No. Able Player is a hard external dependency. We never bundle, fork, or vendor its assets.

= Does it register a shortcode? =

No. We *consume* Able Player's `[ableplayer]` shortcode via `do_shortcode()`. We never define one of our own.

= Will the transcript work without JavaScript? =

Yes for the default inline mode and the VTT-derived mode. The "Able Player native" transcript mode requires JavaScript.

== Changelog ==

= 0.1.0 =
* Initial release.
