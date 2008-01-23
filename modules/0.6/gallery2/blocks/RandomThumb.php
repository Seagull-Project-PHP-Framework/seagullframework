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
// | RandomThumb.php                                                           |
// +---------------------------------------------------------------------------+
// | Author: John Craig <john@iondistillery.com>                               |
// +---------------------------------------------------------------------------+
// $Id: RandomThumb.php,v 1.0 2006/10/03 00:44:52 demian Exp $

class Gallery2_Block_RandomThumb{

    var $template     = 'RandomThumb.html';
    var $templatePath = 'gallery2';

    function init(&$output, $block_id, &$aParams){
        return $this->getBlockContent($output, $aParams);
    }

    function getBlockContent($output, &$aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $blockOutput          = &new SGL_Output();
        $blockOutput->theme   = $output->theme;
        $blockOutput->webRoot = $output->webRoot;

        /* TODO: Determine if g2Dir, $g2EmbedUri, and g2Uri are available from the Gallery2Mgr */
        if (array_key_exists('g2Dir',$aParams)){
            $g2Dir      = $aParams['g2Dir'];
        } else {
            return false;
        }
        if (array_key_exists('g2EmbedUri',$aParams)){
            $g2EmbedUri = $aParams['g2EmbedUri'];
        } else {
            return false;
        }

        $g2FullInit = true;

        if (array_key_exists('g2Uri',$aParams)){
            $g2Uri      = $aParams['g2Uri'];
        } else {
            return false;
        }

        $g2Blocks   = 'randomImage'; /* Pipe(|) separate list chosen from: randomImage, recentImage, viewedImage, randomAlbum, recentAlbum, viewedAlbum, dailyImage, weeklyImage, monthlyImage, dailyAlbum, weeklyAlbum, monthlyAlbum, specificItem; default is randomImage */

        $g2ShowAry  = array();

        if (array_key_exists('g2ShowHeading',$aParams)){
            if ($aParams['g2ShowHeading']){
                $g2ShowAry[] = 'heading';
            }
        }
        if (array_key_exists('g2ShowTitle',$aParams)){
            if ($aParams['g2ShowTitle']){
                $g2ShowAry[] = 'title';
            }
        }
        if (array_key_exists('g2ShowDate',$aParams)){
            if ($aParams['g2ShowDate']){
                $g2ShowAry[] = 'date';
            }
        }
        if (array_key_exists('g2ShowViewCount',$aParams)){
              if ($aParams['g2ShowViewCount']){
                  $g2ShowAry[] = 'views';
            }
        }
        if (array_key_exists('g2ShowOwner',$aParams)){
            if ($aParams['g2ShowOwner']){
                $g2ShowAry[] = 'owner';
            }
        }

        if (count($g2ShowAry) > 0){
            $g2Show = implode("|",$g2ShowAry);
        } else {
            $g2Show = 'none';
        }

        require_once $g2Dir . '/embed.php';
        $ret = GalleryEmbed::init(array(
            'fullInit'      => $g2FullInit,
            'g2Uri'         => $g2Uri,
            'baseUri'       => $g2EmbedUri));

        if ($ret) {
            SGL::raiseMsg("GalleryEmbed::init failed, here is the error message: " . $ret->getAsHtml());
            exit;
        }
        /*
         * See "Site admin" -> "image block" for all available options. the parameters are the same
         * as for the external imageblock
         */
        list ($ret, $bodyHtml, $headHtml) = GalleryEmbed::getImageBlock(array(
            'blocks' => $g2Blocks,
            'show'   => $g2Show));

        if ($ret) {
            SGL::raiseMsg("GalleryEmbed::getImageBlock failed, here is the error message: " . $ret->getAsHtml());
            exit;
        }

        $blockOutput->randomThumb = $bodyHtml;

        return $this->process($blockOutput);
    }

    function process(&$output)
    {
        // use moduleName for template path setting
        $output->moduleName     = $this->templatePath;
        $output->masterTemplate = $this->template;

        $view = new SGL_HtmlSimpleView($output);
        return $view->render();
    }

}

?>