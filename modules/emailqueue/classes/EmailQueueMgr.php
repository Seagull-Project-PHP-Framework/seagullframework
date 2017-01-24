<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2007, Demian Turner                                         |
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
// | Seagull 0.6.3                                                             |
// +---------------------------------------------------------------------------+
// | EmailQueueMgr.php                                                         |
// +---------------------------------------------------------------------------+
// | Authors: Peter Termaten <peter.termaten@gmail.com>                        |
// |          Dmitri Lakachauskis <lakiboy83@gmail.com>                        |
// +---------------------------------------------------------------------------+

/**
 * CLI manager, which processes email queue.
 *
 * @package seagull
 * @subpackage emailqueue
 * @author Peter Termaten <peter.termaten@gmail.com>
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class EmailQueueMgr extends SGL_Manager
{
    public function __construct()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->_aActionsMapping = array(
            'list'    => array('list', 'cliResult'),
            'process' => array('process','cliResult'),
            'flush'   => array('flush', 'process', 'cliResult')
        );
    }

    public function validate(SGL_Request $req, SGL_Registry $input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated = true;
        $input->action   = $req->get('action') ? $req->get('action') : 'list';
        $input->tty      = "\n";

        $input->batchId      = $req->get('batchId');
        $input->deliveryDate = $req->get('deliveryDate');
        $input->limit        = $req->get('limit');
        $input->interval     = $req->get('interval');
    }

    /**
     * By default we just show availabe actions.
     *
     * @param SGL_Registry $input
     * @param SGL_Output $output
     */
    public function _cmd_list(SGL_Registry $input, SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $input->tty .= <<< HELP
Available actions:
  1. process            process emails in queue
       --batchId          process emails by certain batch id
       --deliveryDate     process emails of specified date
       --limit            process only certain number of emails
                          (unlimited by default)
       --interval         delay in seconds between every email
                          (anti-spam measure)

  2. flush              process all emails in queue, same as
                        --action=process --deliveryDate=all

HELP;
    }

    /**
     * Send emails from queue.
     *
     * Example usage: php www/index.php
     *   --moduleName=emailqueue
     *   --managerName=emailqueue
     *   --action=process
     *   --groupId=1
     *
     * @param SGL_Registry $input
     * @param SGL_Output $output
     */
    public function _cmd_process(SGL_Registry $input, SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        // header
        $msg = "Notice: `removeSent` option is set, processed messages"
            . " will be removed from queue\n";
        $input->tty .= $msg;
        $this->_flush($input->tty);

        // get config
        $conf = $this->conf['EmailQueueMgr'];
        if (!empty($input->limit)) {
            $conf['limit'] = $input->limit;
        }
        // queue params
        $aParams = array();
        if (!empty($input->batchId)) {
            $aParams['batch_id'] = $input->batchId;
        }

        $emailerClass = trim($this->conf['EmailQueueMgr']['emailer']);
        $emailerFile  = str_replace('_', DIRECTORY_SEPARATOR,
            $emailerClass) . '.php';
        require_once $emailerFile;

        // process queue
        $oQueue = new $emailerClass($conf);
        $aRet   = $oQueue->processQueue($input->deliveryDate,
            $input->interval, $aParams);

        if (PEAR::isError($aRet)) {
            $input->tty .= sprintf("Error: %s\n", $aRet->getMessage());
        } else {
            $msg = "Messages proccesed %s; sent: %s\n";
            $input->tty .= sprintf($msg, $aRet['processed'], $aRet['sent']);
        }
    }

    public function _cmd_flush(SGL_Registry $input, SGL_Output $output)
    {
        $input->deliveryDate = 'all';
    }

    /**
     * Action, which outputs CLI result.
     *
     * @param SGL_Registry $input
     * @param SGL_Output $output
     */
    public function _cmd_cliResult(SGL_Registry $input, SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $input->tty .= "\n";
        $this->_flush($input->tty, $stopScript = true);
    }

    /**
     * Send data to terminal.
     *
     * @param string $string
     * @param boolean $stopScript
     */
    private function _flush(&$string, $stopScript = false)
    {
        echo $string;
        flush();
        $string = '';
        if ($stopScript) {
            exit;
        }
    }
}
?>