<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005, Demian Turner                                         |
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
// | Seagull 0.5                                                               |
// +---------------------------------------------------------------------------+
// | WizardTestDbConnection.php                                                |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+

function canConnectToDbServer()
{
    $aFormValues = $_SESSION['_installationWizard_container']['values']['page3'];

    $socket = (isset($aFormValues['dbProtocol']['protocol'])
                && $aFormValues['dbProtocol']['protocol'] == 'unix'
                && !empty($aFormValues['socket']))
        ? '(' . $aFormValues['socket'] . ')'
        : '';

	$protocol = isset($aFormValues['dbProtocol']['protocol']) 
        ? $aFormValues['dbProtocol']['protocol'] . $socket
        : '';
    $host = empty($aFormValues['socket']) ? '+' . $aFormValues['host'] : '';
    $port = (!empty($aFormValues['dbPort']['port'])
                && isset($aFormValues['dbProtocol']['protocol'])
                && ($aFormValues['dbProtocol']['protocol'] == 'tcp'))
        ? ':' . $aFormValues['dbPort']['port']
        : '';
    $dbName = (!empty($aFormValues['dbName']) && ($aFormValues['dbName'] != 'not required for MySQL'))
                ? '/'.$aFormValues['dbName']
                : '';
    $dsn = $aFormValues['dbType']['type'] . '://' .
        $aFormValues['user'] . ':' .
        $aFormValues['pass'] . '@' .
        $protocol .
        $host . $port . $dbName;

    //  attempt to get db connection
    $dbh = & SGL_DB::singleton($dsn);

    if (PEAR::isError($dbh)) {
        SGL_Install_Common::errorPush($dbh);
        return false;
    } else {
        return true;
    }
}

class WizardTestDbConnection extends HTML_QuickForm_Page
{
    function buildForm()
    {
        $this->_formBuilt = true;
        $this->addElement('header', null, 'Test DB Connection: page 3 of 5');

        //  FIXME: use detect.php info to supply sensible defaults
        $this->setDefaults(array(
            'host' => 'localhost',
            'dbProtocol'  => array('protocol' => 'unix'),
            'dbType'  => array('type' => 'mysql_SGL'),
            'dbPort'  => array('port' => 3306),
            'dbName'  => 'not required for MySQL',
            ));

        //  type
        $radio[] = &$this->createElement('radio', 'type',     'Database type: ',"mysql_SGL (all sequences in one table)", 'mysql_SGL');
        $radio[] = &$this->createElement('radio', 'type',     '', "mysql",  'mysql');
        $radio[] = &$this->createElement('radio', 'type',     '', "postgres", 'pgsql');
#        $radio[] = &$this->createElement('radio', 'type',     '', "oci8", 'oci8_SGL');
#        $radio[] = &$this->createElement('radio', 'type',     '', "maxdb", 'maxdb_SGL');
#        $radio[] = &$this->createElement('radio', 'type',     '', "db2", 'db2_SGL');
        $this->addGroup($radio, 'dbType', 'Database type:', '<br />');
        $this->addGroupRule('dbType', 'Please specify a db type', 'required');

        //  host
        $this->addElement('text',  'host',     'Host: ');
        $this->addRule('host', 'Please specify the hostname', 'required');

        //  socket
        $this->addElement('text', 'socket', 'Socket: ');

        //  protocol
        unset($radio);
        $radio[] = &$this->createElement('radio', 'protocol', 'Protocol: ',"unix (fine for localhost connections)", 'unix');
        $radio[] = &$this->createElement('radio', 'protocol', '',"tcp", 'tcp');
        $this->addGroup($radio, 'dbProtocol', 'Protocol:', '<br />');
        $this->addGroupRule('dbProtocol', 'Please specify a db protocol', 'required');

        //  port
        unset($radio);
        $radio[] = &$this->createElement('radio', 'port',     'TCP port: ',"3306 (MySQL default)", 3306);
        $radio[] = &$this->createElement('radio', 'port',     '',"5432 (Postgres default)", 5432);
#        $radio[] = &$this->createElement('radio', 'port',     '',"1521 (Oracle default)", 1521);
#        $radio[] = &$this->createElement('radio', 'port',     '',"7210 (MaxDB default)", 7210);
#        $radio[] = &$this->createElement('radio', 'port',     '',"50001 (DB2 default)", 50001);
        $this->addGroup($radio, 'dbPort', 'TCP port:', '<br />');
        $this->addGroupRule('dbPort', 'Please specify a db port', 'required');

        //  credentials
        $this->addElement('text',  'user',    'Database username: ');
        $this->addElement('password', 'pass', 'Database password: ');
        $this->addElement('text',  'dbName',    'Database name: ');
        $this->addRule('user', 'Please specify the db username', 'required');

        //  test db connect
        $this->registerRule('canConnectToDbServer','function','canConnectToDbServer');
        $this->addRule('user', 'cannot connect to the db, please check all credentials', 'canConnectToDbServer');

        //  submit
        $prevnext[] =& $this->createElement('submit',   $this->getButtonName('back'), '<< Back');
        $prevnext[] =& $this->createElement('submit',   $this->getButtonName('next'), 'Next >>');
        $this->addGroup($prevnext, null, '', '&nbsp;', false);
        $this->setDefaultAction('next');
    }
}
?>