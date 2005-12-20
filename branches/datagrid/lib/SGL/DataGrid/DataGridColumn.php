<?php
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//Varico ...na wszystko mamy program/one stop source for software
//http://www.varico.com ... od 1989/since 1989
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


/**
 * SGL_DataGridColumn
 * For creating columns to dataGrid and updating them by specified methods
 * @package SGL_DataGridColumn
 * @author Varico
 * @copyright Copyright (c) 2005
 * @version $Id: DataGridColumn.php,v 1.2 2005/10/11 12:09:08 krzysztofk Exp $
 * @access public
 **/
class SGL_DataGridColumn {
    var $type;
    var $name;
    var $dbName;
    var $sort;
    var $filter;
    var $sum;
    var $sumTot;
    var $filterSelect = array();
    var $transform    = array();
    var $cError       = "";
    var $align        = "";
    var $tooltip      = array();

    /**
     * SGL_DataGridColumn::SGL_DataGridColumn()
     * Initialize column object with specified parameters
     * @param string $type - column type
     * @param string $name - column name
     * @param string $dbName - column name in database
     * @param boolean $sortable - indicate if column may be sorted
     * @param boolean $filterable - indicate if column may be filtered
     * @param boolean $sumable - indicate if column may be summed per page
     * @param boolean $sumTotalable - indicate if column may be summed per total
     * @access public
     * @return void
     **/
    function SGL_DataGridColumn($params)
    {	// KK 26938 poprawa standardu kodowania zgodnie z PEAR
        if ((!empty($params)) && (is_array($params))) {
            if (isset($params['type']))
                $this->type = $params['type'];
            else
                $this->type = '';

            if (isset($params['name']))
                $this->name = SGL_String::translate($params['name']);
            else
                $this->name = '';

            if (isset($params['dbName']))
                $this->dbName = $params['dbName'];
            else
                $this->dbName = '';

            if (isset($params['sortable']))
                $this->sort = $params['sortable'];
            else
                $this->sort = false;

            if (isset($params['filterable']))
                $this->filter = $params['filterable'];
            else
                $this->filter = false;

            if (isset($params['sumable']))
                $this->sum = $params['sumable'];
            else
                $this->sum = false;

            if (isset($params['sumTotalable']))
                $this->sumTot = $params['sumTotalable'];
            else
                $this->sumTot = false;

            if (isset($params['tooltip']))
                $this->tooltip = $params['tooltip'];
            else
                $this->tooltip = '';

            if (isset($params['align'])) 
                $this->align = $params['align'];
            else {
                switch ($this->type) {
                    case 'id'        : $this->align = 'center'; break;
                    case 'text'      : $this->align = ''; break;
                    case 'hidden'    : $this->align = ''; break;
                    case 'html'      : $this->align = ''; break;
                    case 'user'      : $this->align = ''; break;
                    case 'colour'    : $this->align = ''; break;
                    case 'integer'   : $this->align = 'center'; break;
                    case 'real'      : $this->align = 'right'; break;
                    case 'date'      : $this->align = 'right'; break;
                    case 'hour'      : $this->align = ''; break;
                    case 'image'     : $this->align = 'center'; break;
                    case 'thumbnail' : $this->align = ''; break;
                    case 'enclosure' : $this->align = 'center'; break;
                    case 'email'     : $this->align = ''; break;
                    case 'link'      : $this->align = ''; break;
                    case 'radio'     : $this->align = ''; break;
                    case 'action'    : $this->align = ''; break;
                }
            }
        }
    }

    /**
     * SGL_DataGridColumn::addAction()
     * Add specified action name and link to column for all rows in dataGrid
     * @param string $keyActionName - name for action
     * @param string $valueUrlAction - url address for action
     * @param string $actionIcon - name of action icon file
     * @param string $actionTips overlib text
     * @param string $javaScript Java Scrip code
     * @access public
     * @return void
     **/
    function addAction($params)
    {
        if ((!empty($params)) && (is_array($params))) {
            if (isset($params['icon']))
                $object->img = $params['icon'];
            else
                $object->img = '';

            if (isset($params['name']))
                $object->name = $params['name'];
            else
                $object->name = '';

            if (isset($params['url']))
                $object->url = $params['url'];
            else
                $object->url = '';

            if (isset($params['tips']))
                $object->tips = $params['tip'];
            else
                $object->tips = '';

            if (isset($params['jacaScript']))
                $object->javaCode = $params['javaScript'];
            else
                $object->javaCode = '';
        }
        if (isset($object))
            $this->actionData[] = $object;
    }

    /**
     * SGL_DataGridColumn::setFilterSelect()
     * Add options for SELECT filter type
     * @param array $filterSelectIn - array of options to add to SELECT element in dataGrid filter
     * @access public
     * @return void
     **/
    function setFilterSelect($filterSelectIn = array())
    {
        $this->filterSelect = $filterSelectIn;
    }

    /**
     * SGL_DataGridColumn::setTransformInColumn()
     * Transform given values as array keys to values given as array elements
     * @param array $tarnsformIn - array of values in database and transform values
     * @access public
     * @return void
     **/
    function setTransformInColumn($transformIn = array())
    {
        $this->transform = $transformIn;
    }
}
?>
