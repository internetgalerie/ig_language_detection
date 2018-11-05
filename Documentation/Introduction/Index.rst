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

The language detection is only called when "https://www.example.com" was entered, but all sites have "https://www.example.com/lang-key/".
The configuraion is done in the site YAML config file (e.x. typo3conf/sites/website/config.yaml)

It is more porefull than simple apache rules like

RewriteCond %{HTTP:Accept-Language} ^de [NC]

