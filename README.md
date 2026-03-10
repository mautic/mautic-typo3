The Mautic Extension for TYPO3
===========
![Mautic](https://i.imgur.com/dfbouP1.png "Mautic Open Source Marketing Automation together with the CMS power of TYPO3")

Welcome to the official Mautic extension for TYPO3.

## Supported Versions
We currently support Mautic v4 or greater.

* **For TYPO3 v12 please use extension release 12.0.x** 
* **For TYPO3 v11 and above on PHP8, please use extension release 4.4.x**
* **For TYPO3 v10 and v11 on PHP7, please use extension release 4.3.x**
* For TYPO3 v9, please use extension release 3.x

## Features
The Mautic TYPO3 extension has many features that allow you to integrate your marketing automation workflow in TYPO3.

### Dynamic Content Blocks
Ever wanted to serve different content to different users based on their Mautic segments? With this extension you will 
be able to set aside content in your TYPO3 website for specific Mautic segments. This way, you will be able to decide 
what content to serve to which people. In good TYPO3 fashion, this can apply not only to content elements but also to 
entire pages or even data sets, templates, ...

### Form Synchronization
With the Mautic extension for TYPO3 you can create your forms in the TYPO3 backend, and have all data collected in 
Mautic too! You no longer need to maintain two forms, the extension will automatically sync all forms you have marked
as 'Mautic forms' with Mautic. You can then easily post form results to Mautic, while your form will always stay
up-to-date with your TYPO3 edits.

### TYPO3 Form Customization
In case you got own elements or similiar customization within the EXT:form component you should be aware of the possibly 
necessary adaptions you have to do. A custom element needs a transformation and field property depending of the element 
content and what you want to do with it. In case of an e.g. LinkedCheckbox element providing an GDPR checkbox along with 
a linked text you might not want the data to be transferred into mautic.
In this case you can do the following:


    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['LinkedCheckbox'] = \Leuchtfeuer\Mautic\Transformation\FormField\IgnoreTransformation::class;


If you actually want to transfer the data into mautic you need to choose a fitting Transformation class along with 
extending the inheritance of the custom element to use the mautic mixin. This provides the possibility to choose a 
property for the element in the form module. The side effect of not handling those custom elements could be that the 
form data is not transferred into mautic correctly. This is only the case on initial setup and first submitting of the 
form with such a non configured element. Once the form is submitted, those custom elements are getting ignored.

### Mautic Form Actions
Create contacts or modify the points of a contact straight from a TYPO3 Form.

### Mautic Form Content Element
If you wish to use Mautic forms directly, you can now add them with the Mautic Form content element that comes shipped 
with this extension.

### Tracking Script Integration
Integrate the Mautic tracking script into your frontend with one click of a button!

### OAuth 2.0 support
All requests made by this extension are secured using OAuth (with Mautic only supping OAuth2 in recent versions). You 
can easily configure your API tokens in the extension manager of TYPO3.

### Tags ###
Set tags for users when they are visiting a page!

### Assets ###
Link to Mautic assets directly in TYPO3 using file relations or the TYPO3 link wizard!

### API ###
The Mautic API https://developer.mautic.org/#rest-api is made available (see "ContactRepository")

## Installation
First, install the extensions *marketing_automation*  and *mautic* in your TYPO3.

Afterwards, establish the API connection:
* Go to "API Credentials" in Mautic and create a new "OAuth2" credential set (give your TYPO3 website's domain as "redirect URI")
* write down public and secret key
* go to the "API Connection" backend module in TYPO3, select "OAuth2", and enter the keys from above
* Now hit "Authorize with Mautic"
* A Mautic login windows comes up. Log in, and click "Accept"

## Documentation
Documentation and a getting started guide can be found [here](https://docs.typo3.org/p/mautic/mautic-typo3/master/en-us/).

## Known Issues
### TYPO3 Form Builder
* "Mautic Property Type" currently not working: Mautic field mappings are currently not possible through the TYPO3 UI, 
only for the pre-defined fields in template "Simple Contact Form"

### Form Framework: Currently limited support
* Make sure to use Predefined Form from Prototype "Mautic Form"
* Recommended: Use template "Simple Contact Form"
* Mautic-side changes in form are NOT sync'd back to TYPO3 
* TYPO3-side changes (fields, field properties, form type, ...) after creation are NOT sync'd to Mautic 
* Make sure to use finisher "Create Mautic Contact"
* Use "Send to Mautic form" only if you wish to send to a different form ID

### Tags
If a visitor lands directly on a page with a Mautic tag, it may happen that the tracking cookie has not yet been set. 
In this case, the visitor's page tag will not be saved either.

## Packaging for use in the Extension Manager
Clone the repository and run the following in the extension root directory:
```
composer package
```

## Contributing Partners
* [Beech](https://beech.it)
* [Leuchtfeuer Digital Marketing](https://leuchtfeuer.com)
* [TYPO3 GmbH](https://typo3.com)

## Contributing
You can contribute by making a pull request to the master branch of this repository.

## Questions or Suggestions?
You can always open an issue in this repository if you find a bug or have a feature request. Next to that you can 
also come visit us on Slack ([Mautic](https://www.mautic.org/slack) or [TYPO3](https://typo3.org/article/how-to-use-slack-in-the-typo3-community/)) in the channel `#typo3-mautic`.
