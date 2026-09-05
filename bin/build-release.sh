#!/usr/bin/env bash
#
# Assembles an upload-ready copy of the app in a temp directory.
#
# Shared hosting has no Node and usually no Composer, so both front-end builds
# and the production dependency install happen here. The work is done in a copy
# so the working tree keeps its dev dependencies and stays testable.
#
# Usage: bin/build-release.sh [output-directory]

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DEST="${1:-$(mktemp -d)/tuxcms}"

cd "$ROOT"

echo "==> Building site CSS"
npm run build --silent

echo "==> Building dashboard"
(cd dashboard && npm run build --silent)

echo "==> Copying application into $DEST"
rm -rf "$DEST" && mkdir -p "$DEST"

# Everything git tracks, minus what a server has no use for. Note the prefixes
# are deliberately unanchored at the end: 'tests/' must match every file under
# it, not just the literal directory name.
git ls-files -z \
  | grep -zvE '^(tests/|dashboard/|bin/|\.github/|\.claude/)|^(phpunit\.xml|architecture\.html|[^/]*\.md)$' \
  | rsync -a --files-from=- --from0 ./ "$DEST/"

# Gitignored, but precisely what the server cannot build for itself.
rsync -a public/build public/admin "$DEST/public/"

# Writable directories Laravel expects to already exist.
mkdir -p "$DEST"/storage/{app/public,framework/{cache/data,sessions,views},logs}
mkdir -p "$DEST"/bootstrap/cache

echo "==> Installing production dependencies"
(cd "$DEST" && composer install --no-dev --optimize-autoloader --no-interaction --quiet)

# A stray .env here would ship live credentials to the server.
if [ -e "$DEST/.env" ]; then
    echo "ERROR: .env made it into the release. Refusing to continue." >&2
    exit 1
fi

echo
echo "Release ready: $DEST"
du -sh "$DEST" | awk '{print "Size:         " $1}'
