EXT:mautic ![Travis](https://travis-ci.org/mautic/mautic-typo3.svg?branch=master "Travis build")
===========
![Mautic](http://i.imgur.com/g56p37X.jpg "Mautic Open Source Marketing Automation together with the CMS power of TYPO3")

Welcome to the official TYPO3 extension for Mautic!

Version: 1.3.5

State: Beta.

### Dependencies
* PHP 7.0^
* TYPO3 8.7^
* Mautic API Library 2.6^
* Escopecz\MauticFormSubmit 1.1^
* Composer

### Features
* Create forms in TYPO3 and automatically sync them with your Mautic installation (supports create, duplicate, edit and delete)
* Send form data directly to Mautic
* Create a contact in Mautic from data collected in TYPO3 forms
* Integration of the Mautic tracking script

Don't forget to head into the extension configuration to set up TYPO3 with the details of your Mautic installation.

Tracking is also disabled by default. Head to the extension configuration to turn it on.

### Installation (via Composer) (recommended)
Add the following to the composer.json of your TYPO3 installation
```
"repositories": [
    { "type": "vcs", "url": "git@github.com:mautic/mautic-typo3.git" }
	],
require {
    "mautic/mautic-typo3": "^1.3"
}
```

Run the following command

    $ composer install
    
Go into the TYPO3 backend to the Extensions module and enable the 'Mautic' extension

### Installation (without composer)

When retrieving the file from TER you can install this file using the extension manager.

If you want to build the latest master you need to create the extension zip file:

* Clone package
* Run composer run-script package
* A mautic.zip is created, this file can be imported by your extension manager


### A thank you to
* [Beech.it](https://beech.it) for helping in getting started and tackling questions about TYPO3
* [John Linhart](http://johnlinhart.com) (escopecz) for helping me get started with the Mautic development environment
* [TYPO3 Community](https://typo3.org) for help, tips and feature requests
* [Mautic Community](https://mautic.org) for help, tips and feature requests

### How to contribute
* Fork the repository
* Create a new branch with your feature or fix
* Make sure to run php-cs-fixer over your code
* Push changes to your branch
* Create a pull request to this repository
* Note that the travis build should succeed before requests are approved

### Need help with integration?
* Contact support@beech.it

### Got questions?
* Create an issue in this repository
* Find us on the [Mautic Slack](https://mautic.slack.com)
* Find us on the [TYPO3 Slack](https://typo3.slack.com), channel #typo3-mautic


### Development

* When updating/changing composer requirements don't forget to update the composer.json in the private directory. (Only for non TYPO3 composer packages, TYPO3 packages should be installed by extension manager)