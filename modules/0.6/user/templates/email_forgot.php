<?php
//  translations
$passwdReminder = SGL_Output::translate('Password Reminder');
$message = SGL_Output::translate('You are being sent this email because');
$logonIs = SGL_Output::translate('Your username is');
$passwdIs = SGL_Output::translate('Your new password is');
$click = SGL_Output::translate('Click');
$here = SGL_Output::translate('here');
$toLogon = SGL_Output::translate('to logon to the');
$siteNow = SGL_Output::translate('site now');

$body = <<< EOF
<table class="wide">
        <tr>
            <td><h3>$passwdReminder</h3></td>
        </tr>
        <tr>
            <td>$message</td>
        </tr>
        <tr>
            <td></td>
        </tr>
        <tr>
            <td>$logonIs: <strong>{$this->options['username']}</strong></td>
        </tr>
        <tr>
            <td>$passwdIs: <strong>{$this->options['password']}</strong></td>
        </tr>
        <tr>
            <td>$click <a href="{$this->options['siteUrl']}">$here</a> $toLogon {$this->options['siteName']} $siteNow</td>
        </tr>
</table>
EOF;
?>