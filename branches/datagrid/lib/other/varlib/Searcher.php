<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | SYSTEM LACINSKI 2.0                                                       |
// +---------------------------------------------------------------------------+
// | Searcher.php                                                              |
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005 Varico.com                                             |
// |                                                                           |
// | Author: Varico <varico@varico.com>                                        |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | Varico Lincence                                                           |
// |                                                                           |
// +---------------------------------------------------------------------------+
/**
 * Search data in database
 * @package Searcher.php
 * @author  Varico
 * @version $Revision: 1.4 $
 * @since   PHP 4.1
 */
class Searcher 
{
    // list of tables where we are looking for
    var $aListOfTables = array();
    
    // list of words
    var $aWords = array();
    
    // table with searchresult
    var $aResult = array();

    var $resultPhrase = 0;
    
    /**
     * constructor searcher
     * @param array $aListOfTables - array with table name and colums;
     *  array(['table name'] => array('column 1', 'column 2' ), ......
     * @access public
     * @return void
    **/
    function searcher($aListOfTables) {
        if ((is_array($aListOfTables)) && (count($aListOfTables) > 0)) {
            $this->aListOfTables = $aListOfTables;
        } 
    }    
        
    /**
     * function addWords
     * @param string $keywords
     * @access public
     * @return void
    **/
    function addWords($keywords) 
    {
        $this->aWords = $keywords;
        /*
        if (($keywords[0] == "\"") && ($keywords[(strlen($keywords) - 1)] == "\"")) {
            $this->aWords[] = substr($keywords, 1, strlen($keywords) - 2);
            return 1;
            
        } 
        $data = explode(" ", $keywords);         
        if ((is_array($data)) && (count($data) > 0)) {
            $this->aWords = array_merge($this->aWords, $data);
        } 
        else 
        {
            if ($data <> '') {
                $this->aWords[] = $data;
            }
        }
        */
    }
    
    /**
     * function createQuery - create search query
     * @param 
     * @access private
     * @return void
    **/
    function createQuery () 
    {
        $dbh = & SGL_DB::singleton();
        $this->reaultPhrase = 0;
        foreach ($this->aListOfTables as $key_table => $table) {
            // create like query section
            $query = '';
            $listOfColumn  = $dbh->tableInfo($key_table);
            foreach($listOfColumn as $key => $column) {
                if (($column['table'] == $key_table) && ((string)array_search($column['name'], $table) <> '')
                    ){
                    for ($j = 0; $j < count($this->aWords); $j++) {
                        $likeQuery = 'WHERE 1=2 ';
                        $likeQuery .= " OR " . $column['name'] . " LIKE '%" . $this->aWords[$j] . "%' ";
                        $query = "SELECT * from " . $key_table . " " . $likeQuery . ";";
                        $res = & $dbh->getAll($query, array(), DB_FETCHMODE_ASSOC);
                        if (count($res) > 0) {
                            // add two special fields with search word and column name
                            for($i = 0; $i < count($res); $i++) {
                                $res[$i]['keyword'] = $column['name'];
                                $res[$i]['search_word'] = $this->aWords[$j];      
                            }
                            $this->aResult[$key_table][] = $res;
                            $this->resultPhrase = $this->resultPhrase + count($res);
                        }
                    }    
                }
            }            
        }
    }
    
    /**
     * function search
     * @param 
     * @access public
     * @return array  (['table_name' = > array[row]=> array[columns]])
    **/
    function search() {
        $this->createQuery();
        return $this->aResult;
    }
    
    /**
     * function getSearchPhrase
     * @param 
     * @access public
     * @return int rows of query result
    **/
    function getSearchPhrase() {
        return $this->resultPhrase;
    }
}
?>