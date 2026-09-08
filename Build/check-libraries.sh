#!/usr/bin/env bash
#
# Verifies that Libraries/composer.json declares the same runtime dependencies
# as the root composer.json. Libraries/ is only consumed by TYPO3 Classic Mode
# installations (TER package); Composer installations resolve the root
# composer.json instead. Both must agree, otherwise the TER package ships
# different dependency versions than the Composer package.
#
# Packages already shipped by TYPO3 Core must NOT be vendored into
# Libraries/vendor/ - they are listed under "replace" in Libraries/composer.json
# and are therefore expected to be absent here.

DIR=$(git rev-parse --show-toplevel 2>/dev/null)

# shellcheck source=Build/composer-env.sh
source "$(dirname "${BASH_SOURCE[0]}")/composer-env.sh"

if [ -z "$DIR" ]; then
  echo "Not inside a git repository. Exit."
  exit 1;
fi

ROOT_MANIFEST="$DIR/composer.json"
LIBRARIES_MANIFEST="$DIR/Libraries/composer.json"

# Runtime dependencies the TER package has to ship itself: everything from the
# root manifest except PHP platform requirements, PHP extensions, TYPO3 Core
# packages and everything Libraries/composer.json declares as provided by the
# TYPO3 environment via "replace".
PROVIDED_BY_ENVIRONMENT=$(jq -S '.replace // {} | keys' "$LIBRARIES_MANIFEST")

VENDORED_REQUIREMENTS=$(jq -S --argjson provided "$PROVIDED_BY_ENVIRONMENT" '.require
  | with_entries(select(
      (.key | test("^(php$|ext-|typo3/)")) or
      ([.key] | inside($provided))
    | not))' "$ROOT_MANIFEST")

DECLARED_REQUIREMENTS=$(jq -S '.require' "$LIBRARIES_MANIFEST")

if [ "$VENDORED_REQUIREMENTS" != "$DECLARED_REQUIREMENTS" ]; then
  echo "Libraries/composer.json is out of sync with composer.json."
  echo "Expected requirements:"
  echo "$VENDORED_REQUIREMENTS"
  echo "Declared requirements:"
  echo "$DECLARED_REQUIREMENTS"
  exit 1;
fi

# The platform override pins the PHP version Libraries/vendor is resolved
# against. It has to match the lowest PHP version the extension supports,
# otherwise the TER package ships dependencies the target environment cannot run.
ROOT_PHP_FLOOR=$(jq -r '.require.php' "$ROOT_MANIFEST" | grep -oE '[0-9]+\.[0-9]+' | head -1)
LIBRARIES_PHP_PLATFORM=$(jq -r '.config.platform.php' "$LIBRARIES_MANIFEST" | grep -oE '^[0-9]+\.[0-9]+')

if [ "$ROOT_PHP_FLOOR" != "$LIBRARIES_PHP_PLATFORM" ]; then
  echo "PHP version mismatch: composer.json requires ${ROOT_PHP_FLOOR}, Libraries/composer.json resolves against ${LIBRARIES_PHP_PLATFORM}."
  exit 1;
fi

if [ ! -f "$DIR/Libraries/composer.lock" ]; then
  echo "Libraries/composer.lock is missing. Exit."
  exit 1;
fi

resolve_composer_context || exit 1
run_composer "$DIR/Libraries" validate --no-check-all --no-check-publish || exit 1

echo "Libraries/composer.json is in sync with composer.json."

exit 0;
