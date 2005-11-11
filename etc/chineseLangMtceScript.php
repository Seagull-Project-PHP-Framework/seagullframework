<?php
//---Configuration Area Start---//
$seagullPath = 'seagull/modules'; // The path of seagull's module
$oldLanguageFile = '/lang/chinese-big5.php'; //
$baseLanguageFile = '/lang/english-iso-8859-15.php';
$newLanguageFile = '/lang/chinese_traditional-utf-8.php';
$backupFolder = 'modules/';
$useIconv = true;
$srcEncode = 'big5';
$targetEncode = 'utf-8';
//---Configuration Area End---//
$indent = '    ';
$dh = opendir($seagullPath);
while($data = readdir($dh))
{
	if($data != '.' && $data != '..' )
	{
		//echo $data . '<br>';
		if(file_exists($seagullPath . '/' .$data . $oldLanguageFile))
		{
			include $seagullPath . '/' .$data . $oldLanguageFile;
			if($data == 'default')
				$Cwords = $defaultWords;
			else
				$Cwords = $words;
			unset($words);
			unset($defaultWords);
		}
		include $seagullPath . '/' .$data . $baseLanguageFile;
		if($data == 'default')
			$Ewords = $defaultWords;
		else
			$Ewords = $words;
		unset($words);
		unset($defaultWords);
		foreach($Ewords as $key => $var)
		{
			if(array_key_exists($key, $Cwords))
				$newArray[$key] = $Cwords[$key];
			else
				$newArray[$key] = $Ewords[$key];
		}
		ksort($newArray);
		
		$newFile = $seagullPath . '/' .$data . $newLanguageFile;
		$fh = fopen($newFile, 'w');
		if($data == 'default')
			fwrite($fh, '<?php' . chr(10) . '$defaultWords = ');
		else
			fwrite($fh, '<?php' . chr(10) . '$words = ');
		fwrite($fh, 'array(');
		foreach($newArray as $nKey => $nVar)
		{
			if(!is_array($nVar)){
				$nVar = preg_replace('/\'/', '\\\'', $nVar);
				if($useIconv)
					$nVar = iconv($srcEncode, $targetEncode, $nVar);
				fwrite($fh, chr(10) . $indent . '\''.$nKey.'\' => \''.$nVar.'\',');
			} else {
				fwrite($fh, chr(10) . $indent . '\''.$nKey.'\' => array(');
				foreach($nVar as $sKey => $sVar)
				{
					$sVar = preg_replace('/\'/', '\\\'', $sVar);
					if($useIconv)
						$sVar = iconv($srcEncode, $targetEncode, $sVar);
					fwrite($fh, chr(10) . $indent . $indent . '\''.$sKey.'\' => \''.$sVar.'\',');					
				}
				fwrite($fh, chr(10) . $indent . '),');
			}
		}
		fwrite($fh, chr(10) . ');' . chr(10) . '?>');
		fclose($fh);
		unset($newArray);
  	if(!is_dir($backupFolder . $data))
  		mkdir($backupFolder . $data);
  	if(!is_dir($backupFolder . $data . '/lang'))
  		mkdir($backupFolder . $data . '/lang');
  	copy($seagullPath . '/' .$data . $newLanguageFile, $backupFolder . $data . $newLanguageFile);
	}
}
?>