.. include:: ../Includes.txt

.. _installation:

============
Installation
============

This chapter will show you how to install the mautic extension in your TYPO3 instance.

Installation via Composer
=========================

If your TYPO3 instance is running in composer mode, you can simply require the extension by running

.. code-block:: bash

   composer require mautic/mautic-typo3

Installation via Extension Manager
==================================

Open the extension manager module of your TYPO3 instance and select "Get Extensions" in the select menu above the upload
button. There you can search for `mautic` and simply install the extension. Please make sure you are using the latest
version of the extension by updating the extension list before installing the mautic extension.

Installation via zip file
=========================

First you need the Marketing Automation extension, which can be found here https://extensions.typo3.org/extension/marketing_automation/.
Download the zip file, upload it to the extension manager of your TYPO3 instance and activate it. Then download the Mautic extension
at https://extensions.typo3.org/extension/mautic/ and also upload this zip file to the extension manager of your
TYPO3 instance and activate it.
Note: Please do not use GitHub to export a zip file because dependencies are missing.

Configuration
=============

Log in to your Mautic dashboard. Click on the little cog-wheel icon at the top-right of the screen. A menu opens,
select "API Credentials". Click "New" at the top right. Select the authorization protocol "OAuth 1.0a" or "OAuth2", that you want to use, fill in a name, and leave the Callback URI
field blank to restrict callbacks. Then hit "Save & Close" at the top right of the screen.

You should now have two keys, "Public Key" and "Secret Key". We will come back to them later.

Click the little cog-wheel icon at the top-right of the screen again, then click "Configuration". Scroll down to the
"CORS settings" section and add the URL of your TYPO3 instance in the "Valid Domains" field, also check that
"Restrict Domains" is set to Yes. Save the configuration.

Again in configuration, on the left side click the tab "API Settings". Set "API enabled?" to Yes. Save the configuration.

Before we continue you must clear Mautic's cache. This can be done by deleting the contents of the `app/cache` directory
on the server.

Go back to the TYPO3 backend and open the Mautic backend module. Select the right authorization protocol. Fill in the root URL of your Mautic installation. Then
fill the public and the secret key with the values we generated earlier in Mautic. Click save. A new button should now
pop up that reads "Authorize with Mautic". Click this button, then log in and accept. You will be redirected back to
your TYPO3 instance.

If all went well a green flashmessage should show you that the connection to the Mautic API was successfull.

Your extension has now been configured.

Override Configuration
----------------------

All configuration made in the backend module can simply be overwritten in your configuration files:

.. code-block:: php

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['baseUrl'] = 'https://mautic.example.com';
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['publicKey'] = '...';
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['secretKey'] = '...';
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['accessToken'] = '...';
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['accessTokenSecret'] = '...';
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['tracking'] = '1';
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['trackingScriptOverride'] = '';
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['authorizeMode'] = 'OAuth2';
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['refreshToken'] = '...';
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['expires'] = '...';
