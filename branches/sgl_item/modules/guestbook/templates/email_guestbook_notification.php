<?php
$body = <<< EOF
<table class="wide">
        <tr>
            <td><h3>New guestbook entry in {$this->options['siteName']}</h3></td>
        </tr>
        <tr>
            <td><strong>{$this->options['fromRealName']}</strong> has submitted the following guestbook entry in {$this->options['siteName']}:</td>
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
            <td><strong>Comment</strong>: {$this->options['body']}</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>Click <a href="{$this->options['deleteURL']}">Delete</a> to logon to the {$this->options['siteName']} site and delete the entry.</td>
        </tr>
</table>
EOF;
?>