<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

define('HEAD', 0);
define('TAG' , 1);
define('TAIL', 2);

// taken from php man as legal variable name (used here as legal function name)
define('VF_NAME', '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*'); 

/**
* Flexy2Smarty Conversion
* takes a flexy template and converts it to a smarty one.
* 
* @version 0.1
* @author Ilya Hegai <vyacheslavovich@gmail.com> 
*/
class Flexy2Smarty {
    
    var $md5 = null;

    /**
    * stack for conditional and closers.
    *
    * @var array
    * @access private
    */
    var $stack = array(
            'if'           => 0,
            'foreach'      => 0,            
            'foreach_vars' => array(), // array(value) || array(value, key)
            'last'         => array()  // stack of last opening tags (foreach or if)
        );
    
    /**
    * The core work of parsing a flexy template and converting it into smarty.
    *
    * @param string $data    the contents of the flexy template
    *
    * @return string         the smarty version of the template.
    * @access public
    */
    function convert($data) 
    {
        $this->_flexyEmbed($data, 0);

        $text = preg_split('/(<script[^>]+>(?!<).+?<\/script>)/s', $data, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $leftq = preg_quote('{');
        $rightq = preg_quote('}');

        $tags = array();
        foreach ($text as $t) {
            if (substr(trim($t), 0, 7) != '<script' || preg_match('/^\s*<script[^>]*>(?=<)/', $t)) {
                preg_match_all('/' . $leftq . '\s*(.*?)\s*' . $rightq . '/', $t, $matches);
                $tags = array_merge($tags, $matches[1]);
            }
        }

        $data = implode('', $text);

        // escape javascript with {literal}
        $data = preg_replace('/<script[^>]*>(?!<)/i', "\\0\n{literal}", $data);
        $data = preg_replace('/(?<!>)<\/script>/i', "{/literal}\n\\0", $data);

        // find all the tags/text...
        $text = preg_split('/' . $leftq . '.*?(?<!literal)' . $rightq . '/', $data);

        $max_tags = count($tags);

        $compiled_tags = $this->_compile($tags, $max_tags);
        
        $data = $this->_assemble($text, $compiled_tags, $max_tags);
        
        $this->_flexyEmbed($data, 1);

        // finalizing conversion
        $data = $this->_flexyRaw($data);
        $data = $this->_flexyIgnore($data);
        $data = $this->_smartyInc($data);
        
        return $data;        
    }
    
    function _compile($tags, $max_tags)
    {
        $compiled_tags = array();
        for ($i = 0; $i < $max_tags; $i++) {
            $compiled_tags[] = $this->_compileTag($tags[$i]);
        }
        return $compiled_tags;
    }

    function _assemble($text, $compiled_tags, $max_tags)
    {
        $data = '';
        for ($i = 0; $i < $max_tags; $i++) {
            $data .= $text[$i].$compiled_tags[$i];
        }
        $data .= $text[$i];
        return $data;
    }
    
    function _traverseCh($tag, &$match)
    {
        if ($match) {
            return;
        }
        if (isset($tag->children)) {
            foreach ($tag->children as $c) {
                if (md5(serialize($c)) == $this->md5) {
                    $match = true;
                    return;
                } elseif (isset($c->children)) {
                    $this->_traverseCh($c, $match);
                }
            }
        }
    }
    
    function _traverseFlexyDOM($tag, &$dom)
    {
        if ((is_a($tag, 'HTML_Template_Flexy_Token') || is_a($tag, 'HTML_Template_Flexy_Token_Tag')) && !is_a($tag, 'HTML_Template_Flexy_Token_Text')) {
            //$_tag = clone $tag;
            //unset($_tag->children);
            //$dom[] = $_tag;
            $dom[] = $tag;
            //unset($_tag);
            if ($tag->children) {
                foreach ($tag->children as $ctag) {
                    $this->_traverseFlexyDOM($ctag, $dom);
                }
            }
        }
    }

    function _flexyEmbed(&$data, $mode)
    {
        $this->_traverseFlexyDOM($this->_buildTree($data), $dom = array());

        for ($i = count($dom) - 1; $i >= 0; $i--) {
            if (isset($dom[$i]) && $out = $this->_flexyEmbedTag($dom[$i], $data, $mode)) {
                $data = implode('', $out);
                $this->md5 = md5(serialize($dom[$i]));

                if ($i) {
                    $match = false;
                    $this->_traverseCh($dom[$i-1], $match);
                    if ($match) {
                            $this->_traverseFlexyDOM($this->_buildTree($data), $dom = array());
                            $i = count($dom) - 1;
                    }
                }
            }
        }
    }
    
    function _flexyEmbedTag($tag, &$data, $mode)
    {
        $out = array();
        switch(true) {
        
            /*
            // check comment to Flexy2Smarty::_smartyInc()
            case $mode && isset($tag->tag) && $tag->tag == 'FLEXY:INCLUDE':
                $out = $this->_extractTag($data, $tag);
                $out[TAG] = $this->_smartyInc($tag);
                break;
            */
            
            case $mode && isset($tag->ucAttributes) && in_array('FLEXY:IF', array_keys($tag->ucAttributes)):
                $out = $this->_extractTag($data, $tag);
                $out[TAG] = $this->_smartyIf($tag, $out[TAG]);
                break;

            /*
            case $mode && isset($tag->ucAttributes) && in_array('FLEXY:RAW', array_keys($tag->ucAttributes)):
                $out = $this->_extractTag($data, $tag);
                $out[TAG] = $this->_flexyRaw($tag, $out[TAG]);
                break;
            */

            case !$mode && isset($tag->ucAttributes) && in_array('FLEXY:FOREACH', array_keys($tag->ucAttributes)):
                $out = $this->_extractTag($data, $tag);
                $out[TAG] = $this->_flexyForeach($tag, $out[TAG]);
                break;

            default:
                break;
        }
        return $out;
    }
    
    /**
    * compile a flexy { tag } into a smarty one.
    *
    * @param   string           the tag
    *
    * @return   string      the converted version
    * @access   private
    */
    function _compileTag($str)
    {
        switch(true) {

            case (preg_match('/^foreach:\s*(.+)\s*$/', $str, $matches)):
                $this->stack['foreach']++;
                $this->stack['last'][] = 'foreach';

                $args = explode(',', $matches[1]);
                $smarty_foreach = 'foreach from='.$this->_convertVar($args[0]).' item="'.$args[1].'"';
                if (count($args) == 2) {
                    $this->stack['foreach_vars'][] = array($args[1]);
                    return '{foreach from='.$this->_convertVar($args[0], false).' item="'.$args[1].'"}';
                } elseif (count($args) == 3) {
                    $this->stack['foreach_vars'][] = array($args[2], $args[1]);
                    return '{foreach from='.$this->_convertVar($args[0], false).' item="'.$args[2].'" key="'.$args[1].'"}';
                }

            case (preg_match('/^if:(!)?(.+)$/', $str, $matches)):
                $this->stack['if']++;
                $this->stack['last'][] = 'if';
                if ($smarty_func = $this->_convertFunc($str, false)) {
                    return '{if '.$matches[1].$smarty_func.'}';
                } else {
                    $var = $this->_convertVar($matches[2], false);
                    return '{if '.$matches[1].$var.'}';
                }

            case ($str == 'else:'):
                return '{else}';
                
            case ($str == 'end:'):
                if (!$this->stack['if'] && !$this->stack['foreach']) {
                    break;
                }
                if (end($this->stack['last']) == 'if') {
                    array_pop($this->stack['last']);
                    $this->stack['if']--;
                    return '{/if}';
                } elseif (end($this->stack['last']) == 'foreach') {
                    array_pop($this->stack['last']);
                    $this->stack['foreach']--;
                    array_pop($this->stack['foreach_vars']);
                    return '{/foreach}';
                }

            case ($smarty_func = $this->_convertFunc($str)):
                return $smarty_func;

            default:
                return $this->_convertVar($str);
        }
    }
    
    /**
    * convert a flexy function into a smarty one.
    * 
    * @param   string       the inside of the flexy tag
    *
    * @return   string      a smarty version of it. 
    * @access   private
    */
    function _convertFunc($str, $delim = true)
    {
        if (preg_match('/((?:GLOBALS\.)?'.VF_NAME.'|this\.plugin)\(\s*(.*)\s*\)(:h)?/s', $str, $matches)) {
            if (!empty($matches[2])) {
                $sargs = array();
                $fargs_pre = preg_split('/\s*,\s*/', $matches[2]);
                
                // checking if we split by ',' which is inside #...#
                for ($i = 0; $i < count($fargs_pre); $i++) {
                    $s = $fargs_pre[$i];
                    if ($fargs_pre[$i][0] == '#' && substr($fargs_pre[$i], -1) != '#') {
                        do {
                            $s .= ','.$fargs_pre[++$i];
                        } while (substr($fargs_pre[$i], -1) != '#');
                    }
                    $fargs[] = $s;
                }
                
                foreach ($fargs as $farg) {
                    $sargs[] = $this->_convertVar($farg, false);
                }
                
                // {this.plugin(#foo#,bar):h}
                if ($matches[1] == 'this.plugin') {
                    $func = array_shift($sargs);
                }
                $sarg = implode(',', $sargs);
            } else {
                $sarg = $matches[2];            
            }

            // modificator
            $mod = '';
            if (!isset($matches[3])) {
                $mod = '|escape';
            }
            
            if ($matches[1] == 'this.plugin') {
                $tag = '$result->'.substr($func, 1, -1).'('.$sarg.')'.$mod;
            } elseif (strpos($matches[1], 'GLOBALS.') !== false) {
                $tag = str_replace('GLOBALS.', '', $matches[1]).'('.$sarg.')'.$mod;
            } else {
                $tag = '$result->'.$matches[1].'('.$sarg.')'.$mod;
            }
            if ($delim) {
                $tag = '{'.$tag.'}';                
            }
            return $tag;
        }
        return false;
    }
    
    /**
    * convert a flexy var into a smarty one.
    *
    * @param   str          the inside of the flexy tag
    * @param   delim        wrap with {}
    *
    * @return   string      a smarty version of it.
    * @access   private
    */
    function _convertVar($str, $delim = true) 
    {
        // flexy constant like #foobar#
        if ($str[0] == '#' && $str[strlen($str) - 1] == '#') {
            return '"'.str_replace('"', '\"', substr($str, 1, strlen($str) - 2)).'"';
        }
        
        // modificator, NB! we don't need it anyway if $delim is false
        if (substr($str, -2) == ':h') {
            $mod = '';
            $str = substr($str, 0, -2);
        } else {
            $mod = '|escape';
        }
        
        // keeps in mind this conversions
        // aaaa.bbbb  => aaaa->bbbb
        // aaaa[bbbb] => aaaa.bbbb
        $str = str_replace('.', '->', $str);
        $str = preg_replace('/\[([^\]]+)\]/', '.\\1', $str);

        // checking variables in foreach scope
        if ($this->stack['foreach'] && $this->_chkForeachStack($this->stack['foreach_vars'], $str)) {
            if ($delim) {
                return '{$'.$str.$mod.'}';
            } else {
                return '$'.$str;
            }
        }
        
        if ($delim) {
            return '{$result->'.$str.$mod.'}';
        } else {
            return '$result->'.$str;
        }
    }

    function _chkForeachStack($stack, $str)
    {
        while ($foreach_vars = array_pop($stack)) {
            foreach ($foreach_vars as $v) {
                if ($v == $this->_baseVar($str)) {
                    return true;
                }
            }
        }
        return false;
    }

    function _baseVar($var)
    {    
        if (false !== ($pos = strpos($var, '->')) || false !== ($pos = strpos($var, '.'))) {
            return substr($var, 0, $pos);
        }
        return $var;
    }

    /**
    * Run a Flexy Tokenizer and Store its results and return the tree.
    * It should build a DOM Tree of the HTML 
    * 
    * @param string $data data to parse
    * @access private
    * @return base token (really a dummy token, which contains the tree)
    */
    function _buildTree($data)
    {
        require_once 'HTML/Template/Flexy.php';
        require_once 'HTML/Template/Flexy/Tree.php';

        $tree = HTML_Template_Flexy_Tree::construct($data);
        
        return $tree;
    }

    /**
    * slice $data into three strings, medium contains html tag needed to be replaced
    * 
    * @param string $data flexy template
    * @param object $tag tag needed to be replaced
    * @access private
    * @return array
    */
    function _extractTag($data, $tag)
    {
        $out = array();
        $b   = strrpos(substr($data, 0, $tag->charPos), '<'); // position where tag begins

        $out[HEAD] = substr($data, 0, $b);
        
        if (isset($tag->ucAttributes['/'])) { // in case tag doesn't having closing pair
            $out[TAG]  = substr($data, $b, $tag->charPos - $b + 2);
            $out[TAIL] = substr($data, $tag->charPos + 2, strlen($data) - $tag->charPos);
        } else { // and in case it does
            $e = strpos($data, '>', $tag->close->charPos);
            $out[TAG]  = substr($data, $b, $e - $b + 1);
            $out[TAIL] = substr($data, $e + 1);
        }
        return $out;
    }

    function _extractAttr($tag, $htmltag, $attr, $replacement = '')
    {
        return preg_replace('/(<\s*'.$tag->tag.'.+?)('.$attr.'='.preg_quote($tag->ucAttributes[$attr]).')([^>]*>)/i', "\\1$replacement\\3", $htmltag);
    }
    
    /*
    function _smartyInc($tag)
    {
        return '{include file='.$tag->ucAttributes['SRC'].'}';
    }
    */

    /**
    * convert flexy include to smarty include  
    * since parsing template isn't strict html - Flexy parser (which is called in Flexy2Smarty::_buildTree())
    * fails to deal with it in Flexy2Smarty::_flexyEmbedTag(), so we do just replacing after all
    * and original Flexy2Smarty::_smartyInc() is commented now
    *
    *
    * @param string $data template
    * @access private
    * @return parsed template
    */
    function _smartyInc($data)
    {
        return preg_replace('/<flexy:include src="([^"]*)" \/>/i', '{include file="\\1"}', $data);
    }

    function _smartyIf($tag, $htmltag)
    {
        $neg = '';
        $attr = substr($tag->ucAttributes['FLEXY:IF'], 1, -1);
        if ($attr[0] == '!') {
            $neg = '!';
            $attr = substr($attr, 1);
        }
        if (!$clause = $this->_convertFunc($attr, false)) {
            $clause = $this->_convertVar($attr, false);
        }
        return '{if '.$neg.$clause.'}'.$this->_extractAttr($tag, $htmltag, 'FLEXY:IF').'{/if}';
    }
    
    /*
    function _flexyRaw($tag, $htmltag)
    {
        preg_match('/flexy:raw="([^"]*)"/i', $htmltag, $matches);
        return $this->_extractAttr($tag, $htmltag, 'FLEXY:RAW', $matches[1]);
    }
    */

    function _flexyRaw($data)
    {
        return preg_replace('/flexy:raw="([^"]*)"/i', "\\1", $data);
    }
    
    function _flexyIgnore($data)
    {
        return str_replace('flexy:ignore', '', $data);
    }
    
    function _flexyForeach($tag, $htmltag)
    {
        $args = explode(',', substr($tag->ucAttributes['FLEXY:FOREACH'], 1, -1));
        
        $out = '{foreach:'.implode(',', $args).'}';

        $out .= "\n".$this->_extractAttr($tag, $htmltag, 'FLEXY:FOREACH');
        $out .= "\n".'{end:}';
        return $out;
    }
}
