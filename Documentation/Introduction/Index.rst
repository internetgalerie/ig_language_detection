.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _introduction:

Introduction
============


.. _what-it-does:

What does it do?
----------------

* The language detection is only called when "https://www.example.com" was entered, but all sites have "https://www.example.com/lang-key/".
* Extra configuraion is done in the site YAML config file (e.g. typo3conf/sites/website/config.yaml)
* The language detection is done with accept header (HTTP_ACCEPT_LANGUAGE) from the browser.

If you just want the language detection on the activated languages of the site configuraion, you don't need any extra configuraion.

