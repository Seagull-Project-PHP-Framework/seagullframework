<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2006, Seagull Systems                                       |
// | All rights reserved.                                                      |
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software               |
// | Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301,|
// | USA                                                                       |
// |                                                                           |
// +---------------------------------------------------------------------------+
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | FileAssocMgr.php                                                          |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@seaugllproject.org>                         |
// +---------------------------------------------------------------------------+

require_once 'DB/DataObject.php';

/**
 * Associates media with entities.
 *
 * @package seagull
 * @subpackage media
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class FileAssocMgr extends SGL_Manager
{
    function FileAssocMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle        = 'File Association Manager';
        $this->masterLayout = 'layout-navtop-2col_localleft.css';
        $this->_aActionsMapping =  array(
            'list'   => array('list'),
            'listImageChoices'   => array('listImageChoices'),
            'associateToEvent'   => array('associateToEvent', 'redirectToCaller'),
            'associateToUser'    => array('associateToUser', 'redirectToCaller'),
            'associateToCategory' => array('associateToCategory', 'redirectToCaller'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->validated        = true;
        $input->error           = array();
        $input->pageTitle       = $this->pageTitle;
        $input->masterLayout   = $this->masterLayout;

        //  form vars
        $input->action          = $req->get('action');
        $input->submitted       = $req->get('submitted');
        $input->callerMgr       = $req->get('frmCallerMgr');
        $input->callerMod       = $req->get('frmCallerMod');
        $input->mediaTypeId     = $req->get('frmMediaTypeId');
        $input->fileTypeId      = $req->get('frmFileTypeId');
        $input->mediaId         = $req->get('frmMediaId');
        $input->categoryId      = $req->get('frmCategoryId');
        $input->aAssocIds       = $req->get('frmAssociateIds');
        $input->eventId         = $req->get('frmEventId');
        $input->isEventImage    = $req->get('frmIsEventImage');
        $input->defaultImgId    = $req->get('frmDefaultImg');
        $input->userId          = $req->get('frmUserId');

        if ($input->action == 'list') {
            $input->nextAction = ($input->callerMgr == 'category')
                ? 'associateToCategory'
                : 'associateToEvent';
        }
    }

    function _cmd_listImageChoices(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'fileAssocList.html';
        $query = "
            SELECT      m.media_id,
                        m.name, file_size, mime_type,
                        m.date_created, description,
                        ft.name AS file_type_name,
                        u.username AS media_added_by,
                        m.file_type_id
            FROM        {$this->conf['table']['media']} m
            JOIN        {$this->conf['table']['file_type']} ft ON ft.file_type_id = m.file_type_id
            LEFT JOIN   {$this->conf['table']['user']} u ON u.usr_id = m.added_by
            WHERE       m.file_type_id = " . $input->fileTypeId . "
            ORDER BY    m.date_created DESC";
        $aMedia = $this->dbh->getAll($query);
        $output->aMedia = $aMedia;
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $fileTypeConstraint = (!is_null($input->fileTypeId))
            ? " WHERE m.file_type_id = " . $input->fileTypeId
            : '';
        $query = "
            SELECT      m.media_id,
                        m.name, file_size, mime_type,
                        m.date_created, description,
                        ft.name AS file_type_name,
                        u.username AS media_added_by,
                        m.file_type_id
            FROM        {$this->conf['table']['media']} m
            JOIN        {$this->conf['table']['file_type']} ft ON ft.file_type_id = m.file_type_id
            LEFT JOIN   {$this->conf['table']['user']} u ON u.usr_id = m.added_by
            $fileTypeConstraint
            ORDER BY    m.date_created DESC";
        $aMedia = $this->dbh->getAll($query);

        foreach ($aMedia as $k => $media) {
            //  check for event links
            if (SGL::moduleIsEnabled('event')) {
                if (!is_null($input->isEventImage)) {
                    if ($this->isAssociatedToEventImage($media->media_id, $input->eventId)) {
                        $aMedia[$k]->associated = true;
                    }
                } else {
                    if ($this->isAssociatedToEvent($media->media_id, $input->eventId)) {
                        $aMedia[$k]->associated = true;
                    }
                }
            }
            //  check for category links
            if (SGL::moduleIsEnabled('cms')) {
                if ($this->isAssociatedToCategory($media->media_id, $input->categoryId)) {
                    $aMedia[$k]->associated = true;
                }
            }
        }
        $output->aMedia = $aMedia;
        $output->template = 'fileAssocList.html';
    }

    function isAssociatedToCategory($mediaId, $categoryId)
    {
        $query = "
            SELECT  media_id
            FROM    ".$this->dbh->quoteIdentifier($this->conf['table']['category-media'])."
            WHERE   media_id = $mediaId
            AND     category_id = $categoryId
        ";
        $mediaId = $this->dbh->getOne($query);
        return $mediaId;
    }

    function isAssociatedToEventImage($mediaId, $eventId)
    {
        $query = "
            SELECT  media_id
            FROM    ".$this->dbh->quoteIdentifier($this->conf['table']['event-media'])."
            WHERE   media_id = $mediaId
            AND     event_id = $eventId
            AND     is_event_image = 1";
        $mediaId = $this->dbh->getOne($query);
        return $mediaId;
    }

    function isAssociatedToEvent($mediaId, $eventId)
    {
        $query = "
            SELECT  media_id
            FROM    ".$this->dbh->quoteIdentifier($this->conf['table']['event-media'])."
            WHERE   media_id = $mediaId
            AND     event_id = $eventId
            AND     is_event_image = 0";
        $mediaId = $this->dbh->getOne($query);
        return $mediaId;
    }

    function _cmd_associateToUser(&$input, &$output)
    {
        if (isset($input->defaultImgId)) {
            $user = DB_DataObject::factory($this->conf['table']['user']);
            $user->get($input->userId);
            $user->media_id = $input->defaultImgId;
            $success = $user->update();
        }
    }

    function _cmd_associateToEvent(&$input, &$output)
    {
        //  first delete existing associations for this event id
        $isEventImage = ($input->isEventImage) ? 1 : 0;
        $query = "
            DELETE FROM ".$this->dbh->quoteIdentifier($this->conf['table']['event-media'])."
            WHERE       event_id = $input->eventId
            AND         is_event_image = $isEventImage";
        $ok = $this->dbh->query($query);

        //  then add new ones
        if (count($input->aAssocIds)) {
            if ($isEventImage) {
                $mediaId = (integer) $input->aAssocIds[0];
                $query = "
                    INSERT INTO ".$this->dbh->quoteIdentifier($this->conf['table']['event-media'])."
                    VALUES      ($input->eventId, $mediaId, $isEventImage)";
                $ok = $this->dbh->query($query);
            } else {
                foreach ($input->aAssocIds as $mediaId) {
                    $query = "
                        INSERT INTO ".$this->dbh->quoteIdentifier($this->conf['table']['event-media'])."
                        VALUES      ($input->eventId, $mediaId, $isEventImage)";
                    $ok = $this->dbh->query($query);
                }
            }
        }
    }

    function _cmd_associateToCategory(&$input, &$output)
    {
        //  first delete existing association for this category
        $query = "
            DELETE FROM ".$this->dbh->quoteIdentifier($this->conf['table']['category-media'])."
            WHERE       category_id = $input->categoryId";
        $ok = $this->dbh->query($query);

        //  then add new one
        $query = "
            INSERT INTO ".$this->dbh->quoteIdentifier($this->conf['table']['category-media'])."
            VALUES      ($input->categoryId, $input->mediaId)";
        $ok = $this->dbh->query($query);
    }

    function _cmd_redirectToCaller(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $aRedirectParams = array(
            'moduleName'  => $input->callerMod,
            'managerName' => $input->callerMgr
            );

        if (SGL::moduleIsEnabled('cms') && $input->action == 'associateToCategory') {
            $aRedirectParams['frmCatID'] = $input->categoryId;
        }
        SGL_HTTP::redirect($aRedirectParams);
    }
}
?>
