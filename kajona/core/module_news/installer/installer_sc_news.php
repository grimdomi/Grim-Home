<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: installer_sc_news.php 5962 2013-10-18 13:41:48Z sidler $                                       *
********************************************************************************************************/


/**
 * Installer of the news samplecontenht
 *
 * @package module_news
 */
class class_installer_sc_news implements interface_sc_installer  {
    /**
     * @var class_db
     */
    private $objDB;
    private $strContentLanguage;

    private $strIndexID = "";
    private $strMasterID = "";

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

        //search the index page
        $objIndex = class_module_pages_page::getPageByName("index");
        $objMaster = class_module_pages_page::getPageByName("master");
        if($objIndex != null)
            $this->strIndexID = $objIndex->getSystemid();

        if($objMaster != null)
            $this->strMasterID = $objMaster->getSystemid();

        $strReturn .= "Creating new category...\n";
        $objNewsCategory = new class_module_news_category();
        $objNewsCategory->setStrTitle("TOP-News");
        $objNewsCategory->updateObjectToDb();
        $strCategoryID = $objNewsCategory->getSystemid();
        $strReturn .= "ID of new category: ".$strCategoryID."\n";
        $strReturn .= "Creating news\n";
        $objNews = new class_module_news_news();

        if($this->strContentLanguage == "de") {
            $objNews->setStrTitle("Erfolgreich installiert");
            $objNews->setStrText("Eine weitere Installation von Kajona V3 war erfolgreich. Für weitere Infomationen zu Kajona besuchen Sie www.kajona.de.");
            $objNews->setStrIntro("Kajona wurde erfolgreich installiert...");
        }
        else {
            $objNews->setStrTitle("Installation successful");
            $objNews->setStrText("Another installation of Kajona was successful. For further information, support or proposals, please visit our website: www.kajona.de");
            $objNews->setStrIntro("Kajona installed successfully...");
        }

        $objNews->setObjDateStart(new class_date());
        $objNews->setArrCats(array($strCategoryID => 1));
        $objNews->setBitUpdateMemberships(true);
        $objNews->updateObjectToDb();
        $strNewsId = $objNews->getSystemid();
        $strReturn .= "ID of new news: ".$strNewsId."\n";


        $strReturn .= "Creating news\n";
        $objNews = new class_module_news_news();

        $objNews->setStrTitle("Sed non enim est");
        $objNews->setStrText("Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non enim est, id hendrerit metus. Sed tempor quam sed ante viverra porta. Quisque sagittis egestas tortor, in euismod sapien iaculis at. Nullam vitae nunc tortor. Mauris justo lectus, bibendum et rutrum id, fringilla eget ipsum. Nullam volutpat sodales mollis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Duis tempor ante eget justo blandit imperdiet. Praesent ut risus tempus metus sagittis fermentum eget eu elit. Mauris consequat ornare massa, a rhoncus enim sodales auctor. Duis lacinia dignissim eros vel mollis. Etiam metus tortor, pellentesque eu ultricies sit amet, elementum et dolor. Proin tincidunt nunc id magna volutpat lobortis. Vivamus metus quam, accumsan eget vestibulum vel, rutrum sit amet mauris. Phasellus lectus leo, vulputate eget molestie et, consectetur nec urna. ");
        $objNews->setStrIntro("Quisque sagittis egestas tortor");

        $objNews->setObjDateStart(new class_date());
        $objNews->setArrCats(array($strCategoryID => 1));
        $objNews->setBitUpdateMemberships(true);
        $objNews->updateObjectToDb();
        $strNewsId = $objNews->getSystemid();
        $strReturn .= "ID of new news: ".$strNewsId."\n";



        $strReturn .= "Adding news element to the master-page...\n";
        if($this->strMasterID != "") {
            
            if(class_module_pages_element::getElement("news") != null) {
                $objPagelement = new class_module_pages_pageelement();
                $objPagelement->setStrPlaceholder("mastertopnews_news");
                $objPagelement->setStrName("mastertopnews");
                $objPagelement->setStrElement("news");
                $objPagelement->updateObjectToDb($this->strMasterID);
                $strElementId = $objPagelement->getSystemid();
                $strQuery = "UPDATE "._dbprefix_."element_news
                                SET news_category=?,
                                    news_view = ?,
                                    news_mode = ?,
                                    news_order = ?,
                                    news_amount = ?,
                                    news_detailspage = ?,
                                    news_template = ?
                                WHERE content_id = ?";
                if($this->objDB->_pQuery($strQuery, array($strCategoryID, 0, 0, 0, 10, "newsdetails", "demo.tpl", $strElementId)))
                    $strReturn .= "Newselement created.\n";
                else
                    $strReturn .= "Error creating newselement.\n";
            }
        }
        $strReturn .= "Creating news-detail\n";
        $objPage = new class_module_pages_page();
        $objPage->setStrName("newsdetails");
        $objPage->setStrBrowsername("News");
        $objPage->setStrTemplate("standard.tpl");
        $objPage->updateObjectToDb();
        $strNewsdetailsId = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strNewsdetailsId."\n";
        $strReturn .= "Adding newsdetails to new page\n";
        
        if(class_module_pages_element::getElement("news") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("special_news|guestbook|downloads|gallery|galleryRandom|form|tellafriend|maps|search|navigation|faqs|postacomment|votings|userlist|rssfeed|tagto|portallogin|portalregistration|portalupload|directorybrowser|lastmodified|tagcloud|downloadstoplist|flash|mediaplayer|tags|eventmanager");
            $objPagelement->setStrName("special");
            $objPagelement->setStrElement("news");
            $objPagelement->updateObjectToDb($strNewsdetailsId);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_news
                            SET news_category=?,
                                news_view = ?,
                                news_mode = ?,
                                news_order = ?,
                                news_amount = ?,
                                news_detailspage = ?,
                                news_template = ?
                            WHERE content_id = ?";
            if($this->objDB->_pQuery($strQuery, array($strCategoryID, 1, 0, 0, 20, "index", "demo.tpl", $strElementId)))
                $strReturn .= "Newselement created.\n";
            else
                $strReturn .= "Error creating newselement.\n";
        
        }

        $strReturn .= "Adding headline-element to new page\n";
        
        if(class_module_pages_element::getElement("row") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("headline_row");
            $objPagelement->setStrName("headline");
            $objPagelement->setStrElement("row");
            $objPagelement->updateObjectToDb($strNewsdetailsId);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                SET paragraph_title = ?
                                WHERE content_id = ?";
            if($this->objDB->_pQuery($strQuery, array("News", $strElementId)))
                $strReturn .= "Headline element created.\n";
            else
                $strReturn .= "Error creating headline element.\n";

        }





        $strReturn .= "Creating news-list-pge\n";
        $objPage = new class_module_pages_page();
        $objPage->setStrName("news");
        $objPage->setStrBrowsername("News");
        $objPage->setStrTemplate("standard.tpl");
        $objPage->updateObjectToDb($strNaviFolderId);
        $strNewslistId = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strNewsdetailsId."\n";
        $strReturn .= "Adding newsdetails to new page\n";

        if(class_module_pages_element::getElement("news") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("special_news|guestbook|downloads|gallery|galleryRandom|form|tellafriend|maps|search|navigation|faqs|postacomment|votings|userlist|rssfeed|tagto|portallogin|portalregistration|portalupload|directorybrowser|lastmodified|tagcloud|downloadstoplist|flash|mediaplayer|tags|eventmanager");
            $objPagelement->setStrName("special");
            $objPagelement->setStrElement("news");
            $objPagelement->updateObjectToDb($strNewslistId);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_news
                            SET news_category=?,
                                news_view = ?,
                                news_mode = ?,
                                news_order = ?,
                                news_amount = ?,
                                news_detailspage = ?,
                                news_template = ?
                            WHERE content_id = ?";
            if($this->objDB->_pQuery($strQuery, array($strCategoryID, 0, 0, 0, 20, "newsdetails", "demo.tpl", $strElementId)))
                $strReturn .= "Newselement created.\n";
            else
                $strReturn .= "Error creating newselement.\n";

        }

        $strReturn .= "Adding headline-element to new page\n";

        if(class_module_pages_element::getElement("row") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("headline_row");
            $objPagelement->setStrName("headline");
            $objPagelement->setStrElement("row");
            $objPagelement->updateObjectToDb($strNewslistId);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                SET paragraph_title = ?
                                WHERE content_id = ?";
            if($this->objDB->_pQuery($strQuery, array("Newslist", $strElementId)))
                $strReturn .= "Headline element created.\n";
            else
                $strReturn .= "Error creating headline element.\n";

        }






        $strReturn .= "Creating news-feed\n";
        $objNewsFeed = new class_module_news_feed();
        $objNewsFeed->setStrTitle("kajona news");
        $objNewsFeed->setStrUrlTitle("kajona_news");
        $objNewsFeed->setStrLink("http://www.kajona.de");

        if($this->strContentLanguage == "de")
            $objNewsFeed->setStrDesc("Dies ist ein Kajona demo news feed");
        else
            $objNewsFeed->setStrDesc("This is a Kajona demo news feed");

        $objNewsFeed->setStrPage($objPage->getStrName());
        $objNewsFeed->setStrCat("0");
        $objNewsFeed->setIntAmount(25);
        $objNewsFeed->updateObjectToDb();
        $strNewsFeedId = $objNewsFeed->getSystemid();
        $strReturn .= "ID of new news-feed: ".$strNewsFeedId."\n";

        $strReturn .= "Creating navigation entries...\n";

        return $strReturn;
    }

    public function setObjDb($objDb) {
        $this->objDB = $objDb;
    }

    public function setStrContentlanguage($strContentlanguage) {
        $this->strContentLanguage = $strContentlanguage;
    }

    public function getCorrespondingModule() {
        return "news";
    }

}
