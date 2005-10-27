<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005, Demian Turner                                         |
// | All rights reserved.                                                      |
// |                                                                           |
// | Redistribution and use in source and binary forms, with or without        |
// | modification, are permitted provided that the following conditions        |
// | are met:                                                                  |
// |                                                                           |
// | o Redistributions of source code must retain the above copyright          |
// |   notice, this list of conditions and the following disclaimer.           |
// | o Redistributions in binary form must reproduce the above copyright       |
// |   notice, this list of conditions and the following disclaimer in the     |
// |   documentation and/or other materials provided with the distribution.    |
// | o The names of the authors may not be used to endorse or promote          |
// |   products derived from this software without specific prior written      |
// |   permission.                                                             |
// |                                                                           |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS       |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT         |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR     |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT      |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,     |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT          |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,     |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY     |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT       |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE     |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.      |
// |                                                                           |
// +---------------------------------------------------------------------------+
// | Seagull 0.5                                                               |
// +---------------------------------------------------------------------------+
// | WizardDetectEnv.php                                                       |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: setup.php,v 1.5 2005/02/03 11:29:01 demian Exp $

/*
sgl setup
=========
- ability to upload and unzip/tar a packaged module
- file permission handling ideas from FUDforum installer
- more user-friendly error messages from Gallery2
- if no DB detected, prompt to create, otherwise offer to create tables

PROCESS
========

- create lockfile, system in 'setup' mode
- block public access
    one idea, using key in hidden file
        - put main site in standby mode
        - create randomly named dir
        - perform install in above dir
        - delete dir when finished

php interpreter
===============
- min php version, not over max
- get php sapi type
- check loaded extensions

php ini
=======
- deal with register_globals and set session.use_trans_sid = 0
- allow_url_fopen = Off
- detect and deal with safe_mode
- magic_quotes must be off
- file_uploads ideally enabled

filesystem
==========
- check pear libs exists and are loadable
- determine location in filesystem
- test if seagull/var exists & is writable
- copy config file there
- rewrite with correct values
- create seagull/var tmp dir for session
    
db setup
========
- test db connection
- test db perms
- get prefix, db params
- create tables
- insert default SQL data
- insert sample SQL data
- load constraints

config setup
============
- form
    - system paths
    - general
        - name + desc of site [metas]
        - admin email address
        - lang
        - server time offset
        
- the domain of the cookie that will be used
        
module setup
============
- choose modules and permissions must be created and set at install time
- attempt to 
    - uncompress
    - move to correct locatin
    - apply user perms
    - apply prefs
    - add module's db tables to Config
    - load module's schema + data
    - add 'section' or 'screen' navigation links
    - register module in registry

user setup
==========
- create admin, set username, password and email
- option to add user

- For security reasons, you must remove the installation script ...
- remove lockfile, system set to 'production' mode
*/

require_once dirname(__FILE__) . '/../TaskRunner.php';
require_once dirname(__FILE__) . '/../Tasks/All.php';

SGL_Install::printHeader('Detecting Environment');

$runner = new SGL_TaskRunner();
$runner->addTask(new SGL_Task_GetLoadedModules());
$runner->addTask(new SGL_Task_GetPhpEnv());
$runner->addTask(new SGL_Task_GetPhpIniValues());
$runner->addTask(new SGL_Task_GetFilesystemInfo());
$runner->addTask(new SGL_Task_GetPearInfo());
$output = $runner->main();

//  store output for later processing
$serialized = serialize($runner);
file_put_contents(SGL_Install::getInstallRoot() . '/var/env.php', $serialized);

print $output;

//  process errors
print '<p>&nbsp;</p>';
print "<div class=\"messageContainer\">";

if (SGL_Install::errorsExist()) {
    print "<div class=\"errorHeader\">Errors Detected</div>";
    foreach ($_SESSION['ERRORS'] as $error) {
        print "<div class=\"errorContent\"><strong>{$error[0]}</strong> : {$error[1]}</div>";
    }
    print '<p>You must fix the above error(s) before you can continue.</p>';       
} else {
    print '<input type="submit" name="envDetect" value="Next >>" onClick="document.location.href=\'setup.php\'" />';   
}
print '</div>';

SGL_Install::printFooter();
?>