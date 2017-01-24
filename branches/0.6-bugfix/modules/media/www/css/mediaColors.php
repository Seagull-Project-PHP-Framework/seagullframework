<?php
//$aColors[] = '#f0ffd9';
//$aColors[] = '#c6d7aa';
//$aColors[] = '#E0EFB8';
//$aColors[] = '#C3CEA5';
//$aColors[] = '#333333';
//$aColors[] = '#0066CC';
//$aColors[] = '#C3CEA5';
//$aColors[] = '#5F7032';
//$aColors[] = '#F6F5F2';
//$aColors[] = '#D9D8CB';
//$aColors[] = '#C3CEA5';
//$aColors[] = '#666666';
//$aColors[] = '#FFFFFF';
//$aColors[] = '#E5E5E5';
//$aColors[] = '#CCCCCC';
//$aColors[] = '#000000';
//$aColors[] = '#F3F3EC';
//$aColors[] = '#999791';
//$aColors[] = '#9CB20A';
//$aColors[] = '#855F48';
//$aColors[] = '#0066CC';
//$aColors[] = '#CC3300';
//$aColors[] = '#f0ffd9';
//$aColors[] = '#999999';
//$aColors[] = '#D0DCE0';
//
echo'<pre>';
//
//$aColors = array_map('strtoupper', $aColors);
//print_r($aColors);
//echo "Nb keys " . count($aColors) . "<br />";
//
//$aColors = array_unique($aColors);
//print_r($aColors);
//echo "Nb keys " . count($aColors) . "<br />";

/* REORDERED COLOR ARRAY */
$aColors = array();
$aColors[] = '#F0FFD9';
$aColors[] = '#E0EFB8';
$aColors[] = '#C6D7AA';
$aColors[] = '#C3CEA5';
$aColors[] = '#9CB20A';
$aColors[] = '#5F7032';

$aColors[] = '#FFFFFF';
$aColors[] = '#F6F5F2';
$aColors[] = '#F3F3EC';
$aColors[] = '#E5E5E5';
$aColors[] = '#CCCCCC';
$aColors[] = '#D9D8CB';

$aColors[] = '#999999';
$aColors[] = '#999791';
$aColors[] = '#666666';
$aColors[] = '#333333';
$aColors[] = '#000000';
$aColors[] = '';

$aColors[] = '#855F48';
$aColors[] = '#D0DCE0';
$aColors[] = '#0066CC';
$aColors[] = '#CC3300';
$aColors[] = '';
$aColors[] = '';

/* From default theme to compare primary colors */
$aColors[] = '#EEF7D4';
$aColors[] = '#99CC00';
$aColors[] = '#66A326';
$aColors[] = '#EEEEEE';
$aColors[] = '#BBBBBB';
$aColors[] = '#666666';

print_r($aColors);
echo "Nb keys " . count($aColors) . "<br />";

echo "<div style='float:left;width:700px;'>";
foreach($aColors as $k => $color) {
    echo "<div style='float:left;width:100px;height:100px;margin:5px;background:$color;text-align:center;line-height:100px'>$k $color</div>";
}
echo "</div>";
?>