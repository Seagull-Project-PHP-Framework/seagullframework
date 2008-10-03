<?php

class DefaultController extends SGL_Controller_Page
{
    public function __construct()
    {
        parent::__construct();

        $this->pageTitle    = 'Home';
        $this->template     = 'default.html';
        $this->_aActionsMapping =  array(
            'list'  => array('list'),
        );
    }

    public function validate(SGL_Request $input)
    {
        $input->masterTemplate = $this->masterTemplate;
        $input->template    = $this->template;
        $input->pageTitle   = $this->pageTitle;
        $input->action      = ($input->get('action')) ? $input->get('action') : 'list';

        // filtering and validation example
        // see http://framework.zend.com/manual/en/zend.filter.input.html
        $filters = array(
            '*'     => 'StringTrim',
            'month' => 'Digits'
        );
        $validators = array(
            'month'   => array(
                'Digits',                // string
                new Zend_Validate_Int(), // object instance
                array('Between', 1, 12)  // string with constructor arguments
            )
        );
        $zfi = new Zend_Filter_Input($filters, $validators, $input->getTainted());
        if ($zfi->hasInvalid() || $zfi->hasMissing()) {
            $this->setMessages($zfi->getMessages());
            $ret = false;
        } else {
            $input->add($zfi->getUnescaped());
            $ret = true;
        }
        return $ret;
    }

    protected function _doList(SGL_Request $input, SGL_Response $output)
    {
        $input->foo = 'myFoo';
        $output->bar = 'myBar';
    }

    public function display(SGL_Response $output)
    {
        $output->baz = 'myBaz';
    }
}
?>