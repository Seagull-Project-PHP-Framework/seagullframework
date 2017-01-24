<?php
//  translations
$title = SGL_Output::translate('New Registration at');
$message = SGL_Output::translate('The following user has just registered');
$username = SGL_Output::translate('username');
$click = SGL_Output::translate('Click');
$here = SGL_Output::translate('here');
$toEnable = SGL_Output::translate('to enable the new users account');

$body = <<< EOF
<table class="wide">
        <tr>
            <td><h3>$title {$this->options['siteName']}</h3></td>
        </tr>
        <tr>
            <td><br /><br />$message:</td>
        </tr>
        <tr>
            <td></td>
        </tr>
        <tr>
            <td>$username: <strong>{$this->options['username']}</strong></td>
        </tr>
        <tr>
            <td></td>
        </tr>
EOF;

$body .= <<< EOF
        <tr>
            <td>$click <a href="{$this->options['activationUrl']}">$here</a> $toEnable.</td>
        </tr>
</table>
EOF;

?>