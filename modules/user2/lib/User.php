<?php

require_once SGL_MOD_DIR . '/messaging2/lib/Item.php';
require_once SGL_MOD_DIR . '/user2/classes/User2DAO.php';

/**
 * @todo remove dependency on messaging2
 *
 * @package user2
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_User extends SGL_Item
{
    protected $_aFields = array(
        'usr_id',
        'first_name',
        'last_name',
        'username',
        'email',
        'date_created',
        'last_updated',
    );

    public function __construct($aData = array())
    {
        parent::__construct($aData);
    }

	/**
	 * Returns a populated user object.
	 *
	 * @param integer $id
	 *
	 * @return SGL_User
	 */
	public static function getById($id)
	{
	    $aData = User2DAO::singleton()->getUserById($id);
	    if (!PEAR::isError($aData)) {
	        $ret = new SGL_User($aData);
	    } else {
	        $ret = $aData;
	    }
        return $ret;
	}

    public function getFullName()
    {
        $ret = trim($this->firstName . ' ' . $this->lastName);
        if (empty($ret)) {
            $ret = $this->username;
        }
        return $ret;
    }
}
?>