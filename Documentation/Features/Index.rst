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

Dynamic content elements are content elemants that are hidden or shown based on the Mautic segment(s) a user is a member of.

Before moving on to creating dynamic content elements we would first like to explain the structure and hierarchy of the different
objects associated with orchestrating dynamic content.

* Mautic Segments
* TYPO3 Personas
* TYPO3 Dynamic Content Elements

Let's start with the Segments in Mautic. If you have used Mautic before, you should be familiar with segments. In short,
segments are groups of contacts/leads that have been created and/or filtered by the marketing logic defined
in your Mautic instance.

TYPO3 Personas are objects in TYPO3 that hold one or more Mautic segments. Personas can be seen as the "middle-man" between
Mautic segments and TYPO3 content elements. Content elements can be configured to only show for certain Personas.
This means the content elements are not directly tied to Mautic segments, but rather to a Persona. Which allows you
to effieciently configure groups of Segments and bind a content element to all of the immediately (via the Persona).

TYPO3 Dynamic Content Elements are content elements that are bound to one or more Personas. These content elements will
only show when the user is a member of one of these Personas.

Lets start creating one of these. First navigate to the List module and click the "Create record" button.

   .. figure:: 003.png
      :class: with-shadow

Select Marketing Automation -> Persona

   .. figure:: 004.png
      :class: with-shadow