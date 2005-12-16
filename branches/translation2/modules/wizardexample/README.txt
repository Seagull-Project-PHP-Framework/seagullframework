WIZARDS IN SEAGULL
==================

This is new functionality in Seagull that allows you to link multiple forms together, maintaining state between them, effectively creating a multi-page wizard.  The result to the end-user is similar to that achieved by PEAR's QuickFormController, but with only a few short methods.

To use this in your code, follow the example 'WizardExample' in the modules directory and make sure all your Manager classes extend the Wizard class.

To enable the example:

1. log on as admin
2. select the 'navigation' module
3. created a test section called 'wizard' linking to module wizardexample and manager WizardMgr
4. log off, select 'wizard' and away you go.

KNOWN ISSUES
============
Because WizardMgr needs to forward you to the first of your sequence of pages, the wizard tab does not get selected since it breaks the logic that looks for the original start file name.
