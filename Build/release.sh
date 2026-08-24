#!/usr/bin/env bash

NEW_RELEASE=$1
DIR=$(pwd)

# shellcheck source=Build/composer-env.sh
source "$(dirname "${BASH_SOURCE[0]}")/composer-env.sh"


if [ -n "${NEW_RELEASE}" ]; then
  NEW_RELEASE=v${NEW_RELEASE}
  echo "Create release of version ${NEW_RELEASE}"
else
  echo "No version defined. Exit."
  exit 1;
fi

EXISTS=$(git describe --contains "${NEW_RELEASE}" 2>&1)

if [[ "$EXISTS" == "$NEW_RELEASE" ]]; then
  echo "Release already exists. Exit."
  exit 1;
fi

if [ "$DIR" == "$(git rev-parse --show-toplevel 2>/dev/null)" ]; then
  resolve_composer_context || exit 1

  echo "Checking Libraries/composer.json..."
  "$DIR/Build/check-libraries.sh" || exit 1

  echo "Installing composer dependencies..."
  run_composer "$DIR/Libraries" install --no-dev --no-progress --quiet || exit 1
  echo "Done."

  echo "Create git tag"
  cd "$DIR" || exit 1
  git tag "$NEW_RELEASE"

  echo "Archive repository..."
  zip -r "../mautic_${1}.zip" ./* -x Build/\* Documentation/\* Libraries/composer.\* README.md
  echo "Done."

  echo "Remove composer dependencies..."
  rm -rf "$DIR/Libraries/vendor"
  echo "Done."

  echo "Please add and push the git tag: gp --tags"
else
  echo "This script has to be executed from the git root directory!"
  exit 1;
fi

exit 0;
