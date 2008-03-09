<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2008, Demian Turner                                         |
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
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | Output.php                                                                |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: Output.php,v 1.9 2005/01/27 12:33:45 demian Exp $

class PublisherOutput 
{   
    function articleOutput($oOutput)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);       
        
        $templ = & new HTML_Template_Flexy();
        $aCurrentType = explode(' ', $oOutput->leadArticle['type']);              
        $oOutput->articleTmpl = 'articleView';
        
        foreach ($aCurrentType as $key) {
            $oOutput->articleTmpl .= $key;
        }
        $oOutput->articleTmpl .= '.html';
        $templ->compile($oOutput->articleTmpl);

        //  if some Flexy 'elements' exist in the output object, send them as
        //  2nd arg to Flexy::outputObject()

        $elements = (   isset($oOutput->flexyElements) && 
                        is_array($oOutput->flexyElements))
                ? $this->flexyElements 
                : array(); 
        $data = $templ->bufferedOutputObject($oOutput, $elements);

        return $data;     
    }

    /**
     * Takes doc type ID from DB and converts to corresponding icon.
     *
     * @access  public
     * @param   int     $documentID doc type
     * @return  string  $iconString
     */
    function id2AssetIcon($documentID)
    {
        $theme = $_SESSION['aPrefs']['theme'];
        switch ($documentID) {
        case 1: $iconString = '<img src="' . SGL_BASE_URL . '/themes/' . $theme . '/images/docIcons/word.gif">';break;
        case 2: $iconString = '<img src="' . SGL_BASE_URL . '/themes/' . $theme . '/images/docIcons/excel.gif">';break;
        case 3: $iconString = '<img src="' . SGL_BASE_URL . '/themes/' . $theme . '/images/docIcons/ppt.gif">';break;
        case 4: $iconString = '<img src="' . SGL_BASE_URL . '/themes/' . $theme . '/images/docIcons/url.gif">';break;
        case 5: $iconString = '<img src="' . SGL_BASE_URL . '/themes/' . $theme . '/images/docIcons/image.gif">';break;
        case 6: $iconString = '<img src="' . SGL_BASE_URL . '/themes/' . $theme . '/images/docIcons/pdf.gif">';break;
        case 7: $iconString = '<img src="' . SGL_BASE_URL . '/themes/' . $theme . '/images/docIcons/unknown.gif">';break;
        default: $iconString = '&nbsp';
        }
        return $iconString;
    }

    /**
     * Maps icon HTML to corresponding position in a grid.
     *
     * the idea is to allow for filtering views by doc type
     *
     * @access  public
     * @param   int     $documentID doc type
     * @return  string  $tdString
     * @see     id2AssetIcon()
     */
    function outputIcon($documentID)
    {
        $iconString = $this->id2AssetIcon($documentID);
        if ($documentID == 7) {
            $pos = 1;
        } else {
            $pos = $documentID;
        }
        $tdString = '';
        for ($i = 1; $i <= 6; $i++) {
            $icon = ($i == $pos) ? $iconString : '&nbsp;';
            $tdString .= "<td align=\"center\">". $icon ."</td>\n";
        }
        return $tdString;
    }

    /**
     * Colour codes status indicators in Article Manager.
     *
     * @access  public
     * @param   string  $input
     * @return  string  $output colour-coded status indicator
     */
    function applyStatusColour($input, $webRoot = '', $theme = '') 
    {
        $imageDir = "$webRoot/themes/$theme/images/16/";
        switch($input) {
        case SGL_STATUS_ARCHIVED:
            $output = "<span class='archived'><img src='{$imageDir}status_archived.gif' alt='" . SGL_String::translate('Archived') . "' title='" . SGL_String::translate('Archived') . "' /></span>";
            break;

        case SGL_STATUS_PUBLISHED:
            $output = "<span class='published'><img src='{$imageDir}status_published.gif' alt='" . SGL_String::translate('Published') . "' title='" . SGL_String::translate('Published') . "' /></span>";
            break;

        case SGL_STATUS_APPROVED:
            $output = "<span class='approved'><img src='{$imageDir}status_approved.gif' alt='" . SGL_String::translate('Approved') . "' title='" . SGL_String::translate('Approved') . "' /></span>";
            break;

        case SGL_STATUS_FOR_APPROVAL:
            $output = "<span class='forApproval'><img src='{$imageDir}status_forapproval.gif' alt='" . SGL_String::translate('For Approval') . "' title='" . SGL_String::translate('For Approval') . "' /></span>";
            break;

        default:
            $output = 'no status available';
        }
        return $output;
    }
}
?>
