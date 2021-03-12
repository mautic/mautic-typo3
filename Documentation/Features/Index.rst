.. include:: ../Includes.txt

.. _features:

========
Features
========

This chapter will go through all the features the Mautic extension for TYPO3 provides.

Enabling the Mautic Tracking Script
===================================

To enable Mautic tracking on your TYPO3 frontend, navigate to the Mautic configuration module. On the settings
page there is a tab called "Tracking". In this tab check the "Enable Mautic user tracking" checkbox and save the
settings. This implements the default Mautic tracking script based on the base URL of your Mautic installation.

   .. figure:: 002.png
      :class: with-shadow

If you would like to customize the tracking script you can do so by parsing it into the "Override default Mautic
tracking script (without script tags)" field. If the default script suits your needs, leave this field blank.

Including Mautic Forms
======================

To include an existing Mautic form into your TYPO3 frontend, create a new content element. Select the tab
"Form elements", then select "Mautic Form".

   .. figure:: 001.png
      :class: with-shadow

You can configure this content element like any other content element. At the bottom of the tab "General" you will find
a dropdown list of all the forms present in Mautic. You can select one, and then save your content element.

Adding this content element to a page will render the Mautic form on the frontend.

Syncing TYPO3 forms with Mautic
===============================

Instead of including forms from Mautic, the extension also allows you to sync your TYPO3 forms to Mautic. With this
feature you can keep using the TYPO3 Forms environment (`EXT:forms`) to create your forms, and make sure Mautic
receives all the relevant data.

To start creating a form, go to the Forms module. Click the "Create a new form" button at the top. You can now name your
form. Make sure to check the "Advanced settings" checkbox. Then click next.

In the next screen select "Mautic Form" under "Form prototype". Under "Start template" you can either select
"Blank form" to start with a blank form, or "Simple contact form" to start with a simple template form. We recommend
selecting the "Simple contact form" for the sake of this tutorial. then click next, and click next again.

You should now see the simple contact form before you in the form editor. Before we continue, let's save our form. By
saving we automatically let Mautic know that a form has been created or updated.

   .. figure:: 007.png
      :class: with-shadow

After saving, our form should also appear in Mautic. Let's check there if it is present. Go to Mautic and navigate to
Components -> Forms.

   .. figure:: 008.png
      :class: with-shadow

As you can see, the form has been created. You can have a look inside the form if you want, you will see that all the
fields are present too.

To make sure the form data is sent to Mautic when the form is submitted, the "Send to Mautic Form" finisher must be
added to the form. The demo form should already have this added.

   .. figure:: 009.png
      :class: with-shadow

With this set, all data submitted to the form will automatically be saved in Mautic.

Now let's make sure Mautic knows what kind of data is submitted in the form fields. For instance, if you have a field
in which the user should enter his or her email address, we would like Mautic to know that we are dealing with an email
address in this specific field.

To do this, click on the field in the form editor. In the right hand side menu you will find a dropdown menu called
"Mautic Property Type". This dropdown will contain all the contact fields Mautic knows (including fields you have
created yourself in Mautic). Select "Contact: Email" in the dropdown and save the form.

   .. figure:: 010.png
      :class: with-shadow

Now Mautic knows that data submitted in this field must be treated as an email address. Let's have a look in Mautic and
see what it looks like. Navigate to the form in Mautic and click "Edit" in the top-right. Then navigate to the tab
"Fields".

It should show that the "Email" field has been linked to the contact property "Email".

   .. figure:: 011.png
      :class: with-shadow

That's it. You can do this for every field in the form.

TYPO3 Form Customization
==============================

In case you got own elements or similiar customization within the EXT:form component you should be aware of the possibly necessary adaptions you have to do.
A custom element needs a transformation and field property depending of the element content and what you want to do with it.
In case of an e.g. LinkedCheckbox element providing an GDPR checkbox along with a linked text you might not want the data to be transferred into mautic.
In this case you can do the following:

   .. code-block:: php
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['LinkedCheckbox'] = \Bitmotion\Mautic\Transformation\FormField\IgnoreTransformation::class;


If you actually want to transfer the data into mautic you need to choose a fitting Transformation class along with extending the inheritance of the custom element to use the mautic mixin. This provides the possibility to choose a property for the element in the form module.

The side effect of not handling those custom elements could be that the form data is not transferred into mautic correctly.
This is only the case on initial setup and first submitting of the form with such an non configured element.
Once the form is submitted those custom elements are getting ignored.

Using Dynamic Content Elements
==============================

Dynamic content elements are content elemants that are hidden or shown based on the Mautic segment(s) a user is a
member of.

Before moving on to creating dynamic content elements we would first like to explain the structure and hierarchy of
the different objects associated with orchestrating dynamic content.

* Mautic Segments
* TYPO3 Personas
* TYPO3 Dynamic Content Elements

Let's start with the Segments in Mautic. If you have used Mautic before, you should be familiar with segments. In short,
segments are groups of contacts/leads that have been created and/or filtered by the marketing logic defined
in your Mautic instance.

TYPO3 Personas are objects in TYPO3 that hold one or more Mautic segments. Personas can be seen as the "middle-man"
between Mautic segments and TYPO3 content elements. Content elements can be configured to only show for certain Personas.
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

Give your Persona a name and fill it in the title field. Now lets open the "Mautic" tab. It should look like the image below:

   .. figure:: 005.png
      :class: with-shadow

On the right we see a list of Mautic segments. This list comes directly from Mautic. If it is out of date, you can update
the list by clicking the refresh button right next to the list.

To add segments to your new Persona, simply click them. They should move into the list on the left. Repeat this with all
the segments you would like to add to this Persona. Then save the Persona. Our Persona is now ready.

Now we will go to the Page module. We are now going to add a Persona to a content element. Either create a new content element
or edit an already existing one.

Configure the content element to your liking. Once done, we will add the Persona. Go to the "Access" tab and scroll down.
There will be a section called "Limit to Targeting Personas". On the right we have all known Personas on the current page.
On the right we have the Personas bound to this content element. Once a content element is bound to a Persona (or more)
that content element will only show if the user is a member of (one of) the Persona(s). You can again click the Personas
in the right list, they will move to the left. Once you are happy with the configuration, save the content element.

   .. figure:: 006.png
      :class: with-shadow

Done. You have successfully created a content element that is shown/hidden based on information coming from Mautic!

Tagging
=======

Assign tags to your contacts when the enter a page. Go into the settings of your page and choose the tab `mautic`.
If you already have some tags in your Mautic instance, you can synchronize them with TYPO3 by pressing the synchronize
Button. If you do not, just create a new tag on the fly and assign it to the page. The tag is automatically created in
Mautic, too.

   .. figure:: 014.png
      :class: with-shadow

Link Assets
===========

You can link Mautic assets directly in TYPO3 using file relations or the link wizard. To start, you have to create a
new file storage within TYPO3. Open your TYPO3 backend and navigate to the list module, select the root page (pid = 0)
and add a new file storage. All you have to do is to select `Mautic` as driver and choose a name. We recommend to use
the processing folder of the TYPO3 default storage (e.g. `fileadmin`):

   .. figure:: 012.png
      :class: with-shadow

You can now link your assets:

   .. figure:: 013.png
      :class: with-shadow
