# Accessible Video Block — agent guide

Block-authoring layer for WordPress that emits `[ableplayer]` shortcodes from a Gutenberg UI. Wraps Joe Dolson's Able Player plugin (`ableplayer` on WP.org) — does NOT bundle it, register shortcodes, or manage its assets.

## Don't do these

- **No `add_shortcode()`** anywhere in the plugin. We consume Able Player's shortcode via `do_shortcode()`. Verify with `grep -r "add_shortcode" .` — should return nothing.
- **No bundled Able Player assets.** Joe's plugin is a hard dep declared via WP 6.5+ `Requires Plugins:` header; if missing, `includes/FallbackRenderer.php` emits plain HTML5 `<video>`/`<audio>` plus the inline transcript wrapper.
- **No override of `core/video`, `core/audio`, or `core/embed`.** This block is opt-in by inserter choice.
- **No tracking, telemetry, external API calls, ActivityPub/POSSE behavior, or auto-fetched YouTube transcripts.** Content authoring stays manual.
- **No vendor copies of Automattic / Awesome Motive / Freemius / Jetpack code.** Same vendor discipline as the rest of the courtneyr.dev plugin set.

## Architecture quick map

- **Single dynamic block** registered from `src/block.json`, server-rendered via `includes/render-shim.php` → `includes/BlockRenderer.php`
- **Hand-rolled PSR-4 autoloader** in `accessible-video-block.php` (~11 lines). The `vendor/` directory is dev-only (PHPUnit). Production never relies on composer's autoloader because the GoDaddy rsync deploy doesn't run `composer install`.
- **`build/` is committed** for the same reason — deploy host doesn't run `npm run build`.
- **Two pure-function units have PHPUnit tests:** `ShortcodeBuilder` and `VttToProse`. Everything else is integration-tested manually.

## Build / test commands

```bash
composer install              # PHPUnit only; runtime never reads composer's autoload
npm install
npm run build                 # produces /build/ — must be committed
vendor/bin/phpunit            # 18 tests as of v0.1.0
```

## Composer platform pin — DO NOT REMOVE

`composer.json` has `config.platform.php = "7.4"`. The lockfile must resolve as if running on PHP 7.4 (the `Requires PHP` floor) so CI's matrix (7.4 → 8.4) can install identically. Without the pin, dev machines on PHP 8.4 will lock packages that require ≥8.4 (e.g. `doctrine/instantiator` 2.1.0) and break every other matrix job.

If you ever need to regenerate the lockfile: `rm -rf vendor composer.lock && composer install` — the pin ensures portability.

## Sync to deploy repo

This is the canonical source. The deployable copy lives at `~/Documents/courtneyr-dev-site/wp-content/plugins/accessible-video-block/`. Workflow:

```bash
DRY_RUN=1 bin/sync.sh     # preview the rsync
bin/sync.sh               # one-way: canonical → deploy
```

The script refuses to write into a directory that doesn't already contain `accessible-video-block.php` — defensive guard against typo'd paths.

After syncing, in the deploy repo: stage the changes, commit, push to a branch, smoke-test, then merge to `main` (which triggers the GoDaddy rsync action and deploys to production immediately — no staging gate).

## Tunable: VTT cue-collapsing heuristic

`includes/VttToProse.php` lines ~103-149. Decides when to break a paragraph in a transcript derived from a WebVTT track. Current rule:

- Same speaker → always collapse (per spec)
- Different speaker → break
- No speakers anywhere → break only on `.!?` followed by uppercase / digit / opening quote/paren

Fixtures live at `tests/fixtures/*.vtt`. PHPUnit will tell you what broke if you change the rule.

## CI

`.github/workflows/test.yml` runs PHPUnit across PHP 7.4 / 8.0 / 8.1 / 8.2 / 8.3 / 8.4 with `fail-fast: false`, plus a PHP 7.4 syntax check and a `npm run build` verification job that catches forgotten rebuilds before merge.
