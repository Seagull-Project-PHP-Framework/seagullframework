<?php
//  translations
$thanks = SGL_Output::translate('Thanks for registering with');
$dear = SGL_Output::translate('Dear');
$message = SGL_Output::translate('You are being sent this email because you just registered, your logon details are as follows');
$logonIs = SGL_Output::translate('Your username is');
$passwdIs = SGL_Output::translate('Your password is');
$click = SGL_Output::translate('Click');
$here = SGL_Output::translate('here');
$toLogon = SGL_Output::translate('to logon to the');
$siteNow = SGL_Output::translate('site now');

$body = <<< EOF
<table class="wide">
        <tr>
            <td><h3>$thanks {$this->options['siteName']}</h3></td>
        </tr>
        <tr>
            <td>$dear {$this->options['toRealName']}, <br /><br />
                $message:</td>
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
            <td></td>
        </tr>
EOF;

if (!$this->conf['RegisterMgr']['autoEnable']) {
    $pending = SGL_Output::translate('Your registration is being reviewed');
    $body .= <<< EOF
        <tr>
            <td>$pending</td>
        </tr>
</table>
EOF;
} else {
    $body .= <<< EOF
        <tr>
            <td>$click <a href="{$this->options['siteUrl']}">$here</a> $toLogon {$this->options['siteName']} $siteNow</td>
        </tr>
</table>
EOF;
}
?>
