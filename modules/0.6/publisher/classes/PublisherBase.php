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
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | PublisherBase.php                                                         |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: PublisherBase.php,v 1.11 2005/06/23 22:54:06 demian Exp $

/**
 * Helper methods for publisher module.
 *
 * @package publisher
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class PublisherBase
{
    /**
     * Toggles the queryRange string.
     *
     * a collection can represent the contents from the entire db for
     * a given type, or only the results specific to the current category
     *
     * @access  public
     * @param   string  either 'all' or 'thisCategory'
     * @return  string  html to construct the queryRange toggle
     */
    function getQueryRange($input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $sessionInfo = (SID) ? SID : '';
        if (!empty($sessionInfo)) {
                $sessionInfo = '&' . $sessionInfo;
        }
        if ($input->queryRange == 'all') {
            $html = "<a href='".SGL_Url::makeLink()."frmQueryRange/thisCategory/" . $sessionInfo . "'>" . SGL_String::translate('whole DB') . "</a>";
        } else {
            $html = "<a href='".SGL_Url::makeLink()."frmQueryRange/all/" . $sessionInfo . "'>" . SGL_String::translate('this category') . "</a>";
        }
        return $html;
    }

    /**
     * Propagates certain values in the session.
     *
     * currently only currentCatId, currentResRange
     * and dataTypeId (article template type)
     *
     * @access  public
     * @param   object  $input  processed $input from validate()
     * @return  void
     */
    function maintainState(& $input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  look for catID
        $sessCatID = SGL_Session::get('currentCatId');
        if (!$input->catID && !$sessCatID) {    //  if not in input or session, default to 1
            $input->catID = 1;
        } elseif (!$input->catID)               //  if not in input, grab from session
            $input->catID = SGL_Session::get('currentCatId');

        //  add to session
        SGL_Session::set('currentCatId', $input->catID);

        //  look for resource range, ie: 'all' or 'thisCategory'
        $sessResourceRange = SGL_Session::get('currentResRange');
        if (!isset($input->queryRange) && !$sessResourceRange) {    // if not in input or session, default to 'all'
            $input->queryRange = 'all';
        } elseif (!isset($input->queryRange))               // if not in input, grab from session
            $input->queryRange = SGL_Session::get('currentResRange');

        //  add to session
        SGL_Session::set('currentResRange', $input->queryRange);

        //  look for dataTypeID for template selection in article manager
        $sessDatatypeID = SGL_Session::get('dataTypeId');

        //    if not in input or session, set default article type
        if (!isset($input->dataTypeID) && !$sessDatatypeID) {
            $c = &SGL_Config::singleton();
            $conf = $c->getAll();
            $defaultArticleType = (array_key_exists('defaultArticleViewType', $conf['site']))
             ? $conf['site']['defaultArticleViewType']
             : 1;
            $input->dataTypeID = $defaultArticleType;

        // if not in input, grab from session
        } elseif (!isset($input->dataTypeID))
            $input->dataTypeID = SGL_Session::get('dataTypeId');

        //  add to session
        SGL_Session::set('dataTypeId', $input->dataTypeID);
    }

    function getDocumentListByCatID($catID)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        require_once 'DB/DataObject.php';
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();
        $documentList = DB_DataObject::factory($conf['table']['document']);
        $documentList->category_id = $catID;
        $result = $documentList->find();
        $documents = array();
        if ($result > 0) {
            while ($documentList->fetch()) {
                $documents[] = clone($documentList);
            }
        }
        return $documents;
    }
}
?>