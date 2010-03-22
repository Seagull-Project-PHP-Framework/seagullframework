<?php
final class Uber_Lazy_Mixin
{
    private $_mixins = array();
    private $_consumer;

    public final function __construct(&$consumer)
    {
        $this->_consumer = $consumer;
        $args = func_get_args();
        if (isset($this->_consumer->mixins)) {
            $this->_mixin($this->_consumer->mixins);
        }
    }

    /**
     * Registering mixin config:
     * 
     * $mixins = array('text_helper'=>'TextHelper','form_helper'=>'FormHelper')
     * 
     * or 
     * 
     * $mixins = 'TextHelper';
     * 
     * or 
     * 
     * $mixins = array('TextHelper','FormHelper');
     * 
     *
     * @param mixed $mixinConfig string or array
     */
    private function _mixin($mixinConfig)
    {
        if (! is_array($mixinConfig)) {
            $mixinConfig = array($mixinConfig);
        }
        foreach ($mixinConfig as $key => $class) {
            $alias = $class;
            if (! is_numeric($key)) {
                $alias = $key;
            }
            $this->_mixins[$alias] = $class;
        }
    }

    public function getMixin($alias)
    {
        $methodExists = false;
        if (isset($this->_mixins[$alias]) || ($methodExists = method_exists($this->_consumer, '_getMixin'))) {
            if ($methodExists) {
                $mixin = $this->_consumer->_getMixin($alias);
            } else {
                $mixin = new $this->_mixins[$alias]();
            }
            if (method_exists($mixin, 'setMixinConsumer')) {
                $mixin->setMixinConsumer($this->_consumer);
            }
            return $mixin;
        }
        return false;
    }
}
?>