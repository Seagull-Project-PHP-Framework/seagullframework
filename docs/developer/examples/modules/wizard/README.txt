SGL_WizardController
Author:  Malaney J. Hill
Date:  2006-08-02

I built this class out of a need to develop multi-page forms within the construct
of a Seagull module.  There had been some previous development on Wizards in Seagull
using a SGL_Wizard class, but I thought it would be much easier for developers to get
up to speed using PEAR's HTML_QuickForm_Controller (HQFC) library.  HQFC is built on
top of HTML_QuickForm and it is pretty simple to build complex forms with client and/or
server-side validation.

How Do I Use It?
Following the example provided you should ...
    1.  Include SGL_LIB_DIR . '/SGL/WizardController.php' in your module file.
    2.  Create a separate file in your module's "classes" directory for your wizard pages.
    3.  Build your individual pages and include that file in your module file
    4.  Create a 'wizard' method similar to the example provided in WizardMgr.php
    5.  Customize your template accordingly.


Pros
    * uses PEAR libs
    * easily allows for custom wizards within Seagull applications
    * behaves in MVC fashion (all page calls go to same URL)
    * wizards can be handled in the same manner as other class methods

Cons
    * Uses HTML_QuickForm, which some argue mixes logic with presentation too much
    * Decentralizes form validation.  Each form now has its own validation. 

TODO
    * Test with other URL Handlers besides Seagull SEF
    * Test with cookies off

Known Issues:
  None so far


References:
    1. HTML_QuickForm_Controller: http://pear.php.net/package/HTML_QuickForm_Controller
    2. HTML_QuickForm: http://pear.php.net/package/HTML_QuickForm

