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
        $this->_aActionsMapping =  array(
            'list'   => array('list'),
            'listImageChoices'   => array('listImageChoices'),
            'associateToEvent'   => array('associateToEvent', 'redirectToCaller'),
            'associateToArtwork' => array('associateToArtwork', 'redirectToCaller'),
            'associateToUser'    => array('associateToUser', 'redirectToCaller'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->validated        = true;
        $input->error           = array();
        $input->pageTitle       = $this->pageTitle;
        $input->masterTemplate  = 'masterLeftCol.html';

        //  form vars
        $input->action          = $req->get('action');
        $input->submitted       = $req->get('submitted');
        $input->callerMgr       = $req->get('frmCallerMgr');
        $input->callerMod       = $req->get('frmCallerMod');
        $input->mediaTypeId     = $req->get('frmMediaTypeId');
        $input->fileTypeId      = $req->get('frmFileTypeId');
        $input->aAssocIds       = $req->get('frmAssociateIds');
        $input->eventId         = $req->get('frmEventId');
        $input->artworkId       = $req->get('frmArtworkId');
        $input->isEventImage    = $req->get('frmIsEventImage');
        $input->defaultImgId    = $req->get('frmDefaultImg');
        $input->userId          = $req->get('frmUserId');

        if ($input->action == 'list') {
            $input->nextAction = (is_null($input->eventId))
                ? 'associateToArtwork'
                : 'associateToEvent';
        }
    }

    function _cmd_listImageChoices(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'fileAssocList.html';
        $query = "
            SELECT m.media_id,
                m.name, file_size, mime_type,
                m.date_created, description,
                ft.name AS file_type_name,
                u.username AS media_added_by,
                m.file_type_id
            FROM
                {$this->conf['table']['media']} m
            JOIN file_type ft ON ft.file_type_id = m.file_type_id
            LEFT JOIN usr u ON u.usr_id = m.added_by
            WHERE m.file_type_id = " . $input->fileTypeId . "
            ORDER BY m.date_created DESC";
        $aMedia = $this->dbh->getAll($query);
        $output->nextAction = 'associateToUser';
        $output->aMedia = $aMedia;
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $mediaConstraint = (!is_null($input->mediaTypeId))
            ? ' WHERE  media_type_id = ' . $input->mediaTypeId
            : '';
        $glue = !empty($mediaConstraint) ? 'AND' : 'WHERE';
        $fileTypeConstraint = (!is_null($input->fileTypeId))
            ? " $glue m.file_type_id = " . $input->fileTypeId
            : '';
        $query = "
            SELECT m.media_id,
                m.name, file_size, mime_type,
                m.date_created, description,
                ft.name AS file_type_name,
                u.username AS media_added_by,
                m.file_type_id
            FROM
                {$this->conf['table']['media']} m
            JOIN file_type ft ON ft.file_type_id = m.file_type_id
            LEFT JOIN usr u ON u.usr_id = m.added_by
            $mediaConstraint
            $fileTypeConstraint
            ORDER BY m.date_created DESC";
        $aMedia = $this->dbh->getAll($query);
        $entity = (is_null($input->eventId))
            ? 'Artwork'
            : 'Event';
        $method = 'isAssociatedTo' . $entity;
        $pk = strtolower($entity) . 'Id';

        foreach ($aMedia as $k => $media) {
            if (!is_null($input->isEventImage)) {
                if ($this->isAssociatedToEventImage($media->media_id, $input->$pk, $input->isEventImage)) {
                    $aMedia[$k]->associated = true;
                }
            } else {
                if ($this->$method($media->media_id, $input->$pk, $input->isEventImage)) {
                    $aMedia[$k]->associated = true;
                }
                if ($entity == 'Artwork') {
                    if ($this->isDefaultImage($media->media_id)) {
                        $aMedia[$k]->isDefaultImg = true;
                    }
                }
            }
        }
        $output->aMedia = $aMedia;
        $output->template = 'fileAssocList.html';
    }

    function isDefaultImage($mediaId)
    {
        $query = "
            SELECT is_default_image
            FROM `artwork-media`
            WHERE media_id = $mediaId
            AND is_default_image = 1
            ";
        $yes = $this->dbh->getOne($query);
        return $yes;
    }

    function isAssociatedToEventImage($mediaId, $eventId, $isEventImage)
    {
        $query = "
            SELECT media_id
            FROM `event-media`
            WHERE media_id = $mediaId
            AND event_id = $eventId
            AND is_event_image = 1
            ";
        $yes = $this->dbh->getOne($query);
        return $yes;
    }

    function isAssociatedToEvent($mediaId, $eventId)
    {
        $query = "
            SELECT media_id
            FROM `event-media`
            WHERE media_id = $mediaId
            AND event_id = $eventId
            ";
        $yes = $this->dbh->getOne($query);
        return $yes;
    }

    function isAssociatedToArtwork($mediaId, $artworkId)
    {
        $query = "
            SELECT media_id
            FROM `artwork-media`
            WHERE media_id = $mediaId
            AND artwork_id = $artworkId
            ";
        $yes = $this->dbh->getOne($query);
        return $yes;
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
            DELETE FROM `event-media`
            WHERE event_id = $input->eventId
            AND is_event_image = $isEventImage
            ";
        $ok = $this->dbh->query($query);

        //  then add new ones
        if (count($input->aAssocIds)) {
            if ($isEventImage) {
                $mediaId = $input->aAssocIds[0];
                $query = "INSERT INTO `event-media` VALUES(
                          $input->eventId, $mediaId, $isEventImage
                        )";
                $ok = $this->dbh->query($query);
            } else {
                foreach ($input->aAssocIds as $mediaId) {
                    $query = "INSERT INTO `event-media` VALUES(
                              $input->eventId, $mediaId, $isEventImage
                            )";
                    $ok = $this->dbh->query($query);
                }
            }
        }
    }

    function _cmd_associateToArtwork(&$input, &$output)
    {
        //  first delete existing associations for this artworkId id
        $query = "
            DELETE FROM `artwork-media`
            WHERE artwork_id = $input->artworkId
            AND media_type_id = $input->mediaTypeId
            ";
        $ok = $this->dbh->query($query);

        if (count($input->aAssocIds)) {
            //  then add new ones
            foreach ($input->aAssocIds as $mediaId) {
                $isDefaultImage = ($input->defaultImgId == $mediaId) ? 1 : 0;
                $query = "INSERT INTO `artwork-media` VALUES(
                          $input->artworkId, $mediaId, $input->mediaTypeId, $isDefaultImage
                        )";
                $ok = $this->dbh->query($query);
            }
        }
    }

    function _cmd_redirectToCaller(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        SGL_HTTP::redirect(array(
            'moduleName'  => $input->callerMod,
            'managerName' => $input->callerMgr)
            );
    }
}
?>
