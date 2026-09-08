#!/usr/bin/env bash
#
# Determines how Composer is invoked and provides run_composer() for it.
#
# Releases are usually built by developers on a DDEV host. Running Composer
# inside the web container guarantees the PHP version the extension actually
# targets, instead of whatever happens to be installed on the host. Inside the
# container and in CI there is no DDEV to delegate to, so the local Composer is
# already the correct one.
#
# Source this file, then call resolve_composer_context once.

DDEV_CONTAINER_ROOT=/var/www/html

resolve_composer_context() {
  if [ -z "${IS_DDEV_PROJECT}" ] && command -v ddev >/dev/null 2>&1; then
    DDEV_APPROOT=$(ddev describe --json-output 2>/dev/null | jq -r '.raw.approot // empty')
  fi

  if [ -n "${DDEV_APPROOT}" ]; then
    COMPOSER_CONTEXT="ddev"

    if [ "$(ddev describe --json-output 2>/dev/null | jq -r '.raw.status // empty')" != "running" ]; then
      echo "DDEV project ${DDEV_APPROOT} is not running. Run 'ddev start' first. Exit."
      return 1;
    fi
  else
    COMPOSER_CONTEXT="local"
  fi

  echo "Using composer context: ${COMPOSER_CONTEXT}"
}

# run_composer <host working directory> <composer arguments...>
run_composer() {
  local working_directory=$1
  shift

  if [ "${COMPOSER_CONTEXT}" == "ddev" ]; then
    # ddev exec does not inherit the host working directory, so the host path
    # has to be translated into its counterpart inside the web container.
    ddev exec -d "${DDEV_CONTAINER_ROOT}${working_directory#"${DDEV_APPROOT}"}" composer "$@"
    return $?
  fi

  composer "$@" --working-dir="${working_directory}"
}
