<?php
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//Varico ...one stop source for software
//http://www.varico.com ... since 1989
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

include 'DataSource.php';

/**
 * SGL_DataGridSQLDataSource.
 *
 * For selecting and preparing selected from database data
 *
 * @package SGL
 * @author Varico
 * @version $Id
 * @access public
 **/
class SGL_DataGridSQLDataSource extends SGL_DataGridDataSource {

    var $dataGridQuery = "";
    var $prepareFiltersArray = array();
    var $automaticFilter = '';
    var $sortString = '';
    var $primaryKey;

    /**
     * SGL_DataGridSQLDataSource::SGL_DataGridSQLDataSource()
     * Initialize SGL_DataGridSQLDataSource object
     * @param string $query - SQL query for database
     * @access public
     * @return void
     **/
    function SGL_DataGridSQLDataSource($query, $primaryKey = 'u_id')
    {
        $this->primaryKey = $primaryKey;
        $this->dataGridQuery = $query;
    }

    /**
     * SGL_DataGridSQLDataSource::setSort()
     * Set sort key and sort order in SQL query
     * @param array $sortsArray - given from dataGrid with sort values for each sortable column
     * @access public
     * @return void
     **/
    function setSort($sortsArray)
    {
        $this->sortByElement = "";
        $this->sortOrder = "";
        //find sort and sort order element
        foreach($sortsArray as $key => $sort) {
            if ($sort != '') {
                $this->sortOrder = $sort;
                $this->sortByElement = $key;
                break;
            }
        }

        $this->sortString = '';

        if ($this->sortByElement != '') {
            $this->sortString = $this->sortByElement . ' ' . $this->sortOrder . ', ';
        }

        $this->sortString .= $this->primaryKey;
    }

    /**
     * SGL_DataGridSQLDataSource::setFilter()
     * Set prepareFiltersArray for prepare function for SQL query
     * @param array $filtersArray - given from dataGrid with filter
                          values for each filterable column
     * @access public
     * @return void
     **/
    function setFilter($filtersArray)
    {
        $this->automaticFilter = '';
        $prepareAutomaticFilterArray = array();
        //find all given filters in SQL query statement
        foreach($filtersArray as $key => $filter) {
            if ($filter != '') {
                if ($this->automaticFilter != '') {
                    $this->automaticFilter .= ' and ';
                }

                //for "_from" and "_to" date filters
                if ($pos = strpos($key, '__from__')) {
                    $this->automaticFilter .= substr($key, 0, $pos) . ' >= ?';
                    $prepareAutomaticFilterArray[] = $filter;
                    //$positionsArray[$pos] = $filter;
                } elseif ($pos = strpos($key, '__to__')) {
                    $this->automaticFilter .= substr($key, 0, $pos) . ' <= ?';
                    $prepareAutomaticFilterArray[] = $filter;
                } else {
                    if ($GLOBALS['_SGL']['CONF']['db']['type'] == 'pgsql') {
                        //AM in postgres like is case sensitive (default),
                        // and ilike is insensitive
                        $this->automaticFilter .= $key . ' ilike ?';
                    } else {
                        $this->automaticFilter .= $key . ' like ?';
                    }

                    $prepareAutomaticFilterArray[] = '%' . $filter . '%';
                }
            }
        }

        if (isset($positionsArray)) {
            //sort filters by theirs position in SQL query statement
            ksort($positionsArray);

            //prepare filters array for prepare()/execute() query methods
            foreach($positionsArray as $position => $filter) {
                $this->prepareFiltersArray[] = $filter;
            }
        }

        $this->prepareFiltersArray += $prepareAutomaticFilterArray;
        $pos = strpos($this->dataGridQuery, '#_FILTER#');
        if ($pos) {
            $tempArray = $this->prepareFiltersArray;
            while ($pos = strpos($this->dataGridQuery, '#_FILTER#', $pos + 1)) {
                foreach ($this->prepareFiltersArray as $value) {
                    $tempArray[] = $value;
                }
            }
            $this->prepareFiltersArray = $tempArray;
        }
    }

    /**
     * SGL_DataGridSQLDataSource::getPageLinks()
     * Get links for current page
     * @access public
     * @return actual page links
     **/
    function getPageLinks()
    {
        return parent::getPageLinks();
    }

    /**
     * SGL_DataGridSQLDataSource::getNumberOfPages()
     * Get number of total pages
     * @access public
     * @return actual number of pages
     **/
    function getNumberOfPages()
    {
        return parent::getNumberOfPages();
    }

    /**
     * SGL_DataGridSQLDataSource::getNumberOfRows()
     * Get number of all rows i data collection
     * @access public
     * @return total number of rows
     **/
    function getNumberOfAllRows()
    {
        $dbh = & SGL_DB::singleton();
        $countQuery = SGL_DataGridSQLDataSource::modifySelectQuerySetCount($this->dataGridQuery);
        $res = &$dbh->query($countQuery);
        $totalItems = 0;
        while ($res->fetchInto($row)) {
            $totalItems = $totalItems + $row->count;
        }
        return $totalItems;
    }

    /**
     * SGL_DataGridSQLDataSource::getSummarysOfPage()
     * Get page sum for each sumable column
     * @param array $columnsArray
     * @access public
     * @return summary of actual page for $columnsArray
     **/
    function getSummarysOfPage($columnsArray)
    {
        return parent::getSummarysOfPage($columnsArray);
    }

    /**
     * SGL_DataGridSQLDataSource::getSummarysTotal()
     * Get total sum for each sumable column
     * @param array $columnsArray
     * @access public
     * @return total summary for $columnsArray
     **/

    function getSummarysTotal($columnsArray)
    {
        $summarys = array();
        $dbh = & SGL_DB::singleton();
        //get sum for every sumTotalable column by send SELECT SUM()... query to database
        foreach ($columnsArray as $keyArray => $column) {
            if ($column->sumTot) {
                if (!(is_array($column->dbName))) {
                    $tempQuery = "SELECT SUM($column->dbName) FROM (" .
                                    SGL_DataGridSQLDataSource::modifySelectQuery(
                                        $this->dataGridQuery, "", $this->automaticFilter,
                                        $this->sortString) .
                                    ') as tmp';
                    $summaryTotal = $dbh->getOne($tempQuery, $this->prepareFiltersArray);
                    $summarys[$column->dbName] = number_format($summaryTotal, 2, '.', '');
                }
            }

            if ($column->avgTot) {
                if (!(is_array($column->dbName))) {
                    $tempQuery = "SELECT AVG($column->dbName) FROM (" .
                                    SGL_DataGridSQLDataSource::modifySelectQuery(
                                        $this->dataGridQuery, "", $this->automaticFilter,
                                        $this->sortString) .
                                    ') as tmp';
                    $summaryTotal = $dbh->getOne($tempQuery, $this->prepareFiltersArray);
                    $summarys[$column->dbName] = number_format($summaryTotal, 2, '.', '');
                }
            }
        }

        return $summarys;
    }

    /**
     * SGL_DataGridSQLDataSource::getActualPage()
     * Get page to display in dataGrid with given id in row
     * @param numeric $setIDLoc
     * @param numeric $perPageLoc
     * @param array $columnsArray
     * @access public
     * @return page to display
     **/
    function getActualPage($setIDLoc, $perPageLoc, $columnsArray)
    {
        $idSortElement = "";
        //search for column with "id" type
        foreach ($columnsArray as $keyColumn => $column) {
            if ($column->type == 'id') {
                $idSortElement = $column->dbName;
                break;
            }
        }

        //when colum with "id" type exist
        if (!isset($_GET['pageID_0']) && ($setIDLoc != "") && (is_numeric($setIDLoc))
                && ($idSortElement != "")) {
            $dbh = & SGL_DB::singleton();
            $query = SGL_DataGridSQLDataSource::modifySelectQuery(
                                   $this->dataGridQuery, '', $this->automaticFilter,
                                   $this->sortString);
            //count all rows from database
            $tempQuery = SGL_DataGridSQLDataSource::modifySelectQuerySetCount(
                                            $this->dataGridQuery, $this->automaticFilter,
                                            $this->sortString);
            $rowCount = $dbh->getOne($tempQuery, $this->prepareFiltersArray);

            if ($this->sortByElement == '') {
                $tempDBName = $this->primaryKey;
                $tempDBValue = $setIDLoc;
            } else {
                $tempDBName = $this->sortByElement;

                $tempQuery = SGL_DataGridSQLDataSource::modifySelectQuery(
                                            $this->dataGridQuery, '', $this->primaryKey .
                                            ' = ' . $setIDLoc, $this->sortString);
                $findDBValues = $dbh->getRow($tempQuery);
                $tempDBValue = $findDBValues->$tempDBName;

            }

            $limitFound = false;
            $count = $rowCount/2; //first bisection
            $offset = $count;
            $findElement = "";

            //main loop for bisection algorithm to find offset for given id
            $valueFound = false;
            if ($count>1) {
                while ((!$limitFound) && $count >= 0.5) {
                    $count = $count / 2;
                    $tempFindingQuery = $dbh->modifyLimitQuery($tempQuery, round($offset), 1);

                    $findDBValues = $dbh->getRow($tempFindingQuery, $this->prepareFiltersArray);

                    if (is_null($findDBValues->$tempDBName)) {
                        $findDBValues->$tempDBName = '';
                    }

                    if (is_null($tempDBValue)) {
                        $tempDBValue = '';
                    }

                    $cmpResult = $dbh->getOne('select CASE WHEN ? = ? THEN 0 ELSE -1 END',
                                            array($findDBValues->$tempDBName, $tempDBValue));
                    if ($tempDBName == $idSortElement) {
                        //compare actual selected from database order by column value

                        if ($findDBValues->$tempDBName == $tempDBValue) {
                            $findElement = $findDBValues->$tempDBName;
                            $valueFound = true;
                        //increase or decrease offset if value wasn't found
                        } elseif ($cmpResult == -1) {
                            $offset += $count;
                        } else {
                            $offset -= $count;
                        }
                    } else {
                        //if there are two order by columns, and the second is "id" type
                        if ($findDBValues->$tempDBName == $tempDBValue) {
                            //if there's more then one row with the same order by column value
                            //find that with given id
                            if ($findDBValues->$idSortElement == $setIDLoc) {
                                $findElement = $findDBValues->$tempDBName;
                                $valueFound = true;
                            //increase or decrease offset if value wasn't found
                            } elseif  ($findDBValues->$idSortElement < $setIDLoc) {
                                $offset += $count;
                            } else {
                                $offset -= $count;
                            }
                        //increase or decrease offset if value wasn't found
                        } elseif ($cmpResult == -1) {
                            $offset += $count;
                        } else {
                            $offset -= $count;
                        }
                    }
                }
            }

            if (($valueFound) && ($perPageLoc >= 1)) {
                return ceil((round($offset) + 1)/$perPageLoc);
            }
        }

        return "";
    }

    /**
     * SGL_DataGridSQLDataSource::fill()
     * Fill current page with data in dataGrid
     * @param string $pageID - dataGrid id to different pages for more than
                                one dataGrid in form
     * @param numeric $perPage - indicate howmany rows can be displayed on single page
     * @param numeric $actualPage - if given set page to display in dataGrid
     * @access public
     * @return actual data for dataGrid
     **/
    function fill($pageID, $perPage, $actualPage = "")
    {
        if ($actualPage != "") {
            $_GET['pageID_' . $pageID] = $actualPage;
            $_REQUEST['pageID_' . $pageID] = $actualPage;
        }
        $dbh = & SGL_DB::singleton();
        $pagerOptions = array(
            'mode'      => 'Sliding',
            'delta'     => SGL_DATAGRID_DELTA_LINKS_PAGE,
            'perPage'   => $perPage,
            'urlVar'    => 'pageID_' . $pageID,
            'itemData'  => $this->_itemsData
        );
        $query = SGL_DataGridSQLDataSource::modifySelectQuery(
                                $this->dataGridQuery, '', $this->automaticFilter,
                                $this->sortString);
        // query and page the slected data for current page
        $this->_pagedData = SGL_DB::getPagedData($dbh, $query, $pagerOptions,
                                                 false, DB_FETCHMODE_ASSOC,
                                                 $this->prepareFiltersArray);
        return $this->_pagedData['data'];
    }

    /**
     * SGL_DataGridSQLDataSource::exportPageToExcel()
     * Allow to export data from current page to Excel file
     * @param string $fileName
     * @access public
     * @return void
     **/
    function exportPageToExcel($fileName = 'dataGridPageXLS.xls')
    {
        parent::exportPageToExcel($fileName);
    }

    /**
     * SGL_DataGridSQLDataSource::exportTotalToExcel()
     * Allow to export all data queried from data base to Excel file
     * @param string $fileName
     * @access public
     * @return void
     **/
    function exportTotalToExcel($fileName = 'dataGridTotalXLS.xls')
    {
        $totalSQLData = array();
        $dbh = & SGL_DB::singleton();

        //select all data from database
        $query = SGL_DataGridSQLDataSource::modifySelectQuery(
                                    $this->dataGridQuery, '', $this->automaticFilter,
                                    $this->sortString);
        $res = & $dbh->query($query, $this->prepareFiltersArray);
        while ($res->fetchInto($row)) {
            $totalSQLData[] = $row;
        }

        //export all selected data by exportPageToExcell() method
        parent::exportPageToExcel($fileName, $totalSQLData);
    }

    /**
     * SGL_DataGridSQLDataSource::exportPageToWord()
     * Allow to export data from current page to Word file
     * @param string $fileName
     * @access public
     * @return void
     **/
    function exportPageToWord($fileName = 'dataGridPageDOC.doc')
    {
        parent::exportPageToWord($fileName);
    }

    /**
     * SGL_DataGridSQLDataSource::exportTotalToWord()
     * Allow to export all data queried from data base to Word file
     * @param string $fileName
     * @access public
     * @return void
     **/
    function exportTotalToWord($fileName = 'dataGridTotalDOC.doc')
    {
        $totalSQLData = array();
        $dbh = & SGL_DB::singleton();
        //select all data from database
        $query = SGL_DataGridSQLDataSource::modifySelectQuery(
                            $this->dataGridQuery, '', $this->automaticFilter,
                            $this->sortString);
        $res = & $dbh->query($query, $this->prepareFiltersArray);
        while ($res->fetchInto($row)) {
            $totalSQLData[] = $row;
        }

        //export all selected data by exportPageToWord() method
        parent::exportPageToWord($fileName, $totalSQLData);
    }

    /** Static function to parse and modify query and set select in
     * the query (useful for counts etc)
     * @param   string  $query query to parse and change
     * @param   string  $setSelect select to change
     * @param   string  $setFilter filter to add (it replaces #_FILTER# in query)
     * @param   string  $setOrderBy filter sort at the end
     * @return  string  query after change
     * @uses    PEAR/SQL_Parser module
     */
    function modifySelectQuery($query, $setSelect = '', $setFilter = '', $setOrderBy = '')
    {
        $selectQuery = array();
        $openBrackets = 0;
        $selectExpStart = 0;
        $selectExpEnd = 0;
        $selectStart = 0;
        $groupBy = false;
        $returnQuery = null;
        $columns = array();
        $columnStart = 0;
        $orderByStart = 0;
        include_once 'SQL/Parser.php';
        $sqlParser = & new SQL_Parser($query);
        do {
            $sqlParser->getTok();

            switch ($sqlParser->token) {
                case ',':
                    if ($openBrackets == 0 && $selectExpEnd == 0) {
                        $columnStart = $sqlParser->lexer->tokPtr;
                    }
                    break;
                case 'as':
                    if ($openBrackets == 0 && $selectExpEnd == 0 && isset($columnStart)) {
                        $columnText = substr($query, $columnStart,
                           $sqlParser->lexer->tokPtr - $columnStart - $sqlParser->lexer->tokLen);
                        unset($columnStart);
                    }
                    break;
                case 'ident':
                    if (isset($columnText)) {
                        $columns[$sqlParser->lexer->tokText] = $columnText;
                        unset($columnText);
                    }
                    break;
                case '(':
                    $openBrackets++;
                    break;
                case ')':
                    if ($openBrackets > 0) {
                         $openBrackets--;
                    }
                    break;
                case 'select':
                    if ($selectExpStart == 0) {
                        $selectExpStart = $sqlParser->lexer->tokPtr;
                        $columnStart = $selectExpStart;
                    }
                    break;
                case 'from':
                    if ($openBrackets == 0) {
                        $fromStart = $sqlParser->lexer->tokPtr;
                        $selectExpEnd = $fromStart - $sqlParser->lexer->tokLen;
                    }
                    break;
                case 'group':
                    if ($openBrackets == 0) {
                        $groupBy = true;
                    }
                    break;
                case 'order':
                    if ($openBrackets == 0) {
                        $orderByStart = $sqlParser->lexer->tokLen;
                    }
                    break;
            default:
                if (!isset($sqlParser->token) || $sqlParser->token == 'union') {
                    $elemSelectQuery['filter'] = SGL_DataGridSQLDataSource::parseFilter(
                                                                         $setFilter, $columns);
                    $elemSelectQuery['selectStart'] = $selectStart;
                    $elemSelectQuery['selectExpStart'] = $selectExpStart;
                    $elemSelectQuery['selectExpEnd'] = $selectExpEnd;
                    $elemSelectQuery['selectExpLen'] = $selectExpEnd - $selectExpStart;
                    $elemSelectQuery['groupBy'] = $groupBy;
                    if (isset($sqlParser->token)) {
                        $elemSelectQuery['selectEnd'] =
                                    $sqlParser->lexer->tokPtr - $sqlParser->lexer->tokLen;
                    } else {
                        $elemSelectQuery['selectEnd'] = strlen($query);
                    }
                    $elemSelectQuery['selectLen'] =
                                $elemSelectQuery['selectEnd'] - $elemSelectQuery['selectStart'];

                    $selectQuery[] = $elemSelectQuery;
                    $selectStart = $sqlParser->lexer->tokPtr;
                    $selectExpStart = 0;
                    $selectExpEnd = 0;
                    $columns = array();
                    $groupBy = false;

                }
            }
        }
        while (isset($sqlParser->token));
        $returnQuery = $query;
        for ($i = count($selectQuery) - 1; $i >= 0; $i --) {
            $elemSelectQuery = $selectQuery[$i];
            if ($setSelect != '') {
                if ($elemSelectQuery['groupBy']) {
                    $returnQuery = substr($returnQuery, 0, $elemSelectQuery['selectStart']) .
                                         ' SELECT ' . $setSelect . ' FROM (' .
                                          substr($returnQuery, $elemSelectQuery['selectStart'],
                                                    $elemSelectQuery['selectLen']) .
                                          ') as temp ' .
                                          substr($returnQuery, $elemSelectQuery['selectEnd']);
                } else {
                    $returnQuery = substr($returnQuery, 0, $elemSelectQuery['selectExpStart']) .
                                         ' ' . $setSelect . ' ' .
                                         substr($returnQuery, $elemSelectQuery['selectExpEnd']);
                }
            }

            $changeFilter = $elemSelectQuery['filter'];
            if (strpos($returnQuery, '#_FILTER#')) {
                if ($setFilter == '') {
                    $returnQuery = str_replace('#_FILTER#', '1=1', $returnQuery);
                } elseif (count($changeFilter) == 0) {
                    $returnQuery = str_replace('#_FILTER#', $setFilter, $returnQuery);
                } else {
                    $posFilter = strpos($returnQuery, '#_FILTER#',
                                            $elemSelectQuery['selectStart']);
                    if ($posFilter !== false) {
                        usort($changeFilter, array("SGL_DataGridSQLDataSource","_compareByPos"));
                        $setFilterTemp = $setFilter;
                        foreach ($changeFilter as $filterElement) {
                            $setFilterTemp = substr($setFilterTemp, 0, $filterElement['from']) .
                                                 $filterElement['text'] .
                                                 substr($setFilterTemp, $filterElement['len'] +
                                                    $filterElement['from']);
                        }
                        $returnQuery = substr($returnQuery, 0, $posFilter) . $setFilterTemp .
                                          substr($returnQuery, $posFilter + strlen('#_FILTER#'));
                    }
                }
            }

        }
        if ($setOrderBy != '' || is_null($setOrderBy)) {
            if ($orderByStart > 0) {
                $orderByStart = 0;
                $returnQueryTemp = strtoupper($returnQuery);
                while (($orderByPosTemp = strpos($returnQueryTemp, 'ORDER BY', $orderByStart + 1)) !== false) {
                    $orderByStart = $orderByPosTemp;

                }

                if ($orderByStart > 0) {
                    $returnQuery = substr($returnQuery, 0, $orderByStart);
                }
            }
            if ($setOrderBy != '') {
                $returnQuery .= ' ORDER BY ' . $setOrderBy;
            }
        }
        if (is_null($returnQuery)) {
            return DB::raiseError(DB_ERROR_SYNTAX);
        }
        return $returnQuery;
    }

    function modifySelectQuerySetCount($query, $setFilter = '')
    {
        if (!DB::isError(($queryCount = SGL_DataGridSQLDataSource::modifySelectQuery($query,
                                                    'count(*) as count', $setFilter, null)))) {
            return $queryCount;
        } else {
            return 'SELECT sum(count) FROM (' . $queryCount . ') as temp';
        }
    }

    function _compareByPos($a, $b)
    {
        if ($a['from'] == $b['from']) {
            return 0;
        }
        return ($a['from'] > $b['from']) ? -1 : 1;
    }
    /** Helper function to make filter having aliases (in $setFilter) and to
     * translate them into proper filter
     * (ie when a column in select is a subselect we cannot use alias to
     * filter but we have to insert whole subselect,
     * ie:
     * main query: select (select name from person where id = parent) as name1,
     * name from person;
     * $setFilter: name1 like ? and name like ?
     * we have to make from it: (select name from person where id = parent) like ?
     * and name like ?
     * @param string $setFilter - filter string (ie name like ? and id = ?)
     * @param array  $columns - columns in select, to
     * @return array with information about filter
     * @uses    PEAR/SQL_Parser module
     */
    function parseFilter($setFilter, &$columns)
    {
        $changeFilter = array();
        if ($setFilter != '') {
            require_once 'SQL/Parser.php';
            $sqlParser = & new SQL_Parser($setFilter);
            do {
                $sqlParser->getTok();

                switch ($sqlParser->token) {
                    case 'ident':
                        if (isset($columns[$sqlParser->lexer->tokText])) {
                            $changeFilter[] = array(
                                                    'from' => $sqlParser->lexer->tokPtr - $sqlParser->lexer->tokLen,
                                                    'len' => $sqlParser->lexer->tokLen,
                                                    'text' => $columns[$sqlParser->lexer->tokText]
                                                    );
                        }
                        break;
                }
            }
            while (isset($sqlParser->token));
        }

        return $changeFilter;
    }
}

?>
