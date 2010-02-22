<?php
require_once 'XML/Unserializer.php';

/**
 * Unserializes XML data into PHP objects.
 *
 * Used when the REST server receives an XML post
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
            $entityName = SGL_Config::get('REST.entityName');
            $xml = $req->get($entityName, $allowTags = true);
            $unserializer = &new XML_Unserializer();
            $unserializer->setOption('tagAsClass', true);
            $unserializer->setOption('ignoreKeys', $this->getKeysToIgnore());
            $unserializer->setOption('complexType', $this->getComplexTypes());
            $xml = stripslashes($xml);
            $result = $unserializer->unserialize($xml);
            if (PEAR::isError($result)) {
                return $result;
            }
            $data = $unserializer->getUnserializedData();
            $input->$entityName = $data;
        }
        $this->processRequest->process($input, $output);
    }

    /**
     * Builds an array of keys from the configuration to ignore.
     *
     * @return array
     */
    function getKeysToIgnore()
    {
        $keys = SGL_Config::get('REST.keysToIgnore');
        $aKeys = explode(',', $keys);
        return $aKeys;
    }

    /**
     * Builds a hash  from the configuration of complex type names and their type.
     *
     * Type = 'object' by default
     *
     * @return array
     */
    function getComplexTypes()
    {
        $keys = SGL_Config::get('REST.complexType');
        $aKeys = explode(',', $keys);
        $aRet = array();
        foreach ($aKeys as $key) {
            $aRet[$key] = 'object';
        }
        return $aRet;
    }
}
?>