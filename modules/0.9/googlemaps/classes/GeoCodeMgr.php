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
// | GeoCodeMgr.php                                                            |
// +---------------------------------------------------------------------------+
// | Author: Steven Stremciuc <steve@freeslacker.net>                          |
// +---------------------------------------------------------------------------+

require_once 'HTTP/Request.php';

class GeoCodeMgr extends SGL_Manager
{
    function GeoCodeMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->template     = 'geoCodeManager.html';
        $this->pageTitle    = 'Geo Code Manager';

        $this->_aActionsMapping =  array(
            'view'      => array('view'),
            'doGeoCode' => array('doGeoCode', 'redirectToDefault'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->validated    = true;
        $input->template    = $this->template;
        $input->pageTitle   = $this->pageTitle;

        $input->geocode     = (object) $req->get('geocode');
        $input->submitted   = $req->get('submitted');
        if ($input->submitted) {
            $input->geocode->start_id   = (int) $input->geocode->start_id;
            $input->geocode->end_id     = (int) $input->geocode->end_id;
            $input->overwrite           = (isset($input->geocode->overwrite) && $input->geocode->overwrite) ? 'checked' : '';
        }

        $input->action      = ($req->get('action')) ? $req->get('action') : 'view';
    }

    function _cmd_view(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
    }

    function _cmd_doGeoCode(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  get existing geocoded users
        $query = "
            SELECT  usr_id
            FROM    {$this->conf['table']['googlemaps_user_geocode']}
            ";
        $aUserIds = $this->dbh->getCol($query);

        //  get all users
        $query = "
                SELECT  *
                FROM    {$this->conf['table']['user']}
                WHERE   usr_id >= {$input->geocode->start_id}
                ";
        if ($input->geocode->end_id !== 0) {
            $query .= " AND usr_id <= {$input->geocode->end_id}";
        }
        $oUsers = $this->dbh->getAssoc($query, false, array(), DB_FETCHMODE_OBJECT);

        //  geocode users
        foreach ($oUsers as $oUser) {
            //  make sure we don't geo code a user we aren't going to update
            if (!$input->overwrite && in_array($oUser->usr_id, $aUserIds)) {
                continue;
            }

            $state = ($oUser->country == 'US')
                        ? $oUser->region
                        : $oUser->country;

            $url    = 'http://api.local.yahoo.com/MapsService/V1/geocode?appid=SeagullFramework'
                    . '&street=' . urlencode($oUser->addr_1)
                    . '&city=' . urlencode($oUser->city)
                    . '&state=' . urlencode($state)
                    . '&output=php';

            $req =& new HTTP_Request($url);
            if (!PEAR::isError($req->sendRequest())) {
                $serializedResponse = $req->getResponseBody();
            }
            //  the notice muffler on unserialize is necessary because yahoo does
            //  not return a serialized string if it can't parse the address
            $aResponse = @unserialize($serializedResponse);
            if (!$aResponse) {
                //  reduce it to the city level
                $url    = 'http://api.local.yahoo.com/MapsService/V1/geocode?appid=SeagullFramework'
                        . '&city=' . urlencode($oUser->city)
                        . '&state=' . urlencode($state)
                        . '&output=php';

                $req =& new HTTP_Request($url);
                if (!PEAR::isError($req->sendRequest())) {
                    $serializedResponse = $req->getResponseBody();
                }
                $aResponse = @unserialize($serializedResponse);
                if (!$aResponse) {
                    //  reduce it to the state/country level
                    $url    = 'http://api.local.yahoo.com/MapsService/V1/geocode?appid=SeagullFramework'
                            . '&state=' . urlencode($state)
                            . '&output=php';
    
                    $req =& new HTTP_Request($url);
                    if (!PEAR::isError($req->sendRequest())) {
                        $serializedResponse = $req->getResponseBody();
                    }
                    $aResponse = @unserialize($serializedResponse);
                }
            }
            //  see if we got any results at this point
            if (is_array($aResponse) && isset($aResponse['ResultSet']) && is_array($aResponse['ResultSet'])) {
                $aResult = $aResponse['ResultSet'];
                $update = (in_array($oUser->usr_id, $aUserIds)) ? true : false;
                if (isset($aResult['Result']['precision'])) {
                    // only one result
                    $this->saveUserGeoCode($oUser->usr_id, $aResult['Result']['Latitude'], $aResult['Result']['Longitude'], $aResult['Result']['precision'], $update);
                } else {
                    if (isset($aResult['Result'][0]['precision'])) {
                        // use first result returned
                        $this->saveUserGeoCode($oUser->usr_id, $aResult['Result'][0]['Latitude'], $aResult['Result'][0]['Longitude'], $aResult['Result'][0]['precision'], $update);
                    }
                }
            }
        }
    }

    function saveUserGeoCode($uid, $latitude, $longitude, $precision, $update)
    {
        require_once 'DB/DataObject.php';
        $geo = DB_DataObject::factory($this->conf['table']['googlemaps_user_geocode']);
        if ($update) {
            $geo->whereAdd('usr_id = ' . $uid);
        } else {
            $geo->googlemaps_user_geocode_id = $this->dbh->nextId('googlemaps_user_geocode');
        }
        $geo->usr_id = $uid;
        $geo->latitude = $latitude;
        $geo->longitude = $longitude;
        $geo->precision_estimate = $precision;
        $geo->last_updated = SGL_Date::getTime(true);
        $ok = ($update) ? $geo->update() : $geo->insert();
    }
}
?>
