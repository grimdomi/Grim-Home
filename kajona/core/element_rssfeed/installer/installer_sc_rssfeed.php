<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: installer_sc_rssfeed.php 5909 2013-10-01 12:30:12Z sidler $                              *
********************************************************************************************************/


/**
 * Installer of the rssfeed samplecontent
 *
 * @package element_rssfeed
 */
class class_installer_sc_rssfeed implements interface_sc_installer  {

    /**
     * @var class_db
     */
    private $objDB;
    private $strContentLanguage;

    /**
     * Does the hard work: installs the module and registers needed constants
     *
     */
    public function install() {
        $strReturn = "";

        //fetch navifolder-id
        $strNaviFolderId = "";
        $arrFolder = class_module_pages_folder::getFolderList();
        foreach($arrFolder as $objOneFolder)
            if($objOneFolder->getStrName() == "mainnavigation")
                $strNaviFolderId = $objOneFolder->getSystemid();

        $strReturn .= "Creating new page rssfeed...\n";

        $objPage = new class_module_pages_page();
        $objPage->setStrName("rssfeed");
        $objPage->setStrBrowsername("Rssfeed");
        $objPage->setStrTemplate("standard.tpl");
        $objPage->updateObjectToDb($strNaviFolderId);

        $strPageId = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strPageId."\n";
        $strReturn .= "Adding pagelement to new page\n";

        if(class_module_pages_element::getElement("rssfeed") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("special_news|guestbook|downloads|gallery|galleryRandom|form|tellafriend|maps|search|navigation|faqs|postacomment|votings|userlist|rssfeed|tagto|portallogin|portalregistration|portalupload|directorybrowser|lastmodified|tagcloud|downloadstoplist|flash|mediaplayer|tags|eventmanager");
            $objPagelement->setStrName("special");
            $objPagelement->setStrElement("rssfeed");
            $objPagelement->updateObjectToDb($strPageId);
            $strElementId = $objPagelement->getSystemid();

            $arrParams = array();
            if($this->strContentLanguage == "de") {
                $arrParams = array("rssfeed.tpl", 10, "http://www.kajona.de/kajona_news.rss", $strElementId);
            }
            else {
                $arrParams = array("rssfeed.tpl", 10, "http://www.kajona.de/kajona_news_en.rss", $strElementId);
            }

            $strQuery = "UPDATE "._dbprefix_."element_universal
                            SET char1 = ?,
                                ".$this->objDB->encloseColumnName("int1")." = ?,
                                char2 = ?
                            WHERE content_id = ?";
            if($this->objDB->_pQuery($strQuery, $arrParams))
                $strReturn .= "Rssfeed element created.\n";
            else
                $strReturn .= "Error creating Rssfeed element.\n";

        }

        $strReturn .= "Adding headline-element to new page\n";
        if(class_module_pages_element::getElement("row") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("headline_row");
            $objPagelement->setStrName("headline");
            $objPagelement->setStrElement("row");
            $objPagelement->updateObjectToDb($strPageId);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                SET paragraph_title = ?
                                WHERE content_id = ?";
            if($this->objDB->_pQuery($strQuery, array("Rssfeed", $strElementId)))
                $strReturn .= "Headline element created.\n";
            else
                $strReturn .= "Error creating headline element.\n";
        }

        return $strReturn;
    }

    public function setObjDb($objDb) {
        $this->objDB = $objDb;
    }

    public function setStrContentlanguage($strContentlanguage) {
        $this->strContentLanguage = $strContentlanguage;
    }

    public function getCorrespondingModule() {
        return "pages";
    }

}
