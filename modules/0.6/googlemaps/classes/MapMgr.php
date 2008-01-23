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
// | MapMgr.php                                                                |
// +---------------------------------------------------------------------------+
// | Author: Steven Stremciuc <steve@freeslacker.net>                          |
// +---------------------------------------------------------------------------+

class MapMgr extends SGL_Manager
{
    function MapMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->_aActionsMapping =  array(
            'view'  => array('view'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->validated = true;

        if (empty($this->conf['MapMgr']['apiKey'])) {
            $this->validated = false;
            SGL::raiseMsg('You must enter an api key');
        }

        $input->action = ($req->get('action')) ? $req->get('action') : 'view';
    }

    function _cmd_view(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'viewMap.html';
        $apiKey = $this->conf['MapMgr']['apiKey'];
        $output->addJavascriptFile('http://maps.google.com/maps?file=api&v=2&key=' . $apiKey);
        $output->addJavascriptFile('googlemaps/js/pruner.js');
        $output->addJavascriptFile('googlemaps/js/googleMaps.js');
        $output->addOnLoadEvent('loadGoogleMap()');
        $output->addOnUnloadEvent('GUnload()');

        $output->aCodes = $this->getUserGeoCodes();
    }

    function getUserGeoCodes()
    {
        $query = "
            SELECT  latitude, longitude
            FROM    {$this->conf['table']['googlemaps_user_geocode']}
            ";

        $aCodes = $this->dbh->getAll($query, array(), DB_FETCHMODE_ASSOC);
        return $aCodes;
    }
}
?>