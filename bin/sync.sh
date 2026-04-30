#!/usr/bin/env bash
# Sync this repo into the courtneyr-dev-site deploy repo's plugins/ directory.
#
# Direction: standalone (canonical) -> deploy. One-way. Never reverse.
#
# Usage:
#   bin/sync.sh                 # sync to default deploy path
#   bin/sync.sh /path/to/site   # sync to a custom path
#   DRY_RUN=1 bin/sync.sh       # preview without changing anything
#
# The default deploy path is the courtneyr-dev-site clone at
# ~/Documents/courtneyr-dev-site. Override with the first arg or AVB_DEPLOY_PATH.

set -euo pipefail

REPO_ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )/.." && pwd )"
DEFAULT_DEPLOY="$HOME/Documents/courtneyr-dev-site/wp-content/plugins/accessible-video-block"
DEPLOY_PATH="${1:-${AVB_DEPLOY_PATH:-$DEFAULT_DEPLOY}}"

if [[ ! -d "$REPO_ROOT/includes" ]]; then
	echo "error: $REPO_ROOT does not look like the accessible-video-block repo" >&2
	exit 1
fi

if [[ ! -d "$DEPLOY_PATH" ]]; then
	echo "error: deploy path does not exist: $DEPLOY_PATH" >&2
	echo "       create it first, or pass a different path as the first argument" >&2
	exit 1
fi

# Safety check: refuse to write into a path that doesn't already contain
# a recognizable copy of the plugin. Avoids accidentally nuking an
# unrelated directory.
if [[ ! -f "$DEPLOY_PATH/accessible-video-block.php" ]]; then
	echo "error: $DEPLOY_PATH does not contain accessible-video-block.php" >&2
	echo "       refusing to sync into an unrelated directory" >&2
	exit 1
fi

RSYNC_FLAGS=(
	--archive
	--delete
	--itemize-changes
	--exclude=.git
	--exclude=node_modules
	--exclude=vendor
	--exclude=.phpunit.cache
	--exclude=.phpunit.result.cache
	--exclude=.DS_Store
)

if [[ "${DRY_RUN:-0}" == "1" ]]; then
	RSYNC_FLAGS+=( --dry-run )
	echo "DRY RUN — no changes will be applied"
fi

echo "syncing $REPO_ROOT/  ->  $DEPLOY_PATH/"
rsync "${RSYNC_FLAGS[@]}" "$REPO_ROOT/" "$DEPLOY_PATH/"

if [[ "${DRY_RUN:-0}" != "1" ]]; then
	echo
	echo "done. Now in the deploy repo, review and commit:"
	echo "  cd \"$( dirname "$( dirname "$DEPLOY_PATH" )" )/../..\""
	echo "  git status wp-content/plugins/accessible-video-block"
	echo "  git diff wp-content/plugins/accessible-video-block"
fi
