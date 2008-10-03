<?php
/**
 * Returns systime in ms.
 *
 * @return string   Execution time in milliseconds
 */
function getSystemTime()
{
    $time = gettimeofday();
    $resultTime = $time['sec'] * 1000;
    $resultTime += floor($time['usec'] / 1000);
    return $resultTime;
}
//  start timer
define('SGL_START_TIME', getSystemTime());
$rootDir = dirname(__FILE__) . '/..';
$varDir = dirname(__FILE__) . '/../var';
if (is_file($rootDir .'/lib/SGL/FrontController.php')) {
    require_once $rootDir .'/lib/SGL/FrontController.php';
}
SGL_FrontController::init();


//  main
$output = new SGL_Output();
$blockLoader = & new SGL_BlockLoader(0);
$aBlocks = $blockLoader->render($output);
foreach ($aBlocks as $key => $value) {
    $blocksName = 'blocks'.$key;
    $output->$blocksName = $value;


}

$tmpl = & new SGL_HtmlSimpleView($output);
$html = $tmpl->render();
print $html;
?>
