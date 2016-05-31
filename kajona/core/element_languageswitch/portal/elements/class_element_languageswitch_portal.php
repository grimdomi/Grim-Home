<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_languageswitch_portal.php 5903 2013-09-30 13:40:29Z sidler $                         *
********************************************************************************************************/

/**
 * Portal-Class of the picture element
 *
 * @package element_languageswitch
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class class_element_languageswitch_portal extends class_element_portal implements interface_portal_element {

    /**
     * Returns the ready switch-htmlcode
     *
     * @return string
     */
    public function loadData() {

        //fallback for old elements not yet using the template
        if(!isset($this->arrElementData["char1"]) || $this->arrElementData["char1"] == "")
            $this->arrElementData["char1"] = "languageswitch.tpl";

        $arrObjLanguages = class_module_languages_language::getObjectList(true);

        //load the languageset in order to generate more specific switches
        $objLanguageset = class_module_languages_languageset::getLanguagesetForSystemid($this->getParam("systemid"));

        //Iterate over all languages
        $strRows = "";
        foreach($arrObjLanguages as $objOneLanguage) {
            //Check, if the current page has elements
            $objPage = class_module_pages_page::getPageByName($this->getPagename());
            $objPage->setStrLanguage($objOneLanguage->getStrName());
            if($objPage === null)
                continue;

            if((int)$objPage->getNumberOfElementsOnPage(true) == 0)
                continue;


            $strTargetSystemid = null;
            if($objLanguageset != null) {
                $strTargetSystemid = $objLanguageset->getSystemidForLanguageid($objOneLanguage->getSystemid());
            }

            //the languageswitch is content aware. check if the target id is a news-entry
            $strSeoAddon = "";
            if(validateSystemid($strTargetSystemid)) {
                $objRecord = class_objectfactory::getInstance()->getObject($strTargetSystemid);
                $strSeoAddon = $objRecord->getStrDisplayName();
            }

            //and the link
            $arrTemplate = array();
            if($strTargetSystemid === null)
                $arrTemplate["href"] = getLinkPortalHref($objPage->getStrName(), "", "", "", "", $objOneLanguage->getStrName(), $strSeoAddon);
            else
                $arrTemplate["href"] = getLinkPortalHref($objPage->getStrName(), "", $this->getAction(), "", $strTargetSystemid, $objOneLanguage->getStrName(), $strSeoAddon);

            $arrTemplate["langname_short"] = $objOneLanguage->getStrName();
            $arrTemplate["langname_long"] = $this->getLang("lang_".$objOneLanguage->getStrName());

            $strTemplateRowID = $this->objTemplate->readTemplate("/element_languageswitch/".$this->arrElementData["char1"], "languageswitch_entry");
            $strTemplateActiveRowID = $this->objTemplate->readTemplate("/element_languageswitch/".$this->arrElementData["char1"], "languageswitch_entry_active");

            if($objOneLanguage->getStrName() == $this->getStrPortalLanguage())
                $strRows .= $this->fillTemplate($arrTemplate, $strTemplateActiveRowID);
            else
                $strRows .= $this->fillTemplate($arrTemplate, $strTemplateRowID);

        }

        $strTemplateWrapperID = $this->objTemplate->readTemplate("/element_languageswitch/".$this->arrElementData["char1"], "languageswitch_wrapper");
        return $this->fillTemplate(array("languageswitch_entries" => $strRows), $strTemplateWrapperID);
    }

}
