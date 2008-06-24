<?php
// make sure no PHP errors will happen
$aVars = array('recipientName', 'senderName');
foreach ($aVars as $varName) {
    if (!isset($aParams[$varName])) {
        $aParams[$varName] = '';
    }
}
$bodyTxt = <<< TXT
Hello {$aParams['recipientName']}!

How's things?

--
{$aParams['senderName']}
TXT;
?>