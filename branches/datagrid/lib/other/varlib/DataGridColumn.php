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
    function SGL_DataGridColumn($type, $name, $dbName, $sortable, $filterable,
                           $sumable, $sumTotalable, $align)
    {	// KK 26938 poprawa standardu kodowania zgodnie z PEAR
        $this->type          = $type;
        $this->name          = $name;
        $this->dbName        = $dbName;
        $this->sort          = $sortable;
        $this->filter        = $filterable;
        $this->sum           = $sumable;
        $this->sumTot        = $sumTotalable;
        
        if ($align === '') {
            switch ($type) {
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
        } else {
            $this->align = $align;
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
    function addAction($keyActionName, $valueUrlAction, $actionIcon = '', $actionTips = '', $javaScript = '')
    {
        $object->img        = $actionIcon;
        $object->name       = $keyActionName;
        $object->url        = $valueUrlAction;
        $object->tips       = $actionTips;
        $object->javaCode   = $javaScript;
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
