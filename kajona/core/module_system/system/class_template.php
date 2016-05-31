<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_template.php 5818 2013-09-06 12:38:33Z sidler $                                           *
********************************************************************************************************/
/**
 * This class does all the template stuff as loading, parsing, etc..
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_template {

    private $arrCacheTemplates = array();
    private $arrCacheTemplateSections = array();

    private $strTempTemplate = "";

    private static $objTemplate = null;

    /**
     * @var class_apc_cache
     */
    private $objApcCache;
    private $bitSaveToCacheRequired = false;

    /**
     * Constructor

     */
    private function __construct() {
        $this->objApcCache = class_apc_cache::getInstance();

        //any caches to load from session?
        $this->arrCacheTemplates = $this->objApcCache->getValue("templateSessionCacheTemplate", $this->arrCacheTemplates);
        $this->arrCacheTemplateSections = $this->objApcCache->getValue("templateSessionCacheTemplateSections", $this->arrCacheTemplateSections);

    }

    public function __destruct() {
        //save cache to apc
        if($this->bitSaveToCacheRequired) {
            $this->objApcCache->addValue("templateSessionCacheTemplate", $this->arrCacheTemplates, class_config::getInstance()->getConfig("templatecachetime"));
            $this->objApcCache->addValue("templateSessionCacheTemplateSections", $this->arrCacheTemplateSections, class_config::getInstance()->getConfig("templatecachetime"));
        }
    }


    /**
     * Returns one instance of the template object, using a singleton pattern
     *
     * @return object The template object
     */
    public static function getInstance() {
        if(self::$objTemplate == null) {
            self::$objTemplate = new class_template();
        }

        return self::$objTemplate;
    }

    /**
     * Reads a template from the filesystem
     *
     * @param string $strName
     * @param string $strSection
     * @param bool $bitForce Force the passed template name, not adding the current area
     * @param bool $bitThrowErrors If set true, the method throws exceptions in case of errors
     *
     * @return string The identifier for further actions
     * @throws class_exception
     */
    public function readTemplate($strName, $strSection = "", $bitForce = false, $bitThrowErrors = false) {

        //avoid directory traversals
        $strName = removeDirectoryTraversals($strName);
        if(!$bitForce) {
            try {
                $strName = class_resourceloader::getInstance()->getTemplate($strName);
            }
            catch(class_exception $objEx) {
                    //try to resolve the file in the current skin
                    if(is_file(_realpath_.class_adminskin_helper::getPathForSkin(class_session::getInstance()->getAdminSkin()).$strName))
                        $strName = class_adminskin_helper::getPathForSkin(class_session::getInstance()->getAdminSkin()).$strName;
                    else
                        throw $objEx;
            }

        }

        $bitKnownTemplate = false;
        //Is this template already in the cache?
        $strCacheTemplate = md5($strName);
        $strCacheSection = md5($strName.$strSection);

        if(isset($this->arrCacheTemplateSections[$strCacheSection]))
            return $strCacheSection;

        $this->bitSaveToCacheRequired = true;

        if(isset($this->arrCacheTemplates[$strCacheTemplate]))
            $bitKnownTemplate = true;

        if(!$bitKnownTemplate) {
            //We have to read the whole template from the filesystem
            if(uniSubstr($strName, -4) == ".tpl" && is_file(_realpath_."/".$strName)) {
                $strTemplate = file_get_contents(_realpath_."/".$strName);
                //Saving to the cache
                $this->arrCacheTemplates[$strCacheTemplate] = $strTemplate;
            }
            else {
                $strTemplate = "Template ".$strName." not found!";
                if($bitThrowErrors)
                    throw new class_exception("Template ".$strName." not found!", class_exception::$level_FATALERROR);
            }
        }
        else
            $strTemplate = $this->arrCacheTemplates[$strCacheTemplate];

        //Now we have to extract the section
        if($strSection != "") {
            //find opening tag
            $intStart = uniStrpos($strTemplate, "<".$strSection.">");

            //find closing tag
            $intEnd = uniStrpos($strTemplate, "</".$strSection.">");
            $intEnd = $intEnd - $intStart;

            if($intStart !== false && $intEnd !== false) {
                //delete substring before and after
                $strTemplate = uniSubstr($strTemplate, $intStart, $intEnd);

                $strTemplate = str_replace("<".$strSection.">", "", $strTemplate);
                $strTemplate = str_replace("</".$strSection.">", "", $strTemplate);
            }
            else
                $strTemplate = "";
        }

        //Saving the section to the cache
        $this->arrCacheTemplateSections[$strCacheSection] = $strTemplate;

        return $strCacheSection;
    }

    /**
     * Fills a template with values passed in an array.
     * As an optional parameter an instance of class_lang_wrapper can be passed
     * to fill placeholders matching the schema %%lang_...%% automatically.
     *
     * @param mixed $arrContent
     * @param string $strIdentifier
     * @param bool $bitRemovePlaceholder
     *
     * @return string The filled template
     */
    public function fillTemplate($arrContent, $strIdentifier, $bitRemovePlaceholder = true) {
        if(isset($this->arrCacheTemplateSections[$strIdentifier]))
            $strTemplate = $this->arrCacheTemplateSections[$strIdentifier];
        else
            $strTemplate = "Load template first!";

        if(count($arrContent) >= 1) {
            foreach($arrContent as $strPlaceholder => $strContent) {
                $strTemplate = str_replace("%%".$strPlaceholder."%%", $strContent."%%".$strPlaceholder."%%", $strTemplate);
            }
        }

        if($bitRemovePlaceholder)
            $strTemplate = $this->deletePlaceholderRaw($strTemplate);
        return $strTemplate;
    }


    /**
     * Fills the current temp-template with the passed values.
     * <b>Make sure to have the wanted template loaded before by using setTemplate()</b>
     *
     * @param mixed $arrContent
     * @param bool $bitRemovePlaceholder
     *
     * @return string The filled template
     */
    public function fillCurrentTemplate($arrContent, $bitRemovePlaceholder = true) {
        $strTemplate = $this->strTempTemplate;
        if(count($arrContent) >= 1) {
            foreach($arrContent as $strPlaceholder => $strContent) {
                $strTemplate = str_replace("%%".$strPlaceholder."%%", $strContent."%%".$strPlaceholder."%%", $strTemplate);
            }
        }
        if($bitRemovePlaceholder)
            $strTemplate = $this->deletePlaceholderRaw($strTemplate);
        return $strTemplate;
    }


    /**
     * Replaces constants in the template set by setTemplate()
     *
     * @deprecated use scriptlets instead
     */
    public function fillConstants() {
        $objConstantScriptlet = new class_scriptlet_xconstants();
        $this->strTempTemplate = $objConstantScriptlet->processContent($this->strTempTemplate);
    }

    /**
     * Deletes placeholder in the template set by setTemplate()
     */
    public function deletePlaceholder() {
        $this->strTempTemplate = preg_replace("^%%([A-Za-z0-9_\|]*)%%^", "", $this->strTempTemplate);
    }

    /**
     * Deletes placeholder in the string
     *
     * @param string $strText
     *
     * @return string
     */
    private function deletePlaceholderRaw($strText) {
        return preg_replace("^%%([A-Za-z0-9_\|]*)%%^", "", $strText);
    }

    /**
     * Returns the template set by setTemplate() and sets its back to ""
     *
     * @return string
     */
    public function getTemplate() {
        $strTemp = $this->strTempTemplate;
        $this->strTempTemplate = "";
        return $strTemp;
    }

    /**
     * Checks if the template referenced by the identifier contains the placeholder provided
     * by the second param.
     *
     * @param string $strIdentifier
     * @param string $strPlaceholdername
     *
     * @return bool
     * @deprecated replaced by containsPlaceholder
     * @see class_template::containsPlaceholder
     */
    public function containesPlaceholder($strIdentifier, $strPlaceholdername) {
        return $this->containsPlaceholder($strIdentifier, $strPlaceholdername);
    }


    /**
     * Checks if the template referenced by the identifier contains the placeholder provided
     * by the second param.
     *
     * @param string $strIdentifier
     * @param string $strPlaceholdername
     *
     * @return bool
     */
    public function containsPlaceholder($strIdentifier, $strPlaceholdername) {
        $arrElements = $this->getElements($strIdentifier);
        foreach($arrElements as $arrSinglePlaceholder)
            if($arrSinglePlaceholder["placeholder"] == $strPlaceholdername)
                return true;

        return false;
    }

    /**
     * Checks if the template referenced by the given identifier provides the section passed.
     * @param $strIdentifier
     * @param $strSection
     *
     * @return bool
     */
    public function containsSection($strIdentifier, $strSection) {
        return (isset($this->arrCacheTemplates[$strIdentifier])
            && uniStrpos($this->arrCacheTemplates[$strIdentifier], "<".$strSection.">") !== false
            && uniStrpos($this->arrCacheTemplates[$strIdentifier], "</".$strSection.">") !== false
        );
    }

    /**
     * Returns the elements in a given template
     *
     * @param string $strIdentifier
     * @param int $intMode 0 = regular page, 1 = master page
     *
     * @return mixed
     */
    public function getElements($strIdentifier, $intMode = 0) {
        $arrReturn = array();

        if(isset($this->arrCacheTemplateSections[$strIdentifier]))
            $strTemplate = $this->arrCacheTemplateSections[$strIdentifier];
        else
            return array();

        //search placeholders
        $arrTemp = array();
        preg_match_all("'(%%([A-Za-z0-9_]+?))+?\_([A-Za-z0-9_\|]+?)%%'i", $strTemplate, $arrTemp);


        $intCounter = 0;
        if(count($arrTemp[0]) > 0) {
            foreach($arrTemp[0] as $strPlacehoder) {
                //regular page
                if($intMode != 1) {
                    if(uniStrpos($strPlacehoder, "master") === false) {
                        $strTemp = uniSubstr($strPlacehoder, 2, -2);
                        $arrTemp = explode("_", $strTemp);
                        //are there any pipes?
                        if(uniStrpos($arrTemp[1], "|") !== false) {
                            $arrElementTypes = explode("|", $arrTemp[1]);
                            $intCount2 = 0;
                            $arrReturn[$intCounter]["placeholder"] = $strTemp;

                            foreach($arrElementTypes as $strOneElementType) {
                                $arrReturn[$intCounter]["elementlist"][$intCount2]["name"] = $arrTemp[0];
                                $arrReturn[$intCounter]["elementlist"][$intCount2]["element"] = $strOneElementType;
                                $intCount2++;
                            }
                            $intCounter++;
                        }
                        else {
                            $arrReturn[$intCounter]["placeholder"] = $strTemp;
                            $arrReturn[$intCounter]["elementlist"][0]["name"] = $arrTemp[0];
                            $arrReturn[$intCounter]["elementlist"][0]["element"] = $arrTemp[1];
                            $intCounter++;
                        }
                    }
                }
                //master page
                else {
                    $strTemp = uniSubstr($strPlacehoder, 2, -2);
                    $arrTemp = explode("_", $strTemp);
                    //are there any pipes?
                    if(uniStrpos($arrTemp[1], "|") !== false) {
                        $arrElementTypes = explode("|", $arrTemp[1]);
                        $arrReturn[$intCounter]["placeholder"] = $strTemp;
                        $intCount2 = 0;
                        foreach($arrElementTypes as $strOneElementType) {
                            $arrReturn[$intCounter]["elementlist"][$intCount2]["name"] = $arrTemp[0];
                            $arrReturn[$intCounter]["elementlist"][$intCount2]["element"] = $strOneElementType;
                            $intCount2++;
                        }
                        $intCounter++;
                    }
                    else {
                        $arrReturn[$intCounter]["placeholder"] = $strTemp;
                        $arrReturn[$intCounter]["elementlist"][0]["name"] = $arrTemp[0];
                        $arrReturn[$intCounter]["elementlist"][0]["element"] = $arrTemp[1];
                        $intCounter++;
                    }
                }
            }
        }

        return $arrReturn;
    }

    /**
     * Sets the passed template as the current temp-template
     *
     * @param string $strTemplate
     */
    public function setTemplate($strTemplate) {
        $this->strTempTemplate = $strTemplate;
    }

    public function isValidTemplate($strTemplateId) {
        return isset($this->arrCacheTemplateSections[$strTemplateId]) && $this->arrCacheTemplateSections[$strTemplateId] != "";
    }

    /**
     * Returns the number of cached template sections
     *
     * @return int
     */
    public function getNumberCacheSize() {
        return count($this->arrCacheTemplateSections);
    }
}

