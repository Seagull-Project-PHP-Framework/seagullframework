<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2010, Demian Turner                                         |
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
// | Seagull 1.0                                                               |
// +---------------------------------------------------------------------------+
// | Output.php                                                                |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: Output.php,v 1.22 2005/06/04 23:56:33 demian Exp $

/**
 * High level HTML transform methods, 'Template Helpers' in Yahoo speak, 50% html,
 * 50% php.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.22 $
 * @todo    look at PEAR::Date to improve various date methods used here
 */
class SGL_Output
{
    var $onLoad = '';
    var $aOnLoadEvents = array();
    var $onUnload = '';
    var $aOnUnloadEvents = array();
    var $onReadyDom = '';
    var $aOnReadyDomEvents = array();
    var $aJavascriptFiles = array();
    var $aRawJavascriptFiles = array();
    var $aCssFiles = array();
    var $aHeaders = array();
    var $httpProtocol = 'HTTP/1.0';
    var $aStatusCodes = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',

        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',  // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',

        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',

        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );


    /**
     * @access private
     *
     * @var array
     */
    var $_aJsExportVars = array();

    function __construct()
    {
        $this->exportJsVar('MSG_ERROR', SGL_MESSAGE_ERROR);
        $this->exportJsVar('MSG_INFO', SGL_MESSAGE_INFO);
        $this->exportJsVar('MSG_WARNING', SGL_MESSAGE_WARNING);
        $this->exportJsVar('FC', SGL_Config::get('site.frontScriptName'));
        $this->exportJsVar('WEBROOT', SGL_BASE_URL);
    }

    function SGL_Output()
    {
        $this->__construct();
    }

    /**
     * Translates source text into target language.
     *
     * @access  public
     * @static
     * @param   string  $key    translation term
     * @param   string  $filter optional filter fn, ie, strtoupper()
     * @return  string          translated text
     * @see     setLanguage()
     */
    function translate($key, $filter = false, $aParams = array(), $output = null, $lang = null)
    {
        // in case translation params are specified as a string
        // e.g. {translate(#my string to translate#,#vprintf#,#param1|value1||param2||value2#)}
        if (!empty($aParams) && is_string($aParams)) {
            $aResultParams = array();
            $aStringParams = explode('||', $aParams);
            foreach ($aStringParams as $stringPair) {
                $aStringValues = explode('|', $stringPair);
                if (isset($aStringValues[1])) {
                    if ($var = SGL_Output::_extractVariableValue($aStringValues[1], $output)) {
                        $aResultParams[$aStringValues[0]] = $var;
                    } else {
                        if (isset($output) && is_a($output, 'SGL_Output')) {
                            $aResultParams[$aStringValues[0]] = isset($output->{$aStringValues[1]})
                                ? $output->{$aStringValues[1]}
                                : null;
                        } elseif (is_a($this, 'SGL_Output')) {
                            $aResultParams[$aStringValues[0]] = isset($this->{$aStringValues[1]})
                                ? $this->{$aStringValues[1]}
                                : null;
                        }
                    }
                } else {
                    if ($var = SGL_Output::_extractVariableValue($aStringValues[0], $output)) {
                        $aStringValues[] = $var;
                    } else {
                        if (isset($output) && is_a($output, 'SGL_Output')) {
                            $aResultParams[] = isset($output->{$aStringValues[0]})
                                ? $output->{$aStringValues[0]}
                                : null;
                        } elseif (is_a($this, 'SGL_Output')) {
                            $aResultParams[] = isset($this->{$aStringValues[0]})
                                ? $this->{$aStringValues[0]}
                                : null;
                        }
                    }
                }
            }
            $aParams = $aResultParams;
        }
        return SGL_String::translate($key, $filter, $aParams, $lang);
    }

    function _extractVariableValue($varString, $output = null)
    {
        $ret = false;
        if (strpos($varString, '.') !== false) {
            $aVar = explode('.', $varString);
            if (isset($output) && is_a($output, 'SGL_Output')) {
                $var = $output->{$aVar[0]};
            } else {
                $var = $this->{$aVar[0]};
            }
            if (isset($var) && is_object($var) && isset($var->{$aVar[1]})) {
                $ret = $var->{$aVar[1]};
            }
        }
        return $ret;
    }

    function tr($key, $filter = false, $aParams = array(), $output = null, $lang = null)
    {
        return SGL_Output::translate($key, $filter, $aParams, $output, $lang);
    }

    /**
     * Generates options for an HTML select object.
     *
     * @access  public
     * @param   array   $array      hash of select values
     * @param   mixed   $selected   default selected element, array for multiple elements
     * @param   boolean $multiple   true if multiple
     * @param   array   $options    attibutes to add to the input tag : array() {"class" => "myClass", "onclick" => "myClickEventHandler()"}
     * @return  string  select options
     */
    function generateSelect($aValues, $selected = null, $multiple = false,
                            $options = null, $translate = false)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (!is_array($aValues) || (isset($options) && !is_array($options))) {
            SGL::raiseError('Incorrect param passed to ' . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
        if (is_numeric($selected)) {
            $selected = (int) $selected;
        }
        $optionsString = '';
        if (isset($options)) {
            foreach ($options as $k => $v) {
                $optionsString .= ' ' . $k . '="' . $v . '"';
            }
        }
        $r = '';
        if ($multiple && is_array($selected)) {
            foreach ($aValues as $k => $v) {
                $isSelected = in_array($k, $selected) ? ' selected="selected"' : '';
                if ($translate) {
                    $v = SGL_String::translate($v);
                }
                $r .= "\n<option value=\"$k\"" . $isSelected . $optionsString . ">" . $v . "</option>";
            }
        } else {
            //  ensure $selected is not the default null arg, allowing
            //  zeros to be selected array elements
            $r = '';
            if (!is_array($aValues))
                return $r;
            foreach ($aValues as $k => $v) {

                if ($translate) {
                    // FIXME: remove this, spaces must be in template
                    // avoid trying to translate strings prepended with "&nbsp;"
                    $ok = preg_match('/^((\&nbsp\;)+)(.*)/', $v, $aMatches);
                    if ($ok) {
                        $v = $aMatches[1] . SGL_String::translate($aMatches[3]);
                    } else {
                        $v = SGL_String::translate($v);
                    }
                }
                $isSelected = ($k === $selected && !is_null($selected)) ? ' selected="selected"' : '';
                $r .= "\n<option value=\"$k\"". $isSelected . $optionsString . ">" . $v . "</option>";
            }
        }
        return $r;
    }

    /**
     * Generates sequence checkboxes.
     *
     * @access  public
     * @param   array   $hElements  hash of checkbox values
     * @param   array   $aChecked   array of checked elements
     * @param   string  $groupName  name of element group
     * @param   array   $options    attibutes to add to the input tag : array() {"class" => "myClass", "onclick" => "myClickEventHandler()"}
     * @return  string  html        list of checkboxes
     */
    function generateCheckboxList($hElements, $aChecked, $groupName, $options = null)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (!is_array($hElements) || !is_array($aChecked) || (isset($options) && !is_array($options))) {
            SGL::raiseError('Incorrect param passed to ' . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
            return false;
        }
        $optionsString = '';
        if (isset($options)) {
            foreach ($options as $k => $v) {
                $optionsString .= ' ' . $k . '="' . $v . '"';
            }
        }
        $html = '';
        foreach ($hElements as $k => $v) {
            $isChecked = (in_array($k, $aChecked)) ? ' checked' : '';
            $html .= "<input class='noBorder' type='checkbox' name='$groupName' " .
                     "id='$groupName-$k' value='$k'" . $optionsString . " $isChecked><label for='$groupName-$k'>$v</label><br />\n";
        }
        return $html;
    }

    /**
     * Generate checkbox.
     *
     * @access  public
     * @param   string   $name       element name
     * @param   string   $value      element value
     * @param   boolean  $checked    is checked
     * @param   array   $options     attibutes to add to the input tag : array() {"class" => "myClass", "onclick" => "myClickEventHandler()"}
     * @return  string  html         checkbox tag w/label
     */
    function generateCheckbox($name, $value, $checked, $options = null)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (isset($options) && !is_array($options)) {
            SGL::raiseError('Incorrect param passed to ' . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
            return false;
        }
        $isChecked = $checked ? ' checked' : '';
        $optionsString = '';
        if (isset($options)) {
            foreach ($options as $k => $v) {
                $optionsString .= ' ' . $k . '="' . $v . '"';
            }
        }
        $html = "<input class='noBorder' type='checkbox' name='$name' " .
            "id= '$name' value='$value'" . $optionsString . " $isChecked><label for='$name'>$value</label><br />\n";
        return $html;
    }

    /**
     * Generates a yes/no radio pair.
     *
     * @access  public
     * @param   string   $radioName  name of radio element
     * @param   boolean  $checked    is checked
     * @param   array   $options     attibutes to add to the input tag : array() {"class" => "myClass", "onclick" => "myClickEventHandler()"}
     * @return  string   html        yes/no radio pair
     */
    function generateRadioPair($radioName, $checked, $options = null)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (isset($options) && !is_array($options)) {
            SGL::raiseError('Incorrect param passed to ' . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
            return false;
        }
        $radioString = '';
        if ($checked) {
            $yesChecked = ' checked="checked"';
            $noChecked = '';
        } else {
            $yesChecked = '';
            $noChecked = ' checked="checked"';
        }
        $optionsString = '';
        if (isset($options)) {
            foreach ($options as $k => $v) {
                $optionsString .= ' ' . $k . '="' . $v . '"';
            }
        }
        $radioString .= "<input type='radio' name='$radioName' value='0'" . $optionsString . " $noChecked />".SGL_String::translate('no')."\n";
        $radioString .= "<input type='radio' name='$radioName' value='1'" . $optionsString . " $yesChecked />".SGL_String::translate('yes')."\n";
        return $radioString;
    }

    /**
     * Generates sequence of radio button from array.
     *
     * @access  public
     * @param   array   $elements   array of  values or radio button
     * @param   string  $selected   selected key (there can be only one selected element in a radio list)
     * @param   string  $groupname  usually an array name that will contain all elements
     * @param   integer $newline    how many columns to display for this radio group (one if not informed)
     * @param   array   $options    attibutes to add to the input tag : array() {"class" => "myClass", "onclick" => "myClickEventHandler()"}
     * @param   boolean $inTable    true for adding table formatting
     * @return  string  $html       a list of radio buttons
     */
    function generateRadioList($elements, $selected, $groupname, $newline = false, $inTable = true, $options = null)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (!is_array($elements) || (isset($options) && !is_array($options))) {
            SGL::raiseError('Incorrect param passed to ' . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
            return false;
        }
        $elementcount = count($elements);
        $html = '';
        $i = 0;
        $optionsString = '';
        if (isset($options)) {
            foreach ($options as $k => $v) {
                $optionsString .= ' ' . $k . '="' . $v . '"';
            }
        }
        if ($inTable){
            foreach ($elements as $k => $v) {
                $i = $i + 1;
                $html .= "<input name='" . $groupname . "' type='radio' value='" . $k . "'" . $optionsString . " ";
                if ($selected == $k ){
                    $html .= " checked='checked'";
                }
                $html .= " />$v ";
                if ($newline) {
                    $modvalue = $i % $newline;
                    if ($modvalue == 0 ) {
                        $html .= "<br/>\n";
                    }
                }
            }
        } else {
            $html ="<table>";
            $html .="<tr>";
            foreach ($elements as $k => $v) {
                $i = $i + 1;
                $html .= "<td nowrap='nowrap'><input name='" . $groupname . "' type='radio' value='" . $k . "'" . $optionsString . " ";
                if ($selected == $k ) {
                    $html .= " checked='checked'";
                }
                $html .= " />$v </td>\n";
                if ($newline) {
                    $modvalue = $i % $newline;
                    if ( $modvalue == 0 ) {
                        if ($i < $elementcount){
                            $html .="</tr>\n<tr>";
                        } else {
                            $html .="</tr>\n";
                        }
                    }
                }
            }
            $html .="</table>";
        }
        return $html;
    }

    /**
     * Wrapper for SGL_String::formatBytes(),
     * Converts bytes to Kb or MB as appropriate.
     *
     * @access  public
     * @param   int $bytes
     * @return  int kb/MB
     */
    function formatBytes($size)
    {
        return SGL_String::formatBytes($size);
    }

    // +---------------------------------------+
    // | Date related methods                  |
    // +---------------------------------------+

    /**
     * Converts date (may be in the ISO, TIMESTAMP or UNIXTIME format) into dd.mm.yyyy.
     *
     * @access  public
     * @param   string  $input  date (may be in the ISO, TIMESTAMP or UNIXTIME format) value
     * @return  string  $output user-friendly format (european)
     */
    function formatDate($date = '')
    {
        if (empty($date)) {
            $date = SGL_Date::getTime();
        }
        return SGL_Date::format($date);
    }

    /**
     * Converts date (may be in the ISO, TIMESTAMP or UNIXTIME format) into "Mar 31, 2003 18:29".
     *
     * @access  public
     * @param   string  $date  Date (may be in the ISO, TIMESTAMP or UNIXTIME format) value
     * @return  string  $formatted  user-friendly format (european)
     */
    function formatDatePretty($date = '')
    {
        if (empty($date)) {
            $date = SGL_Date::getTime();
        }
        return SGL_Date::formatPretty($date);
    }

    /**
     * Gets appropriate date format
     *
     * @access  public
     * @return  string  $date template (e.g. "%d %B %Y, %H:%M" for FR date format)
     */
    function getDateFormat()
    {
        return SGL_Date::getDateFormat();
    }

    /**
     * Wrapper for SGL_Date::showDateSelector(),
     * Generates date/time selector widget.
     *
     * @access  public
     * @param   array   $aDate
     * @param   string  $sFormName  name of form
     * @param   boolean $bShowTime  toggle to display HH:MM:SS
     * @param   bool    $asc
     * @param   int     $years      number of years to show
     * @return  string  $html       html for widget
    */
    function showDateSelector($aDate, $sFormName, $bShowTime = true, $asc = true, $years = 5)
    {
        return SGL_Date::showDateSelector($aDate, $sFormName, $bShowTime, $asc, $years);
    }

    /**
     * Creates a checkbox for infinite Articles (no expiry)
     *
     * @access public
     * @param  array $aDate if NULL checkbox is checked
     * @param  string $sFormName Name of Date Selector to reset if checkbox is clicked
     * @return string with checkbox. Name of checkbox will be $sFormName.NoExpire, e.g. ExpiryDateNoExpire
     */
    function getNoExpiryCheckbox($aDate,$sFormName)
    {
        $checked = ($aDate == null) ? 'checked' : '';
        return '<input type="checkbox" name="'.$sFormName.'NoExpire" id="'.$sFormName
            .'NoExpire" value="true" onClick="time_select_reset(\''.$sFormName.'\',true);"  '
            .$checked.' /> '.SGL_Output::translate('No expire');
    }

    /**
     * Generates alternate classes for rows in tables, used to switch
     * row colors.
     *
     * @access  public
     * @param   boolean $isBold
     * @param   string  $pColor optional primary color, override default
     * @param   string  $sColor optional secondary color, override default
     * @return  string  $curRowClass string representing class found in stylesheet
     */

    function switchRowClass($isBold = false, $pColor = 'backDark',
                            $sColor = 'backLight', $id = 'default')
    {
        //  remember the last color we used
        static $curRowClass;
        static $_id;

        if ($_id != $id) {
            $curRowClass = '';
            $_id = $id;
        }

        if (strpos($curRowClass, $sColor) === false) {
            $curRowClass = $sColor;
        } else {
            $curRowClass = $pColor;
        }

        if ($isBold) {
            $curRowClass .= ' bold';
        }
        return $curRowClass;
    }

    /**
     * Generates alternate value (false/true) to be used in template
     *
     * @access  public
     * @param int $elementsToCount Number of elements to reach to switch from false/true, default 2
     * @return  bool  $switcher
     */

    function switchTrueFalse($elementsToCount=2)
    {
        static $count;
        if (empty($elementsToCount)) { // reset counter
            $count = 0;
            return;
        }
        if ($count % $elementsToCount) {
            $switcher = false;
        } else {
            $switcher = true;
        }
        $count++;

        return $switcher;
    }

    /**
     * Wrapper for SGL_String::summarise(),
     * Returns a shortened version of text string.
     *
     * @access  public
     * @param   string  $str    Text to be shortened
     * @param   integer $limit  Number of characters to cut to
     * @param   string  $appendString  Trailing string to be appended
     * @return  string  $processedString    Correctly shortened text
     */
    function summarise($str, $limit=50, $element=SGL_WORD, $appendString=' ...')
    {
         $ret = SGL_String::summarise($str, $limit, $element, $appendString);
         return $ret;
    }

    /**
     * Prints formatted error message to standard out.
     * (For default_admin theme)
     *
     * @return mixed
     */
    function msgGetAdmin()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $message     = SGL_Session::get('message');
        $messageType = SGL_Session::get('messageType');
        if (isset($message) && $message != '') {
            SGL_Session::remove('message');
            SGL_Session::remove('messageType');

            switch ($messageType) {

            case SGL_MESSAGE_INFO:
                $class = 'info';
                break;

            case SGL_MESSAGE_WARNING:
                $class = 'warning';
                break;

            default:
                $class = 'error';
            }
            echo '<div class="' . $class . 'Message">' . $message . '</div>';

            //  required to remove message that persists when register_globals = on
            unset($GLOBALS['message']);
            unset($GLOBALS['messageType']);
        }
        if (SGL_Error::count()) {

            // get all errors from stack
            while ($msg = SGL_Error::pop()) {
                $msg = SGL_Error::toString($msg);
                echo '  <div class="errorContainer">
                            <div class="errorHeader">Error</div>
                            <div class="errorContent">' . $msg . '</div>
                        </div>';
            }
        } else {
            return false;
        }
    }

    /**
     * Returns true if current user or passed role ID is that of an admin.
     *
     * @return boolean
     */
    function isAdmin($rid = null)
    {
        if (is_null($rid)) {
            $rid = SGL_Session::getRoleId();
        }
        return ($rid && $rid == SGL_ADMIN) ? true : false;
    }

    /**
     * Returns true if $rid is 1 or -1.
     *
     * @return boolean
     */
    function isAdminOrUnassigned($rid)
    {
        return (abs($rid) == SGL_ADMIN) ? true : false;
    }

    function isAuthenticated()
    {
        $rid = SGL_Session::getRoleId();
        return ($rid == SGL_GUEST) ? false : true;
    }

    function addOnLoadEvent($event, $bOnReady = false)
    {
        if ($bOnReady) {
            $this->aOnReadyDomEvents[] = $event;
        } else {
            $this->aOnLoadEvents[] = $event;
        }
    }

    function addOnUnloadEvent($event)
    {
        $this->aOnUnloadEvents[] = $event;
    }

    function getOnLoadEvents()
    {
        $c =  SGL_Config::singleton();
        $conf = $c->getAll();

        if (!empty($conf['site']['globalJavascriptOnload'])) {
            $this->aOnLoadEvents[] = $conf['site']['globalJavascriptOnload'];
        }
        if (count($this->aOnLoadEvents)) {
            return $this->aOnLoadEvents;
        }
    }

    function getOnUnloadEvents()
    {
        $c =  SGL_Config::singleton();
        $conf = $c->getAll();

        if (!empty($conf['site']['globalJavascriptOnUnload'])) {
            $this->aOnUnloadEvents[] = $conf['site']['globalJavascriptOnUnload'];
        }
        if (count($this->aOnUnloadEvents)) {
            return $this->aOnUnloadEvents;
        }
    }

    function getOnReadyDomEvents()
    {
        $c =  SGL_Config::singleton();
        $conf = $c->getAll();

        if (!empty($conf['site']['globalJavascriptOnReadyDom'])) {
            $this->aOnReadyDomEvents[] = $conf['site']['globalJavascriptOnReadyDom'];
        }
        if (count($this->aOnReadyDomEvents)) {
            return $this->aOnReadyDomEvents;
        }
    }

    /**
     * For adding Javascript files to include.
     *
     * @access public
     *
     * @param mixed $file  string (file) or array of strings (files)
     *                     path/to/jsFile relative to www/ dir e.g. js/foo.js,
     *                     can also be remote js file
     *                     e.g. http://example.com/foo.js
     * @param boolean $optimize
     *
     * @return void
     */
    function addJavascriptFile($file, $optimize = true)
    {
        if ($optimize) {
// WTFWTF ????????????  aim ... fire .. at whoever wrote this ...
            $aFiles = &$this->aJavascriptFiles;
        } else {
            $aFiles = &$this->aRawJavascriptFiles;
        }
        $aIncludeFiles = !is_array($file) ? array($file) : $file;
        foreach ($aIncludeFiles as $jsFile) {
            $jsFile = strpos($jsFile, 'http://') === 0
                ? $jsFile
                : SGL_BASE_URL . '/' . $jsFile;
            if (!in_array($jsFile, $aFiles)) {
                $aFiles[] = $jsFile;
            }
        }
    }

    function getJavascriptFiles()
    {
        $aFiles = array();

        $c =  SGL_Config::singleton();
        $conf = $c->getAll();
        // Check for global files to include
        if (!empty($conf['site']['globalJavascriptFiles'])) {
            $aTmp = explode(';', $conf['site']['globalJavascriptFiles']);
            foreach ($aTmp as $file) {
                $aFiles[] = (strpos($file, 'http://') === 0)
                    ? $file
                    : SGL_BASE_URL . '/' . $file;
            }
        }
        // BC with old way of including js files
        if (isset($this->javascriptSrc)) {
            if (is_array($this->javascriptSrc)) {
                foreach ($this->javascriptSrc as $file) {
                    $aFiles[] = (strpos($file, 'http://') === 0)
                        ? $file
                        : SGL_BASE_URL . '/' . $file;
                }
            } else {
                $aFiles[] = (strpos($this->javascriptSrc, 'http://') === 0)
                    ? $this->javascriptSrc
                    : SGL_BASE_URL . '/' . $this->javascriptSrc;
            }
        }
        // Get files added with $output->addJavascriptFile()
        if (count($this->aJavascriptFiles)) {
            $aFiles = array_merge(
                $aFiles,
                $this->aJavascriptFiles
            );
        }

        return $aFiles;
    }

    /**
     * For adding CSS files to include.
     *
     * @param  mixed $file or array $file path/to/cssFile, relative to www/ dir e.g. css/foo.css
     * @return void
     */
    function addCssFile($file)
    {
        if (is_array($file)) {
            foreach ($file as $cssFile) {
                if (!in_array($cssFile, $this->aCssFiles)) {
                    $this->aCssFiles[] = $cssFile;
                }
            }
        } else {
            if (!in_array($file, $this->aCssFiles)) {
                $this->aCssFiles[] = $file;
            }
        }
    }

    /**
     * Wrapper for SGL_Url::makeLink.
     * Generates URL for easy access to modules and actions.
     *
     * @access public
     *
     * @param string $action
     * @param string $mgr
     * @param string $mod
     * @param array $aList
     * @param string $params
     * @param integer $idx
     *
     * @return string
     */
    function makeUrl($action = '', $mgr = '', $mod = '', $aList = array(),
        $params = '', $idx = 0)
    {
        $input = SGL_Registry::singleton();
        $req = $input->getRequest();
        // Horde routes work only for browser request types
        if (($req->type == SGL_REQUEST_BROWSER || $req->type == SGL_REQUEST_AJAX)
                && SGL_Config::get('site.inputUrlHandlers') == 'Horde_Routes') {
            $aArgs = func_get_args();
            // new style call
            if (count($aArgs) == 1) {
                if (strpos($aArgs[0], '|') !== false) {
                    $aVars = explode('||', $aArgs[0]);
                    $aArgs = array();
                    foreach ($aVars as $varString) {
                        list($k, $v) = explode('|', $varString);
                        $aArgs[$k] = isset($this->{$v})
                            ? $this->{$v} : $v;
                    }
                    if (isset($aArgs['module'])) {
                        $aArgs['moduleName'] = $aArgs['module'];
                        unset($aArgs['module']);
                    }
                    if (isset($aArgs['manager'])) {
                        $aArgs['managerName'] = $aArgs['manager'];
                        unset($aArgs['manager']);
                    }
                // named route
                } else {
                    $aArgs = $aArgs[0];
                }
            // old style: params string specified as not part of array
            } elseif (count($aArgs) == 5 && empty($aList)) {
                $aVars = explode('||', $aArgs[4]);
                foreach ($aVars as $varKey => $varString) {
                    $aVar = explode('|', $varString);
                    if (isset($aVar[1]) && isset($this->{$aVar[1]})) {
                        $aVar[1] = $this->{$aVar[1]};
                    }
                    $aVars[$varKey] = implode('|', $aVar);
                }
                $aArgs[4] = implode('||', $aVars);
            }
            $url = $input->getCurrentUrl();
            $ret = $url->makeLink($aArgs);
        } else {
            $ret = SGL_Url::makeLink($action, $mgr, $mod, $aList,
                $params, $idx, $this);
        }
        return $ret;
    }

    function getCurrentUrl()
    {
        $reg = SGL_Registry::singleton();
        $oCurrentUrl = $reg->getCurrentUrl();
        return $oCurrentUrl->toString();
    }

    function isVerticalNav($styleSheet)
    {
        return in_array($styleSheet, array('SglListamaticSubtle', 'verticalSimple'));
    }

    function outputBody($templateEngine = null)
    {
        if (empty($this->template)) {
            $this->template = 'null.html';
        }
        $this->masterTemplate = $this->template;
        $view = new SGL_HtmlSimpleView($this, $templateEngine);
        echo $view->render();

        //  suppress error notices in templates
        SGL::setNoticeBehaviour(SGL_NOTICES_DISABLED);
    }

    /**
     * Returns true if client OS is windows.
     *
     * @return boolean
     */
    function isWin()
    {
        return SGL_CLIENT_OS == 'Win';
    }

    /**
     * Returns true if a and b are equal.
     *
     */
    function isEqual($a, $b)
    {
        return $a == $b;
    }

    /**
     * Makes new var and assign value.
     *
     */
    function assign(&$a, $b)
    {
        //  detect is $b is a function/method
        $a = $b;
        return;
    }

    function increment($int)
    {
        return ++ $int;
    }

    function isChecked($value)
    {
        if ($value) {
            $ret = 'checked="checked"';
        } else {
            $ret = '';
        }
        return $ret;
    }

    function getCurrentModule()
    {
        $reg = SGL_Registry::singleton();
        $req = $reg->getRequest();
        $frmCallerMod = $req->get('frmCallerMod');
        $modName = (is_null($frmCallerMod))
            ? $req->getModuleName()
            : $frmCallerMod;
        return $modName;
    }

    function getCurrentManager()
    {
        $reg = SGL_Registry::singleton();
        $req = $reg->getRequest();
        $frmCallerMgr = $req->get('frmCallerMgr');
        $mgrName = (is_null($frmCallerMgr))
            ? $req->getManagerName()
            : $frmCallerMgr;
        return $mgrName;
    }

    function getCurrentTemplate()
    {
        $reg = SGL_Registry::singleton();
        $req = $reg->getRequest();
        $frmCallerTmpl = $req->get('frmCallerTmpl');
        $tmplName = (is_null($frmCallerTmpl))
            ? $this->template
            : $frmCallerTmpl;
        return $tmplName;
    }

    function getCurrentId()
    {
        $reg = SGL_Registry::singleton();
        $req = $reg->getRequest();
        $frmCallerId = $req->get('frmCallerId');
        $id = (is_null($frmCallerId))
            ? $this->articleID
            : $frmCallerId;
        return $id;
    }


    /**
     * Check permission at the template level and returns true if permission
     * exists.
     *
     * Use as follows in any Flexy template:
     * <code>
     * {if:hasPerms(#faqmgr_delete#)} on {else:} off {end:}
     * </code>
     *
     * To get various perm names, select User module then go to 'perms' section.
     *
     * @access  public
     * @param   string  $permName    Name of permission eg. "faqmgr_delete"
     * @return     boolean
     *
     */
    function hasPerms($permName)
    {
        $permId = @constant('SGL_PERMS_' . strtoupper($permName));
        return (!empty($permId) && SGL_Session::hasPerms($permId) ? true : false);
    }

    /**
     * printf function wrapper.
     *
     * @return string
     */
    function printf()
    {
        $argv = func_get_args();
        return @call_user_func_array('sprintf', $argv);
    }

    function makeCssLink($theme, $navStylesheet, $moduleName)
    {
        //  check first if CSS file exists in module
        if (is_file(SGL_MOD_DIR . "/$moduleName/www/css/$moduleName.php")) {
            $ret = SGL_BASE_URL . "/themes/$theme/css/style.php?navStylesheet=$navStylesheet&moduleName=$moduleName&isSymlink=1";
        //  else default to standard css loading with modulename passed as param
        } else {
            $ret = SGL_BASE_URL . "/themes/$theme/css/style.php?navStylesheet=$navStylesheet&moduleName=$moduleName";
        }
        return $ret;
    }

    function humanise($lowerCaseAndUnderscoredWord)
    {
        return SGL_Inflector::humanise($lowerCaseAndUnderscoredWord);
    }

    function camelise($lowerCaseWithSpacesWordsString)
    {
        return SGL_Inflector::camelise($lowerCaseWithSpacesWordsString);
    }

    /**
     * @return current ms since script start
     */
    function getExecutionTime()
    {
        return getSystemTime() - @SGL_START_TIME;
    }

    /**
     * @return query count
     */
    function getQueryCount()
    {
        return $GLOBALS['_SGL']['QUERY_COUNT'];
    }

    /**
     * @return memory usage
     */
    function getMemoryUsage()
    {
        if (function_exists('memory_get_usage')) {
            return number_format(memory_get_usage());
        } else {
            return 'unknown';
        }
    }

    function addHeader($header)
    {
        if (!in_array($header, $this->aHeaders)) {
            $this->aHeaders[] = $header;
        }
    }

    function setStatusCode($code)
    {
        if (array_key_exists($code, $this->aStatusCodes)) {
            $status = $this->httpProtocol .' '. $code .' '. $this->aStatusCodes[$code];
            $this->aHeaders[] = $status;
        }
    }

    function getHeaders()
    {
        $headers = $this->aHeaders;
        return $headers;
    }

    /**
     * Makes optimizer link for JavaScript files.
     *
     * How to use:
     *  1. in your template you need to add the following line
     *     <script type="text/javascript" src="{makeJsOptimizerLink()}" />
     *  2. specify global js files in $conf['site']['globalJavascriptFiles']
     *     separated by comma e.g. 'js/SGL.js,js/SGL/Util/String.js'
     *  3. to add module/manager specific js files just use
     *     $output->addJavascriptFile('path/to/custom/js/file.js')
     *
     * @access public
     *
     * @return string
     */
    function makeJsOptimizerLink()
    {
        $this;
        // save currently loaded files
        $aCurrentFiles = $this->aJavascriptFiles;
        $this->aJavascriptFiles = array();

        // bc for global javascript
        if (!SGL_Config::get('site.globalJavascriptFiles')
                && ($_SESSION['aPrefs']['theme'] == 'default'
                    || $this->adminGuiAllowed)) {
            SGL_Config::set('site.globalJavascriptFiles', 'js/SGL.js');
        }

        // javascript files, which always are loaded
        $this->addJavascriptFile(
            SGL_Config::get('site.globalJavascriptFiles')
                ? explode(',', SGL_Config::get('site.globalJavascriptFiles'))
                : array()
        );

        // merge default js files with custom ones
        // default js files will be loaded first
        $this->addJavascriptFile($aCurrentFiles);

        // remove base url from files
        // NB! this hack should be removed
        $aFiles = array();
        foreach ($this->aJavascriptFiles as $fileName) {
            $aFiles[] = substr($fileName, strlen(SGL_BASE_URL . '/'));
        }

        // actualy we should add revision number instead
        $rev      = SGL_Output::_getFilesModifiedTime($aFiles);
        $jsString = implode(',', $aFiles);
        // make optimizer link
        //  - type: javascript
        //  - rev: current revision number (still to be implemented)
        //  - files: loaded js files
        $link     = SGL_BASE_URL . '/optimizer.php?type=javascript&amp;rev='
            . $rev . '&amp;files=' . $jsString;
        if (SGL_Config::get('cache.javascript')) {
            $link .= '&amp;optimize=1';
        }

        $this->aRawJavascriptFiles = array_diff($this->aRawJavascriptFiles,
            $this->aJavascriptFiles);

        $ret = "<script type=\"text/javascript\" src=\"$link\"></script>\n";
        foreach ($this->aRawJavascriptFiles as $jsFile) {
            $ret .= "<script type=\"text/javascript\" src=\"$jsFile\"></script>\n";
        }
        return $ret;
    }

    /**
     * Makes CSS optimizer link.
     *
     * 1. {makeCssOptimizerLink():h}
     *    Loads default "CSS fw" stylesheets + stylesheets specified in mgr.
     *
     * 2. {makeCssOptimizerLink(##,#a.css,b.css#):h}
     *    Loads "a.css" and "b.css" from current theme + stylesheets
     *    specified in mgr.
     *
     * 3. {makeCssOptimizerLink(##,##):h}
     *    Loads only stylesheets specified in mgr.
     *
     * @access public
     *
     * @param array $aCssHelperParams    additional params passed to css helper
     * @param mixed $aDefaultThemeFiles  if null default css files are loaded
     *                                   otherwise custom files specified as array
     *                                   or string (CSV)
     * @param string $themePreloadFile   file which is "prepended" to every CSS request
     *                                   (even in non-production mode)
     *
     * @return string
     */
    function makeCssOptimizerLink($aCssHelperParams = array(),
            $aDefaultThemeFiles = null, $themePreloadFile = null)
    {
        $theme = $this->theme;

        // get master layout
        $masterLayout = !empty($this->masterLayout)
            ? $this->masterLayout
            : (SGL_Config::get('site.masterLayout') ? SGL_Config::get('site.masterLayout') : 'layout-navtop-3col.css');

        // layout is specified in request for demo purpose on home page
        $req = SGL_Request::singleton();
        $masterLayout = $req->get('masterLayout')
            ? $req->get('masterLayout')
            : $masterLayout;
        // make sure we pass layout to output
        $this->masterLayout = $masterLayout;

        if (!empty($aDefaultThemeFiles) && is_string($aDefaultThemeFiles)) {
            $aTmpThemeFiles = explode(',', $aDefaultThemeFiles);
            $aDefaultThemeFiles = array();
            foreach ($aTmpThemeFiles as $file) {
                $aDefaultThemeFiles[] = "themes/$theme/css/$file";
            }
        }
        if (!is_array($aDefaultThemeFiles) && is_null($aDefaultThemeFiles)) {
            // default files loaded
            $aDefaultThemeFiles = array( // we need to be able to customize it
                "themes/$theme/css/reset.css",
                "themes/$theme/css/tools.css",
                "themes/$theme/css/typo.css",
                "themes/$theme/css/forms.css",
                "themes/$theme/css/layout.css",
                "themes/$theme/css/blocks.css",
                "themes/$theme/css/common.css",
                "themes/$theme/css/$masterLayout",
            );
        } elseif (!is_array($aDefaultThemeFiles)) {
            $aDefaultThemeFiles = array();
        }

        // custom loaded files
        $aCurrentFiles = $this->aCssFiles;
        $this->aCssFiles = array();

        // add common css files
        $this->addCssFile($aDefaultThemeFiles);
        // add custom files
        $this->addCssFile($aCurrentFiles);

        $module = !empty($this->moduleName) ? $this->moduleName : 'default';
        $defaultModule = SGL_Config::get('site.defaultModule')
            ? SGL_Config::get('site.defaultModule')
            : $module;

        // params passed to csshelper
        $aCssHelperParams['theme']           = $theme;
        $aCssHelperParams['langDir']         = $this->langDir;
        $aCssHelperParams['isFormSubmitted'] = !empty($this->submitted);
        $aCssHelperParams['module']          = $module;
        $aCssHelperParams['defaultModule']   = $defaultModule;
        $aCssHelperParams['manager']         = !empty($this->managerName)
            ? $this->managerName
            : '';

        // autoload module's css file
        if (is_file(realpath(SGL_WEB_ROOT . "/themes/$theme/css/$module.css"))) {
            $this->addCssFile("themes/$theme/css/$module.css");
        } elseif (is_file(realpath(SGL_WEB_ROOT . "/$module/css/$module.css"))) {
            $this->addCssFile("$module/css/$module.css");
        }
        // BC
        if (is_file(realpath(SGL_WEB_ROOT . "/$module/css/$module.php"))) {
            $this->addCssFile("$module/css/$module.php");
        } elseif (is_file(realpath(SGL_WEB_ROOT . "/themes/$theme/css/$module.php"))) {
            $this->addCssFile("themes/$theme/css/$module.php");
        }

        $params = '';
        foreach ($aCssHelperParams as $k => $v) {
            $params .= '&amp;aParams[' . urlencode($k) . ']=' . urlencode($v);
        }

        // allow to load each file in a separate request for debug purposes
        if (!SGL_Config::get('debug.production')) {
            $ret = '';
            $rev = time();
            foreach ($this->aCssFiles as $file) {
                $aFiles = array();
                if (!empty($themePreloadFile)) {
                    $aFiles[] = "themes/$theme/css/$themePreloadFile";
                }
                $aFiles[] = $file;
                $cssString = implode(',', $aFiles);
                $link = SGL_BASE_URL . "/optimizer.php?type=css&amp;rev=$rev&amp;files="
                    . $cssString . $params;
                $ret .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$link\" />\n";
            }
        } else {
            $aFiles = !empty($themePreloadFile)
                ? array_merge(
                      array("themes/$theme/css/$themePreloadFile"),
                      $this->aCssFiles
                  )
                : $this->aCssFiles;
            $rev = SGL_Output::_getFilesModifiedTime($aFiles);
            $cssString = implode(',', $aFiles);
            $link = SGL_BASE_URL . "/optimizer.php?type=css&amp;rev=$rev&amp;files="
                . $cssString . $params;
            $ret = "<link rel=\"stylesheet\" type=\"text/css\" href=\"$link\" />\n";
        }

        // reset to default state in case of multiply calls
        $this->aCssFiles = $aCurrentFiles;

        return $ret;
    }

    /**
     * Identifies latest mod time for specified files array.
     * Is used to get "revision" number for optimizer link.
     *
     * @access private
     *
     * @param array $aFiles
     *
     * @return integer
     */
    function _getFilesModifiedTime($aFiles)
    {
        $lastMod = 0;
        foreach ($aFiles as $fileName) {
            if (is_file(realpath(SGL_WEB_ROOT . '/' . $fileName))) {
                $lastMod = max($lastMod, filemtime(SGL_WEB_ROOT . '/' . $fileName));
            }
        }
        return $lastMod;
    }

    /**
     * Get message, which outputs html in default2 style.
     *
     * @access public
     *
     * @return void
     */
    function msgGet()
    {
        // BC for admin GUI
        if ($this->adminGuiAllowed) {
            return SGL_Output::msgGetAdmin();
        }

        $message     = SGL_Session::get('message');
        $messageType = SGL_Session::get('messageType');
        $html        = '';

        // get html for SGL messages
        if (!empty($message)) {
            SGL_Session::remove('message');
            SGL_Session::remove('messageType');

            switch ($messageType) {
                case SGL_MESSAGE_INFO:    $class = 'info';    break;
                case SGL_MESSAGE_WARNING: $class = 'warning'; break;
                default:                  $class = 'error';   break;
            }
            $html .= "<p class=\"message-{$class}\">$message</p>";

            // required to remove message that persists
            // when register_globals = on
            unset($GLOBALS['message']);
            unset($GLOBALS['messageType']);
        }
        // get html for SGL errors
        if (SGL_Error::count()) {
            // get all errors from stack
            while ($msg = SGL_Error::pop()) {
                $msg   = SGL_Error::toString($msg);
                $html .= "<h4>Error</h4><p class=\"pear\">$msg</p>";
            }
        }
        if (empty($html)) {
            $html = '<!-- Do not remove, MSIE fix -->';
        }
        echo $html; // we need to echo, do not replace to return
    }

    function getLangDirection()
    {
        $ret = $this->langDir == 'rtl'
            ? 'right'
            : 'left';
        return $ret;
    }

    function getLangDirectionOpposite()
    {
        $ret = $this->langDir == 'rtl'
            ? 'left'
            : 'right';
        return $ret;
    }

    /**
     * Export js var.
     *
     * @access pubic
     *
     * @param string $k
     * @param string $v
     * @param boolean $replace
     */
    function exportJsVar($k, $v, $replace = true)
    {
        $k = strtoupper($k);
        if ($replace) {
            $this->_aJsExportVars[$k] = $v;
        } elseif (!array_key_exists($k, $this->_aJsExportVars)) {
            $this->_aJsExportVars[$k] = $v;
        }
    }

    /**
     * @access public
     *
     * @return string
     */
    function getExportedJsVars()
    {
        // default vars
        $prefix = 'SGL';
        $ret    = '';
        foreach ($this->_aJsExportVars as $k => $v) {
            $varName = $prefix . '_' . $k;
            $varVal  = addcslashes($v, '"');
            $ret .= "var $varName = \"$varVal\";\n";
        }
        return $ret;
    }

    /**
     * nl2br
     *
     * @access public
     *
     * @param string $text
     *
     * @return string
     */
    function nl2br($text)
    {
        $text = htmlspecialchars($text);
        // echo, dot not return... this allows to avoid :h modifier
        echo nl2br($text);
    }
}
?>