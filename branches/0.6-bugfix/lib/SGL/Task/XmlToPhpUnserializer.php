<?php
require_once 'XML/Unserializer.php';

/**
 * Unserializes XML data into PHP objects.
 *
 * Used when the REST server receives and XML post
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_XmlToPhpUnserializer extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $req = $input->getRequest();
            $entityName = $this->conf['REST']['entityName'];
            $xml = $req->get($entityName, $allowTags = true);
        	$unserializer = &new XML_Unserializer();
        	$unserializer->setOption('tagAsClass', true);
        	$unserializer->setOption('ignoreKeys', $this->getKeysToIgnore());
        	$unserializer->setOption('complexType', $this->getComplexTypes());

            $result = $unserializer->unserialize($xml);
            if (PEAR::isError($result)) {
            	return $result;
            }
            $data = $unserializer->getUnserializedData();
            $input->$entityName = $data;
        }

        $this->processRequest->process($input, $output);
    }

    function getKeysToIgnore()
    {
        $keys = $this->conf['REST']['keysToIgnore'];
        $aKeys = explode(',', $keys);
        return $aKeys;
    }

    function getComplexTypes()
    {
        $keys = $this->conf['REST']['complexType'];
        $aKeys = explode(',', $keys);
        return $aKeys;
    }
}
?>