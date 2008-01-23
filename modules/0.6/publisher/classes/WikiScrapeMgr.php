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
// | WikiScrapeMgr.php                                                         |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: WikiScrapeMgr.php,v 1.2 2005/04/17 02:15:02 demian Exp $

/**
 * Content scraper.
 *
 * @package publisher
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.2 $
 */
class WikiScrapeMgr extends SGL_Manager
{
    var $html = '';

    function WikiScrapeMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle    = 'WikiScrape Manager';
        $this->template     = 'wikiScrapeList.html';
        $this->_aActionsMapping =  array(
            'list'      => array('list'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->validated       = true;
        $input->error          = array();
        $input->pageTitle      = $this->pageTitle;
        $input->masterTemplate = $this->masterTemplate;
        $input->template       = $this->template;
        $input->action         = ($req->get('action')) ? $req->get('action') : 'list';
        $input->wikiUrl        = urldecode($req->get('url'));
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (!SGL::isPhp5() || !extension_loaded('tidy')) {
            return SGL::raiseError('You need to be running PHP5 and have the Tidy extension enabled'
                .' to use this manager.');
        }
        $aLines = file($input->wikiUrl);
        $options = array(
            'wrap' => 0,
            'indent' => true,
            'indent-spaces' => 4,
            'output-xhtml' => true,
            'drop-font-tags' => false,
            'clean' => false,
        );

        if (count($aLines)) {
            $tidy = new Tidy();
            $tidy->parseString(implode("\n", $aLines), $options, 'utf8');
            $tidy->cleanRepair();

            $tree = $tidy->body();

            $this->traverseTree($tree);
            $output->wikiHtml = $this->html;
        }
    }

    function traverseTree($node)
    {
        /* Put something there if the node name is empty */
        $nodename = trim(strtoupper($node->name));
        $nodename = (empty($nodename)) ? "[EMPTY]" : $nodename;

        if (@$node->id == TIDY_TAG_DIV && $node->attribute['class'] == 'page') {
            $this->html .= $node->value;
        }

        /* Recurse along the children to generate the remaining nodes */
        if ($node->hasChildren()) {
            foreach($node->child as $child) {
                $this->html .= $this->traverseTree($child);
            }
        }
    }
}
?>