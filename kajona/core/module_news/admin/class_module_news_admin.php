<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_news_admin.php 5924 2013-10-05 18:08:38Z sidler $                                   *
********************************************************************************************************/


/**
 * Admin class of the news-module. Responsible for editing news, organizing them in categories and creating feeds
 *
 * @package module_news
 * @author sidler@mulchprod.de
 * 
 * @objectListNews class_module_news_news
 * @objectNewNews class_module_news_news
 * @objectEditNews class_module_news_news
 * @objectEdit class_module_news_news
 * @objectListCategory class_module_news_category
 * @objectNewCategory class_module_news_category
 * @objectEditCategory class_module_news_category
 * @objectListFeed class_module_news_feed
 * @objectNewFeed class_module_news_feed
 * @objectEditFeed class_module_news_feed
 *
 * @autoTestable listNews,newNews,listCategory,newCategory,listFeed,newFeed
 *
 * @module news
 * @moduleId _news_module_id_
 */
class class_module_news_admin extends class_admin_evensimpler implements interface_admin, interface_calendarsource_admin {

    const STR_CAT_LIST = "STR_CAT_LIST";
    const STR_NEWS_LIST = "STR_NEWS_LIST";
    
    const STR_CALENDAR_FILTER_NEWS = "STR_CALENDAR_FILTER_NEWS";


    /**
     * Constructor

     */
    public function __construct() {
        parent::__construct();

        if($this->getAction() == "list")
            $this->setAction("listNewsAndCategories");

    }

    public function getOutputModuleNavi() {
        $arrReturn = array();
        $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "listNewsAndCategories", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "newNews", "", $this->getLang("action_new_news"), "", "", true, "adminnavi"));
        $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "newCategory", "", $this->getLang("commons_create_category"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right2", getLinkAdmin($this->arrModule["modul"], "listFeed", "", $this->getLang("modul_titel_feed"), "", "", true, "adminnavi"));
        $arrReturn[] = array("right2", getLinkAdmin($this->arrModule["modul"], "newFeed", "", $this->getLang("action_new_feed"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=" . $this->arrModule["modul"], $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));
        return $arrReturn;
    }


    protected function renderAdditionalActions(class_model $objListEntry) {
        if($objListEntry instanceof class_module_news_category) {
            return array(
                $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "listNewsAndCategories", "&filterId=" . $objListEntry->getSystemid(), "", $this->getLang("kat_anzeigen"), "icon_lens"))
            );
        }

        if($objListEntry instanceof class_module_news_news && $objListEntry->rightEdit()) {
            if(class_module_languages_language::getNumberOfLanguagesAvailable() > 1) {
                return array(
                    $this->objToolkit->listButton(
                        getLinkAdminDialog($this->arrModule["modul"], "editLanguageset", "&systemid=" . $objListEntry->getSystemid(), "", $this->getLang("news_languageset"), "icon_language")
                    )
                );
            }
        }

        return array();
    }
    
    
    protected function getActionNameForClass($strAction, $objInstance) {
        if ($strAction == "list" && ($objInstance instanceof class_module_news_news || $objInstance instanceof class_module_news_category)) {
            return "listNewsAndCategories";
        }
        
        return parent::getActionNameForClass($strAction, $objInstance);
    }
    
    
    protected function getNewEntryAction($strListIdentifier, $bitDialog = false) {
        if($strListIdentifier == class_module_news_admin::STR_CAT_LIST) {
            return $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "newCategory", "", $this->getLang("commons_create_category"), $this->getLang("commons_create_category"), "icon_new"));
        }
        else if($strListIdentifier == class_module_news_admin::STR_NEWS_LIST) {
            return $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "newNews", "", $this->getLang("action_new_news"), $this->getLang("action_new_news"), "icon_new"));
        }

        return parent::getNewEntryAction($strListIdentifier, $bitDialog);
    }


    /**
     * Returns a list of all categories and all news
     * The list could be filtered by categories
     *
     * @return string
     * @autoTestable
     * @permissions view
     */
    protected function actionListNewsAndCategories() {

        $objIterator = new class_array_section_iterator(class_module_news_category::getObjectCount());
        $objIterator->setIntElementsPerPage(class_module_news_category::getObjectCount());
        $objIterator->setPageNumber(1);
        $objIterator->setArraySection(class_module_news_category::getObjectList("", $objIterator->calculateStartPos(), $objIterator->calculateEndPos()));

        $strReturn = $this->renderList($objIterator, false, class_module_news_admin::STR_CAT_LIST);

        $objIterator = new class_array_section_iterator(class_module_news_news::getObjectCount($this->getParam("filterId")));
        $objIterator->setPageNumber($this->getParam("pv"));
        $objIterator->setArraySection(class_module_news_news::getObjectList($this->getParam("filterId"), $objIterator->calculateStartPos(), $objIterator->calculateEndPos()));

        $strReturn .= $this->renderList($objIterator, false, class_module_news_admin::STR_NEWS_LIST);

        return $strReturn;
    }


    /**
     * @return string
     * @permissions edit
     */
    protected function actionEditLanguageset() {
        $strReturn = "";
        $objNews = class_objectfactory::getInstance()->getObject($this->getSystemid());
        $this->setArrModuleEntry("template", "/folderview.tpl");
        if($objNews->rightEdit()) {

            $objLanguageset = class_module_languages_languageset::getLanguagesetForSystemid($this->getSystemid());
            if($objLanguageset == null) {
                $strReturn .= $this->objToolkit->formTextRow($this->getLang("languageset_notmaintained"));
                $strReturn .= $this->objToolkit->formHeadline($this->getLang("languageset_addtolanguage"));

                $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "assignToLanguageset"));
                $arrLanguages = class_module_languages_language::getObjectList();
                $arrDropdown = array();
                foreach($arrLanguages as $objOneLanguage) {
                    $arrDropdown[$objOneLanguage->getSystemid()] = $this->getLang("lang_" . $objOneLanguage->getStrName(), "languages");
                }

                $strReturn .= $this->objToolkit->formInputDropdown("languageset_language", $arrDropdown, $this->getLang("commons_language_field"));
                $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
                $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
                $strReturn .= $this->objToolkit->formClose();
            }
            else {

                $objLanguage = new class_module_languages_language($objLanguageset->getLanguageidForSystemid($this->getSystemid()));
                $strReturn .= $this->objToolkit->formHeadline($this->getLang("languageset_addtolanguage"));
                $strReturn .= $this->objToolkit->formTextRow($this->getLang("languageset_currentlanguage"));
                $strReturn .= $this->objToolkit->formTextRow($this->getLang("lang_" . $objLanguage->getStrName(), "languages"));

                $strReturn .= $this->objToolkit->formHeadline($this->getLang("languageset_maintainlanguages"));

                $arrLanguages = class_module_languages_language::getObjectList();

                $strReturn .= $this->objToolkit->listHeader();
                $intI = 0;
                $intNrOfUnassigned = 0;
                $arrMaintainedLanguages = array();
                foreach($arrLanguages as $objOneLanguage) {

                    $strNewsid = $objLanguageset->getSystemidForLanguageid($objOneLanguage->getSystemid());
                    $strActions = "";
                    if($strNewsid != null) {
                        $arrMaintainedLanguages[] = $objOneLanguage->getSystemid();
                        $objNews = new class_module_news_news($strNewsid);
                        $strNewsName = $objNews->getStrTitle();
                        $strActions .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "removeFromLanguageset", "&systemid=" . $objNews->getSystemid(), "", $this->getLang("languageset_remove"), "icon_delete"));
                        $strReturn .= $this->objToolkit->genericAdminList(
                            $objOneLanguage->getSystemid(), $this->getLang("lang_" . $objOneLanguage->getStrName(), "languages") . ": " . $strNewsName, getImageAdmin("icon_language"), $strActions, $intI++
                        );
                    }
                    else {
                        $intNrOfUnassigned++;
                        $strReturn .= $this->objToolkit->genericAdminList(
                            $objOneLanguage->getSystemid(), $this->getLang("lang_" . $objOneLanguage->getStrName(), "languages") . ": " . $this->getLang("languageset_news_na"), getImageAdmin("icon_language"), $strActions, $intI++
                        );
                    }

                }

                $strReturn .= $this->objToolkit->listFooter();

                //provide a form to add further news-items
                if($intNrOfUnassigned > 0) {
                    $strReturn .= $this->objToolkit->formHeadline($this->getLang("languageset_addnewstolanguage"));

                    $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "addNewsToLanguageset"));
                    $arrLanguages = class_module_languages_language::getObjectList();
                    $arrDropdown = array();
                    foreach($arrLanguages as $objOneLanguage) {
                        if(!in_array($objOneLanguage->getSystemid(), $arrMaintainedLanguages)) {
                            $arrDropdown[$objOneLanguage->getSystemid()] = $this->getLang("lang_" . $objOneLanguage->getStrName(), "languages");
                        }
                    }

                    $strReturn .= $this->objToolkit->formInputDropdown("languageset_language", $arrDropdown, $this->getLang("commons_language_field"));


                    $arrNews = class_module_news_news::getObjectList();
                    $arrDropdown = array();
                    foreach($arrNews as $objOneNews) {
                        if(class_module_languages_languageset::getLanguagesetForSystemid($objOneNews->getSystemid()) == null) {
                            $arrDropdown[$objOneNews->getSystemid()] = $objOneNews->getStrTitle();
                        }
                    }

                    $strReturn .= $this->objToolkit->formInputDropdown("languageset_news", $arrDropdown, $this->getLang("languageset_news"));

                    $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
                    $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
                    $strReturn .= $this->objToolkit->formClose();
                }
            }
        }
        else {
            $strReturn .= $this->getLang("commons_error_permissions");
        }

        return $strReturn;
    }

    protected function actionAddNewsToLanguageset() {
        $objNews = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objNews->rightEdit()) {
            $objLanguageset = class_module_languages_languageset::getLanguagesetForSystemid($this->getSystemid());
            //load the languageset for the current systemid
            $objTargetLanguage = new class_module_languages_language($this->getParam("languageset_language"));
            if($objLanguageset != null && $objTargetLanguage->getStrName() != "") {
                $objLanguageset->setSystemidForLanguageid($this->getParam("languageset_news"), $objTargetLanguage->getSystemid());
            }

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "editLanguageset", "&systemid=" . $this->getSystemid()));
        }
    }

    protected function actionAssignToLanguageset() {
        $objNews = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objNews->rightEdit()) {
            $objLanguageset = class_module_languages_languageset::getLanguagesetForSystemid($this->getSystemid());
            $objTargetLanguage = new class_module_languages_language($this->getParam("languageset_language"));
            if($objLanguageset == null && $objTargetLanguage->getStrName() != "") {
                $objLanguageset = new class_module_languages_languageset();
                $objLanguageset->setSystemidForLanguageid($this->getSystemid(), $objTargetLanguage->getSystemid());
            }

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "editLanguageset", "&systemid=" . $this->getSystemid()));
        }
    }

    protected function actionRemoveFromLanguageset() {
        $objNews = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objNews->rightEdit()) {
            $objLanguageset = class_module_languages_languageset::getLanguagesetForSystemid($this->getSystemid());
            if($objLanguageset != null) {
                $objLanguageset->removeSystemidFromLanguageeset($this->getSystemid());
            }

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "editLanguageset", "&systemid=" . $this->getSystemid()));
        }
    }
    

    /**
     * Shows the form to edit or create news
     *
     * @param string $strMode new || edit
     * @param class_admin_formgenerator $objForm
     *
     * @return string
     */
    protected function actionNewNews($strMode = "new", class_admin_formgenerator $objForm = null) {
        $strReturn = "";
        $objNews = new class_module_news_news();
        if($strMode == "edit") {
            $objNews = new class_module_news_news($this->getSystemid());
            $objNews->getLockManager()->lockRecord();

            if(!$objNews->rightEdit()) {
                return $this->getLang("commons_error_permissions");
            }

            //search the languages maintained
            $objLanguageManager = class_module_languages_languageset::getLanguagesetForSystemid($this->getSystemid());
            if($objLanguageManager != null) {

                $arrMaintained = $objLanguageManager->getArrLanguageSet();
                $arrDD = array();
                foreach($arrMaintained as $strLanguageId => $strSystemid) {
                    $objLanguage = new class_module_languages_language($strLanguageId);
                    $arrDD[$strSystemid] = $this->getLang("lang_" . $objLanguage->getStrName(), "languages");
                }

                class_module_languages_admin::enableLanguageSwitch();
                class_module_languages_admin::setArrLanguageSwitchEntries($arrDD);
                class_module_languages_admin::setStrOnChangeHandler("window.location='" . getLinkAdminHref("news", "editNews") . (_system_mod_rewrite_ == "true" ? "?" : "&") . "systemid='+this.value+'&pe=" . $this->getParam("pe") . "';");
                class_module_languages_admin::setStrActiveKey($this->getSystemid());
            }
        }

        if($objForm == null) {
            $objForm = $this->getNewsAdminForm($objNews);
        }

        $objForm->addField(new class_formentry_hidden("", "mode"))->setStrValue($strMode);
        $strReturn .= $objForm->renderForm(getLinkAdminHref($this->getArrModule("modul"), "saveNews"));
        return $strReturn;
    }


    /**
     * Saves the passed values as a new category to the db
     *
     * @return string "" in case of success
     * @permissions edit
     */
    protected function actionSaveNews() {
        $objNews = null;

        if($this->getParam("mode") == "new") {
            $objNews = new class_module_news_news();
        }

        else if($this->getParam("mode") == "edit") {
            $objNews = new class_module_news_news($this->getSystemid());
        }

        if($objNews != null) {

            $objForm = $this->getNewsAdminForm($objNews);
            if(!$objForm->validateForm()) {
                return $this->actionNewNews($this->getParam("mode"), $objForm);
            }

            $objForm->updateSourceObject();

            $arrParams = $this->getAllParams();
            $arrCats = array();
            if(isset($arrParams["news_cat"])) {
                foreach($arrParams["news_cat"] as $strCatID => $strValue) {
                    $arrCats[$strCatID] = $strValue;
                }
            }
            $objNews->setArrCats($arrCats);

            $objNews->setBitUpdateMemberships(true);
            $objNews->updateObjectToDb();

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], $this->getActionNameForClass("list", $objNews), ($this->getParam("pe") != "" ? "&peClose=1" : "")));
            return "";
        }

        return $this->getLang("commons_error_permissions");
    }

    
    protected function getAdminForm(interface_model $objInstance) {
        
        if ($objInstance instanceof class_module_news_news) {
            return $this->getNewsAdminForm($objInstance);
        }
        else if ($objInstance instanceof class_module_news_feed) {
            return $this->getFeedAdminForm($objInstance);
        }
        
        return parent::getAdminForm($objInstance);
    }
    

    private function getNewsAdminForm(class_module_news_news $objNews) {
        
        $objForm = new class_admin_formgenerator("news", $objNews);
        $objForm->generateFieldsFromObject();

        $arrCats = class_module_news_category::getObjectList();
        if(count($arrCats) > 0) {
            $objForm->addField(new class_formentry_headline())->setStrValue($this->getLang("commons_categories"));
        }

        $arrFaqsMember = class_module_news_category::getNewsMember($this->getSystemid());

        foreach($arrCats as $objOneCat) {
            $bitChecked = false;
            foreach($arrFaqsMember as $objOneMember) {
                if($objOneMember->getSystemid() == $objOneCat->getSystemid()) {
                    $bitChecked = true;
                }
            }

            $objForm->addField(new class_formentry_checkbox("news", "cat[" . $objOneCat->getSystemid() . "]"))->setStrLabel($objOneCat->getStrTitle())->setStrValue($bitChecked);

        }

        return $objForm;
    }

    
    private function getFeedAdminForm(class_module_news_feed $objFeed) {
        $objForm = new class_admin_formgenerator("feed", $objFeed);
        $objForm->generateFieldsFromObject();

        $arrNewsCats = class_module_news_category::getObjectList();
        $arrCatsDD = array();
        foreach($arrNewsCats as $objOneCat) {
            $arrCatsDD[$objOneCat->getSystemid()] = $objOneCat->getStrTitle();
        }
        $arrCatsDD["0"] = $this->getLang("commons_all_categories");
        $objForm->getField("cat")->setArrKeyValues($arrCatsDD);

        return $objForm;
    }

    /**
     * Returns a xml-based representation of all categories available
     * Return format:
     * <categories>
     *    <category>
     *        <title></title>
     *        <systemid></systemid>
     *    </category>
     * </categories>
     *
     * @return string
     * @xml
     */
    protected function actionListCategories() {
        $strReturn = "";
        if($this->getObjModule()->rightView()) {
            $arrCategories = class_module_news_category::getObjectList();
            $strReturn .= "<categories>\n";
            foreach($arrCategories as $objOneCategory) {
                if($objOneCategory->rightView()) {
                    $strReturn .= " <category>\n";
                    $strReturn .= "   <title>" . xmlSafeString($objOneCategory->getStrTitle()) . "</title>";
                    $strReturn .= "   <systemid>" . $objOneCategory->getSystemid() . "</systemid>";
                    $strReturn .= " </category>\n";
                }
            }
            $strReturn .= "</categories>\n";
        }
        else {
            $strReturn = "<error>" . $this->getLang("commons_error_permissions") . "</error>";
        }

        return $strReturn;
    }

    /**
     * Returns a xml-based representation of all news available.
     * In this case only a limited set of attributes is returned, namely the title and the
     * systemid of each entry.
     * Return format:
     * <newslist>
     *    <news>
     *        <title></title>
     *        <systemid></systemid>
     *    </news>
     * </newslist>
     *
     * @return string
     * @xml
     */
    protected function actionListNews() {
        $strReturn = "";
        if($this->getObjModule()->rightView()) {
            $arrNews = class_module_news_news::getObjectList();
            $strReturn .= "<newslist>\n";
            foreach($arrNews as $objOneNews) {
                if($objOneNews->rightView()) {
                    $strReturn .= " <news>\n";
                    $strReturn .= "   <title>" . xmlSafeString($objOneNews->getStrTitle()) . "</title>";
                    $strReturn .= "   <systemid>" . $objOneNews->getSystemid() . "</systemid>";
                    $strReturn .= " </news>\n";
                }
            }
            $strReturn .= "</newslist>\n";
        }
        else {
            $strReturn = "<error>" . $this->getLang("commons_error_permissions") . "</error>";
        }

        return $strReturn;
    }

    /**
     * Returns a xml-based representation of a single news.
     * Return format:
     *    <news>
     *        <title></title>
     *        <systemid></systemid>
     *        <intro></intro>
     *        <text></text>
     *        <image></image>
     *        <categories></categories>
     *        <startdate></startdate>
     *        <enddate></enddate>
     *        <archivedate></archivedate>
     *    </news>
     *
     * @return string
     * @xml
     */
    protected function actionNewsDetails() {
        $strReturn = "";
        $objNews = new class_module_news_news($this->getSystemid());
        $arrCats = class_module_news_category::getNewsMember($objNews->getSystemid());

        array_walk($arrCats, function (&$objValue) {
            $objValue = $objValue->getSystemid();
        });


        if($objNews->rightView()) {
            $strReturn .= " <news>\n";
            $strReturn .= "   <title>" . xmlSafeString($objNews->getStrTitle()) . "</title>";
            $strReturn .= "   <systemid>" . $objNews->getSystemid() . "</systemid>";
            $strReturn .= "   <intro>" . xmlSafeString($objNews->getStrIntro()) . "</intro>";
            $strReturn .= "   <text>" . xmlSafeString($objNews->getStrText()) . "</text>";
            $strReturn .= "   <image>" . xmlSafeString($objNews->getStrImage()) . "</image>";
            $strReturn .= "   <categories>" . xmlSafeString(implode(",", $arrCats)) . "</categories>";
            $strReturn .= "   <startdate>" . xmlSafeString($objNews->getObjStartDate() != null ? $objNews->getObjStartDate()->getTimeInOldStyle() : "") . "</startdate>";
            $strReturn .= "   <enddate>" . xmlSafeString($objNews->getObjEndDate() != null ? $objNews->getObjEndDate()->getTimeInOldStyle() : "") . "</enddate>";
            $strReturn .= "   <archivedate>" . xmlSafeString($objNews->getObjDateSpecial() != null ? $objNews->getObjDateSpecial()->getTimeInOldStyle() : "") . "</archivedate>";
            $strReturn .= " </news>\n";
        }
        else {
            $strReturn = "<error>" . $this->getLang("commons_error_permissions") . "</error>";
        }

        return $strReturn;
    }

    /**
     * Saves newscontent as passed by post-paras via an xml-request.
     * Params expected are: newstitle, newsintro, newsimage, newstext, categories, startdate, enddate, archivedate
     *
     * @return string
     * @xml
     */
    protected function actionUpdateNewsXml() {
        $strReturn = "";
        $objNews = new class_module_news_news($this->getSystemid());
        if($objNews->rightEdit() || $this->getSystemid() == "") {

            $arrCats = array();
            foreach(explode(",", $this->getParam("categories")) as $strCatId) {
                $arrCats[$strCatId] = "c";
            }

            $objNews->setStrTitle($this->getParam("newstitle"));
            $objNews->setStrIntro($this->getParam("newsintro"));
            $objNews->setStrImage($this->getParam("newsimage"));
            $objNews->setStrText($this->getParam("newstext"));

            if($this->getParam("startdate") > 0) {
                $objDate = new class_date($this->getParam("startdate"));
                $objNews->setObjDateStart($objDate);
            }

            if($this->getParam("enddate") > 0) {
                $objDate = new class_date($this->getParam("enddate"));
                $objNews->setObjDateEnd($objDate);
            }

            if($this->getParam("archivedate") > 0) {
                $objDate = new class_date($this->getParam("archivedate"));
                $objNews->setObjDateSpecial($objDate);
            }

            $objNews->setArrCats($arrCats);
            if($objNews->updateObjectToDb()) {
                $strReturn = "<success></success>";
            }
            else {
                $strReturn = "<error></error>";
            }

        }
        else {
            $strReturn = "<error>" . $this->getLang("commons_error_permissions") . "</error>";
        }

        return $strReturn;
    }


    /**
     * @see interface_calendarsource_admin::getArrCalendarEntries()
     */
    public function getArrCalendarEntries(class_date $objStartDate, class_date $objEndDate) {
        $arrEntries = array();

        if($this->objSession->getSession(self::STR_CALENDAR_FILTER_NEWS) != "disabled") {

            $arrNews = class_module_news_news::getObjectList("", null, null, $objStartDate, $objEndDate);

            foreach($arrNews as $objOneNews) {

                $objEntry = new class_calendarentry();
                $objEntry->setStrClass("calendarEvent calendarNews");
                $strAlt = $this->getLang("calendar_type_news");

                $strTitle = $objOneNews->getStrTitle();
                if(uniStrlen($strTitle) > 15) {
                    $strAlt = $strTitle . "<br />" . $strAlt;
                    $strTitle = uniStrTrim($strTitle, 14);
                }

                $strName = getLinkAdmin($this->arrModule["modul"], "edit", "&systemid=" . $objOneNews->getSystemid(), $strTitle, $strAlt);
                $objEntry->setStrName($strName);
                $arrEntries[] = $objEntry;
            }
        }


        return $arrEntries;
    }

    /**
     * @see interface_calendarsource_admin::getArrLegendEntries()
     */
    public function getArrLegendEntries() {
        return array($this->getLang("calendar_type_news") => "calendarEvent calendarNews");
    }

    /**
     * @see interface_calendarsource_admin::getArrFilterEntries()
     */
    public function getArrFilterEntries() {
        return array(
            self::STR_CALENDAR_FILTER_NEWS => $this->getLang("calendar_filter_news"),
        );
    }

}

