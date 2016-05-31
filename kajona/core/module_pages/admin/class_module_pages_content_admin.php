<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_pages_content_admin.php 5964 2013-10-18 15:32:04Z sidler $                          *
********************************************************************************************************/


/**
 * This class is used to edit the content of a page. So, to create / delete / modify elements on a
 * given page.
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 *
 * @module pages
 * @moduleId _pages_content_modul_id_
 */
class class_module_pages_content_admin extends class_admin_simple implements interface_admin {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();

        if(_xmlLoader_)
            $this->setArrModuleEntry("modul", "pages_content");

        //If there's anything to unlock, do it now
        if($this->getParam("unlockid") != "") {
            $objLockmanager = new class_lockmanager($this->getParam("unlockid"));
            $objLockmanager->unlockRecord();
        }
        if($this->getParam("adminunlockid") != "") {
            $objLockmanager = new class_lockmanager($this->getParam("adminunlockid"));
            $objLockmanager->unlockRecord(true);
        }
    }


    /**
     * Adds the current page-name to the module-title
     *
     * @return string
     */
    public function getOutputModuleTitle() {
        $objPage = new class_module_pages_page($this->getSystemid());
        if($objPage->getStrName() == "")
            $objPage = new class_module_pages_page($objPage->getPrevId());
        return $this->getLang("modul_titel")." (".$objPage->getStrName().")";
    }

    /**
     * Returns a list of available placeholders & elements on this page
     *
     * @return string
     * @permissions edit
     */
    protected function actionList() {
        $strReturn = "";
        class_module_languages_admin::enableLanguageSwitch();
        $objPage = new class_module_pages_page($this->getSystemid());
        //get infos about the page
        $arrToolbarEntries = array();
        $arrToolbarEntries[0] = "<a href=\"".getLinkAdminHref("pages", "editPage", "&systemid=".$this->getSystemid())."\">".class_adminskin_helper::getAdminImage("icon_edit").$this->getLang("contentToolbar_pageproperties")."</a>";
        $arrToolbarEntries[1] = "<a href=\"".getLinkAdminHref("pages_content", "list", "&systemid=".$this->getSystemid())."\">".class_adminskin_helper::getAdminImage("icon_page").$this->getLang("contentToolbar_content")."</a>";
        $arrToolbarEntries[2] = "<a href=\"".getLinkPortalHref(
            $objPage->getStrName(), "", "", "&preview=1", "", $this->getLanguageToWorkOn())."\" target=\"_blank\">".class_adminskin_helper::getAdminImage("icon_lens").$this->getLang("contentToolbar_preview"
        )."</a>";

        if($objPage->getIntType() != class_module_pages_page::$INT_TYPE_ALIAS)
            $strReturn .= $this->objToolkit->getContentToolbar($arrToolbarEntries, 1);

        $arrTemplate = array();
        $arrTemplate["pagetemplate"] = $objPage->getStrTemplate();
        $arrTemplate["pagetemplateTitle"] = $this->getLang("template");

        $arrTemplate["lastuserTitle"] = $this->getLang("lastuserTitle");
        $arrTemplate["lasteditTitle"] = $this->getLang("lasteditTitle");
        $arrTemplate["lastuser"] = $objPage->getLastEditUser();

        $arrTemplate["lastedit"] = timeToString($objPage->getIntLmTime());
        $strReturn .= $this->objToolkit->getPageInfobox($arrTemplate);

        //try to load template, otherwise abort
        $strTemplateID = null;
        try {
            $strTemplateID = $this->objTemplate->readTemplate("/module_pages/".$objPage->getStrTemplate(), "", false, true);
        }
        catch(class_exception $objException) {
            $strReturn .= $this->getLang("templateNotLoaded")."<br />";
        }

        //Load elements on template, master-page special case!
        if($objPage->getStrName() == "master")
            $arrElementsOnTemplate = $this->objTemplate->getElements($strTemplateID, 1);
        else
            $arrElementsOnTemplate = $this->objTemplate->getElements($strTemplateID, 0);

        //Language-dependant loading of elements, if installed
        $arrElementsOnPage = class_module_pages_pageelement::getElementsOnPage($this->getSystemid(), false, $this->getLanguageToWorkOn());
        //save a copy of the array to be able to check against all values later on
        $arrElementsOnPageCopy = $arrElementsOnPage;

        //Loading all Elements installed on the system ("RAW"-Elements)
        $arrElementsInSystem = class_module_pages_element::getObjectList();


        //So, loop through the placeholders and check, if there's any element already belonging to this one
        $intI = 0;
        if(is_array($arrElementsOnTemplate) && count($arrElementsOnTemplate) > 0) {
            //Iterate over every single placeholder provided by the template
            foreach($arrElementsOnTemplate as $intKeyElementOnTemplate => $arrOneElementOnTemplate) {

                $strOutputAtPlaceholder = "";
                //Do we have one or more elements already in db at this placeholder?
                $bitHit = false;

                //Iterate over every single element-type provided by the placeholder
                foreach($arrElementsOnPage as $intArrElementsOnPageKey => $objOneElementOnPage) {
                    //Check, if its the same placeholder
                    $bitSamePlaceholder = false;
                    if($arrOneElementOnTemplate["placeholder"] == $objOneElementOnPage->getStrPlaceholder()) {
                        $bitSamePlaceholder = true;
                    }

                    if($bitSamePlaceholder) {
                        $bitHit = true;
                        $strActions = $this->getActionIcons($objOneElementOnPage);
                        //Put all Output together
                        $strOutputAtPlaceholder .= $this->objToolkit->simpleAdminList($objOneElementOnPage, $strActions, $intI++);

                        //remove the element from the array
                        unset($arrElementsOnPage[$intArrElementsOnPageKey]);
                    }

                }

                //Check, if one of the elements in the placeholder is allowed to be used multiple times
                foreach($arrOneElementOnTemplate["elementlist"] as $arrSingleElementOnTemplateplaceholder) {

                    /** @var class_module_pages_element $objOneElementInSystem  */
                    foreach($arrElementsInSystem as $objOneElementInSystem) {
                        if($objOneElementInSystem->getStrName() == $arrSingleElementOnTemplateplaceholder["element"]) {
                            if($objOneElementInSystem->getIntRepeat() == 1 || $bitHit === false) {
                                //So, the Row for a new element: element is repeatable or not yet created
                                $strActions = $this->objToolkit->listButton(getLinkAdmin("pages_content", "new", "&placeholder=".$arrOneElementOnTemplate["placeholder"]."&element=".$arrSingleElementOnTemplateplaceholder["element"]."&systemid=".$this->getSystemid(), "", $this->getLang("element_anlegen"), "icon_new"));
                                $strOutputAtPlaceholder .= $this->objToolkit->genericAdminList("", $objOneElementInSystem->getStrDisplayName(), "", $strActions, $intI++);
                            }
                            else {
                                //element not repeatable.
                                //Is there already one element installed? if not, then it IS allowed to create a new one
                                $bitOneInstalled = false;
                                foreach($arrElementsOnPageCopy as $objOneElementToCheck) {
                                    if($arrOneElementOnTemplate["placeholder"] == $objOneElementToCheck->getStrPlaceholder() && $arrSingleElementOnTemplateplaceholder["element"] == $objOneElementToCheck->getStrElement())
                                        $bitOneInstalled = true;
                                }
                                if(!$bitOneInstalled) {
                                    //So, the Row for a new element
                                    $strActions = $this->objToolkit->listButton(getLinkAdmin("pages_content", "new", "&placeholder=".$arrOneElementOnTemplate["placeholder"]."&element=".$arrSingleElementOnTemplateplaceholder["element"]."&systemid=".$this->getSystemid(), "", $this->getLang("element_anlegen"), "icon_new"));
                                    $strOutputAtPlaceholder .= $this->objToolkit->genericAdminList("", $objOneElementInSystem->getStrDisplayName(), "", $strActions, $intI++);
                                }
                            }
                        }
                    }
                }

                if((int)uniStrlen($strOutputAtPlaceholder) > 0) {
                    $arrSinglePlaceholder = explode("_", $arrOneElementOnTemplate["placeholder"]);
                    if(count($arrSinglePlaceholder == 2))
                        $strOutputAtPlaceholder .= $this->objToolkit->formHeadline($arrSinglePlaceholder[0]);

                    $strListId = generateSystemid();
                    $strReturn .= $this->objToolkit->dragableListHeader($strListId, true);
                    $strReturn .= $strOutputAtPlaceholder;
                    $strReturn .= $this->objToolkit->dragableListFooter($strListId);
                }

            }

        }
        else {
            $strReturn .= $this->getLang("element_liste_leer");
        }

        //if there are any page-elements remaining, print a warning and print the elements row
        if(count($arrElementsOnPage) > 0) {
            $strReturn .= $this->objToolkit->divider();
            $strReturn .= $this->objToolkit->warningBox($this->getLang("warning_elementsremaining"));
            $strReturn .= $this->objToolkit->listHeader();

            //minimized actions now, plz. this ain't being a real element anymore!
            foreach($arrElementsOnPage as $objOneElement) {
                $strActions = "";
                $strActions .= $this->objToolkit->listDeleteButton($objOneElement->getStrDisplayName(), $this->getLang("element_loeschen_frage"), getLinkAdminHref("pages_content", "deleteElementFinal", "&systemid=".$objOneElement->getSystemid().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));

                //Put all Output together
                $strReturn .= $this->objToolkit->genericAdminList("", $objOneElement->getStrDisplayName().$this->getLang("placeholder").$objOneElement->getStrPlaceholder(), "", $strActions, $intI++);
            }
            $strReturn .= $this->objToolkit->listFooter();
        }


        return $strReturn;
    }

    /**
     * @param class_model|interface_admin_listable|interface_model|class_module_pages_pageelement $objOneIterable
     * @param string $strListIdentifier
     *
     * @return string
     */
    public function getActionIcons($objOneIterable, $strListIdentifier = "") {
        $strActions = "";

        if($objOneIterable instanceof class_module_pages_pageelement) {
            $objLockmanager = $objOneIterable->getLockManager();

            //Create a row to handle the element, check all necessary stuff such as locking etc
            $strActions = "";
            //First step - Record locked? Offer button to unlock? But just as admin! For the user, who locked the record, the unlock-button
            //won't be visible
            if(!$objLockmanager->isAccessibleForCurrentUser()) {
                //So, return a button, if we have an admin in front of us
                if($objLockmanager->isUnlockableForCurrentUser()) {
                    $strActions .= $this->objToolkit->listButton(getLinkAdmin("pages_content", "list", "&systemid=".$this->getSystemid()."&adminunlockid=".$objOneIterable->getSystemid(), "", $this->getLang("ds_entsperren"), "icon_lockerOpen"));
                }
                //If the Element is locked, then its not allowed to edit or delete the record, so disable the icons
                $strActions .= $this->objToolkit->listButton(getImageAdmin("icon_editLocked", $this->getLang("ds_gesperrt")));
                $strActions .= $this->objToolkit->listButton(getImageAdmin("icon_deleteLocked", $this->getLang("ds_gesperrt")));
            }
            else {
                //if it's the user who locked the record, unlock it now
                if($objLockmanager->isLockedByCurrentUser())
                    $objLockmanager->unlockRecord();

                $strActions .= $this->objToolkit->listButton(getLinkAdmin("pages_content", "edit", "&systemid=".$objOneIterable->getSystemid(), "", $this->getLang("element_bearbeiten"), "icon_edit"));
                $strActions .= $this->objToolkit->listDeleteButton($objOneIterable->getStrName().($objOneIterable->getConcreteAdminInstance()->getContentTitle() != "" ? " - ".$objOneIterable->getConcreteAdminInstance()->getContentTitle() : "").($objOneIterable->getStrTitle() != "" ? " - ".$objOneIterable->getStrTitle() : ""), $this->getLang("element_loeschen_frage"), getLinkAdminHref("pages_content", "deleteElementFinal", "&systemid=".$objOneIterable->getSystemid().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));
            }

            //The Icons to sort the list and to copy the element
            $strActions .= $this->objToolkit->listButton(getLinkAdminDialog("pages_content", "copyElement", "&systemid=".$objOneIterable->getSystemid(), "", $this->getLang("element_copy"), "icon_copy"));

            //The status-icons
            $strActions .= $this->objToolkit->listStatusButton($objOneIterable->getSystemid());

        }
        else if($objOneIterable instanceof class_module_pages_element) {
            $objAdminInstance = class_module_system_module::getModuleByName("pages")->getAdminInstanceOfConcreteModule();
            if($objAdminInstance != null && $objAdminInstance instanceof class_admin_simple) {
                return $objAdminInstance->getActionIcons($objOneIterable);
            }
        }


        return $strActions;
    }


    /**
     * Loads the form to create a new element
     *
     * @param bool $bitShowErrors
     *
     * @return string
     */
    protected function actionNew($bitShowErrors = false) {
        $strReturn = "";
        //check rights
        $objCommon = new class_module_system_common($this->getSystemid());
        if($objCommon->rightEdit()) {
            //OK, here we go. So, what information do we have?
            $strPlaceholderElement = $this->getParam("element");
            //Now, load all infos about the requested element
            $objElement = class_module_pages_element::getElement($strPlaceholderElement);
            //Build the class-name
            $strElementClass = str_replace(".php", "", $objElement->getStrClassAdmin());
            //and finally create the object
            /** @var $objElement class_element_admin */
            $objElement = new $strElementClass();
            if($bitShowErrors)
                $objElement->setDoValidation(true);

            $strReturn = $objElement->actionEdit("new");
        }
        else
            $strReturn .= $this->getLang("commons_error_permissions");

        return $strReturn;
    }

    /**
     * Loads the form to edit the element
     *
     * @param bool $bitShowErrors
     *
     * @return string
     * @permissions edit
     */
    protected function actionEdit($bitShowErrors = false) {
        $strReturn = "";
        //check rights
        /** @var $objElement class_module_pages_element */
        $objElement = class_objectfactory::getInstance()->getObject($this->getSystemid());

        if($objElement instanceof class_module_pages_element) {
            $this->adminReload(getLinkAdminHref("pages", "edit", "&systemid=".$objElement->getSystemid()));
            return "";
        }


        if($objElement->rightEdit()) {
            //Load the element data
            //check, if the element isn't locked
            if($objElement->getLockManager()->isAccessibleForCurrentUser()) {
                $objElement->getLockManager()->lockRecord();

                //Load the class to create an object

                $strElementClass = str_replace(".php", "", $objElement->getStrClassAdmin());
                //and finally create the object
                /** @var $objPageElement class_element_admin */
                $objPageElement = new $strElementClass();
                if($bitShowErrors)
                    $objPageElement->setDoValidation(true);
                $strReturn .= $objPageElement->actionEdit("edit");

            }
            else {
                $strReturn .= $this->objToolkit->warningBox($this->getLang("ds_gesperrt"));
            }
        }
        else
            $strReturn .= $this->getLang("commons_error_permissions");

        return $strReturn;
    }

    /**
     * Saves the passed Element to the database (edit or new modes)
     *
     * @throws class_exception
     * @return string "" in case of success
     */
    protected function actionSaveElement() {
        $strReturn = "";
        //There are two modes - edit and new
        //The element itself just knows the edit mode, so in case of new, we have to create a dummy element - before
        //passing control to the element
        if($this->getParam("mode") == "new") {
            //Using the passed placeholder-param to load the element and get the table
            $strPlaceholder = $this->getParam("placeholder");
            //Split up the placeholder
            $arrPlaceholder = explode("_", $strPlaceholder);
            $strPlaceholderName = $arrPlaceholder[0];
            $strPlaceholderElement = $this->getParam("element");
            //Now, load all infos about the requested element
            $objElement = class_module_pages_element::getElement($strPlaceholderElement);
            //Load the class to create an object
            $strElementClass = str_replace(".php", "", $objElement->getStrClassAdmin());
            //and finally create the object
            /** @var class_element_admin $objElement */
            $objElement = new $strElementClass();

            //really continue? try to validate the passed data.
            if($objElement->getAdminForm() !== null && !$objElement->getAdminForm()->validateForm()) {
                class_carrier::getInstance()->setParam("peClose", "");
                $strReturn .= $this->actionNew(true);
                return $strReturn;
            }
            else if(!$objElement->validateForm()) {
                class_carrier::getInstance()->setParam("peClose", "");
                $strReturn .= $this->actionNew(true);
                return $strReturn;
            }

            //So, lets do the magic - create the records
            $objPageElement = new class_module_pages_pageelement();
            $objPageElement->setStrName($strPlaceholderName);
            $objPageElement->setStrPlaceholder($strPlaceholder);
            $objPageElement->setStrElement($strPlaceholderElement);
            $objPageElement->setStrLanguage($this->getParam("page_element_ph_language"));
            if(!$objPageElement->updateObjectToDb($this->getSystemid()))
                throw new class_exception("Error saving new element-object to db", class_exception::$level_ERROR);
            $strElementSystemId = $objPageElement->getSystemid();

            $objLockmanager = new class_lockmanager($strElementSystemId);
            $objLockmanager->lockRecord();

            //To have the element working as expected, set the systemid
            $this->setSystemid($strElementSystemId);
        }


        // ************************************* Edit the current Element *******************************

        //check, if the element isn't locked
        $objCommons = new class_module_system_common($this->getSystemid());
        $strPageSystemid = $objCommons->getPrevId();

        $objLockmanager = new class_lockmanager($this->getSystemid());

        if($objLockmanager->isLockedByCurrentUser()) {
            //Load the data of the current element
            $objElementData = new class_module_pages_pageelement($this->getSystemid());
            /** @var $objElement class_element_admin */
            $objElement = $objElementData->getConcreteAdminInstance();

            //really continue? try to validate the passed data.
            if($objElement->getAdminForm() !== null && !$objElement->getAdminForm()->validateForm()) {
                class_carrier::getInstance()->setParam("peClose", "");
                $strReturn .= $this->actionEdit(true);
                return $strReturn;
            }
            else if(!$objElement->validateForm()) {
                class_carrier::getInstance()->setParam("peClose", "");
                $strReturn .= $this->actionEdit(true);
                return $strReturn;
            }

            //pass the data to the element, maybe the element wants to update some data
            $objElement->setArrParamData($this->getAllParams());

            if($objElement->getAdminForm() !== null)
                $objElement->getAdminForm()->updateSourceObject();

            $objElement->doBeforeSaveToDb();

            //check, if we could save the data, so the element needn't to
            //woah, we are soooo great
            $objElement->updateForeignElement();

            //Edit Date of page & unlock
            $objPage = class_objectfactory::getInstance()->getObject($strPageSystemid);
            $objPage->updateObjectToDb();
            $objLockmanager->unlockRecord();

            //And update the internal comment and language
            $objElementData->setStrTitle($this->getParam("page_element_ph_title"));
            $objElementData->setStrLanguage($this->getParam("page_element_ph_language"));
            //placeholder to update?
            if($this->getParam("placeholder") != "")
                $objElementData->setStrPlaceholder($this->getParam("placeholder"));

            if(!$objElementData->updateObjectToDb())
                throw new class_exception("Error updating object to db", class_exception::$level_ERROR);


            //check, if we have to update the date-records
            $objStartDate = new class_date("0");
            $objEndDate = new class_date("0");
            $objStartDate->generateDateFromParams("start", $this->getAllParams());
            $objEndDate->generateDateFromParams("end", $this->getAllParams());

            $objSystemCommon = new class_module_system_common($this->getSystemid());
            if($objStartDate->getIntYear() == "0000" && $objEndDate->getIntYear() == "0000") {
                //Delete the record (maybe) existing in the dates-table
                if(!$objSystemCommon->deleteDateRecord())
                    throw new class_exception("Error deleting dates from db", class_exception::$level_ERROR);
            }
            else {
                //inserts needed
                $objSystemCommon->setStartDate($objStartDate);
                $objSystemCommon->setEndDate($objEndDate);
            }

            //allow the element to run actions after saving
            $objElement->doAfterSaveToDb();


            //Loading the data of the corresponding site
            $objPage = new class_module_pages_page($strPageSystemid);
            $this->flushCompletePagesCache();

            $this->adminReload(getLinkAdminHref("pages_content", "list", "systemid=".$objPage->getSystemid()));

        }
        else {
            $strReturn = $this->objToolkit->warningBox($this->getLang("ds_gesperrt"));
        }
        return $strReturn;
    }

    /**
     * Deletes an Element
     *
     * @throws class_exception
     * @return string , "" in case of success
     */
    protected function actionDeleteElementFinal() {
        $strReturn = "";

        $objPageElement = new class_module_pages_pageelement($this->getSystemid());
        if($objPageElement->rightDelete()) {
            //Locked?
            $objLockmanager = new class_lockmanager($this->getSystemid());
            $strPrevId = $objPageElement->getPrevId();

            if($objLockmanager->isAccessibleForCurrentUser()) {
                //delete object
                if(!$objPageElement->deleteObject())
                    throw new class_exception("Error deleting element from db", class_exception::$level_ERROR);

                $this->adminReload(getLinkAdminHref("pages_content", "list", "systemid=".$strPrevId.($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));
            }
            else {
                $strReturn .= $this->objToolkit->warningBox($this->getLang("ds_gesperrt"));
            }
        }
        else
            $strReturn = $this->getLang("commons_error_permissions");

        return $strReturn;
    }


    /**
     * Provides a form to set up the params needed to copy a single element from one placeholder to another.
     * Collects the target language, the target page and the target placeholder, invokes the copy-procedure.
     *
     * @throws class_exception
     * @return string , "" in case of success
     * @permissions edit
     */
    protected function actionCopyElement() {
        $strReturn = "";

        $this->setArrModuleEntry("template", "/folderview.tpl");

        $objSourceElement = new class_module_pages_pageelement($this->getSystemid());
        if($objSourceElement->rightEdit($this->getSystemid())) {

            $objLang = null;
            if($this->getParam("copyElement_language") != "") {
                $objLang = new class_module_languages_language($this->getParam("copyElement_language"));
            }
            else {
                $objLang = class_module_languages_language::getLanguageByName($this->getLanguageToWorkOn());
            }

            $objPage = null;
            if($this->getParam("copyElement_page") != "") {
                $objPage = class_module_pages_page::getPageByName($this->getParam("copyElement_page"));
                if($objPage == null)
                    throw new class_exception("failed to load page ".$this->getParam("copyElement_page"), class_exception::$level_ERROR);
                $objPage->setStrLanguage($objLang->getStrName());
                $objPage->initObject();
            }
            else {
                $objPage = new class_module_pages_page($objSourceElement->getPrevId());
            }

            //form header
            $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref("pages_content", "copyElement"), "formCopyElement");
            $strReturn .= $this->objToolkit->formInputHidden("copyElement_doCopy", 1);
            $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());

            $strReturn .= $this->objToolkit->formHeadline($this->getLang("copyElement_element")." ".$objSourceElement->getStrName()."_".$objSourceElement->getStrElement()." (".$objSourceElement->getStrTitle().")");


            //step one: language selection
            $arrLanguages = class_module_languages_language::getObjectList(true);
            $arrLanguageDD = array();
            foreach($arrLanguages as $objSingleLanguage)
                $arrLanguageDD[$objSingleLanguage->getSystemid()] = $this->getLang("lang_".$objSingleLanguage->getStrName(), "languages");

            $strReturn .= $this->objToolkit->formInputDropdown("copyElement_language", $arrLanguageDD, $this->getLang("copyElement_language"), $objLang->getSystemid());


            //step two: page selection
            $strReturn .= $this->objToolkit->formInputPageSelector("copyElement_page", $this->getLang("copyElement_page"), $objPage->getStrName(), "inputText", false);


            //step three: placeholder-selection
            //here comes the tricky part. load the template, analyze the placeholders and validate all those against things like repeatable and more...
            $strTemplate = $objPage->getStrTemplate();

            //load the placeholders
            $strTemplateId = $this->objTemplate->readTemplate("/module_pages/".$strTemplate);
            $arrPlaceholders = $this->objTemplate->getElements($strTemplateId);
            $arrPlaceholdersDD = array();

            foreach($arrPlaceholders as $arrSinglePlaceholder) {

                foreach($arrSinglePlaceholder["elementlist"] as $arrSinglePlaceholderlist) {
                    if($objSourceElement->getStrElement() == $arrSinglePlaceholderlist["element"]) {
                        if($objSourceElement->getIntRepeat() == 1) {
                            //repeatable, ok in every case
                            $arrPlaceholdersDD[$arrSinglePlaceholder["placeholder"]] = $arrSinglePlaceholder["placeholder"];
                        }
                        else {
                            //not repeatable - element already existing at placeholder?
                            $arrElementsOnPage = class_module_pages_pageelement::getElementsOnPage($objPage->getSystemid(), false, $objLang->getStrName());
                            //loop in order to find same element-types - other elements may be possible due to piped placeholders, too
                            $bitAdd = true;
                            //var_dump($arrElementsOnPage);
                            foreach($arrElementsOnPage as $objSingleElementOnPage) {
                                if($objSingleElementOnPage->getStrElement() == $objSourceElement->getStrElement())
                                    $bitAdd = false;
                            }

                            if($bitAdd)
                                $arrPlaceholdersDD[$arrSinglePlaceholder["placeholder"]] = $arrSinglePlaceholder["placeholder"];
                        }
                    }
                }
            }


            $bitCopyingAllowed = true;
            if(count($arrPlaceholdersDD) == 0) {
                $strReturn .= $this->objToolkit->formTextRow($this->getLang("copyElement_err_placeholder"));
                $bitCopyingAllowed = false;
            }
            else {
                $strReturn .= $this->objToolkit->formInputDropdown("copyElement_placeholder", $arrPlaceholdersDD, $this->getLang("copyElement_placeholder"));
            }
            $strReturn .= $this->objToolkit->formTextRow($this->getLang("copyElement_template")." ".$strTemplate);

            $strReturn .= $this->objToolkit->divider();

            $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("copyElement_submit"), "Submit", "", "inputSubmit", $bitCopyingAllowed);
            $strReturn .= $this->objToolkit->formClose();


            $strReturn .= "
                <script type=\"text/javascript\">

                $(function() {
                        var reloadForm = function() {
                            setTimeout( function() {
                                document.getElementById('copyElement_doCopy').value = 0;
                                var formElement = document.getElementById('formCopyElement');
                                formElement.submit();
                            }, 100);

                        };

	                    KAJONA.admin.copyElement_page.bind('autocompleteselect', reloadForm);

	                    var languageField = document.getElementById('copyElement_language');
	                    languageField.onchange = reloadForm;

                        var pageField = document.getElementById('copyElement_page');
	                    pageField.onchange = reloadForm;
	             });

                </script>";

            //any actions to take?
            if($this->getParam("copyElement_doCopy") == 1) {
                $objNewElement = $objSourceElement->copyObject($objPage->getSystemid());
                $objNewElement->setStrLanguage($objLang->getStrName());
                $objNewElement->setStrPlaceholder($this->getParam("copyElement_placeholder"));
                if($objNewElement->updateObjectToDb()) {
                    $this->setSystemid($objNewElement->getSystemid());
                    $strReturn = "";

                    $this->adminReload(getLinkAdminHref("pages_content", "list", "systemid=".$objNewElement->getPrevId()."&peClose=1"));
                }
                else
                    throw new class_exception("Error copying the pageelement ".$objSourceElement->getSystemid(), class_exception::$level_ERROR);

            }

        }
        else
            $strReturn = $this->getLang("commons_error_permissions");
        return $strReturn;
    }


    /**
     * Helper to generate a small path-navigation
     *
     * @return array
     */
    protected function getArrOutputNaviEntries() {
        $arrPath = $this->getPathArray();

        $arrPathLinks = parent::getArrOutputNaviEntries();
        array_pop($arrPathLinks);
        $arrPathLinks[] = getLinkAdmin("pages", "list", "&unlockid=".$this->getSystemid(), $this->getLang("modul_titel", "pages"));

        foreach($arrPath as $strOneSystemid) {
            /** @var $objObject class_module_pages_folder|class_module_pages_page */
            $objObject = class_objectfactory::getInstance()->getObject($strOneSystemid);
            //Skip Elements: No sense to show in path-navigation
            if($objObject == null || $objObject->getIntModuleNr() == _pages_content_modul_id_)
                continue;

            if($objObject instanceof class_module_pages_folder) {
                $arrPathLinks[] = getLinkAdmin("pages", "list", "&systemid=".$strOneSystemid."&unlockid=".$this->getSystemid(), $objObject->getStrName());
            }
            if($objObject instanceof class_module_pages_page) {
                $arrPathLinks[] = getLinkAdmin("pages", "list", "&systemid=".$strOneSystemid."&unlockid=".$this->getSystemid(), $objObject->getStrBrowsername());
            }

        }
        return $arrPathLinks;
    }

    /**
     * Sorts the current element upwards
     */
    protected function actionElementStatus() {
        //Create the object
        $objElement = new class_module_pages_pageelement($this->getSystemid());
        $objElement->setStatus();
        $this->adminReload(getLinkAdminHref("pages_content", "list", "systemid=".$objElement->getPrevId().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));
    }


    /**
     * Method to move an element from one placeholder to another
     * Expects the params
     * - systemid
     * - placeholder
     *
     * @permissions edit
     * @xml
     */
    protected function actionMoveElement() {
        $strReturn = "";
        //get the object to update
        /** @var $objObject class_module_pages_pageelement */
        $objObject = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objObject instanceof class_module_pages_pageelement && $objObject->rightEdit()) {

            $strPageSystemid = $objObject->getPrevId();
            $objLockmanager = new class_lockmanager($objObject->getSystemid());

            $strPlaceholder = $this->getParam("placeholder");
            $arrParts = explode("_", $strPlaceholder);

            if(uniStrpos($arrParts[1], $objObject->getStrElement()) !== false) {

                if(!$objLockmanager->isLocked())
                    $objLockmanager->lockRecord();

                if($objLockmanager->isLockedByCurrentUser()) {

                    //ph_placeholder
                    $objObject->setStrPlaceholder($strPlaceholder);

                    //ph_name
                    $objObject->setStrName($arrParts[0]);

                    $objObject->updateObjectToDb();

                    //Edit Date of page & unlock
                    $objPage = class_objectfactory::getInstance()->getObject($strPageSystemid);
                    $objPage->updateObjectToDb();
                    $objLockmanager->unlockRecord();

                    //Loading the data of the corresp site
                    $this->flushCompletePagesCache();

                    $strReturn = "<message><success>element update succeeded</success></message>";
                }
                else {
                    class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_UNAUTHORIZED);
                    $strReturn = "<message><error>element not allowed for target placeholder</error></message>";

                }
            }

        }
        else {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_UNAUTHORIZED);
            $strReturn = "<message><error>".$this->getLang("ds_gesperrt").".".$this->getLang("commons_error_permissions")."</error></message>";
        }
        return $strReturn;
    }


    /**
     * @xml
     * @permissions edit
     */
    protected function actionUpdateObjectProperty() {
        $strReturn = "";
        //get the object to update
        /** @var $objObject class_module_pages_element */
        $objObject = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objObject->rightEdit()) {
            //differ between two modes - page-elements or regular objects
            if($objObject instanceof class_module_pages_pageelement) {

                $strPageSystemid = $objObject->getPrevId();
                $objLockmanager = new class_lockmanager($objObject->getSystemid());

                if(!$objLockmanager->isLocked())
                    $objLockmanager->lockRecord();

                if($objLockmanager->isLockedByCurrentUser()) {
                    //and finally create the object
                    /** @var class_module_pages_pageelement $objElement */
                    $strElementClass = str_replace(".php", "", $objObject->getStrClassAdmin());
                    //and finally create the object
                    /** @var $objElement class_element_admin */
                    $objElement = new $strElementClass();
                    $objElement->setSystemid($this->getSystemid());
                    $arrElementData = $objElement->loadElementData();

                    //see if we could set the param to the element
                    if($this->getParam("property") != "") {

                        $strProperty = null;

                        //try to fetch the matching setter
                        $objReflection = new class_reflection($objElement);

                        //try to fetch the property based on the orm annotations
                        $strTargetTable = $objReflection->getAnnotationValuesFromClass(class_orm_mapper::STR_ANNOTATION_TARGETTABLE);
                        if(count($strTargetTable) > 0)
                            $strTargetTable = $strTargetTable[0];

                        $arrTable = explode(".", $strTargetTable);
                        if(count($arrTable) == 2)
                            $strTargetTable = $arrTable[0];

                        $arrOrmProperty = $objReflection->getPropertiesWithAnnotation(class_orm_mapper::STR_ANNOTATION_TABLECOLUMN);
                        foreach($arrOrmProperty as $strCurProperty => $strValue) {
                            if($strValue == $strTargetTable.".".$this->getParam("property"))
                                $strProperty = $strCurProperty;
                        }

                        if($strProperty == null) {
                            $strProperty = $this->getParam("property");
                        }

                        $strSetter = $objReflection->getSetter($strProperty);
                        if($strSetter != null) {
                            call_user_func(array($objElement, $strSetter), $this->getParam("value"));
                        }
                        else {
                            $arrElementData[$this->getParam("property")] = $this->getParam("value");
                            $objElement->setArrParamData($arrElementData);
                        }
                    }

                    //pass the data to the element, maybe the element wants to update some data
                    $objElement->doBeforeSaveToDb();

                    //check, if we could save the data, so the element needn't to
                    //woah, we are soooo great
                    $objElement->updateForeignElement();

                    //Edit Date of page & unlock
                    $objPage = class_objectfactory::getInstance()->getObject($strPageSystemid);
                    $objPage->updateObjectToDb();
                    $objLockmanager->unlockRecord();

                    //allow the element to run actions after saving
                    $objElement->doAfterSaveToDb();

                    //Loading the data of the corresp site
                    $objPage = new class_module_pages_page($strPageSystemid);
                    $this->flushCompletePagesCache();

                    $strReturn = "<message><success>element update succeeded</success></message>";
                }
            }
            else {
                //any other object - try to find the matching property and write the value
                if($this->getParam("property") == "") {
                    class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_BADREQUEST);
                    return "<message><error>missing property param</error></message>";
                }

                $objReflection = new class_reflection($objObject);
                $strSetter = $objReflection->getSetter($this->getParam("property"));
                if($strSetter == null) {
                    class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_BADREQUEST);
                    return "<message><error>setter not found</error></message>";
                }

                call_user_func(array($objObject, $strSetter), $this->getParam("value"));
                $objObject->updateObjectToDb();
                $this->flushCompletePagesCache();

                $strReturn = "<message><success>object update succeeded</success></message>";

            }
        }
        else {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_UNAUTHORIZED);
            $strReturn = "<message><error>".$this->getLang("ds_gesperrt").".".$this->getLang("commons_error_permissions")."</error></message>";
        }
        return $strReturn;

    }

}
