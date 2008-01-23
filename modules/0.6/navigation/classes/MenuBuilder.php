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
// | MenuBuilder.php                                                           |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: MenuBuilder.php,v 1.12 2005/03/29 08:35:03 demian Exp $

/**
 * Base class for navigation module.
 *
 * @package navigation
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.12 $
 */
class MenuBuilder
{
    var $GUI        = null;
    var $options    = array();
    var $_startId   = 0;
    var $_options   = array();

    function MenuBuilder($type, $options = array())
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->_options = $options;
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();
        $this->GUI = & $this->_factory($type, $conf, $options);
        $this->GUI->dbCatTableName  = (isset($options['table'])) ? $options['table']:
            $conf['table']['category'];
    }

    function setStartId($startId = 0)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->_startId = $startId;
    }

    function &_factory($type, $conf, $options = array())
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $guiPath = SGL_MOD_DIR . "/navigation/classes/menu/$type.php";
        require_once $guiPath;
        $guiClass = 'Menu_' . $type;
        if (!class_exists($guiClass)) {
            SGL::raiseError("$guiClass is not a valid classname", SGL_ERROR_NOCLASS);
        }
        @$obj = & new $guiClass($options, $conf);
        return $obj;
    }

    function toHtml()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $menuType = strtolower(get_class($this->GUI));
        switch ($menuType) {

        case 'menu_explorer':

            //  cannot cache at this level because output is sent to blocks
            //  which are cacheable
            $ret = $this->GUI->render($this->_startId);

            //  add closing js script tags
            $ret .= 'document.write(tree);}';
            $ret .= '</script>';
            break;

        case 'menu_explorerbsd':
            $ret = $this->GUI->render($this->_startId);
            break;

        case 'menu_selectbox':
            $cache = & SGL_Cache::singleton();
            $cacheId = 'categorySelect' . $this->_startId . serialize($this->_options);
            if ($data = $cache->get($cacheId, 'categorySelect')) {
                $ret = unserialize($data);
                SGL::logMessage('categorySelect from cache', PEAR_LOG_DEBUG);
            } else {
                SGL::logMessage('categorySelect from db', PEAR_LOG_DEBUG);
                $ret = $this->GUI->render($this->_startId);
                $data = serialize($ret);
                $cache->save($data, $cacheId, 'categorySelect');
            }
            break;

        case 'menu_anchortoc':
            $ret = $this->GUI->render($this->_startId);
            break;

        default:
            $ret = 'incorrect menu type specified';
        }
        return $ret;
    }

    function getBreadCrumbs($catID, $links = true)
    {
        return $this->GUI->getBreadCrumbs($catID, $links);
    }
}
?>