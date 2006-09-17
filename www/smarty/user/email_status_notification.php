<?php
//  translations
$thanks = SGL_Output::translate('Thanks for registering with');
$dear = SGL_Output::translate('Dear');
$message = SGL_Output::translate('You are being sent this email because your new account status is now');
$newStatus = ($this->options['isEnabled']) ? SGL_Output::translate('active') : SGL_Output::translate('disabled');
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
                $message: <strong>$newStatus</strong></td>
        </tr>
        <tr>
            <td></td>
        </tr>
        <tr>
            <td></td>
        </tr>
EOF;

    $body .= <<< EOF
        <tr>
            <td>$click <a href="{$this->options['siteUrl']}">$here</a> $toLogon {$this->options['siteName']} $siteNow</td>
        </tr>
</table>
EOF;
?>