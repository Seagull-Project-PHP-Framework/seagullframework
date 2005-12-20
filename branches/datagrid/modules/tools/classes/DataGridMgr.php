<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Seagull 0.3                                                               |
// +---------------------------------------------------------------------------+
// | DataGridMgr.php                                                        |
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
// $Id: DataGridMgr.php,v 1.2 2005/09/26 15:21:41 adamm Exp $



/**
 * DataGridMgr
 * Present the DataGrid, DataGridDataSource and DataGridSQLDataSource functionality
 * @package DataGridMgr.php
 * @author Tomasz Przybysz, VARICO Poznan
 * @copyright Copyright (c) 2004
 * @version $Id: DataGridMgr.php,v 1.2 2005/09/26 15:21:41 adamm Exp $
 * @access public
 **/
class DataGridMgr extends SGL_Manager {

    /**
     * DataGridMgr::DataGridMgr()
     * constructor for DataGridTest
     * @access public
     * @return void
     **/
    function DataGridMgr() {
        SGL::logMessage(   __CLASS__ . '::' . __FUNCTION__ ,
            null, null, PEAR_LOG_DEBUG);
        
        parent::SGL_Manager();
        $this->module       = 'tools';
        $this->pageTitle    = 'dataGridTEST';
        $this->template     = 'dataGridMgr.html';

        $this->_aActionsMapping =  array(
            'create'       => array('create'),
            'info'      => array('info'),
            'dataGrid1'      => array('dataGrid1'),
            'dataGrid2'      => array('dataGrid2'),
            'dataGrid3'      => array('dataGrid3'),
            'dataGrid4'      => array('dataGrid4'),
            'dataGrid5'      => array('dataGrid5'),
            'list'      => array('list'),
        );
    }

    /**
     * DataGridMgr::validate()
     * for validating $input object
     * @param $req
     * @param $input
     * @access public
     * @return $input object
     **/
    function validate($req, &$input) {
        SGL::logMessage(   __CLASS__ . '::' . __FUNCTION__ ,
            null, null, PEAR_LOG_DEBUG);
        $this->validated    = true;
        $input->error       = array();
        $input->pageTitle   = $this->pageTitle;
        $input->masterTemplate = $this->masterTemplate;
        $input->template    = $this->template;
        //get specified action from FORM
        $input->action      = ($req->get('action')) ? $req->get('action') : 'list';
        //die($input->action);
        $input->frmId       = $req->get('frmId');
        $input->frmParentId    = ($req->get('frmParentId')) ? $req->get('frmParentId') : 1;
        $input->rightCol = false;
        $input->leftCol = false;
        //$input->charset = 'UTF-8';
        //sgl_http_request object
        $input->inputReq = $req;
        //location for document files and images
        $input->uploadPath = SGL_BASE_URL . '/../var/uploads';
        $input->wwwPath = SGL_BASE_URL;
        //return $input;
    }

    /**
     * DataGridMgr::display()
     * for displaying $output object
     * @param $output
     * @access public
     * @return $output object
     **/
   /* function display($output) {
        SGL::logMessage(   __CLASS__ . '::' . __FUNCTION__ ,
            null, null, PEAR_LOG_DEBUG);
        return $output;
    }*/

    /**
     * DataGridMgr::_list()
     * display main HTML site for testing, action selector
     * @param $input
     * @param $output
     * @access private
     * @return void
     **/
    function _list(&$input, &$output) {
    }

    /**
     * DataGridMgr::_info()
     * Display PHP information
     * @param $input
     * @param $output
     * @access private
     * @return void
     **/
    function _info(&$input, &$output) {
        ob_start();
        phpinfo();
        $php_info .= ob_get_contents();
        ob_end_clean();
        $output->outputText = $php_info;
    }

    /**
     * DataGridMgr::_create()
     * Ceate table PEOPLE in database and copy files
     * @param $input
     * @param $output
     * @access private
     * @return void or $res if error
     **/
    function addData(&$dbo, $id, $name, $surname, $city, $birthdate, $datetimeadded, $colour, $description, $picture, $fileattach, $salary, $email, $www) {
            $query = "INSERT INTO people (id, name, surname, city, birthdate, datetimeadded, colour, description, picture, fileattached, salary, email, www) VALUES ($id, '$name', '$surname', '$city',
            '$birthdate', '$datetimeadded', '$colour', '$description', '$picture', '$fileattach', $salary, '$email', '$www')";
            $res = $dbo->query($query);
            if (DB::isError($res)) {
                $output->outputText = 'There was a problem while inserting data to table in database<br>';
                return $res;
            }
    }

    function _create(&$input, &$output) {
         $db = & SGL_DB::singleton();
        //insert one row into database

        $query = "DROP TABLE people";
        $res = & $db->query($query);
        /* AM it is not needed to check it if (DB::isError($res)) {
            //$output->outputText = 'There was a problem while deleting table from database<br>';
        }*/
        //add PEOPLE table to database
        $query = "CREATE TABLE people (
                  id int NOT NULL ,
                  name varchar(50) NOT NULL ,
                  surname varchar(50) NOT NULL,
                  city varchar(50) NOT NULL,
                  birthdate date NOT NULL,
                  datetimeadded timestamp NOT NULL,
                  colour varchar(50) NOT NULL,
                  description varchar(50) ,
                  picture varchar(50) NOT NULL ,
                  fileattached varchar(50) NOT NULL,
                  salary float NOT NULL,
                  email varchar(30) NOT NULL,
                  www varchar(60) NOT NULL)";
        $res = & $db->query($query);
        if (DB::isError($res)) {
            $output->outputText = 'There was a problem while creating table in database<br>';
            return $res;
        }
        //insert data do database
        $this->addData ($db, 0, 'Andrew', 'Jones', 'Brisbane', '1990-11-27', '2004-11-19', 'lightyellow', 'This is a very long description of person simply t', '3.bmp', 'b.doc', '54.17', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 1, 'Jan', 'Nowak', 'Poznan', '1960-01-01', '2004-11-19', 'lightgrey', 'Very short description', '3.bmp', 'b.doc', '459.36', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 2, 'Stephen', 'Kossowski', 'Washington', '1950-08-05', '2004-11-19', 'lightblue', '', '3.bmp', 'b.doc', '288.92', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 3, 'Ann', 'Smith', 'London', '1951-09-11', '2004-11-19', 'lightgreen', 'This is a very long description of person simply t', '3.bmp', 'b.doc', '245.43', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 4, 'Jackie', 'Malone', 'Pretoria', '1980-05-12', '2004-11-19', '#FFCCCC', 'Very short description', '3.bmp', 'b.doc', '1000.91', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 5, 'Andrew', 'Jones', 'Brisbane', '1965-11-27', '2004-11-19', 'lightyellow', 'This is a very long description of person simply t', '3.bmp', 'b.doc', '54.17', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 6, 'Jan', 'Nowak', 'Poznan', '1960-01-01', '2004-11-19', 'lightgrey', 'Very short description', '3.bmp', 'b.doc', '459.36', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 7, 'Stephen', 'Kossowski', 'Washington', '1950-08-05', '2004-11-19', 'lightblue', '', '3.bmp', 'b.doc', '288.92', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 8, 'Ann', 'Smith', 'London', '1951-09-11', '2004-11-19', 'lightgreen', 'This is a very long description of person simply t', '3.bmp', 'b.doc', '245.43', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 9, 'Jackie', 'Malone', 'Pretoria', '1980-05-12', '2004-11-19', '#FFCCCC', 'Very short description', '3.bmp', 'b.doc', '1000.91', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 10, 'Andrew', 'Jones', 'Brisbane', '1965-11-27', '2004-11-19', 'lightyellow', 'This is a very long description of person simply t', '3.bmp', 'b.doc', '54.17', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 11, 'Jan', 'Nowak', 'Poznan', '1960-01-01', '2004-11-19', 'lightgrey', 'Very short description', '3.bmp', 'b.doc', '459.36', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 12, 'Stephen', 'Kossowski', 'Washington', '1950-08-05', '2004-11-19', 'lightblue', '', '3.bmp', 'b.doc', '288.92', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 13, 'Ann', 'Smith', 'London', '1951-09-11', '2004-11-19', 'lightgreen', 'This is a very long description of person simply t', '3.bmp', 'b.doc', '245.43', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 14, 'Jackie', 'Malone', 'Pretoria', '1980-05-12', '2004-11-19', '#FFCCCC', 'Very short description', '3.bmp', 'b.doc', '1000.91', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 15, 'Andrew', 'Jones', 'Brisbane', '1965-11-27', '2004-11-19', 'lightyellow', 'This is a very long description of person simply t', '3.bmp', 'b.doc', '54.17', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 16, 'Jan', 'Nowak', 'Poznan', '1960-01-01', '2004-11-19', 'lightgrey', 'Very short description', '3.bmp', 'b.doc', '459.36', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 17, 'Stephen', 'Kossowski', 'Washington', '1950-08-05', '2004-11-19', 'lightblue', '', '3.bmp', 'b.doc', '288.92', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 18, 'Ann', 'Smith', 'London', '1951-09-11', '2004-11-19', 'lightgreen', 'This is a very long description of person simply t', '3.bmp', 'b.doc', '245.43', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 19, 'Jackie', 'Malone', 'Pretoria', '1980-05-12', '2004-11-19', '#FFCCCC', 'Very short description', '3.bmp', 'b.doc', '1000.91', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 20, 'Andrew', 'Jones', 'Brisbane', '1990-11-27', '2004-11-19', 'lightyellow', 'This is a very long description of person simply t', '3.bmp', 'b.doc', '54.17', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 21, 'Jan', 'Nowak', 'Poznan', '1960-01-01', '2004-11-19', 'lightgrey', 'Very short description', '3.bmp', 'b.doc', '459.36', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 22, 'Stephen', 'Kossowski', 'Washington', '1950-08-05', '2004-11-19', 'lightblue', '', '3.bmp', 'b.doc', '288.92', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 23, 'Ann', 'Smith', 'London', '1951-09-11', '2004-11-19', 'lightgreen', 'This is a very long description of person simply t', '3.bmp', 'b.doc', '245.43', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 24, 'Jackie', 'Malone', 'Pretoria', '1980-05-12', '2004-11-19', '#FFCCCC', 'Very short description', '3.bmp', 'b.doc', '1000.91', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 25, 'Andrew', 'Jones', 'Brisbane', '1990-11-27', '2004-11-19', 'lightyellow', 'This is a very long description of person simply t', '3.bmp', 'b.doc', '54.17', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 26, 'Jan', 'Nowak', 'Poznan', '1960-01-01', '2004-11-19', 'lightgrey', 'Very short description', '3.bmp', 'b.doc', '459.36', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 27, 'Stephen', 'Kossowski', 'Washington', '1950-08-05', '2004-11-19', 'lightblue', '', '3.bmp', 'b.doc', '288.92', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 28, 'Ann', 'Smith', 'London', '1951-09-11', '2004-11-19', 'lightgreen', 'This is a very long description of person simply t', '3.bmp', 'b.doc', '245.43', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 29, 'Jackie', 'Malone', 'Pretoria', '1980-05-12', '2004-11-19', '#FFCCCC', 'Very short description', '3.bmp', 'b.doc', '1000.91', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 30, 'Andrew', 'Jones', 'Brisbane', '1990-11-27', '2004-11-19', 'lightyellow', 'This is a very long description of person simply t', '3.bmp', 'b.doc', '54.17', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 31, 'Jan', 'Nowak', 'Poznan', '1960-01-01', '2004-11-19', 'lightgrey', 'Very short description', '3.bmp', 'b.doc', '459.36', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 32, 'Stephen', 'Kossowski', 'Washington', '1950-08-05', '2004-11-19', 'lightblue', '', '3.bmp', 'b.doc', '288.92', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 33, 'Ann', 'Smith', 'London', '1951-09-11', '2004-11-19', 'lightgreen', 'This is a very long description of person simply t', '3.bmp', 'b.doc', '245.43', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 34, 'Jackie', 'Malone', 'Pretoria', '1980-05-12', '2004-11-19', '#FFCCCC', 'Very short description', '3.bmp', 'b.doc', '1000.91', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 35, 'Andrew', 'Jones', 'Brisbane', '1990-11-27', '2004-11-19', 'lightyellow', 'This is a very long description of person simply t', '3.bmp', 'b.doc', '54.17', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 36, 'Jan', 'Nowak', 'Poznan', '1960-01-01', '2004-11-19', 'lightgrey', 'Very short description', '3.bmp', 'b.doc', '459.36', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 37, 'Stephen', 'Kossowski', 'Washington', '1950-08-05', '2004-11-19', 'lightblue', '', '3.bmp', 'b.doc', '288.92', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 38, 'Ann', 'Smith', 'London', '1951-09-11', '2004-11-19', 'lightgreen', 'This is a very long description of person simply t', '3.bmp', 'b.doc', '245.43', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 39, 'Jackie', 'Malone', 'Pretoria', '1980-05-12', '2004-11-19', '#FFCCCC', 'Very short description', '3.bmp', 'b.doc', '1000.91', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 40, 'Andrew', 'Jones', 'Brisbane', '1990-11-27', '2004-11-19', 'lightyellow', 'This is a very long description of person simply t', '3.bmp', 'b.doc', '54.17', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 41, 'Jan', 'Nowak', 'Poznan', '1960-01-01', '2004-11-19', 'lightgrey', 'Very short description', '3.bmp', 'b.doc', '459.36', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 42, 'Stephen', 'Kossowski', 'Washington', '1950-08-05', '2004-11-19', 'lightblue', '', '3.bmp', 'b.doc', '288.92', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 43, 'Ann', 'Smith', 'London', '1951-09-11', '2004-11-19', 'lightgreen', 'This is a very long description of person simply t', '3.bmp', 'b.doc', '245.43', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 44, 'Jackie', 'Malone', 'Pretoria', '1980-05-12', '2004-11-19', '#FFCCCC', 'Very short description', '3.bmp', 'b.doc', '1000.91', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 45, 'Andrew', 'Jones', 'Brisbane', '1990-11-27', '2004-11-19', 'lightyellow', 'This is a very long description of person simply t', '3.bmp', 'b.doc', '54.17', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 46, 'Jan', 'Nowak', 'Poznan', '1960-01-01', '2004-11-19', 'lightgrey', 'Very short description', '3.bmp', 'b.doc', '459.36', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 47, 'Stephen', 'Kossowski', 'Washington', '1950-08-05', '2004-11-19', 'lightblue', '', '3.bmp', 'b.doc', '288.92', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 48, 'Ann', 'Smith', 'London', '1951-09-11', '2004-11-19', 'lightgreen', 'This is a very long description of person simply t', '3.bmp', 'b.doc', '245.43', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 49, 'Jackie', 'Malone', 'Pretoria', '1980-05-12', '2004-11-19', '#FFCCCC', 'Very short description', '3.bmp', 'b.doc', '1000.91', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 50, 'Andrew', 'Jones', 'Brisbane', '1990-11-27', '2004-11-19', 'lightyellow', 'This is a very long description of person simply t', '3.bmp', 'b.doc', '54.17', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 51, 'Jan', 'Nowak', 'Poznan', '1960-01-01', '2004-11-19', 'lightgrey', 'Very short description', '3.bmp', 'b.doc', '459.36', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 52, 'Stephen', 'Kossowski', 'Washington', '1950-08-05', '2004-11-19', 'lightblue', '', '3.bmp', 'b.doc', '288.92', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 53, 'Ann', 'Smith', 'London', '1951-09-11', '2004-11-19', 'lightgreen', 'This is a very long description of person simply t', '3.bmp', 'b.doc', '245.43', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 54, 'Jackie', 'Malone', 'Pretoria', '1980-05-12', '2004-11-19', '#FFCCCC', 'Very short description', '3.bmp', 'b.doc', '1000.91', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 55, 'Andrew', 'Jones', 'Brisbane', '1990-11-27', '2004-11-19', 'lightyellow', 'This is a very long description of person simply t', '3.bmp', 'b.doc', '54.17', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 56, 'Jan', 'Nowak', 'Poznan', '1960-01-01', '2004-11-19', 'lightgrey', 'Very short description', '3.bmp', 'b.doc', '459.36', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 57, 'Stephen', 'Kossowski', 'Washington', '1950-08-05', '2004-11-19', 'lightblue', '', '3.bmp', 'b.doc', '288.92', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 58, 'Ann', 'Smith', 'London', '1951-09-11', '2004-11-19', 'lightgreen', 'This is a very long description of person simply t', '3.bmp', 'b.doc', '245.43', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 59, 'Jackie', 'Malone', 'Pretoria', '1980-05-12', '2004-11-19', '#FFCCCC', 'Very short description', '3.bmp', 'b.doc', '1000.91', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 60, 'Andrew', 'Jones', 'Brisbane', '1990-11-27', '2004-11-19', 'lightyellow', 'This is a very long description of person simply t', '3.bmp', 'b.doc', '54.17', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 61, 'Jan', 'Nowak', 'Poznan', '1960-01-01', '2004-11-19', 'lightgrey', 'Very short description', '3.bmp', 'b.doc', '459.36', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 62, 'Stephen', 'Kossowski', 'Washington', '1950-08-05', '2004-11-19', 'lightblue', '', '3.bmp', 'b.doc', '288.92', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 63, 'Ann', 'Smith', 'London', '1951-09-11', '2004-11-19', 'lightgreen', 'This is a very long description of person simply t', '3.bmp', 'b.doc', '245.43', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        $this->addData ($db, 64, 'Jackie', 'Malone', 'Pretoria', '1980-05-12', '2004-11-19', '#FFCCCC', 'Very short description', '3.bmp', 'b.doc', '1000.91', 'emailname@emailhost.pl', 'http://www.ownwwwsite.pl/');
        //copy specified images to specified location
        $copyFrom = SGL_MOD_DIR . '/tools/files/3.bmp';
        $copyTo   = SGL_UPLOAD_DIR . '/3.bmp';
        if (!copy($copyFrom, $copyTo)) {
            $output->outputText = 'There was a problem while copying file<br>';
            return 0;
        }
        //copy specified document files to specified location
        $copyFrom = SGL_MOD_DIR . '/tools/files/b.doc';
        $copyTo   = SGL_UPLOAD_DIR . '/b.doc';
        if (!copy($copyFrom, $copyTo)) {
            $output->outputText = 'There was a problem while copying file<br>';
            return 0;
        }
        $output->outputText = 'OK :-)';
    }


        /**
         * avaiable column types for dataGrid:
         *
         * id            | Checkbox
         * text          | Tekst
         * html          | Tekst HTML
         * user          | Kolor definiowany
         * colour        | Kolor
         * integer       | Liczba calkowita
         * real          | Liczba rzeczywista
         * date          | Data
         * hour          | Godzina
         * image         | Grafika
         * thumbnail     | Ikonka
         * enclosure     | Zalacznik
         * mail          | E-mail
         * link          | Odnosnik
         * radio         | Radiobutton
         * action        | Avaiable actions for rows
         */

        /** DataGrid::addColumn($type, $name, $dbName,
         *                    $sortable   = true|false, $filterable   = true|false,
         *                    $sumable    = true|false, $sumTotalable = true|false)
         */


    /**
     * DataGridMgr::_dataGrid1()
     * Show functionality of DataGrid and DataSource class
     * One simple dataGrid
     * @param object $input
     * @param object $output
     * @access private
     * @return void
     **/
    function _dataGrid1(&$input, &$output) {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'dataGridMgr.html'; //template for all dataGrids

        require_once SGL_LIB_DIR . '/SGL/DataGrid.php';
        require_once SGL_LIB_DIR . '/SGL/DataGrid/DataGridDataSource.php';

        //create new dataGrid
        $dataGrid = & new SGL_DataGrid();
        //add columns to dataGrid
        $col = &$dataGrid->addColumn(array ('type'=>'text', 'name'=>'name', 'dbName'=>'name', 'sortable'=>true, 'filterable'=>true));
        $col->setTransformInColumn(array('Adamus' => 'ad'));
        $col2 = &$dataGrid->addColumn(array('type'=>'text', 'name'=>'surname', 'dbName'=>'surname', 'sortable'=>true, 'filterable'=>true));
        $col2->setTransformInColumn(array('Mielcarus' => 'mtest'));
        $dataGrid->addColumn(array('type'=>'text', 'name'=>'city','dbName'=>'city', 'sortable'=>true, 'filterable'=>true));
        //create instance of data source
        $dataSource = & new SGL_DataGridDataSource();
        //add manually data to source
        $dataSource->addRow(array
            ('name' => 'Adamus', 'surname' => 'Mielcarus', 'city' => 'Olimp'));
        $dataSource->addRow(array
            ('name' => 'Grzegorzus', 'surname' => 'Dabrawus', 'city' => 'Sparta'));
        $dataSource->addRow(array
            ('name' => 'Robertus', 'surname' => 'Borkowus', 'city' => 'Ateny'));
        $dataSource->addRow(array
            ('name' => 'Piotrus', 'surname' => 'Skrzypus', 'city' => 'Troja'));
        $dataSource->addRow(array
            ('name' => 'Tomaszus', 'surname' => 'Przybyszus', 'city' => 'Saloniki'));
        //get all data, fill prepared data to dataGrid and display the dataGrid with data
        $dataGrid->validate($input->inputReq);
        $dataGrid->setDataSource($dataSource);
        $dataGrid->display($output);
    }

    /**
     * DataGridMgr::_dataGrid2()
     * Show functionality of DataGrid and SQLDataSource class
     * One dataGrid with enclosures and images, with summed columns and pageing opton
     * @param object $input
     * @param object $output
     * @access private
     * @return void
     **/
    function _dataGrid2(&$input, &$output) {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'dataGridMgr.html';

        require_once SGL_LIB_DIR . '/SGL/DataGrid.php';
        require_once SGL_LIB_DIR . '/SGL/DataGrid/DataGridSQLDataSource.php';

        $dataGrid = & new SGL_DataGrid();
        $dataGrid->addColumn(array('type'=>'id', 'name'=>'', 'dbName'=>'id'));
        $dataGrid->addColumn(array('type'=>'text', 'name'=>'name', 'dbName'=>'name', 'sortable'=>true, 'filterable'=>true));
        $dataGrid->addColumn(array('type'=>'text', 'name'=>'description', 'dbName'=>'description', 'sortable'=>true, 'filterable'=>true));
        $dataGrid->addColumn(array('type'=>'real', 'name'=>'salary', 'dbName'=>'salary', 'sortable'=>true, 'filterable'=>true, 'sumable'=>true, 'sumTotalable'=>true));
        $dataGrid->addColumn(array('type'=>'enclosure', 'name'=>'file', 'dbName'=>'fileattached'));
        $dataGrid->addColumn(array('type'=>'image', 'name'=>'picture', 'dbName'=>'picture'));
        $dataSource = & new SGL_DataGridSQLDataSource("
            SELECT id, name, description, salary, fileattached, picture
            FROM people WHERE #_FILTER#
        ", 'id');
        $dataGrid->validate($input->inputReq);
        $dataGrid->setDataSource($dataSource);
        $dataGrid->display($output);
    }

    /**
     * DataGridMgr::_dataGrid3()
     * Show functionality of DataGrid and SQLDataSource class
     * One dataGrid with lniks and column and rows coulored options
     * @param object $input
     * @param object $output
     * @access private
     * @return void
     **/
    function _dataGrid3(&$input, &$output) {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'dataGridMgr.html';

        require_once SGL_LIB_DIR . '/SGL/DataGrid.php';
        require_once SGL_LIB_DIR . '/SGL/DataGrid/DataGridSQLDataSource.php';

        $dataGrid = & new SGL_DataGrid();
        $dataGrid->addColumn(array('type'=>'user', 'name'=>'name', 'dbName'=>'name', 'sortable'=>true, 'filterable'=>true));
        $dataGrid->addColumn(array('type'=>'colour', 'name'=>'colour column', 'dbName'=>'colour', 'sortable'=>true, 'filterable'=>true));
        $dataGrid->addColumn(array('type'=>'email', 'name'=>'email', 'dbName'=>'email', 'sortable'=>true, 'filterable'=>true));
        $dataGrid->addColumn(array('type'=>'link', 'name'=>'www','dbName'=>'www', 'sortable'=>true, 'filterable'=>true));
        $dataSource = & new SGL_DataGridSQLDataSource("
            SELECT name, birthdate, colour, email, www
            FROM people WHERE #_FILTER#
            ORDER BY name
        ", 'id');
        $dataGrid->validate($input->inputReq);
        $dataGrid->setDataSource($dataSource);
        $dataGrid->display($output);
    }

    /**
     * DataGridMgr::_dataGrid4()
     * Show functionality of DataGrid, DataSource and SQLDataSource class
     * Two dataGrid with different source data on one page with all kinds of filter types
     * @param object $input
     * @param object $output
     * @access private
     * @return void
     **/
    function _dataGrid4(&$input, &$output) {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'dataGridMgr.html';

        require_once SGL_LIB_DIR . '/SGL/DataGrid.php';
        require_once SGL_LIB_DIR . '/SGL/DataGrid/DataGridSQLDataSource.php';

        //first dataGrid - SQLDataSource
        $dataGrid = & new SGL_DataGrid();
        $dataGrid->addColumn(array('type'=>'id', 'name'=>'', 'dbName'=>'id'));
        $col = &$dataGrid->addColumn(array('type'=>'text', 'name'=>'name', 'dbName'=>'name', 'sortable'=>true, 'filterable'=>true));
        $col->setFilterSelect(array('Jan', 'Andrew', 'Stephen'));
        $dataGrid->addColumn(array('type'=>'text', 'name'=>'surname', 'dbName'=>'surname', 'sortable'=>true, 'filterable'=>true));
        $dataGrid->addColumn(array('type'=>'date', 'name'=>'birth date', 'dbName'=>'birthdate', 'sortable'=>true, 'filterable'=>true));
        $dataGrid->addColumn(array('type'=>'real', 'name'=>'salary', 'dbName'=>'salary', 'sortable'=>true, 'filterable'=>true, 'sumable'=>true, 'sumTotalable'=>true));
        $dataSource = & new SGL_DataGridSQLDataSource("
            SELECT id, name, surname, birthdate, salary
            FROM people WHERE #_FILTER#
            ORDER BY id
        ", 'id');
        $dataGrid->validate($input->inputReq);
        $dataGrid->setDataSource($dataSource);
        $dataGrid->display($output);

        //second dataGrid - DataSource
        $dataGrid2 = & new SGL_DataGrid('2');
        $dataGrid2->addColumn(array('type'=>'id', 'name'=>'', 'dbName'=>'id'));
        $dataGrid2->addColumn(array('type'=>'text', 'name'=>'name', 'dbName'=>'name', 'sortable'=>true, 'filterable'=>true));
        $dataGrid2->addColumn(array('type'=>'date', 'name'=>'birth date', 'dbName'=>'date', 'sortable'=>true, 'filterable'=>true));
        $dataGrid2->addColumn(array('type'=>'real', 'name'=>'salary', 'dbName'=>'salary', 'sortable'=>true, 'filterable'=>true, 'sumable'=>true, 'sumTotalable'=>true));
        $dataSource2 = & new SGL_DataGridDataSource();
        $dataSource2->addRow(array
            ('id' => 12, 'name' => 'dddd', 'date' => '2005-12-09', 'salary' => 1200.3));
        $dataSource2->addRow(array
            ('id' => 9, 'name' => 'aaaa', 'date' => '2003-04-01', 'salary' => 12.5));
        $dataSource2->addRow(array
            ('id' => 11, 'name' => 'cccc', 'date' => '2000-08-30', 'salary' => 206.54));
        $dataSource2->addRow(array
            ('id' => 10, 'name' => 'bbbb', 'date' => '2001-01-01', 'salary' => 999.9));
        $dataSource2->addRow(array
            ('id' => 13, 'name' => 'bbb', 'date' => '1999-07-29', 'salary' => 1.8));
        $dataSource2->addRow(array
            ('id' => 14, 'name' => 'bb', 'date' => '1900-05-00', 'salary' => 1000.789));
        $dataGrid2->validate($input->inputReq);
        $dataGrid2->setDataSource($dataSource2);
        $dataGrid2->display($output);
    }

    /**
     * DataGridMgr::_dataGrid5()
     * Show functionality of DataGrid, DataSource and SQLDataSource class
     * Two dataGrids on one page with action column
     * @param object $input
     * @param object $output
     * @access private
     * @return void
     **/
    function _dataGrid5(&$input, &$output) {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'dataGridMgr.html';

        require_once SGL_LIB_DIR . '/SGL/DataGrid.php';
        require_once SGL_LIB_DIR . '/SGL/DataGrid/DataGridSQLDataSource.php';

        //first dataGrid - SQLDataSource
        $dataGrid = & new SGL_DataGrid();
        $dataGrid->addColumn(array('type'=>'id', 'dbName'=>'id'));
        $dataGrid->addColumn(array('type'=>'user', 'name'=>'name', 'dbName'=>'name', 'sortable'=>true, 'filterable'=>true));
        $dataGrid->addColumn(array('type'=>'user', 'name'=>'surname', 'dbName'=>'surname', 'sortable'=>true, 'filterable'=>true));
        $dataGrid->addColumn(array('type'=>'colour', 'name'=>'colour', 'dbName'=>'colour', 'sortable'=>true, 'filterable'=>true));
        $dataGrid->addColumn(array('type'=>'real', 'name'=>'salary', 'dbName'=>'salary', 'sortable'=>true, 'filterable'=>true, 'sumable'=>true, 'sumTotalable'=>true));
        $col = &$dataGrid->addColumn(array('type'=>'action'));
        $col->addAction(array(
                              'name' => 'Edit',
                              'url' => SGL_Url::makeLink('example/id/{id}/action/edit/name/{name}','dataGrid','test'),
                              ));
        $col->addAction(array(
                              'name' => 'Delete', 
                              'url' => SGL_Url::makeLink('example/id/{id}/action/delete/','dataGrid','test'),
                              ));
        $dataSource = & new SGL_DataGridSQLDataSource("
            SELECT id, name, surname, colour, salary
            FROM people WHERE #_FILTER#
            ORDER BY id", 'id');
        $dataGrid->validate($input->inputReq);
        $dataGrid->setDataSource($dataSource);
        $dataGrid->display($output); 

        //second dataGrid - DataSource
        $dataGrid2 = & new SGL_DataGrid('2');
        $dataGrid2->addColumn(array('type'=>'id', 'dbName'=>'id'));
        $dataGrid2->addColumn(array('type'=>'user', 'name'=>'name', 'dbName'=>'name', 'sortable'=>true, 'filterable'=>true));
        $dataGrid2->addColumn(array('type'=>'colour', 'name'=>'colour', 'dbName'=>'colour', 'sortable'=>true, 'filterable'=>true));
        $dataGrid2->addColumn(array('type'=>'real', 'name'=>'salary', 'dbName'=>'salary', 'sortable'=>true, 'filterable'=>true, 'sumable'=>true, 'sumTotalable'=>true));
        $col2 = &$dataGrid2->addColumn(array('type'=>'action', 'name'=>'Actions'));
        $col2->addAction(array(
                            'name' => 'Edit',
                            'url' => SGL_Url::makeLink('example/id/{id}/action/edit/name/{name}','dataGrid','test')));
        $col2->addAction(array(
                             'name' => 'Delete',
                             'url' => SGL_Url::makeLink('example/id/{id}/action/delete/','dataGrid','test')));
        $dataSource2 = & new SGL_DataGridDataSource();
        $dataSource2->addRow(array
            ('id' => '12', 'name' => 'dddd', 'colour' => 'yellow', 'salary' => 12.56));
        $dataSource2->addRow(array
            ('id' => '9', 'name' => 'aaaa', 'colour' => 'red', 'salary' => 09.5));
        $dataSource2->addRow(array
            ('id' => '11', 'name' => 'cccc', 'colour' => 'green', 'salary' => 129.6));
        $dataSource2->addRow(array
            ('id' => '10', 'name' => 'bbbb', 'colour' => 'blue', 'salary' => 46.95));
        $dataSource2->addRow(array
            ('id' => '13', 'name' => 'bbb', 'colour' => 'red', 'salary' => 233.11));
        $dataSource2->addRow(array
            ('id' => '14', 'name' => 'bb', 'colour' => 'blue', 'salary' => 4.5));
        $dataGrid2->validate($input->inputReq);
        $dataGrid2->setDataSource($dataSource2);
        $dataGrid2->display($output);
    }

}

?>