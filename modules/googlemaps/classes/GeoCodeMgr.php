<?php

require_once 'HTTP/Request.php';

class GeoCodeMgr extends SGL_Manager
{
    function GeoCodeMgr()
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

        $input->startId = ($req->get('startId')) ? (int) $req->get('startId') : 0;
        $input->endId   = ($req->get('endId')) ? (int) $req->get('endId') : 0;

        $input->action = ($req->get('action')) ? $req->get('action') : 'view';
    }

    function _cmd_view(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  get users
        $query = "
                SELECT  *
                FROM    {$this->conf['table']['user']}
                WHERE   usr_id >= $input->startId
                ";
        if ($input->endId !== 0) {
            $query .= " AND usr_id <= $input->endId";
        }
        $oUsers = $this->dbh->getAssoc($query, false, array(), DB_FETCHMODE_OBJECT);

        foreach ($oUsers as $oUser) {
            $state = ($oUser->country == 'US')
                        ? $oUser->region
                        : $oUser->country;

            $url    = 'http://api.local.yahoo.com/MapsService/V1/geocode'
                    . '?appid=SeagullFramework'
                    . '&street=' . urlencode($oUser->addr_1)
                    . '&city=' . urlencode($oUser->city)
                    . '&state=' . urlencode($state)
                    . '&output=php';

            $req =& new HTTP_Request($url);
            if (!PEAR::isError($req->sendRequest())) {
                $serializedResponse = $req->getResponseBody();
            }
            $aResponse = unserialize($serializedResponse);
            if (is_array($aResponse) && isset($aResponse['ResultSet']) && is_array($aResponse['ResultSet'])) {
                $aResult = $aResponse['ResultSet'];
                if (isset($aResult['Result']['precision'])) {
                    // only one result
                    $this->insertUserGeoCode($oUser->usr_id, $aResult['Result']['Latitude'], $aResult['Result']['Longitude'], $aResult['Result']['precision']);
                } else {
                    if (isset($aResult['Result'][0]['precision'])) {
                        // use first result returned
                        $this->insertUserGeoCode($oUser->usr_id, $aResult['Result'][0]['Latitude'], $aResult['Result'][0]['Longitude'], $aResult['Result'][0]['precision']);
                    }
                }
            }
        }
    }

    function insertUserGeoCode($uid, $latitude, $longitude, $precision)
    {
        require_once 'DB/DataObject.php';
        $geo = DB_DataObject::factory($this->conf['table']['googlemaps_user_geocode']);
        $geo->googlemaps_user_geocode_id = $this->dbh->nextId('googlemaps_user_geocode');
        $geo->usr_id = $uid;
        $geo->latitude = $latitude;
        $geo->longitude = $longitude;
        $geo->precision_estimate = $precision;
        $geo->last_updated = SGL_Date::getTime(true);
        $ok = $geo->insert();
    }
}
?>
