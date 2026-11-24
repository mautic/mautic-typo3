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
select "API Credentials". Click "New" at the top right. Select the authorization protocol "OAuth2", fill in a name, and
enter the URL of your TYPO3 instance as Callback URI. Then hit "Save & Close" at the top right of the screen.

You should now have two keys, "Public Key" and "Secret Key". We will come back to them later.

Click the little cog-wheel icon at the top-right of the screen again, then click "Configuration". Scroll down to the
"CORS settings" section and add the URL of your TYPO3 instance in the "Valid Domains" field, also check that
"Restrict Domains" is set to Yes. Save the configuration.

Again in configuration, on the left side click the tab "API Settings". Set "API enabled?" to Yes. Save the configuration.

Before we continue you must clear Mautic's cache.

Go back to the TYPO3 backend and open the Mautic backend module. Fill in the root URL of your Mautic installation. Then
fill the public and the secret key with the values we generated earlier in Mautic. Click save. A new button should now
pop up that reads "Authorize with Mautic". Click this button, then log in and accept. You will be redirected back to
your TYPO3 instance.

If all went well a green flashmessage should show you that the connection to the Mautic API was successfull.

Your extension has now been configured.

Where the configuration is stored
---------------------------------

The configuration is split into two locations:

*  The connection settings are stored in :file:`config/mautic/config.yaml` of your TYPO3 instance. The file is written
   by the backend module and can also be edited or deployed manually:

   .. code-block:: yaml

      baseUrl: 'https://mautic.example.com'
      publicKey: '...'
      secretKey: '...'
      tracking: '1'
      trackingScriptOverride: ''
      timeout: 5

   ``timeout`` limits every request to Mautic to the given number of seconds (default ``5``; an empty value in the
   backend module is saved as ``5``). If Mautic does not answer in time, the page is delivered without Mautic content
   and all further requests to Mautic within the same page request fail immediately. The value can be changed in the
   "misc" tab of the backend module.

   ``0`` sets no timeout of its own. The TYPO3 HTTP settings ``$GLOBALS['TYPO3_CONF_VARS']['HTTP']['connect_timeout']``
   (default ``10``) and ``$GLOBALS['TYPO3_CONF_VARS']['HTTP']['timeout']`` (default ``0``, no limit) apply instead.
   Their current values are shown in the backend module :guilabel:`System > Configuration` (requires
   ``typo3/cms-lowlevel``) under ``TYPO3_CONF_VARS`` > ``HTTP``; they can be changed in
   :guilabel:`Admin Tools > Settings > Configure Installation-Wide Options`.

*  The OAuth2 tokens (``accessToken``, ``refreshToken`` and ``expires``) are stored in the TYPO3 registry within the
   namespace ``tx_mautic_oauth``. They are written and refreshed by the extension itself, must not be edited manually
   and are deliberately kept out of the configuration file so that they never end up in your version control system.

   Tokens that are still present in :file:`config/mautic/config.yaml` from an earlier version are migrated to the
   registry automatically and removed from the file.

Override Configuration
----------------------

:file:`config/mautic/config.yaml` is the default source of the configuration. All values can still be overwritten in
your configuration files, which takes precedence over the values from the configuration file and the registry:

.. code-block:: php

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['baseUrl'] = 'https://mautic.example.com';
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['publicKey'] = '...';
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['secretKey'] = '...';
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['tracking'] = '1';
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['trackingScriptOverride'] = '';
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['timeout'] = 5;

.. note::

   Overriding the tokens (``accessToken``, ``refreshToken`` and ``expires``) this way is discouraged. They are refreshed
   at runtime and the refreshed values are written to the registry, where a static override would then no longer match.
   Use the "Reset Authorization" button in the backend module to discard the stored tokens instead.
