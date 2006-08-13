<?php
$body = <<< EOF
<table class="wide">
        <tr>
            <td><h3>Contact Enquiry from {$this->options['siteName']}</h3></td>
        </tr>
        <tr>
            <td><strong>{$this->options['fromRealName']}</strong> has submitted the following feedback from {$this->options['siteName']}:</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td><strong>Name</strong>: {$this->options['fromRealName']}</td>
        </tr>
        <tr>
            <td><strong>Email</strong>: <a href="mailto:{$this->options['fromEmailAdress']}">{$this->options['fromEmailAdress']}</a></td>
        </tr>
        <tr>
            <td><strong>Enquiry Type</strong>: {$this->options['type']}</td>
        </tr>
        <tr>
            <td><strong>Comment</strong>: {$this->options['body']}</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>Click <a href="{$this->options['siteUrl']}">here</a> to logon to the {$this->options['siteName']} site now.</td>
        </tr>
</table>
EOF;
?>