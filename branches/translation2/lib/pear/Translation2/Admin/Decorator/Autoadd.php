<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Contains the Translation2_Admin_Decorator_Autoadd class
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Internationalization
 * @package    Translation2
 * @author     Lorenzo Alberton <l dot alberton at quipo dot it>
 * @author     Ian Eure <ieure at php dot net>
 * @copyright  2004-2005 Lorenzo Alberton, Ian Eure
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id: Autoadd.php,v 1.5 2005/02/22 17:55:08 quipo Exp $
 * @link       http://pear.php.net/package/Translation2
 */

/**
 * Load Translation2_Decorator class
 */
require_once 'Translation2/Admin/Decorator.php';

/**
 * Automatically add requested strings
 *
 * This Decorator will add strings to a language when a request for them to be
 * translated happens. The 'autoaddlang' option must be set to the language the
 * strings will be added as.
 *
 * Example:
 * <pre>
 * $tr =& Translation2_Admin::factory(...);
 * $tr->setLang('en');
 * $tr =& $tr->getAdminDecorator('Autoadd');
 * $tr->setOption('autoaddlang', 'en');
 * ...
 * $tr->get('Entirely new string', 'samplePage', 'de');
 * </pre>
 *
 * 'Entirely new string' will be added to the English language table.
 *
 * @category   Internationalization
 * @package    Translation2
 * @author     Ian Eure <ieure at php dot net>
 * @copyright  2004-2005 Ian Eure
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link       http://pear.php.net/package/Translation2
 * @since      2.0.0beta3
 */
class Translation2_Admin_Decorator_Autoadd extends Translation2_Admin_Decorator
{
    /**
     * Language to add strings in
     *
     * @var  string
     */
    var $autoaddlang = '';

    /**
     * Get a translated string
     *
     * @see   Translation2::get()
     */
    function get($stringID, $pageID = TRANSLATION2_DEFAULT_PAGEID, $langID = null)
    {
        $pageID = ($pageID == TRANSLATION2_DEFAULT_PAGEID ? $this->translation2->currentPageID : $pageID);
        $string = $this->translation2->get($stringID, $pageID, $langID);
        if (PEAR::isError($string)
            || empty($string)
            && !empty($this->autoaddlang)
        ) {
            // Add the string
            $this->translation2->add($stringID, $pageID, array(
                $this->autoaddlang => $stringID
            ));
        }
        return $string;
    }
}
?>