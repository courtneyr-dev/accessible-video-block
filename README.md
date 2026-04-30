# Accessible Video Block

Gutenberg block that renders Joe Dolson's [Able Player](https://wordpress.org/plugins/ableplayer/) plugin from a structured editor UI, with a JS-free inline transcript wrapper.

**Able Player is a hard dependency.** This plugin is a block-authoring layer only — it does not bundle Able Player, register shortcodes, or manage runtime assets.

## Architecture

```
src/                Editor source (React/JSX, built with @wordpress/scripts)
build/              Committed build artifacts — deploy host doesn't run npm
includes/           PHP runtime
  Plugin.php           Bootstrap, hook registration
  DependencyGuard.php  plugins_loaded check + admin notice
  BlockRenderer.php    render_callback entry — maps block attrs → shortcode
  ShortcodeBuilder.php Pure: attrs array → "[ableplayer …]" string  ← UNIT TESTED
  TranscriptRenderer.php  Picks mode 1 / 2 / 3
  VttToProse.php       Pure: VTT text → array of paragraphs        ← UNIT TESTED
  TranscriptCache.php  Object cache + post meta, keyed on URL+freshness
  FallbackRenderer.php When Able Player is missing
  UrlParser.php        Server-side YouTube/Vimeo ID extraction
  Uninstall.php        Clean up post meta on uninstall
tests/              PHPUnit unit tests for the two pure-function classes
```

## Build

```sh
composer install        # PHPUnit + autoload
npm install             # @wordpress/scripts
npm run build           # produces build/
vendor/bin/phpunit      # 18 tests
```

For deploy, the `build/` directory MUST be committed — the deploy target does not run npm.

## Development

* `npm start` — webpack watch mode for editor JS.
* `vendor/bin/phpunit` — runs `ShortcodeBuilderTest` and `VttToProseTest`.
* All UI integration is verified manually; see the test plan in `docs/superpowers/plans/`.

## Contributing

Source layout follows PSR-4. Code style: tabs, WordPress phpcs conventions for PHP, `@wordpress/scripts` defaults for JS.
