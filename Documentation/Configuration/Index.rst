.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _configuration:



The language detection is only called when "https://www.example.com" was entered, but all sites have "https://www.example.com/lang-key/".
The configuraion is done in the site YAML config file (e.x. typo3conf/sites/website/config.yaml)


Configuration Reference
=======================

the configuration looks like

.. code-block:: yaml
	:linenos:
	:emphasize-lines: 1-11
				     
        languageDetection:
	  debug: false
	  appendPath: false
	  defaultLanguageId: 2
	  aliases:
	    -
	      alias: en
	      languageId: '0'
	    -
	      alias: it
	      languageId: '0'



the attributes are:

.. container:: ts-properties

	=========================== ============== =============================================================== ====================
	Property                    Data type      Description                                                     Default
	=========================== ============== =============================================================== ====================
	defaultLanguageId           integer        Language Uid taken if nothing is found                          0
	aliases                     array          map other languages to the available languages                         
	alias                       string         iso-639-1 of the language to map              
	languageId                  integer        The language id               
	debug                       boolean        if true - no redirect, debug infos are displayed                false
	appendPath                  boolean        should the requested path appended? (translated error pages)    false
	=========================== ============== =============================================================== ====================




language detection
^^^^^^^^^^^^^^^^^^
the order of the tests are:

* the accepted languages of the browser (in the order of the quality) are compared to the available languages of the site, if found this language is choosen
* the aliases are compared, if found the language with the id "languageId" is taken
* defaultLanguageId is taken
* language id 0
  