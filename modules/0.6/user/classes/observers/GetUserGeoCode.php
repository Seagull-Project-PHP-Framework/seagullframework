<?php
require_once 'HTTP/Request.php';

class GetUserGeoCode
{
    function update($observable)
    {
        $state = ($observable->oUser->country == 'US')
                    ? $observable->oUser->region
                    : $observable->oUser->country;

        $url    = 'http://api.local.yahoo.com/MapsService/V1/geocode?appid=SeagullFramework'
                . '&street=' . urlencode($observable->oUser->addr_1)
                . '&city=' . urlencode($observable->oUser->city)
                . '&state=' . urlencode($state)
                . '&output=php';

        $req =& new HTTP_Request($url);
        if (!PEAR::isError($req->sendRequest())) {
            $serializedResponse = $req->getResponseBody();
        }
        $aResponse = @unserialize($serializedResponse);
        if (!$aResponse) {
            $url    = 'http://api.local.yahoo.com/MapsService/V1/geocode?appid=SeagullFramework'
                    . '&city=' . urlencode($observable->oUser->city)
                    . '&state=' . urlencode($state)
                    . '&output=php';

            $req =& new HTTP_Request($url);
            if (!PEAR::isError($req->sendRequest())) {
                $serializedResponse = $req->getResponseBody();
            }
            $aResponse = @unserialize($serializedResponse);
            if (!$aResponse) {
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
        if (is_array($aResponse) && isset($aResponse['ResultSet']) && is_array($aResponse['ResultSet'])) {
            $aResult = $aResponse['ResultSet'];
            if (isset($aResult['Result']['precision'])) {
                // only one result
                $this->insertUserGeoCode($observable, $aResult['Result']['Latitude'], $aResult['Result']['Longitude'], $aResult['Result']['precision']);
            } else {
                if (isset($aResult['Result'][0]['precision'])) {
                    // use first result returned
                    $this->insertUserGeoCode($observable, $aResult['Result'][0]['Latitude'], $aResult['Result'][0]['Longitude'], $aResult['Result'][0]['precision']);
                }
            }
        }
    }

    function insertUserGeoCode(&$observable, $latitude, $longitude, $precision)
    {
        require_once 'DB/DataObject.php';
        $dbh =& SGL_DB::singleton();
        $geo = DB_DataObject::factory($observable->conf['table']['googlemaps_user_geocode']);
        $geo->googlemaps_user_geocode_id = $dbh->nextId('googlemaps_user_geocode');
        $geo->usr_id = $observable->oUser->usr_id;
        $geo->latitude = $latitude;
        $geo->longitude = $longitude;
        $geo->precision_estimate = $precision;
        $geo->last_updated = SGL_Date::getTime(true);
        $ok = $geo->insert();
    }
}
?>