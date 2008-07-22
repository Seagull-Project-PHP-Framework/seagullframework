<?php

require_once SGL_CORE_DIR . '/Translation.php';

/**
 * Test suite.
 *
 * @package Test
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class TranslationTest extends UnitTestCase
{
    function TranslationTest()
    {
        $this->UnitTestCase('Translation Test');

        // enforce t2 usage
        $c = &SGL_Config::singleton();
        $c->set('translation', array('container' => 'db'));

        // common data for all case
        $this->trans = &SGL_Translation::singleton('admin');

        // use connection to simpletest base
        $this->trans->storage->db = &SGL_DB::singleton();
        if (in_array($this->trans->storage->db->phptype, array('mysqli', 'mysqli_SGL'))) {
            $this->trans->storage->db->phptype = 'mysql';
        }

        // languages to use in case
        $this->aLangs = array(
            'ru-utf-8',
            'en-iso-8859-15'
        );
    }

    function tearDown()
    {
        // drop languages
        $aLangs = $this->trans->getLangs('ids');
        foreach ($aLangs as $langId) {
            $ok = $this->trans->removeLang($langId, true);
        }
        // restore config
        $c = &SGL_Config::singleton();
        foreach ($this->oldConf as $section => $aParam) {
            foreach ($aParam as $key => $value) {
                $c->set($section, array($key => $value));
            }
        }
    }

    function setUp()
    {
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        // clear current tables
        $tables = &$this->trans->storage->options['strings_tables'];
        $tables = array();

        $aLangs = SGL_Util::getLangsDescriptionMap();
        // add languages
        foreach ($this->aLangs as $langId) {
            $aLang = array(
                'lang_id'    => SGL_Translation::transformLangID($langId),
                'table_name' => $conf['translation']['tablePrefix'] .
                    '_' . SGL_Translation::transformLangID($langId),
                'meta'       => '',
                'name'       => $aLangs[$langId],
                'error_text' => 'not available',
                'encoding'   => substr($langId, strpos($langId, '-') + 1)
            );
            $ok = $this->trans->addLang($aLang);
            $tables[$aLang['lang_id']] = $aLang['table_name'];
        }

        // change conf value to current languages
        $this->oldConf['translation']['installedLanguages'] =
            $conf['translation']['installedLanguages'];
        $callback = array('SGL_Translation', 'transformLangID');
        $aConfigLangs = array_map($callback, $this->aLangs);
        $c->set('translation',
            array('installedLanguages' => implode(',', $aConfigLangs)));

        // make sure user singleton has same options and connection
        $foo = &SGL_Translation::singleton();
        $foo->storage->db = &$this->trans->storage->db;
        $foo->storage->options = $this->trans->storage->options;
    }

    function _fillTables($aPages, $aStrings)
    {
        foreach ($aPages as $pageId) {
            foreach ($aStrings as $stringId) {
                $aTrans    = array();
                $oneExists = false;
                for ($i = 0, $langCount = count($this->aLangs); $i < $langCount; $i++) {
                    // make insertion random
                    if (rand(1, $langCount) == 1) {
                        // translation for at least one languge should exist
                        if (($i + 1 != $langCount) || $oneExists) {
                            continue;
                        }
                    }
                    $langId = SGL_Translation::transformLangID($this->aLangs[$i]);

                    // generate translation string
                    $aTrans[$langId] = $pageId . '_' . $stringId . '_' . $langId;
                    $oneExists = true;
                }
                $ok = $this->trans->add($stringId, $pageId, $aTrans);
            }
        }
    }

    function _printAll()
    {
        $aPages = $this->trans->getPageNames();
        foreach ($aPages as $pageId) {
            foreach ($this->aLangs as $langId) {
                $aStrings = SGL_Translation::getTranslations($pageId, $langId);
                echo '<pre>';
                print_r($aStrings);
                echo '</pre>';
            }
        }
    }

    function testRemoveTranslations()
    {
        $aPages   = array('seagull', 'rocks');
        $aStrings = array('who', 'let', 'the', 'dogs', 'out');

        // fill languages with translation
        $this->_fillTables($aPages, $aStrings);

        $aPages = $this->trans->getPageNames();
        $this->assertTrue(count($aPages) == 2);

        $ret = SGL_Translation::removeTranslations('seagull');
        $this->assertTrue($ret);

        $ret = SGL_Translation::removeTranslations('seagull');
        $this->assertFalse($ret);

        $aPages = $this->trans->getPageNames();
        $this->assertFalse(in_array('seagull', $aPages));

        $aPages = $this->trans->getPageNames();
        $this->assertTrue(in_array('rocks', $aPages));

        $ret = SGL_Translation::removeTranslations('rocks');
        $this->assertTrue($ret);

        $aPages = $this->trans->getPageNames();
        $this->assertFalse($aPages);
    }
}

?>