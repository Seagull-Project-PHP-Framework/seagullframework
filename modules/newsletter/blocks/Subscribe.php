<?php
/**
 * Newsletter Block
 *
 * @package block
 * @author  Alexander J. Tarachanowicz II <ajt@localhype.net>
 * @version $Revision: 1.1 $
 * @since   PHP 4.1
 */
require_once SGL_MOD_DIR . '/newsletter/classes/NewsletterMgr.php';
require_once SGL_MOD_DIR . '/newsletter/classes/Output.php';

class Newsletter_Block_Subscribe
{
    function init($output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->userID = isset($output->loggedOnUserID) ? $output->loggedOnUserID : '';
        $this->username = isset($output->loggedOnUser) ? $output->loggedOnUser : '';
        return $this->getBlockContent();
    }

    function getBlockContent()
    {

        if ($this->username) {
            $news = & new NewsletterMgr();
            $lists = $news->_getList();

            $subscribedLists = $news->getSubscribedLists($this->userID);
            $unsubscribedLists = $news->getUnsubscribedLists($this->userID);

            $text = '<table width="100%">';
            if ($subscribedLists) {
                $subscribedNewsLists = '';
                foreach ($subscribedLists as $k => $v) {
                    foreach ($lists as $lKey => $lValue) {
                        if ($lValue['name'] == $v->list) {
                            $this->listID = $lKey;
                        }
                    }

                    $subscribedNewsLists .= '<strong>'. $v->list .
                        '</strong> (<a href="'. SGL_Url::makeLink('unsubscribe',
                        'newsletter', 'newsletter') .'frmListName[]/'. $this->listID .
                        '/frmUserID/'. $this->userID . '">unsubscribe</a>)<br />';
                }

                $text .= '
                        <tr>
                            <td><strong>Current Subscriptions:</strong></td>
                        </tr>
                        <tr>
                            <td>'. $subscribedNewsLists .'<br /></td>
                        </tr>';
            }
            if ($unsubscribedLists) {
                $unsubscribedNewsLists = '';
                foreach ($unsubscribedLists as $k => $v) {
                    $this->listID = $k;
                    $unsubscribedNewsLists .= '<strong>'. $v['name'] .'</strong> - '. $v['description']
                        . ' (<a href="'. SGL_Url::makeLink('subscribe', 'newsletter', 'newsletter')
                        .'frmListName[]/'. $this->listID .'/frmUserID/'. $this->userID . '">subscribe</a>)<br />';
                }

                $text .= '
                        <tr>
                            <td><strong>Available Subscriptions:</strong></td>
                        </tr>
                        <tr>
                            <td>'. $unsubscribedNewsLists .'</td>
                        </tr>';
            }
            $text .= '
                    </table>
                ';
        } else {
            $news = & new NewsletterMgr();
            $lists = $news->_getList();

            $newsLists = '';
            foreach ($lists as $k => $v) {
                $newsLists .= '<input name="frmListName[]" type="checkbox" value="'.$k.'" >'.
                         '<strong>'.$v['description'].'</strong><br>';
            }
            $text = '
                <form method="post" name="NewsletterMgr" flexy:ignore id="NewsletterMgr" action="'.SGL_Output :: makeUrl('','newsletter','newsletter').'">
                    <table class="wide">
                        <tr>
                            <td colspan="2"><strong>Please login if you are a registered user.</strong><br /></td>
                        </tr>
                        <tr>
                            <td>' . SGL_String::translate('Name') . ':<br />
                            <input type="text" name="frmName" size="14" value="" /></td>
                        </tr>
                        <tr>
                            <td>' . SGL_String::translate('Email') . ':<br />
                            <input type="text" name="frmEmail" size="14" value="" /></td>
                        </tr>
                        <tr>
                            <td>' . SGL_String::translate('Lists') . ':<br />
                                ' . $newsLists . '
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><strong>' . SGL_String::translate('Action') . ':</strong><br />
                              <input type="radio" name="action" value="subscribe" checked="checked">
                              ' . SGL_String::translate('Subscribe') . ' <br />
                              <input type="radio" name="action" value="unsubscribe">
                              ' . SGL_String::translate('Unsubscribe') . '<br>
                            </td>
                        </tr>
                        <tr>
                            <td><input class="wideButton" type="submit" name="submitted" value="' . SGL_String::translate('Send') . '" /></td>
                        </tr>
                    </table>
                </form>';

        }

        return $text;
    }
}
?>