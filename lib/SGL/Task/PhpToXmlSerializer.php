<?php
/**
 * Serializes PHP to XML.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
require_once 'XML/Serializer.php';

/**
 * Serializes returned PHP objects into XML for requests.
 *
 */
class SGL_Task_PhpToXmlSerializer extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->processRequest->process($input, $output);

        $options = array(
            'encoding'       => 'UTF-8',
            "indent"         => "    ",
            "linebreak"      => "\n",
            "classAsTagName" => true,
            "addDecl" => true,
        );

        $serializer = &new XML_Serializer($options);
        $result = $serializer->serialize($input->result);
        if ($result === true ) {
            $xml = $serializer->getSerializedData();
            $output->data = $xml;
        }
    }
}
?>