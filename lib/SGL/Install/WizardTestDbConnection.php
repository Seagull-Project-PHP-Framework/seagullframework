<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2006, Demian Turner                                         |
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
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | WizardTestDbConnection.php                                                |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+

function canConnectToDbServer()
{
    $aFormValues = $_SESSION['_installationWizard_container']['values']['page4'];

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
    $dbName = (!empty($aFormValues['dbName']) && ($aFormValues['dbName'] != 'not required for MySQL login'))
                ? '/'.$aFormValues['dbName']
                : '';
    $dsn = $aFormValues['dbType']['type'] . '://' .
        $aFormValues['databaseUser'] . ':' .
        $aFormValues['databaseUserPass'] . '@' .
        $protocol .
        $host . $port . $dbName;

    //  attempt to get db connection
    $dbh = & SGL_DB::singleton($dsn);

    if (PEAR::isError($dbh)) {
        SGL_Install_Common::errorPush($dbh);
        return false;
    } else {
        //  detect and store DB info
        if (preg_match("/mysql/", $dbh->phptype)) {
            $mysqlVersion = mysql_get_server_info();
        }
        $aEnvData = unserialize(file_get_contents(SGL_VAR_DIR . '/env.php'));
        $aEnvData['db_info'] = array(
            'dbDriver' => $dbh->phptype,
            'version' => isset($mysqlVersion) ? $mysqlVersion : '',
            );
        $serialized = serialize($aEnvData);
        @file_put_contents(SGL_VAR_DIR . '/env.php', $serialized);
        return true;
    }
}

/**
 * @package Install
 */
class WizardTestDbConnection extends HTML_QuickForm_Page
{
    function buildForm()
    {
        $this->_formBuilt = true;
        $this->addElement('header', null, 'Test DB Connection: page 4 of 6');

        //  FIXME: use env.php info to supply sensible defaults
        $this->setDefaults(array(
            'host' => 'localhost',
            'dbProtocol'  => array('protocol' => 'unix'),
            'dbType'  => array('type' => 'mysql_SGL'),
            'dbPortChoices'  => array('portOption' => 3306),
            'dbPort'  => array('port' => 3306),
            'dbName'  => 'not required for MySQL login',
            ));
        $this->setDefaults(SGL_Install_Common::overrideDefaultInstallSettings());

        //  type
        $radio[] = &$this->createElement('radio', 'type',     'Database type: ',
            "mysql_SGL (all sequences in one table)", 'mysql_SGL', 'onClick="toggleDbNameForLogin(false);toggleMysqlCluster(true);"');
        $radio[] = &$this->createElement('radio', 'type',     '', "mysql",  'mysql',
            'onClick="toggleDbNameForLogin(false);toggleMysqlCluster(false);"');

        if (SGL_MINIMAL_INSTALL == false) {
            $radio[] = &$this->createElement('radio', 'type',     '', "postgres", 'pgsql',
                'onClick="toggleDbNameForLogin(true);toggleMysqlCluster(false);"');
            //$radio[] = &$this->createElement('radio', 'type',     '', "oci8", 'oci8_SGL',
            //    'onClick="toggleDbNameForLogin(true);toggleMysqlCluster(false);"');
        }
        $this->addGroup($radio, 'dbType', 'Database type:', '<br />');
        $this->addGroupRule('dbType', 'Please specify a db type', 'required');

        //  mysql cluster
        $this->addElement('checkbox', 'mysqlCluster', 'Install on MySQL Cluster', 'Yes (only for mysql cluster installs!)', 'id=mysqlCluster');

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
        $radio[] = &$this->createElement('radio', 'portOption', 'TCP port: ',"3306 (MySQL default)",
            3306, 'onClick="copyValueToPortElement(this);"');
        if (SGL_MINIMAL_INSTALL == false) {
            $radio[] = &$this->createElement('radio', 'portOption', '',"5432 (Postgres default)",
                5432, 'onClick="copyValueToPortElement(this);"');
            $radio[] = &$this->createElement('radio', 'portOption', '',"1521 (Oracle default)",
                1521, 'onClick="copyValueToPortElement(this);"');
        }
        $this->addGroup($radio, 'dbPortChoices', 'TCP port:', '<br />');
        $this->addElement('text',  'dbPort[port]',    '', 'id="targetPortElement"');
        #$this->addRule('dbPort[port]', 'Please specify a db port', 'required');

        //  credentials
        $this->addElement('text',  'databaseUser',    'Database username: ');
        $this->addElement('password', 'databaseUserPass', 'Database password: ');
        $this->addElement('text',  'dbName',    'Database name: ', array(
            'id' => 'dbLoginNameElement', 'size'=> 25));
        $this->addRule('databaseUser', 'Please specify the db username', 'required');

        //  test db connect
        $this->registerRule('canConnectToDbServer','function','canConnectToDbServer');
        $this->addRule('databaseUser', 'cannot connect to the db, please check all credentials', 'canConnectToDbServer');

        //  submit
        $prevnext[] =& $this->createElement('submit',   $this->getButtonName('back'), '<< Back');
        $prevnext[] =& $this->createElement('submit',   $this->getButtonName('next'), 'Next >>');
        $this->addGroup($prevnext, null, '', '&nbsp;', false);
        $this->setDefaultAction('next');
    }
}
?>
