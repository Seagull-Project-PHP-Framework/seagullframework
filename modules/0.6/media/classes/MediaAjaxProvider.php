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
// | MediaAjaxProvider.php                                                     |
// +---------------------------------------------------------------------------+
// | Authors:   Julien Casanova  <julien_casanova@yahoo.fr>                    |
// +---------------------------------------------------------------------------+


/**
 * Simple wrapper to MediaDAO to use with AJAX.
 *
 * @package Media
 * @author  Julien Casanova <julien_casanova@yahoo.fr>
 */

require_once SGL_MOD_DIR . '/media/classes/MediaDAO.php';
require_once SGL_CORE_DIR . '/Delegator.php';
require_once 'HTML/Template/Flexy.php';

class MediaAjaxProvider extends SGL_Manager
{
    /**
     *
     */
    function MediaAjaxProvider()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->da  = & MediaDAO::singleton();
    }

    function &singleton()
    {
        static $instance;

        // If the instance is not there, create one
        if (!isset($instance)) {
            $class = __CLASS__;
            $instance = new $class();
        }
        return $instance;
    }

    function getValidIds($options)
    {
        return $this->da->getValidIds($options);
    }

    function getMediaFiles($options)
    {
       SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output = & new SGL_Output();
        $output->theme = "default";
        $output->webRoot = $this->conf['site']['baseUrl'];
        $output->moduleName = "media";
        $output->aMedia = $this->da->getMediaFiles($options);

        $additions = array();

        if ($options['viewType'] == 'thumb') {
            $output->masterTemplate = 'mediaList_viewThumb.html';
        } else {
            $output->masterTemplate = 'mediaList_viewList.html';
        }

        $templ = & new SGL_HtmlSimpleView($output);
        $additions[] = $templ->render();

        return $additions;
    }

    function deleteMediaById($mediaId)
    {
        $ok = $this->da->deleteMediaById($mediaId);
        $ret = (!is_a($ok, 'PEAR_Error'))
            ? array('messageType' => 'info', 'message' => 'Media deleted successfully')
            : array('messageType' => 'error', 'message' => 'Sorry, couldn\'t delete this media');
        return $ret;
    }
}

?>
