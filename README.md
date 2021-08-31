toujou specific fork of the typo3 mautic extension
==================================================

forked from [mautic/mautic-typo3](https://github.com/mautic/mautic-typo3)

![Mautic](https://i.imgur.com/dfbouP1.png "Mautic Open Source Marketing Automation together with the CMS power of TYPO3")

Changes
-------

- [FEATURE] Oauth2 for Mautic 3.x
- [BUGFIX] YamlConfiguration is not loaded via GeneralUtility::makeInstance
- [TASK] Throw SubmitFormException on error during form submission


Installation
------------

    $ composer require toujou/mautic-typo3
    $ vendor/bin/typo3cms extension:install mautic
