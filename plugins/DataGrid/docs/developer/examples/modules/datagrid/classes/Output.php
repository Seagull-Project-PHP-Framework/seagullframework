<?php

class ToolsOutput 
{

    function getMultilangValue($object, $varName, $varLang, $varProp) 
    {
        $varName = $varName . $varLang . $varNameEnd;
        if ($varProp == 'label') {
            return SGL_string::translate($object->$varName->$varProp);
        } else {
            return $object->$varName->$varProp;
        }
    }

    function getVarName($varName = "", $varName1 = "", $varName2 = "") 
    {
        $varName .= $varName1 .= $varName2;
        return $varName;
    }


    function getArrayValue($array, $value, $length = null, $isCurrency = false) 
    {
        if (is_null($length) || strlen($array[$value]) < $length) {
            if ($isCurrency) {
                return number_format($array[$value], 2);
            } else {
                return $array[$value];
            }
        } else {
            return substr($array[$value], 0, $length) . '...';
        }
    }

    function getArrayTranslateValue($array, $value, $length = null, $isCurrency = false) 
    {
        return SGL_String::translate($array[$value]);
    }

    function getDateArrayValue($array, $value, $length = null) 
    {
        $tempDate = $this->getArrayValue($array, $value, $length);
        return $this->formatDate($tempDate);
    }

    function getDateTimeArrayValue($array, $value, $length = null) 
    {
        $tempDate = $this->getArrayValue($array, $value, $length);
        include_once 'Date.php';
        $date = & new Date($tempDate);
        return $date->format('%d.%m.%Y %H:%M');
    }

    function getDateTime2ArrayValue($array, $value, $varName) 
    {
        if ($array[$value.$varName] != '') {
            $tempDate = $this->getArrayValue($array, $value.$varName);
            include_once 'Date.php';
            $date = & new Date($tempDate.':00');
            return $date->format('%d.%m.%Y %H:%M');
        } else {
            return '';
        }
    }

    function getActionValue($action, $valueObj, $cut = true) 
    {
        $subject = $action;
        foreach ($valueObj as $key => $value) {
            $replace = $value;
            $search = "{".$key."}";
            $result = ereg_replace($search, $replace, $subject);
            $subject = $result;
        }
        $subject = str_replace("\n"," ",$subject);
        $subject = str_replace("\r"," ",$subject);
        $subjectLength = strlen($subject);
        if ($cut) {
            $subject = substr($subject,0,150);
            if ($subjectLength > 150) {
                $subject .= ' ...';
            }
        }
        return $subject;
    }

    function getVarNameAndArrayValue($array, $value, $varName) 
    {
        $temp = $array[$value.$varName];
        if ($this->formatDate2DB($temp)) {
            return $this->formatDate($temp);
        }
        return $temp;
    }

    function setTemplateFields($templateField, $templateFieldValue) 
    {
        $this->$templateField = $templateFieldValue;
    }

    function orEqual($firstObject, $object1, $object2, $object3 = '', $object4 = '') 
    {
        return (($firstObject == $object1) || ($firstObject == $object2) 
                 || ($firstObject == $object3) || ($firstObject == $object4));
    }


    function isEqualWithArrayValue($object, $array, $value) 
    {
        return $object == $this->getArrayValue($array, $value);
    }

    function arrayNotEmpty($array) 
    {
        if (count($array) >= 1) {
            return true;
        }
        return false;
    }

    function isGreater($object1, $object2) 
    {
        return $object1 > $object2;
    }


    /**
     * Formats date for the current user
     * @param   string  $sDate  Date in user or DB format  (YYYY-mm-dd or dd.mm.yyyy)
     * @return  string  Date formatted for the DB format (YYYY-mm-dd)
     *                   if date is not proper - return null
     */
    function formatDate2DB($sDate) 
    {
        //check if date is in correct format
        if (preg_match("/([0-9]{4}-[0-9]{2}-[0-9]{2})$/", $sDate)) {
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

    function makeValidLinks($links)
    {
        return str_replace("&nbsp;&nbsp;&nbsp;", "&nbsp;", $links);
    }

    /**
     * Formats datetime for the current user
     * @param   string  $sDateTime  Datetime in user or DB format
     * (YYYY-mm-dd HH:mm:ss or dd.mm.yyyy HH:mm:ss or YYYY-mm-dd or dd.mm.yyyy)
     * @return  string  Datetime formatted for the DB format (YYYY-mm-dd)
     * or (YYYY-mm-dd HH:mm:ss) if hours set; if date is not proper - return null
     */
    function formatDateTime2DB($sDateTime) 
    {
        //check if date is in correct format
        $sResult = null;
        $aDateTime = explode(" ", $sDateTime);
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
}
?>