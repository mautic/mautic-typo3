.. include:: Includes.txt

.. _features:

Introduction
============

This chapter will go through all the features of the Mautic extension for TYPO3.

Enabling the Mautic Tracking Script
===================================

To enable Mautic tracking on your TYPO3 frontend, navigate to the Extension Manager. Find the Mautic extension and go to its settings page. On the settings
page there is a tab called "Tracking". In this tab check the "Enable Mautic user tracking" checkbox and save the settings. This implements the default
Mautic tracking script based on the base URL of your Mautic installation.

   .. figure:: 002.png
      :class: with-shadow

If you would like to customize the tracking script you can do so by parsing it into the "Override default Mautic tracking script (without script tags)"
field. If the default script suits your needs, leave this field blank.

Tagging Leads of Page Visits
============================

Including Mautic Forms
======================

To include a Mautic form into your TYPO3 frontend, create a new content element. Select the tab "Form elements", then select "Mautic Form".

   .. figure:: 001.png
      :class: with-shadow

You can configure this content element like any other content element. At the bottom of the tab "General" you will find a dropdown list of all the forms
present in Mautic. You can select one, and then save your content element.

Adding this content element to a page will render the Mautic form on the frontend.

Syncing TYPO3 forms with Mautic
===============================

ToDo

Using Dynamic Content Elements
==============================

ToDo