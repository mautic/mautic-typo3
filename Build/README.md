# Build

Release tooling for EXT:mautic. Not part of the distributed extension —
`Build/` is `export-ignore`d and excluded from the TER archive.

## Scripts

| Script | Purpose |
|---|---|
| `release.sh <version>` | Builds a release: verifies `Libraries/`, vendors its dependencies, creates the git tag, writes `../mautic_<version>.zip`. |
| `check-libraries.sh` | Verifies that `Libraries/composer.json` still matches the root `composer.json`. Run by `release.sh` and by CI. |
| `composer-env.sh` | Sourced by the other two. Decides whether Composer runs via DDEV or locally. |

## Creating a release

Bump the version in `ext_emconf.php` and `composer.json` first, then from the
repository root:

```bash
./Build/release.sh 12.0.1
git push --tags
```

The script refuses to run outside the repository root and refuses to overwrite
an existing tag. The tag is created locally only — pushing it is a separate,
deliberate step.

## Libraries/

TYPO3 resolves the extension's dependencies from the root `composer.json`.
Classic Mode installations cannot, so `Libraries/vendor/` ships them inside the
TER archive and `ext_localconf.php` registers its autoloader.

Only packages TYPO3 Core does *not* provide belong there. Everything the Core
already ships is listed under `replace` in `Libraries/composer.json` to keep it
out of `Libraries/vendor/`, because a second copy in the autoloader causes
version conflicts at runtime — currently that leaves `mautic/api-library`
alone.

Both manifests therefore declare the same runtime requirements and must stay in
sync; `check-libraries.sh` enforces this in both directions. After changing a
dependency, update `Libraries/composer.json` and refresh its lock file:

```bash
source Build/composer-env.sh && resolve_composer_context
run_composer "$(pwd)/Libraries" update
rm -rf Libraries/vendor
```

Commit the regenerated `Libraries/composer.lock`; `Libraries/vendor/` is
generated at release time and stays untracked.

## Composer context

`composer-env.sh` picks the right Composer automatically:

- **DDEV host** — runs inside the web container, so the PHP version matches the
  target environment. Requires the project to be started.
- **Inside the container, or CI** — uses the local Composer.

Note that `ddev exec` does not inherit the host working directory; the script
translates paths into their container counterparts.

## Requirements

`bash`, `git`, `zip`, `jq`, and either DDEV or a local Composer.
