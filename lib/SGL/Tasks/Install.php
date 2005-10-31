<?php


class SGL_Task_GetLoadedModulesx extends SGL_Task
{
    function run()
    {
    	foreach ($this->aRequirements as $m => $dep) {
    		$this->aData[$m] = bool2int(extension_loaded($m));
    	}
    	return $this->render($this->aData);
    }   
}
?>