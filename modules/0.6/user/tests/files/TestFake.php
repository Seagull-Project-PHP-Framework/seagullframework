<?php

class TestFake {

    function fake()
    {
        $this->_aActionsMapping = array(
            'disallowedMethod'    => array('disallowedFoo')
        );
    }

}

?>