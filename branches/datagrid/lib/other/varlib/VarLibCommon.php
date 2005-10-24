<?php
/**
 * Varico Lib Common
 * Common class for all varico modules
 * This class contains alle methods without DB and dialog methods
 * @since SŁ2.0
 * @author Varico
 */
class VarLibCommon {

    function varLibCommon() {

    }
    /**
     * This function return array accept dataGrid lang
     * @return array
     */
    function getAcceptDataGridLang() 
    {
        if (isset($GLOBALS['_SERVER']['HTTP_ACCEPT_LANGUAGE'])) {
            $acceptLangDataGrid = explode(',', $GLOBALS['_SERVER']['HTTP_ACCEPT_LANGUAGE']);
            $acceptLangDataBase = explode(',', ACCEPT_LANG_DB);

            foreach($acceptLangDataGrid as $langType) {
                $acceptArray = explode(';', $langType);
                $acceptLangList[] = $acceptArray[0];
            }

            foreach($acceptLangList as $acceptLang) {
                if (in_array($acceptLang, $acceptLangDataBase)) {
                    $language[] = $acceptLang;
                }
            }
        }
        if (isset($language)) {
            return $language;
        }
    }

    /**
     * convertQueryMultiLanguageFields
     * convert query fields mark as @text@ on the right language field
     * @param string $query
     */
    function convertQueryMultiLanguageFields($query) 
    {
        //first language is default
        $multiLanguageDBFieldsList = explode(',', ACCEPT_LANG_DB);
        $acceptLang = VarLibCommon::getAcceptDataGridLang();
        if (isset($_SESSION['aPrefs']['language'])) {
            $nativeLang = substr($_SESSION['aPrefs']['language'], 0, 2);
        } else {
            $nativeLang = '';
        }

        if(in_array($nativeLang, $multiLanguageDBFieldsList)) {
            $lang = $nativeLang;
        } else {
            $lang = $multiLanguageDBFieldsList[0];
        }
        while (ereg("\@[A-Za-z0-9_]*\@", $query, $fields)) {
            $fieldName = str_replace('@', '', $fields[0]);
            $nativeColumn  = $fieldName . '__' . $lang . ' AS ' . $fieldName;
            $query = str_replace($fields[0], $nativeColumn, $query);
        }
        return $query;
    }

    /**
     * Function quickFormFlexyCompile
     * @param Object $form references to form object
     * @param String $templateFileName
     * @return Object variable to template      */
    function quickFormFlexyCompile(& $form, $templateFileName) 
    {
        // setup a template object
        $options = &PEAR::getStaticProperty('HTML_Template_Flexy','options');
        $module = $GLOBALS['_SGL']['REQUEST']['moduleName'];
        $theme = $GLOBALS['HTTP_SESSION_VARS']['aPrefs']['theme'];
        $options = array(
               'templateDir'       =>  SGL_THEME_DIR . '/' . $theme . '/' . $module . PATH_SEPARATOR .
                                       SGL_THEME_DIR . '/default/' . $module . PATH_SEPARATOR .
                                       SGL_THEME_DIR . '/' . $theme . '/default'. PATH_SEPARATOR .
                                       SGL_THEME_DIR . '/default/default',
               'templateDirOrder'  => 'reverse',
               'multiSource'       => true,
               'compileDir'        => SGL_CACHE_DIR . '/tmpl/' . $theme,
               'forceCompile'      => SGL_FLEXY_FORCE_COMPILE,
               'debug'             => SGL_FLEXY_DEBUG,
               'allowPHP'          => SGL_FLEXY_ALLOW_PHP,
               'filters'           => SGL_FLEXY_FILTERS,
               'locale'            => SGL_FLEXY_LOCALE,
               'compiler'          => SGL_FLEXY_COMPILER,
               'valid_functions'   => SGL_FLEXY_VALID_FNS,
               'flexyIgnore'       => SGL_FLEXY_IGNORE,
               'globals'           => true,
               'globalfunctions'   => SGL_FLEXY_GLOBAL_FNS,
        );
        $template = new HTML_Template_Flexy($options);
        $renderer =& new HTML_QuickForm_Renderer_ObjectFlexy($template);
        $form->accept($renderer);

        $view = new StdClass;
        $view->form = $renderer->toObject();
        $template->compile($templateFileName);
        return $renderer->toObject();
    }

    /**
     * generateMultiLanguageField
     * create form fields for all accept languages
     * @param object $form
     * @param string $type field type
     * @param string $preName prefix of field name from DB
     * @param string $name form field name
     * @param string $label form field label
     * @param array $option form field options
     * @param string $value form field value
     * @param string $ruleMsg
     * @param string $ruleType
     */
    function generateMultiLanguageField(& $form, $type, $preName = '', $name,
                                        $label, $options = array(), $value = array(),
                                        $ruleMsg = '', $ruleType = '') 
    {
        $acceptLanguage = VarLibCommon::getAcceptDataGridLang();
        foreach($acceptLanguage as $lang) {
            $shortFieldName = $name . $lang;
            $fieldTitle = $label . " [$lang]";
            if (isset($preName)) {
                $fullFieldName = $preName . '[' . $name . $lang . ']';
            } else {
                $fullFieldName = $name . $lang;
            }
            // KK  VIP 27296
            if (!empty($value)) {
                $fieldValue = $value->$shortFieldName;
                $options['value'] = $fieldValue;
            } else {
                $options = array();
            }

            $form->addElement($type, $fullFieldName, $fieldTitle, $options);
            if (isset($ruleMsg) && isset($ruleType)) {
                $form->addRule($fullFieldName, $ruleMsg, $ruleType);
            }
        }
    }

    /**
     * provinceList
     * @return array list of polish province
     */
    function provinceList()      
    {
        $provinceList = explode(',', PROVINCE);
        return $provinceList;
    }
    
    
}
?>
