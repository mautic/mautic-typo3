.. include:: Includes.txt

.. _introduction:

Introduction
============

This chapter gives a basic introduction about the TYPO3 CMS extension "*mautic*".

Target Audience
---------------

This extension is intended for those who use the marketing automation tool Mautic. This manual will walk you through the steps of
connecting your TYPO3 installation to your Mautic installation, as well as how to utilize the features of the extension.

What does it do?
----------------

This extension provides an API for frontend plugins to realize caching on a plugin element level.
It's based on the caching framework included since TYPO3 4.3. This extension fills a gap between USER and USER_INT plugins
by enabling plugins to cache their own content for a given lifetime and using this cache entry to save the computing
resources if other parts of the page have to be re-rendered.

This is especially useful if pages have elements that need to be re-rendered often (like “most clicked hotlists”)
while other elements on the page have a much longer lifetime and therefore clog the processor unnecessarily, when
rendered over and over again along with the other elements. Especially with highly dynamic, heavy traffic pages this
kills dearly needed server resources unnecessarily, slowing down vital performance.

Enetcache implements a mostly automatic clearing of cache elements if records are changed in the backend. If implemented
correctly, editors will not even recognize that this extension is in use and will never need to manually clear caches.

Features
--------

- Dynamic content blocks based on user behaviour
- Form synchronization between TYPO3 and Mautic
- Mautic form actions in TYPO3
- Mautic form embedding
- Tracking script integration
- OAuth support

See also
--------

- https://github.com/mautic/mautic-typo3

Thanks
------

This extension was developed by Bitmotion GmbH in collaboration with Jurian Janssen.