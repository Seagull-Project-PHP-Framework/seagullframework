<?php

require_once SGL_LIB_DIR . '/amf-core/app/Gateway.php';
//You can set this constant appropriately to disable traces and debugging headers
//You will also have the constant available in your classes, for changing
//the mysql server info for example
define("PRODUCTION_SERVER", false);

class SGL_Task_ExecuteAmfAction extends SGL_ProcessRequest
{
    function process(&$input, &$output)
    {
 		SGL::logMessage(null, PEAR_LOG_DEBUG);
        $req = $input->getRequest();
        $moduleName = $req->getModuleName();
        $method = $req->getActionName();

		$gateway = new SGL_Gateway();

		//Set where the services classes are loaded from, *with trailing slash*
		$gateway->setBaseClassPath(SGL_MOD_DIR . '/' .($moduleName) . '/classes/amfservices/');

		//Loose mode means echo'ing or whitespace in your file won't make AMFPHP choke
		$gateway->setLooseMode(true);

		//Read above large note for explanation of charset handling
		//The main contributor (Patrick Mineault) is French,
		//so don't be afraid if he forgot to turn off iconv by default!
		//$gateway->setCharsetHandler("utf8_decode", "ISO-8859-1", "ISO-8859-1");

		//Error types that will be rooted to the NetConnection debugger
		$gateway->setErrorHandling(E_ALL ^ E_NOTICE);

		//choices are php5 (SoapClient), nusoap and pear
		//If you don't plan on using web services with AMFPHP,
		//you can safely let this setting alone
		//Note that for nusoap to work you MUST place the library under /amf-core/lib/nusoap.php
		$gateway->setWebServiceHandler('php5');

		//Adding an adapter mapping will make returns of the mapped typed be intercepted
		//and mapped in adapters/%adapterName%Adapter.php. This works by using get_class
		//So for example, if you return a PEAR resultset object, it is an instance of DB_result
		//And we want this to be processed as a recordset in adapters/peardbAdapter.php,
		//hence the following line:
		$gateway->addAdapterMapping('db_result', 'peardb');
		//For PDO (PHP 5.1 specific)
		$gateway->addAdapterMapping('pdostatement', 'pdo');
		//For oo-style MySQLi
		$gateway->addAdapterMapping('mysqli_result', 'mysqli');
		//For filtered array
		//And for filtered typed array (see adapters/lib/Arrayf.php and Arrayft.php)
		$gateway->addAdapterMapping('arrayf', 'arrayf');
		$gateway->addAdapterMapping('arrayft', 'arrayft');
		//And you can add your own after this point... (note lowercase for both args!)

		if (PRODUCTION_SERVER)
		{
			//Disable trace actions
			$gateway->disableTrace();

			//Disable debugging headers
			$gateway->disableDebug();

			//Disable Service description
			$gateway->disableServiceDescription();
		}

//		include_once('advancedsettings.php');

		//Service now
		$output->data = $gateway->service();

    }

}

class SGL_Gateway extends Gateway
{

	/**
	 * The service method runs the gateway application.  It turns the gateway 'on'.  You
	 * have to call the service method as the last line of the gateway script after all of the
	 * gateway configuration properties have been set.
	 *
	 * Right now the service method also includes a very primitive debugging mode that
	 * just dumps the raw amf input and output to files.  This may change in later versions.
	 * The debugging implementation is NOT thread safe so be aware of file corruptions that
	 * may occur in concurrent environments.
	 */

	function service()
	{
		//Set the parameters for the charset handler
		CharsetHandler::setMethod($this->_charsetMethod);
		CharsetHandler::setPhpCharset($this->_charsetPhp);
		CharsetHandler::setSqlCharset($this->_charsetSql);

		//Attempt to call charset handler to catch any uninstalled extensions
		$ch = new CharsetHandler('flashtophp');
		$ch->transliterate('?');

		$ch2 = new CharsetHandler('sqltophp');
		$ch2->transliterate('?');

		$GLOBALS['amfphp']['actions'] = $this->actions;

		NetDebug::initialize();

//		error_reporting($GLOBALS['amfphp']['errorLevel']);

		//Enable loose mode if requested
		if ($this->_looseMode) {
			ob_start();
		}

		$amf = new AMFObject($GLOBALS["HTTP_RAW_POST_DATA"]);   // create the amf object

		if ($this->incomingMessagesFolder != null) {
			$mt = microtime();
			$pieces = explode(' ', $mt);
			file_put_contents($this->incomingMessagesFolder .
				'in.' . $pieces[1] . '.' . substr($pieces[0], 2) . ".amf",
				$GLOBALS["HTTP_RAW_POST_DATA"]);
		}

		foreach ($this->filters as $key => $filter) {
			$filter($amf); //   invoke the first filter in the chain
		}

		$output = $amf->outputStream; // grab the output stream

		//Clear the current output buffer if requested
		if ($this->_looseMode) {
			if($this->_obLogging !== false) {
				$this->_appendRawDataToFile($this->_obLogging, ob_get_clean());
			} else {
				ob_end_clean();
			}
		}

		//Send content length header
		//Thanks to Alec Horley for pointing out the necessity
		//of this for FlashComm support
		header(AMFPHP_CONTENT_TYPE); // define the proper header
		header("Content-length: " . strlen($output));

		//Send expire header, apparently helps for SSL
		//Thanks to Gary Rogers for that
		//And also to Lucas Filippi from openAMF list
		//And to Robert Reinhardt who appears to be the first who
		//documented the bug
		//Finally to Gary who appears to have find a solution which works even more reliably
		if ($this->useSslFirstMethod) {
			$dateStr = date("D, j M Y ") . date("H:i:s", strtotime("-2 days"));
			header("Expires: $dateStr GMT");
			header("Pragma: no-store");
			header("Cache-Control: no-store");
		}
		//else don't send any special headers at all

		if ($this->outgoingMessagesFolder != null) {
			$mt = microtime();
			$pieces = explode(' ', $mt);
			file_put_contents($this->outgoingMessagesFolder .
				'out.' . $pieces[1] . '.' . substr($pieces[0], 2) . ".amf", $output);
		}
		return $output; // flush the binary data
	}

}
?>