<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Seagull 0.3                                                               |
// +---------------------------------------------------------------------------+
// | ShowQFTestMgr.php                                                        |
// +---------------------------------------------------------------------------+
// | Copyright (c) 2004 Demian Turner                                          |
// |                                                                           |
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This library is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU Library General Public               |
// | License as published by the Free Software Foundation; either              |
// | version 2 of the License, or (at your option) any later version.          |
// |                                                                           |
// | This library is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU         |
// | Library General Public License for more details.                          |
// |                                                                           |
// | You should have received a copy of the GNU Library General Public         |
// | License along with this library; if not, write to the Free                |
// | Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA |
// |                                                                           |
// +---------------------------------------------------------------------------+
// $Id: ShowQFTestMgr.php,v 1.2 2005/09/13 12:08:10 krzysztofk Exp $



/**
 * ShowQFTestMgr
 * @package ShowQFTestMgr.php
 * @copyright Copyright (c) 2004
 * @version $Id: ShowQFTestMgr.php,v 1.2 2005/09/13 12:08:10 krzysztofk Exp $
 * @access public
 **/
class ShowQFTestMgr extends SGL_Manager {

    /**
     * ShowQFTestMgr::ShowQFTestMgr()
     * constructor 
     * @access public
     * @return void
     **/
    function ShowQFTestMgr() {
        Base::logMessage(   __CLASS__ . '::' . __FUNCTION__ ,
            null, null, PEAR_LOG_DEBUG);
        $this->module       = 'tools';
        $this->pageTitle    = 'TEST';
        $this->template     = 'default-flexy-dynamic.html';
        //avaiable action for dataGrid controler
        
        $this->_aAllowedActions = array(
            'start', 'struktura', 'plik', 'typy', 'flexy', 'inne', 'walidacja', 'podsumowanie');
        $this->_aAllowedActionsDescription = array(
            'start',
            'struktura',
            'plik',
            'typy',
            'flexy',
            'inne',
            'walidacja',
            'podsumowanie'
            );
    }

    /**
     * ShowQFTestMgr::validate()
     * for validating $input object
     * @param $req
     * @param $input
     * @access public
     * @return $input object
     **/
    function validate($req, $input) {
        Base::logMessage(   __CLASS__ . '::' . __FUNCTION__ ,
            null, null, PEAR_LOG_DEBUG);
        $this->validated    = true;
        $input->error       = array();
        $input->pageTitle   = $this->pageTitle;
        $input->template    = $this->template;
        //get specified action from FORM
        $input->action      = ($req->get('action')) ? $req->get('action') : 'start';
        $input->frmId       = $req->get('frmId');
        $input->frmParentId    = ($req->get('frmParentId')) ? $req->get('frmParentId') : 1;
        $input->rightCol = false;
        $input->leftCol = false;
        $input->charset = 'UTF-8';
        //location for document files and images
        $input->uploadPath = SGL_UPLOAD_DIR;
        return $input;
    }

    /**
     * ShowQFTestMgr::process()
     * main operative function
     * @param $input
     * @param $output
     * @access public
     * @return $output object
     **/
    function process($input, $output) {
        Base::logMessage(   __CLASS__ . '::' . __FUNCTION__ ,
            null, null, PEAR_LOG_DEBUG);
        //determine method implied by specified parameter
        $methodName = '_' . $input->action;
        $className = get_class($this);
        //build relevant perms constant
        $perm = @constant('SGL_PERMS_' . strtoupper($className . $methodName));
        //check if method allowed
        if (in_array($input->action, $this->_aAllowedActions) &&
            method_exists($this, $methodName)) {
                $this->$methodName($input, $output);
                //and if user has perms for this method
                /*if (SGL_HTTP_Session::hasPerms($perm)) {
                * $this->$methodName($input, $output);
                * } else {
                * Base::raiseError('you do not have the required perms for ' .
                * $className . '::' . $methodName, SGL_ERROR_INVALIDMETHODPERMS);
                * }
                */
        }
        else {
            Base::raiseError('The specified method, ' . $methodName .
                ' does not exist', SGL_ERROR_NOMETHOD, PEAR_ERROR_DIE);
        }
        return $output;
    }

    /**
     * ShowQFTestMgr::display()
     * for displaying $output object
     * @param $output
     * @access public
     * @return $output object
     **/
    function display($output) {
        Base::logMessage(   __CLASS__ . '::' . __FUNCTION__ ,
            null, null, PEAR_LOG_DEBUG);
        return $output;
    }


    /**
     * ShowQFTestMgr::_start()
     * 
     * @return 
     **/
    function _start() {
        echo '<br><br><center>'.
             '<h2>ShowQF TEST</h2>'.
             '<br><br><a href="?action=struktura">struktura bazy danych - tabele z polami (Documentus)</a>'.
             '<br><br><a href="?action=plik">dialog z pliku i do bazy - opis klasy</a>'.
             '<br><br><a href="?action=typy">obs³ugiwane typy pól (Documentus)</a>'.
             '<br><a href="?action=flexy">przyk³ad dialogu QF opartego na flexy</a>'.
             '<br><a href="?action=inne">przyk³ad zaawansowanego dialogu QF</a>'.
             '<br><a href="?action=podsumowanie">podsumowanie - linki, materia³y</a>'.
             '</center>';
             
    }

   
    /**
     * ShowQFTestMgr::_struktura()
     * 
     * @return 
     **/
    function _struktura() {
       ?>
	   <p>Informacje o polach oraz ich typach przechowywane s¹ w poni¿szych tabelach w bazie danych:</p>
	   <table border=1>
                    <tr bgcolor='gray'>
                        <th colspan = 23>_fields --- ver. <small>1.0</small></th></tr><tr><small></small></td><td valign='top'><h4>field_id</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>int</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>11</b><br></small></td><td valign='top'><h4>type_id</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>int</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>11</b><br></small></td><td valign='top'><h4>field_type</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>int</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>11</b><br></small></td><td valign='top'><h4>name</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>string</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>32</b><br></small></td><td valign='top'><h4>db_name</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>string</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>32</b><br></small></td><td valign='top'><h4>constraints</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>blob</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>65535</b><br></small></td><td valign='top'><h4>format</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>blob</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>65535</b><br></small></td><td valign='top'><h4>displayed_value</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>blob</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>65535</b><br></small></td></tr><tr><td valign='top'><h4>invalid_value_message</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>blob</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>65535</b><br></small></td><td valign='top'><h4>default_value</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>string</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>32</b><br></small></td><td valign='top'><h4>is_required</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>int</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>6</b><br></small></td><td valign='top'><h4>is_unique</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>int</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>6</b><br></small></td><td valign='top'><h4>field_position</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>int</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>11</b><br></small></td><td valign='top'><h4>multilanguage</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>int</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>6</b><br></small></td><td valign='top'><h4>description</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>blob</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>65535</b><br></small></td><td valign='top'><h4>filtered</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>int</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>6</b><br></small></td><td valign='top'><h4>visible</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>int</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>6</b><br></small></td><td valign='top'><h4>char_nr</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>int</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>11</b><br></small></td></tr><tr><td valign='top'><h4>multiline</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>int</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>6</b><br></small></td><td valign='top'><h4>select_many</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>int</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>6</b><br></small></td><td valign='top'><h4>select_many_table</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>string</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>32</b><br></small></td><td valign='top'><h4>translated</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>int</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>6</b><br></small></td><td valign='top'><h4>fields_groups_id</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>int</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>11</b><br></small></td></tr></table><table border=1>
                    <tr bgcolor='gray'>
                        <th colspan = 6>_fields_types --- ver. <small>1.0</small></th></tr><tr><small></small></td><td valign='top'><h4>field_type_id</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>int</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>11</b><br></small></td><td valign='top'><h4>name</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>string</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>32</b><br></small></td><td valign='top'><h4>displayed_name</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>string</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>50</b><br></small></td><td valign='top'><h4>db_type</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>string</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>32</b><br></small></td><td valign='top'><h4>select_field</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>int</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>6</b><br></small></td><td valign='top'><h4>deprecated</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>int</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>6</b><br></small></td></tr></table>
							  <table border=1>
                    <tr bgcolor='gray'>
                        <th colspan = 6>_types --- ver. <small>1.0</small></th></tr><tr><small></small></td><td valign='top'><h4>type_id</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>int</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>11</b><br></small></td><td valign='top'><h4>name</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>string</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>32</b><br></small></td><td valign='top'><h4>is_user</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>int</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>6</b><br></small></td><td valign='top'><h4>table_name</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>string</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>32</b><br></small></td><td valign='top'><h4>show_checked</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>int</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>11</b><br></small></td><td valign='top'><h4>available</h4><small><span style="color:#000088;">TYPE 
                              </span> - <b>int</b><br><span style="color:#000088;">VALUE 
                              </span> - <b>6</b><br></small></td></tr></table>
						
	   <?
    }
    
     
    /**
     * ShowQFTestMgr::_plik()
     * 
     * @return 
     **/
    function _plik() {
        //$db = & Base::DB();
        ?>
		<p>Istnieje mo¿liwoœæ zrzucenie struktury z danymi tabel do pliku (skryptu php), <br>który mo¿na dowolnie
		modyfikowaæ a nastepnie uruchomiæ w celu wprowadzenia zmian w typach pól do bazy danych.</p>
		<p>Przyk³ad klasy tworz¹cej dumpa 3 tabel:</p>
<?
$kod = '
require "ImportExportTableData.php";

//serwer, dbname, user, pass
$dbData = new ImportExportTableData(\'localhost\', \'seagull\', \'root\', \'\');
//set table to export
$dbData->exportTableToFile(\'_types\');
$dbData->exportTableToFile(\'_fields\');
$dbData->exportTableToFile(\'_fields_types\');

//write all tables to file
$dbData->writeToFile(\'fieldsANDtypes.php\')
';
echo nl2br(htmlentities($kod));
?>
		<p>Takie rozwi¹zanie pozwala na ³atwe wersjonowanie i update danych dotycz¹cych pól.
		Operacje tak¹ obs³uguje klasa ImportExportTableData.php</p>
		<p>Wynikiem zrzutu jest nastêpuj¹cy skrypt (dane tylko z 1 tabeli):</p>
		<?        
        $kod = '
        		/**
* File Dialog Schema
*
* @program name:
* @version:
* @dumpa data: Thu, 23 Dec 2004 12:35:36 +0000
*/ 
require "ImportExportTableData.php";

$dbData = new ImportExportTableData(\'localhost\', \'seagull\', \'root\', \'\');

/*
* Table _types 
* field name: type_id | name | is_user | table_name | show_checked | available | 
*/
$dbData->exportFileToDB("_types"); 

$dbData->addData("_types", "42", "doc_document", "0", "doc_document", "0", "0");

$dbData->addData("_types", "43", "doc_priority", "0", "doc_priority", "0", "0");

$dbData->addData("_types", "45", "doc_document_role", "0", "doc_document_role", "0", "0");

$dbData->addData("_types", "46", "doc_tree_modify", "0", "doc_document", "0", "0");

$dbData->addData("_types", "48", "doc_document_type", "0", "doc_document_type", "0", "0");

$dbData->addData("_types", "49", "Role", "0", "role", "0", "0");

$dbData->addData("_types", "50", "doc_status", "0", "doc_status", "0", "0");

$dbData->addData("_types", "55", "doc_test_contact", "0", "doc_test_contact", "0", "0");

$dbData->addData("_types", "52", "RoleUser", "0", "role_user", "0", "0");

$dbData->addData("_types", "54", "doc_search", "0", "doc_document", "0", "0");

$dbData->addData("_types", "53", "doc_attach", "0", "enclosures", "0", "0");

$dbData->addData("_types", "42", "doc_document", "0", "doc_document", "0", "0");

$dbData->addData("_types", "43", "doc_priority", "0", "doc_priority", "0", "0");

$dbData->addData("_types", "45", "doc_document_role", "0", "doc_document_role", "0", "0");

$dbData->addData("_types", "46", "doc_tree_modify", "0", "doc_document", "0", "0");

$dbData->addData("_types", "48", "doc_document_type", "0", "doc_document_type", "0", "0");

$dbData->addData("_types", "49", "Role", "0", "role", "0", "0");

$dbData->addData("_types", "50", "doc_status", "0", "doc_status", "0", "0");

$dbData->addData("_types", "51", "doc_flowpath", "0", "doc_flowpath", "0", "0");

$dbData->addData("_types", "52", "RoleUser", "0", "role_user", "0", "0");

$dbData->addData("_types", "54", "doc_search", "0", "doc_document", "0", "0");

$dbData->addData("_types", "53", "doc_attach", "0", "enclosures", "0", "0");
        ';
        echo nl2br(htmlentities($kod));
       
    }
    
    /**
     * ShowQFTestMgr::_start()
     * 
     * @return 
     **/
    function _flexy() {
    require_once "varlib/ShowQuickForm.php";
    $form =& new HTML_QuickForm('form', 'POST', '?action=flexy');

// Fills with some defaults values

$defaultValues['company']  = 'Devils son in law';
$defaultValues['country']  = array();
$defaultValues['name']     = array('first'=>'Petey', 'last'=>'Wheatstraw');
$defaultValues['phone']    = array('513', '123', '4567');
$form->setDefaults($defaultValues);

// Hidden

$form->addElement('hidden', 'session', '1234567890');

// Personal information

$form->addElement('header', 'personal', 'Personal Information');

$form->addElement('hidden', 'ihidTest', 'hiddenField');
$form->addElement('text', 'email', 'Your email:');
$form->addElement('password', 'pass', 'Your password:', 'size=10');
$name['last'] = &HTML_QuickForm::createElement('text', 'first', 'First',
'size=10');
$name['first'] = &HTML_QuickForm::createElement('text', 'last', 'Last',
'size=10');
$form->addGroup($name, 'name', 'Name:', ',&nbsp;');
$areaCode = &HTML_QuickForm::createElement('text', '', null,'size=4
maxlength=3');
$phoneNo1 = &HTML_QuickForm::createElement('text', '', null, 'size=4
maxlength=3');
$phoneNo2 = &HTML_QuickForm::createElement('text', '', null, 'size=5
maxlength=4');
$form->addGroup(array($areaCode, $phoneNo1, $phoneNo2), 'phone',
'Telephone:', '-');

// Company information

$form->addElement('header', 'company_info', 'Company Information');

$form->addElement('text', 'company', 'Company:', 'size=20');

$str[] = &HTML_QuickForm::createElement('text', '', null, 'size=20');
$str[] = &HTML_QuickForm::createElement('text', '', null, 'size=20');
$form->addGroup($str, 'street', 'Street:', '<br />');

$addr['zip'] = &HTML_QuickForm::createElement('text', 'zip', 'Zip', 'size=6
maxlength=10');
$addr['city'] = &HTML_QuickForm::createElement('text', 'city', 'City',
'size=15');
$form->addGroup($addr, 'address', 'Zip, city:');

$select = array('' => 'Please select...', 'AU' => 'Australia', 'FR' =>
'France', 'DE' => 'Germany', 'IT' => 'Italy');
$form->addElement('select', 'country', 'Country:', $select);

$checkbox[] = &HTML_QuickForm::createElement('checkbox', 'A', null, 'A');
$checkbox[] = &HTML_QuickForm::createElement('checkbox', 'B', null, 'B');
$checkbox[] = &HTML_QuickForm::createElement('checkbox', 'C', null, 'C');
$checkbox[] = &HTML_QuickForm::createElement('checkbox', 'D', null, 'D');
$form->addGroup($checkbox, 'destination', 'Destination:', array('&nbsp;',
'<br />'));

// Other elements

$form->addElement('checkbox', 'news', '', " Check this box if you don't want
to receive our newsletter.");

$form->addElement('reset', 'reset', 'Reset');
$form->addElement('submit', 'submit', 'Register');

// Adds some validation rules

$form->addRule('email', 'Email address is required', 'required');
$form->addGroupRule('name', 'Name is required', 'required');
$form->addRule('pass', 'Password must be between 8 to 10 characters',
'rangelength', array(8, 10),'client');
$form->addRule('country', 'Country is a required field', 'required');
$form->addGroupRule('destination', 'Please check at least two boxes',
'required', null, 2);
$form->addGroupRule('phone', 'Please fill all phone fields', 'required');
$form->addGroupRule('phone', 'Values must be numeric', 'numeric');

$AddrRules['zip'][0] = array('Zip code is required', 'required');
$AddrRules['zip'][1] = array('Zip code is numeric only', 'numeric');
$AddrRules['city'][0] = array('City is required', 'required');
$AddrRules['city'][1] = array('City is letters only', 'lettersonly');
$form->addGroupRule('address', $AddrRules);

// Tries to validate the form
if ($form->validate()) {
    // Form is validated, then freezes the data
    $form->freeze();
    $form->process('myProcess',  false);
    echo "\n<hr>\n";
} 

// setup a template object
$options = array(
            //search directory with template file
            'templateDir' => SGL_THEME_DIR . '/' .$_SESSION['aPrefs']['theme']. '/' . $moduleName . PATH_SEPARATOR .
                             SGL_THEME_DIR . '/default/' . $moduleName . PATH_SEPARATOR .
                             SGL_THEME_DIR . '/' . $_SESSION['aPrefs']['theme'] . '/default' . PATH_SEPARATOR .
                             SGL_THEME_DIR . '/default/default',
            'templateDirOrder'  => 'reverse',
            'multiSource'       => true,
            'compileDir'        => SGL_CACHE_DIR . '/tmpl/' . $_SESSION['aPrefs']['theme'],
            'forceCompile'      => SGL_FLEXY_FORCE_COMPILE,
            'debug'             => SGL_FLEXY_DEBUG,
            'allowPHP'          => SGL_FLEXY_ALLOW_PHP,
            'filters'           => SGL_FLEXY_FILTERS,
            'locale'            => SGL_FLEXY_LOCALE,
            'compiler'          => SGL_FLEXY_COMPILER,
            'valid_functions'   => SGL_FLEXY_VALID_FNS,
            'flexyIgnore'       => SGL_FLEXY_IGNORE,
            'globals'           => true,
            'globalfunctions'   => SGL_FLEXY_GLOBAL_FNS,
            );

$template = new HTML_Template_Flexy($options);

$renderer =& new HTML_QuickForm_Renderer_ObjectFlexy($template);
$renderer->setLabelTemplate("autotest_label.html");
$renderer->setHtmlTemplate("autotest_html.html");

$form->accept($renderer);

$view = new StdClass;
$view->form = $renderer->toObject();

$template->compile("autotest_flexy-static.html");
// capture the array stucture
ob_start();
//print_r($view->form);
$view->static_object =  ob_get_contents();
ob_end_clean();

// render and display the template
$template->outputObject($view);
    ?>
	<p>Walidacja odbywa siê po stronie serwera na podstawie ustalonych wczeœniej regu³ dla ka¿dego pola. Istnieje szereg podstawowych regu³, mo¿liwe jest tak¿e definiowanie dowolnych nowych regu³ na podstawie wyra¿eñ regularnych, mo¿liwe jest tak¿e rozszerzenie regu³ poprzez now¹ klasê bazow¹.</p>

<p>Spójnoœæ z FLEXY daje nam swobodê operowania zarówno uk³adem pól, ich etykiet jak i komunikatów o b³êdach. </p>
<?
    
    }

    /**
     * ShowQFTestMgr::_typy()
     * 
     * @return 
     **/
    function _typy() {
    
    require_once "varlib/ShowQuickForm.php";
             $flow = & new showQuickForm('doc_autotest', 1);
             $flow->setTemplateVariable('title', 'Doc auto test');
		     $flow->setFormAction('edit');
             $flow->addElement('date', 'dateTest1', 'Date 1 (programmer add)', array('format'=>'dmY', 'minYear'=>2010, 'maxYear'=>2001));
             $flow->addElement('date', 'dateTest3', 'Today is (programmer add)', array('format'=>'l d M Y'));

		     $flow->process();
    }
    
    function _inne() {
        require_once 'HTML/QuickForm.php';
        $form =& new HTML_QuickForm('frmTest', 'post', '?action=inne');

// Use a two-label template for the elements that require some comments
$twoLabel = <<<_HTML
<tr valign="top">
    <td align="right">
        <!-- BEGIN required --><span style="color: #F00;">*</span><!-- END required --><b>{label}</b>
    </td>
    <td align="left">
        <!-- BEGIN error --><span style="color: #F00;">{error}</span><br /><!-- END error -->{element}
        <!-- BEGIN label_2 --><br /><span style="font-size: 80%;">{label_2}</span><!-- END label_2 -->
    </td>
</tr>
_HTML;

$renderer =& $form->defaultRenderer();
$renderer->setElementTemplate($twoLabel, 'iadvChk');
$renderer->setElementTemplate($twoLabel, 'iautoComp');

// Fills with some defaults values
$form->setDefaults(array(
    'itxtTest'  => 'Test Text Box',
    'itxaTest'  => 'Hello World',
    'ichkTest'  => true,
    'iradTest'  => 1,
    'iselTest'  => array('B', 'C'),
    'name'      => array('first'=>'Adam', 'last'=>'Daniel'),
    'phoneNo'   => array('513', '123', '3456'),
    'iradYesNo' => 'Y',
    'ichkABC'   => array('A'=>true,'B'=>true),
    'dateTest1' => array('d'=>11, 'm'=>1, 'Y'=>2003)
));

$form->setConstants(array(
    'dateTest3' => time()
));

// Elements will be displayed in the order they are declared
$form->addElement('header', '', 'Normal Elements');
// Classic form elements
$form->addElement('hidden', 'ihidTest', 'hiddenField');
$form->addElement('text', 'itxtTest', 'Test Text:');
$form->addElement('textarea', 'itxaTest', 'Test TextArea:', array('rows' => 3, 'cols' => 20));
$form->addElement('password', 'ipwdTest', 'Test Password:');
$form->addElement('checkbox', 'ichkTest', 'Test CheckBox:', 'Check the box');
$form->addElement('radio', 'iradTest', 'Test Radio Buttons:', 'Check the radio button #1', 1);
$form->addElement('radio', 'iradTest', '(Not a group)', 'Check the radio button #2', 2);
$form->addElement('button', 'ibtnTest', 'Test Button', array('onclick' => "alert('This is a test');"));
$form->addElement('reset', 'iresTest', 'Test Reset');
$form->addElement('submit', 'isubTest', 'Test Submit');
$form->addElement('image', 'iimgTest', 'http://pear.php.net/gifs/pear-icon.gif');
$select =& $form->addElement('select', 'iselTest', 'Test Select:', array('A'=>'A', 'B'=>'B','C'=>'C','D'=>'D'), array('onclick' => "alert('This is a test');"));
$select->setSize(5);
$select->setMultiple(true);

$form->addElement('header', '', 'Custom Elements');
// Date elements
$form->addElement('date', 'dateTest1', 'Date1:', array('format'=>'dmY', 'minYear'=>2010, 'maxYear'=>2001));
$form->addElement('date', 'dateTest2', 'Date2:', array('format'=>'d-F-Y H:i', 'language'=>'de', 'optionIncrement' => array('i' => 5)));
$form->addElement('date', 'dateTest3', 'Today is:', array('format'=>'l d M Y'));

$main[0] = "Pop";
$main[1] = "Rock";
$main[2] = "Classical";

$secondary[0][0] = "Belle & Sebastian";
$secondary[0][1] = "Elliot Smith";
$secondary[0][2] = "Beck";
$secondary[1][3] = "Noir Desir";
$secondary[1][4] = "Violent Femmes";
$secondary[2][5] = "Wagner";
$secondary[2][6] = "Mozart";
$secondary[2][7] = "Beethoven";

$opts[] = $main;
$opts[] = $secondary;

$hs =& $form->addElement('hierselect', 'ihsTest', 'Hierarchical select:', array('style' => 'width: 20em;'), '<br />');
$hs->setOptions($opts);

$form->addElement('advcheckbox', 'iadvChk', array('Advanced checkbox:', 'Unlike standard checkbox, this element <b>has</b> a value<br />when it is not checked.'), 'Check the box', null, array('off', 'on'));

$form->addElement('autocomplete', 'iautoComp', array('Your favourite fruit:', 'This is autocomplete element.<br />Start typing and see how it suggests possible completions.'), array('Pear', 'Orange', 'Apple'), array('size' => 30));


$form->addElement('header', '', 'Grouped Elements');
// Grouped elements
$name['last'] = &HTML_QuickForm::createElement('text', 'last', null, array('size' => 30));
$name['first'] = &HTML_QuickForm::createElement('text', 'first', null, array('size' => 20));
$form->addGroup($name, 'name', 'Name (last, first):', ',&nbsp;');
// Creates a group of text inputs
$areaCode = &HTML_QuickForm::createElement('text', '', null, array('size' => 3, 'maxlength' => 3));
$phoneNo1 = &HTML_QuickForm::createElement('text', '', null, array('size' => 3, 'maxlength' => 3));
$phoneNo2 = &HTML_QuickForm::createElement('text', '', null, array('size' => 4, 'maxlength' => 4));
$form->addGroup(array($areaCode, $phoneNo1, $phoneNo2), 'phoneNo', 'Telephone:', '-');

// Creates a radio buttons group
$radio[] = &HTML_QuickForm::createElement('radio', null, null, 'Yes', 'Y');
$radio[] = &HTML_QuickForm::createElement('radio', null, null, 'No', 'N');
$form->addGroup($radio, 'iradYesNo', 'Yes/No:');

// Creates a checkboxes group
$checkbox[] = &HTML_QuickForm::createElement('checkbox', 'A', null, 'A');
$checkbox[] = &HTML_QuickForm::createElement('checkbox', 'B', null, 'B');
$checkbox[] = &HTML_QuickForm::createElement('checkbox', 'C', null, 'C');
$form->addGroup($checkbox, 'ichkABC', 'ABC:', '<br />');
// Creates a group of buttons to be displayed at the bottom of the form
$buttons[] = &HTML_QuickForm::createElement('submit', null, 'Submit');
$buttons[] = &HTML_QuickForm::createElement('reset', null, 'Reset');
$buttons[] = &HTML_QuickForm::createElement('image', 'iimgTest', 'http://pear.php.net/gifs/pear-icon.gif');
$buttons[] = &HTML_QuickForm::createElement('button', 'ibutTest', 'Test Button', array('onClick' => "alert('This is a test');"));
$form->addGroup($buttons, null, null, '&nbsp;', false);


// applies new filters to the element values
$form->applyFilter('__ALL__', 'trim');
// Adds some validation rules
$form->addRule('itxtTest', 'Test Text is a required field', 'required');
$form->addRule('itxaTest', 'Test TextArea is a required field', 'required');
$form->addRule('itxaTest', 'Test TextArea must be at least 5 characters', 'minlength', 5);
$form->addRule('ipwdTest', 'Password must be between 8 to 10 characters', 'rangelength', array(8, 10));

// Tries to validate the form
if ($form->validate()) {
    // Form is validated, then processes the data
    $form->freeze();
    $form->process('myProcess', false);
    echo "\n<HR>\n";
}

// Process callback
function myProcess($values)
{
    echo '<pre>';
    var_dump($values);
    echo '</pre>';
}

$form->display();        
    }
    
    function _podsumowanie() {
    ?>
	<center>
	<li><a href="http://www.pear.php.net/manual/en/package.html.html-quickform.php">Dokumentacja</a></li>
	<li><a href="http://www.sklar.com/talks/show.php/nyphp-quickform/4">Tutorial</a></li>
	<li><a href="http://www.devarticles.com/c/a/Web-Graphic-Design/Using-HTML-Quickform-for-Form-Processing/4/">Tutorial</a></li>
	<li><a href="http://www.devarticles.com/c/a/Web-Design-Usability/Using-HTML-QuickForm-To-Manage-Web-Forms-Part-2/">Tutorial</a></li>
	<li><a href="http://cvs.php.net/pear/HTML_QuickForm/">Examples</a></li>
	</center>
    
	<?
    }
}

?>