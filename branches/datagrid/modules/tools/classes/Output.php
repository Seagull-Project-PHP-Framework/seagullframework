<?php

class ToolsOutput {
    function getMultilangValue($object, $varName, $varLang, $varProp) {

        $varName = $varName . $varLang . $varNameEnd;
        return $object->$varName->$varProp;
    }

    function getVarName($varName = "", $varName1 = "", $varName2 = "") {
        $varName .= $varName1 .= $varName2;
        return $varName;
    }

    function getObjectValue($object, $value, $length = null) {
        if (is_null($length) || strlen($object->$value) < $length)
            return $object->$value;
        else
            return substr($object->$value, 0, $length) . '...';
    }

    function getArrayValue($array, $value, $length = null, $isCurrency = false) {
        if (is_null($length) || strlen($array[$value]) < $length)
            if($isCurrency) {
                return number_format($array[$value], 2);
            }
            else {
                return $array[$value];
            }
        else
            return substr($array[$value], 0, $length) . '...';
    }

    /**
     * Converts date (may be in the ISO, TIMESTAMP or UNIXTIME format) into dd.mm.yyyy.
     *
     * @access  public
     * @param   string  $input  date (may be in the ISO, TIMESTAMP or UNIXTIME format) value
     * @return  string  $output user-friendly format (european)
     */
    function formatDate($date)
    {
        return SGL_Date::format($date);
    }
    
    function getDateArrayValue($array, $value, $length = null) {
        $tempDate = $this->getArrayValue($array, $value, $length);
        return $this->formatDate($tempDate);
    }

    function getActionValue($action, $valueObj) {
        $subject = $action;
        foreach ($valueObj as $key => $value) {
            $replace = $value;
            $search = "{".$key."}";
            $result = ereg_replace($search, $replace, $subject);
            $subject = $result;
        }
        return $subject;
    }

    function getVarNameAndArrayValue($array, $value, $varName) {
        $temp = $array[$value.$varName];
        if ($this->formatDate2DB($temp)) {
            return $this->formatDate($temp);
        }
        return $temp;
    }

    function setTemplateFields($templateField, $templateFieldValue) {
        $this->$templateField = $templateFieldValue;
    }

    function isEqual($object1, $object2) {
        return $object1 == $object2;
    }

    function orEqual($firstObject, $object1, $object2, $object3) {
        return (($firstObject == $object1) || ($firstObject == $object2) || ($firstObject == $object3));
    }

    function isArray($array) {
        return is_array($array);
    }

    function isEqualWithArrayValue($object, $array, $value) {
        return $object == $this->getArrayValue($array, $value);
    }

    function arrayNotEmpty($array) {
        if (count($array) >= 1) {
            return true;
        }
        return false;
    }

    function isGreater($object1, $object2) {
        return $object1 > $object2;
    }
    
    /**
     * Formats date for the current user
     * @param   string  $sDate  Date in user or DB format  (YYYY-mm-dd or dd.mm.yyyy)
     * @return  string  Date formatted for the DB format (YYYY-mm-dd)
     *                   if date is not proper - return null
     */
    function formatDate2DB($sDate) {
        //check if date is in correct format
        if (preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2}/", $sDate)) {
            $aDate = explode("-", $sDate);
            if (checkdate($aDate[1], $aDate[2], $aDate[0])) {
                return date("Y-m-d", mktime (0, 0, 0, $aDate[1], $aDate[2], $aDate[0]));
            }
        } elseif (preg_match("/[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}/", $sDate)) {
            $aDate = explode(".", $sDate);
            if (checkdate($aDate[1], $aDate[0], $aDate[2])) {
                    return date("Y-m-d", mktime (0, 0, 0, $aDate[1], $aDate[0], $aDate[2]));
            }
        } elseif (strcmp($sDate, "now()") == 0) {
            return $sDate;
        }
        return null;
    }
    
    /**
     * Formats datetime for the current user
     * @param   string  $sDateTime  Datetime in user or DB format
     * (YYYY-mm-dd HH:mm:ss or dd.mm.yyyy HH:mm:ss or YYYY-mm-dd or dd.mm.yyyy)
     * @return  string  Datetime formatted for the DB format (YYYY-mm-dd)
     * or (YYYY-mm-dd HH:mm:ss) if hours set; if date is not proper - return null
     */
    function formatDateTime2DB($sDateTime) {
        //check if date is in correct format
        $sResult = null;
        $aDateTime = explode("\ ", $sDateTime);
        $sDate = $aDateTime[0];
        $sTime = $aDateTime[1];
        if (preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2}/", $sDate)) {
            $aDate = explode("-", $sDate);
            if (checkdate($aDate[1], $aDate[2], $aDate[0])) {
                $sResult = date("Y-m-d", mktime (0,0,0, $aDate[1], $aDate[2], $aDate[0]));
            }
        } elseif (preg_match("/[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}/", $sDate)) {
            $aDate = explode(".", $sDate);
            if (checkdate($aDate[1], $aDate[0], $aDate[2])) {
                $sResult = date("Y-m-d", mktime (0,0,0, $aDate[1], $aDate[0], $aDate[2]));
            }
        } elseif (strcmp($sDate, "now()") == 0) {
            $sResult = $sDate;
        }
        if ($sTime != "") {
            $sResult .= " ".$sTime;
        }
        return $sResult;
    }
    
    /**
     * gets path to specified file in theme
     *
     * @access  public
     * @static
     * @param   string  $fileName           file name located in theme
     */
    function getThemeFileDir($fileName) {
        $fileDir = SGL_WWW_ROOT . '/themes/' . $fileName;
        if (file_exists($fileDir)) {
            return $fileDir;
        }
        return null;
    }

    /**
     * gets URL to specified file in theme
     *
     * @access  public
     * @static
     * @param   string  $fileName           file name located in theme
     */
    function getThemeFileURL($fileName) {
        return SGL_BASE_URL . '/themes/' . $fileName;
    }

    function getUserLanguages() {
        //AM this code goes from PEAR::Net_UserAgent_Detect class
        $languages = preg_split(';[\s,]+;', substr(getenv('HTTP_ACCEPT_LANGUAGE'), 0, strpos(getenv('HTTP_ACCEPT_LANGUAGE') . ';', ';')), -1, PREG_SPLIT_NO_EMPTY);
        if (empty($languages)) {
            $languages = array('en');
        }
        return $languages;
    }
    
        

    function getPreferredLanguage() {
        $languages = SGL_Output::getUserLanguages();
        return $languages[0];
    }
};
?>