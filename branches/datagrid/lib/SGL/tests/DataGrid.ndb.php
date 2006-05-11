<?php
require_once dirname(__FILE__) . '/../Output.php';
require_once dirname(__FILE__) . '/../Request.php';
require_once dirname(__FILE__) . '/../DataGrid.php';
require_once dirname(__FILE__) . '/../Column.php';
require_once dirname(__FILE__) . '/../SQLDataSource.php';

/**
 * Test suite.
 *
 * @package SGL
 * @author  Varico
 */

class DataGridTest extends UnitTestCase {

    function DataGridTest()
    {
        $this->UnitTestCase('DataGrid Test');
    }

    function testAddColumn()
    {
        $dataGrid = & new SGL_DataGrid('test');
        $dataGrid->addColumn(array(
                                    'type' => 'text', 
                                    'name' => 'Test', 
                                    'dbName' => 'test'
                                   ));
        $this->assertNotNull($dataGrid->columns[0]);
    }

    function testColumnAddAction()
    {
        $column = & new SGL_DataGridColumn('text', 'name', 'name');

        $column->addAction(array(
                                'name' => 'Name',
                                'url' => 'www.test.com'
                                ));
        $this->assertTrue(isset($column->actionData[0]->img));
        $this->assertTrue($column->actionData[0]->url === 'www.test.com');
    }

    function testFilterValidateWithoutSession()
    {
        $moduleName = 'default';
        $managerName = 'default';
        $action = '';
        $dataGridID = 'test';
        $dataGridName = 'test';

        $dataGrid = & new SGL_DataGrid($dataGridID, $dataGridName);
        $column = &$dataGrid->addColumn(array(
                                    'type' => 'text', 
                                    'name' => 'Test', 
                                    'dbName' => 'test'
                                   ));
        $inReq = & new SGL_Request();
        $inReq->aProps = array (
                            'moduleName' => $moduleName,
                            'managerName' => $managerName,
                            'action' => $action,
                            'testtest' => 'filter test'
                       );
        $dataGrid->filterValidate($column, $inReq, false);
        $this->assertEqual($dataGrid->filters['test'], 'filter test');

        $filterArray = SGL_Session::get('filterArray');
        $this->assertEqual($filterArray[$moduleName . '_' . $managerName . '_' . 
                                       $action . '_' . $dataGridID .
                                       '_' . $dataGridName . '_' . 
                                       $column->dbName], 'filter test');
    }

    function testFilterValidateWithSession()
    {
        $moduleName = 'default';
        $managerName = 'default';
        $action = '';
        $dataGridID = 'test';
        $dataGridName = 'test';

        $dataGrid = & new SGL_DataGrid($dataGridID, $dataGridName);
        $column = &$dataGrid->addColumn(array(
                                    'type' => 'text', 
                                    'name' => 'Test', 
                                    'dbName' => 'test'
                                   ));
        $inReq = & new SGL_Request();
        $inReq->aProps = array (
                            'moduleName' => $moduleName,
                            'managerName' => $managerName,
                            'action' => $action,
                            'testtest' => 'another filter test'
                       );

        $dataGrid->filterValidate($column, $inReq, true);
        $this->assertEqual($dataGrid->filters['test'], 'filter test');

        $filterArray = SGL_Session::get('filterArray');
        $this->assertEqual($filterArray[$moduleName . '_' . $managerName . '_' . 
                                       $action . '_' . $dataGridID .
                                       '_' . $dataGridName . '_' . 
                                       $column->dbName], 'filter test');
    }

    function testFilterValidateWrongDateField()
    {
        $moduleName = 'default';
        $managerName = 'default';
        $action = '';
        $dataGridID = 'test';
        $dataGridName = 'test';

        $dataGrid = & new SGL_DataGrid($dataGridID, $dataGridName);
        $column = &$dataGrid->addColumn(array(
                                    'type' => 'date', 
                                    'name' => 'Test', 
                                    'dbName' => 'test'
                                   ));
        $inReq = & new SGL_Request();
        $inReq->aProps = array (
                            'moduleName' => $moduleName,
                            'managerName' => $managerName,
                            'action' => $action,
                            'testtest__from__' => '2005',
                            'testtest__to__' => ''
                       );
        $dataGrid->filterValidate($column, $inReq, false);
        $this->assertEqual($column->cError, 
                    "<span class='error'>>incorrect date format< !!</span>");

    }

    function testSortValidate()
    {
        $moduleName = 'default';
        $managerName = 'default';
        $action = '';
        $dataGridID = 'test';
        $dataGridName = 'test';

        $dataGrid = & new SGL_DataGrid($dataGridID, $dataGridName);
        $column = &$dataGrid->addColumn(array(
                                    'type' => 'text', 
                                    'name' => 'Test', 
                                    'dbName' => 'test'
                                   ));
        $inReq = & new SGL_Request();
        $inReq->aProps = array (
                            'moduleName' => $moduleName,
                            'managerName' => $managerName,
                            'action' => $action,
                            'testsort_test' => 'ASC'
                       );

        $dataGrid->sortValidate($column, $inReq);
        $this->assertEqual($dataGrid->sorts['test'], 'ASC');

        $sortArray = SGL_Session::get('sortArray');
        $sortColumn = $sortArray[$moduleName . '_' . $managerName . '_' . 
                                 $action . '_' . $dataGridID .
                                 '_' . $dataGridName];
        $this->assertTrue(($sortColumn['column'] == $column->dbName) && 
                            ($sortColumn['direction'] == 'ASC'));
    }

    function testDataSourceSetSort()
    {
        $dataSource = & new SGL_DataGridDataSource();
        $dataSource->addRow(array(
                                    'id' => '1',
                                    'name' => 'zz'
                                  ));
        $dataSource->addRow(array(
                                    'id' => '2',
                                    'name' => 'aa'
                                  ));
        $dataSource->addRow(array(
                                    'id' => '3',
                                    'name' => 'bb'
                                  ));
        $sortArray = array(
                            'name' => 'ASC'
                            );

        $dataSource->setSort($sortArray);
        $this->assertTrue(($dataSource->_itemsData[0]['name'] == 'aa') &&
                          ($dataSource->_itemsData[1]['name'] == 'bb') &&
                          ($dataSource->_itemsData[2]['name'] == 'zz'));
    }

    function testDataSourceSetFilter()
    {
        $dataSource = & new SGL_DataGridDataSource();
        $dataSource->addRow(array(
                                    'id' => '1',
                                    'name' => 'aa1'
                                  ));
        $dataSource->addRow(array(
                                    'id' => '3',
                                    'name' => 'bb'
                                  ));
        $dataSource->addRow(array(
                                    'id' => '2',
                                    'name' => 'aa2'
                                  ));
        $filterArray = array('name' => 'aa');

        $dataSource->setFilter($filterArray);
        $this->assertTrue(($dataSource->_itemsData[0]['name'] == 'aa1') &&
                          ($dataSource->_itemsData[1]['name'] == 'aa2') &&
                          ($dataSource->getNumberOfAllRows() == 2));
    }

    function testSQLDataSourceSetSort()
    {
        $query = 'SELECT id, name, city FROM test';
        $dataSource = & new SGL_DataGridSQLDataSource($query, 'id');
        $sortArray = array(
                            'name' => 'ASC'
                           );
        $dataSource->setSort($sortArray);
        $this->assertEqual($dataSource->sortString, 'name ASC, id');
    }

    function testSQLDataSourceSetFilter()
    {
        $query = 'SELECT id, name, city, date FROM test WHERE #_FILTER#';
        $dataSource = & new SGL_DataGridSQLDataSource($query, 'id');
        $filterArray = array(
                            'name' => 'test',
                            'date__from__' => '2000-01-01' 
                           );
        $dataSource->setFilter($filterArray);
        $this->assertEqual($dataSource->prepareFiltersArray[0], '%test%');
        $this->assertEqual($dataSource->prepareFiltersArray[1], '2000-01-01');
        $this->assertEqual($dataSource->automaticFilter, 'name like ? and date >= ?');
    }

    function testSQLDataSourceParseFilter()
    {
        $columns = array(
                        'address' => '(SELECT city || street || house)'
                         );

        $query = 'SELECT (SELECT city || street || house) AS address 
                  FROM test WHERE #_FILTER#';
        $setFilter = 'address ilike ?';
        $dataSource = & new SGL_DataGridSQLDataSource($query);
        $changeFilter = $dataSource->parseFilter($setFilter, $columns);

        $this->assertEqual($changeFilter[0]['from'], '0');
        $this->assertEqual($changeFilter[0]['len'], '7');
        $this->assertEqual($changeFilter[0]['text'], '(SELECT city || street || house)');
    }

    function testSQLDataSourceModifySelectQuery()
    {
        $query = 'SELECT id, name, (SELECT city || street || house) AS address FROM test WHERE #_FILTER#';
        $setFilter = 'address ilike ?';
        $setOrderBy = 'address ASC, id';
        $dataSource = & new SGL_DataGridSQLDataSource($query, 'id');
        $changeQuery = $dataSource->modifySelectQuery($query, '', $setFilter, $setOrderBy);
        $changeQuery = str_replace(' ', '', $changeQuery);
        $this->assertEqual(trim($changeQuery), 
                'SELECTid,name,(SELECTcity||street||house)ASaddressFROMtestWHERE(SELECTcity||street||house)ilike?ORDERBYaddressASC,id');
    }

    function testFormatDate2DB()
    {
        $this->assertNotNull(SGL_Output::formatDate2DB('2005-01-01'));
        $this->assertNull(SGL_Output::formatDate2DB('2005.01.01'));
        $this->assertNotNull(SGL_Output::formatDate2DB('01.01.2005'));
        $this->assertNull(SGL_Output::formatDate2DB('01.01.05'));
    }
}


?>