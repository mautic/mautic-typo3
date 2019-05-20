.. include:: Includes.txt

.. _installation:

Introduction
============

This chapter will show you how to install the mautic extension in your TYPO3 instance.

Installation via Composer
-------------------------

If your TYPO3 instance is running in composer mode, you can simply require the extension by running
`composer require mautic/mautic-typo3`

Installation via the Extension Manager
--------------------------------------

First you need the Marketing Automation extension, which can be found here https://extensions.typo3.org/extension/marketing_automation/.
Download the zip file and upload it to the extension manager of your TYPO3 instance and activate it. Then download the Mautic extension
at https://extensions.typo3.org/extension/mautic/ and also upload this zip file to the extension manager of your
TYPO3 instance and activate it.

Initial Configuration
---------------------

Log in to your Mautic dashboard. Click on the little cog-wheel icon at the top-right of the screen. A menu opens, select "API Credentials".
Click "New" at the top right. Select "OAuth 1.0a", fill in a name, and leave the Callback URI field blank. Then hit
"Save & Close" at the top right of the screen.

You should now have two keys, "Public key" and "Secret key". We will come back to them later.

Click the little cog-wheel icon at the top-right of the screen again, then click "Configuration". Scroll down to
"CORS settings" and add the URL of your TYPO3 instance in the "Valid Domains" field, also check that "Restrict Domains"
is set to Yes. Save the configuration.

Again in configuration, on the left side click the tab "API Settings". Set "API enabled?" to Yes. Save the configuration.

Before we continue you must clear Mautic's cache. This can be done by deleting the contents of the `app/cache` directory
on the server.

Go back to the TYPO3 backend and navigate to the Extension Manager. Search for the Mautic extension and click the
little cog-wheel icon to go to the extension settings. Fill in the root URL of your Mautic installation. Then fill the
public and the secret key with the values we generated earlier in Mautic. Click save. A new button should now pop up
that reads "Authorize with Mautic". Click this button, then log in and accept. You will be redirected to TYPO3.

If all went well a green flashmessage should show you that the connection to the Mautic API was successfull.

Your extension has now been configured.