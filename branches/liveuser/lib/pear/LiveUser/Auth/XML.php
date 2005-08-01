<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * A framework for authentication and authorization in PHP applications
 *
 * LiveUser is an authentication/permission framework designed
 * to be flexible and easily extendable.
 *
 * Since it is impossible to have a
 * "one size fits all" it takes a container
 * approach which should enable it to
 * be versatile enough to meet most needs.
 *
 * PHP version 4 and 5 
 *
 * LICENSE: This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public 
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston,
 * MA  02111-1307  USA 
 *
 *
 * @category authentication
 * @package  LiveUser
 * @author  Markus Wolff <wolff@21st.de>
 * @author Helgi �ormar �orbj�rnsson <dufuz@php.net>
 * @author  Lukas Smith <smith@backendmedia.com>
 * @author Arnaud Limbourg <arnaud@php.net>
 * @author   Pierre-Alain Joye  <pajoye@php.net>
 * @author  Bjoern Kraus <krausbn@php.net>
 * @copyright 2002-2005 Markus Wolff
 * @license http://www.gnu.org/licenses/lgpl.txt
 * @version CVS: $Id: XML.php,v 1.20 2005/07/18 16:16:56 lsmith Exp $
 * @link http://pear.php.net/LiveUser
 */

/**
 * Require parent class definition and XML::Tree class.
 */
require_once 'LiveUser/Auth/Common.php';
require_once 'XML/Tree.php';

/**
 * XML driver for authentication
 *
 * This is a XML backend driver for the LiveUser class.
 *
 * @category authentication
 * @package  LiveUser
 * @author  Bj�rn Kraus <krausbn@php.net>
 * @copyright 2002-2005 Markus Wolff
 * @license http://www.gnu.org/licenses/lgpl.txt
 * @version Release: @package_version@
 * @link http://pear.php.net/LiveUser
 */
class LiveUser_Auth_XML extends LiveUser_Auth_Common
{
    /**
     * XML file in which the auth data is stored.
     *
     * @var    string
      * @access private
     */
    var $file = '';

    /**
     * XML::Tree object.
     *
     * @var    XML_Tree
     * @access private
     */
    var $tree = null;

    /**
     * XML::Tree object of the user logged in.
     *
     * @var    XML_Tree
     * @access private
     * @see    _readUserData()
     */
    var $userObj = null;

    /**
     * Load the storage container
     *
     * @param  mixed &$conf   Name of array containing the configuration.
     * @param string $containerName name of the container that should be used
     * @return  boolean true on success or false on failure
     *
     * @access  public
     */
    function init(&$conf, $containerName)
    {
        parent::init($conf, $containerName);

        if (is_array($conf['storage'])) {
            if (!is_file($this->file)) {
                if (!is_file(getenv('DOCUMENT_ROOT') . $this->file)) {
                    $this->_stack->push(LIVEUSER_ERROR_INIT_ERROR, 'error',
                        array('container' => "Auth initialisation failed. Can't find xml file."));
                    return false;
                }
                $this->file = getenv('DOCUMENT_ROOT') . $this->file;
            }
            if ($this->file) {
                if (class_exists('XML_Tree')) {
                    $tree =& new XML_Tree($this->file);
                    $err =& $tree->getTreeFromFile();
                    if (PEAR::isError($err)) {
                        $this->_stack->push(LIVEUSER_ERROR_INIT_ERROR, 'error',
                            array('container' => 'could not connect: '.$err->getMessage()));
                        return false;
                    }
                    $this->tree = $tree;
                } else {
                    $this->_stack->push(LIVEUSER_ERROR_INIT_ERROR, 'error',
                        array('container' => "Auth initialisation failed. Can't find XML_Tree class."));
                    return false;
                   ;
                }
            } else {
                $this->_stack->push(LIVEUSER_ERROR_INIT_ERROR, 'error',
                    array('container' => "Auth initialisation failed. Can't find xml file."));
                return false;
            }
        }
        return true;
    }

    /**
     * Writes current values for user back to the database.
     * This method does nothing in the base class and is supposed to
     * be overridden in subclasses according to the supported backend.
     *
     * @return boolean true on success or false on failure
     *
     * @access private
     */
    function _updateUserData()
    {
        $data = array('lastLogin' => $this->currentLogin);

        $index = 0;
        foreach ($this->userObj->children as $value) {
            if (in_array($value->name, array_keys($data))) {
                $el =& $this->userObj->getElement(array($index));
                $el->setContent($data[$value->name]);
            }
            $index++;
        }

        $success = false;
        do {
          $fp = fopen($this->file, 'wb');
          if (!$fp) {
              $errorMsg = "Auth freeze failure. Failed to open the xml file.";
              break;
          }
          if (!flock($fp, LOCK_EX)) {
              $errorMsg = "Auth freeze failure. Couldn't get an exclusive lock on the file.";
              break;
          }
          if (!fwrite($fp, $this->tree->get())) {
              $errorMsg = "Auth freeze failure. Write error when writing back the file.";
              break;
          }
          @fflush($fp);
          $success = true;
        } while (false);

        @flock($fp, LOCK_UN);
        @fclose($fp);

        if (!$success) {
            $this->_stack->push(LIVEUSER_ERROR, 'exception',
                array(), 'Cannot read XML Auth file: '.$errorMsg);
        }

        return $success;
    }

    /**
     *
     * Reads auth_user_id, password from the xml file
     * If only $handle is given, it will read the data
     * from the first user with that handle and return
     * true on success.
     * If $handle and $passwd are given, it will try to
     * find the first user with both handle and passwd
     * matching and return true on success (this allows
     * multiple users having the same handle but different
     * passwords - yep, some people want this).
     * If no match is found, false is being returned.
     *
     * @param string $handle   Handle of the current user.
     * @param mixed $passwd    Can be a string with an
     *                  unencrypted pwd or false.
     * @param string $authUserId auth user id
     * @return boolean true on success or false on failure
     *
     * @access private
     */
    function readUserData($handle = '', $passwd = '', $authUserId = false)
    {
        $success = false;
        $index = 0;

        foreach ($this->tree->root->children as $user) {
            $result = array();
            $names = array_flip($this->alias);
            foreach ($user->children as $value) {
                if (isset($names[$value->name])) {
                    $result[$names[$value->name]] = $value->content;
                }
            }

            if ($authUserId) {
                if (isset($result['auth_user_id']) &&
                    $authUserId === $result['auth_user_id']
                ) {
                    $success = true;
                    break;
                }
            } elseif (isset($result['handle']) && $handle === $result['handle']) {
                if ($this->tables['users']['fields']['passwd']) {
                    if (isset($result['passwd']) &&
                        $this->encryptPW($passwd) === $result['passwd']
                    ) {
                        $success = true;
                        break;
                    } elseif(!$this->allowDuplicateHandles) {
                        // dont look for any further matching handles
                        break;
                    }
                } else {
                    $success = true;
                    break;
                }
            }

            $index++;
        }

        if (!$success) {
            return false;
        }

        $this->handle = $result['handle'];
        unset($result['handle']);
        $this->passwd = $this->decryptPW($result['passwd']);
        unset($result['passwd']);
        $this->authUserId = $result['auth_user_id'];
        unset($result['auth_user_id']);
        $this->isActive = ((!isset($result['is_active']) || $result['is_active']) ? true : false);
        if (isset($result['is_active'])) {
            unset($result['is_active']);
        }
        $this->lastLogin = (isset($result['lastlogin']) && !empty($result['lastlogin']))
            ? $result['lastlogin'] : '';
        if (isset($result['lastlogin'])) {
            unset($result['lastlogin']);
        }
        $this->ownerUserId  = isset($result['owner_user_id']) ? $result['owner_user_id'] : null;
        if (isset($result['owner_user_id'])) {
            unset($result['owner_user_id']);
        }
        $this->ownerGroupid = isset($result['owner_group_id']) ? $result['owner_group_id'] : null;
        if (isset($result['owner_group_id'])) {
            unset($result['owner_group_id']);
        }
        if (!empty($result)) {
            foreach ($result as $name => $value) {
                $this->{$name} = $value;
            }
        }

        $this->userObj      =& $this->tree->root->getElement(array($index));

        return true;
    }

    /**
     * Properly disconnect from resources
     *
     * @return void
     *
     * @access public
     */
    function disconnect()
    {
        $this->tree = null;
        $this->userObj = null;
    }
}
?>