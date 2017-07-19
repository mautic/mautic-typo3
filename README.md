EXT:mautic_typo3 ![Travis](https://travis-ci.org/mautic/mautic-typo3.svg?branch=master "Travis build")
===========
![Mautic](http://i.imgur.com/g56p37X.jpg "Mautic Open Source Marketing Automation together with the CMS power of TYPO3")

Welcome to the official TYPO3 extension for Mautic!

Version: 1.0.0

State: Beta.

### Dependencies
* PHP 7.0^
* TYPO3 8.7^
* Mautic API Library 2.6^
* Composer

### Features
* Create forms in TYPO3 and automatically sync them with your Mautic installation (supports create, duplicate, edit and delete)
* Send form data directly to Mautic
* Create a contact in Mautic from data collected in TYPO3 forms
* Integration of the Mautic tracking script

Don't forget to head into the extension configuration to set up TYPO3 with the details of your Mautic installation.

Tracking is also disabled by default. Head to the extension configuration to turn it on.

### Installation (via Composer)
Add the following to the composer.json of your TYPO3 installation
```
"repositories": [
    { "type": "vcs", "url": "git@github.com:mautic/mautic-typo3.git" }
	],
require {
    "mautic/mautic-typo3": "1.0^"
}
```

Run the following command

    $ composer install
    
Go into the TYPO3 backend to the Extensions module and enable the 'Mautic' extension

### How to contribute
* Fork the repository
* Create a new branch with your feature or fix
* Make sure to run php-cs-fixer over your code
* Push changes to your branch
* Create a pull request to this repository
* Note that the travis build should succeed before requests are approved

### Got questions?
* Create an issue in this repository
* Find us on the [Mautic Slack](https://mautic.slack.com)
* Find us on the [TYPO3 Slack](https://typo3.slack.com), channel #typo3-mautic