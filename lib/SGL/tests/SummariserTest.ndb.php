<?php
class SummariserTest extends UnitTestCase
{
    function SummariserTest() 
    {
        $this->UnitTestCase('Test of Summariser');
    }
    
    function setup()
    {
        $this->html = <<<EOF
<div id="content">
<h1 class="pageTitle">Welcome to Seagull Framework</h1>
 
<p>Welcome to the <strong>Seagull Framework</strong> project. Seagull is an OO <a href="http://php.net" title="PHP"><acronym title="PHP: Hypertext Preprocessor">PHP</acronym></a> framework, with core components <acronym title="Berkeley Software Distribution">BSD</acronym> licensed, that has the following design goals:</p>

<ul class="bullets">
    <li>independence of data, logic &amp; presentation layers</li>
    <li>extensible component architecture</li>
    <li>reduction of repetitive programming tasks</li>
    <li>simplifying data access</li>
    <li>comprehensive error handling</li>

    <li>module workflow routines</li>
    <li>form handling without the donkey work</li>
    <li>component reuse</li>
    <li>authentication management</li>
    <li>integration with <acronym title="PHP Extension and Application Repository">PEAR</acronym> libraries</li>

    <li><acronym title="PHP: Hypertext Preprocessor">PHP</acronym> coding standards</li>
    <li>platform/<acronym title="PHP: Hypertext Preprocessor">PHP</acronym> version/browser independence</li>
    <li>self-generating documentation</li>
    <li>quality end user docs</li>

</ul>
EOF;
    }
    
    function xtestFoo()
    {
        $regex = 
          '#(?!<[^>]*?)
            (?![^<]*?>)
            (?!.*<body>)
           #six';
        $ret = preg_replace($regex, 'foo', $this->html);
    }
}
?>
