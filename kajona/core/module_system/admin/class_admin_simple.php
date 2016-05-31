<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_admin_simple.php 5924 2013-10-05 18:08:38Z sidler $	                                            *
********************************************************************************************************/


/**
 * Class holding common methods for extended and simplified admin-guis.
 *
 * @package module_system
 * @since 4.0
 */
abstract class class_admin_simple extends class_admin {

    private  $strPeAddon = "";

    public function __construct($strSystemid = "") {
        parent::__construct($strSystemid);

        if($this->getParam("pe") == "1")
            $this->strPeAddon = "&pe=1";

        if($this->getParam("unlockid") != "") {
            $objLockmanager = new class_lockmanager($this->getParam("unlockid"));
            $objLockmanager->unlockRecord(true);
        }
    }


    /**
     * Renders the form to create a new entry
     * @abstract
     * @return string
     * @permissions edit
     */
    protected abstract function actionNew();

    /**
     * Renders the form to edit an existing entry
     * @abstract
     * @return string
     * @permissions edit
     */
    protected abstract function actionEdit();

    /**
     * Renders the general list of records
     * @abstract
     * @return string
     * @permissions view
     */
    protected abstract function actionList();


    /**
     * A general action to delete a record.
     * This method may be overwritten by subclasses.
     *
     * @permissions delete
     * @throws class_exception
     */
    protected function actionDelete() {
        $objRecord = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objRecord != null && $objRecord->rightDelete()) {
            if(!$objRecord->deleteObject())
                throw new class_exception("error deleting object ".strip_tags($objRecord->getStrDisplayName()), class_exception::$level_ERROR);

            $strTargetUrl = $this->getHistory(1);
            if(uniStrpos($strTargetUrl, "admin=1") === false)
                $strTargetUrl = "admin=1&module=".$this->getArrModule("modul");

            $this->adminReload(_indexpath_."?".$strTargetUrl.($this->getParam("pe") != "" ? "&peClose=1" : ""));
        }
        else
            throw new class_exception("error loading object ".$this->getSystemid(), class_exception::$level_ERROR);
    }

    /**
     * A general action to delete a record.
     * This method may be overwritten by subclasses.
     *
     * @permissions delete
     * @throws class_exception
     */
    protected function actionCopyObject() {
        $objRecord = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objRecord != null && $objRecord->rightEdit()) {
            if(!$objRecord->copyObject())
                throw new class_exception("error creating a copy of object ".strip_tags($objRecord->getStrDisplayName()), class_exception::$level_ERROR);

            $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), $this->getActionNameForClass("list", $objRecord), "&systemid=".$objRecord->getPrevId()));
        }
        else
            throw new class_exception("error loading object ".$this->getSystemid(), class_exception::$level_ERROR);
    }
    
    
    /**
     * Returns the action name for a given class name.
     * 
     * @param string $strAction
     * @param interface_model $objInstance
     * @return string specific action name
     */
    protected function getActionNameForClass($strAction, $objInstance) {
        return $strAction;
    }


    /**
     * Renders a list of items in a floatable "thumbnail" view, so a grid.
     * Please be aware, that the combination of paging and grids may result in unpredictable ordering.
     * As soon as the list is sortable, the page-size should be at least the same as the number of elements
     *
     * @param class_array_section_iterator $objArraySectionIterator
     * @param string $strListIdentifier an internal identifier to check the current parent-list
     * @param string $strPagerAddon
     * @param bool $bitSortable
     *
     * @throws class_exception
     * @return string
     */
    protected function renderFloatingGrid(class_array_section_iterator $objArraySectionIterator, $strListIdentifier = "", $strPagerAddon = "", $bitSortable = true) {
        $strReturn = "";

        if($objArraySectionIterator->getNrOfPages() > 1) {
            throw new class_exception("sortable lists with more than one page are not supported!", class_exception::$level_ERROR);
        }

        $arrPageViews = $this->objToolkit->getSimplePageview($objArraySectionIterator, $this->getArrModule("modul"), $this->getAction(), "&systemid=".$this->getSystemid().$this->strPeAddon.$strPagerAddon);
        $arrIterables = $arrPageViews["elements"];



        $strListActions = "";
        if($this->renderLevelUpAction($strListIdentifier) != "") {
            $strListActions .= $this->objToolkit->listButton($this->renderLevelUpAction($strListIdentifier));
        }

        $strListActions .= $this->mergeNewEntryActions($this->getNewEntryAction($strListIdentifier));

        if($strListActions != "") {
            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->genericAdminList("", "", "", $strListActions, 0);
            $strReturn .= $this->objToolkit->listFooter();
        }


        if(count($arrIterables) == 0)
            $strReturn .= $this->objToolkit->getTextRow($this->getLang("commons_list_empty"));


        if(count($arrIterables) > 0) {

            $strReturn .= $this->objToolkit->gridHeader($bitSortable);

            /** @var $objOneIterable class_model|interface_model|interface_admin_gridable */
            foreach($arrIterables as $objOneIterable) {

                if(!$objOneIterable->rightView() || !$objOneIterable instanceof interface_admin_gridable)
                    continue;

                $strActions = $this->getActionIcons($objOneIterable, $strListIdentifier);
                $strReturn .= $this->objToolkit->gridEntry($objOneIterable, $strActions, $this->renderGridEntryClickAction($objOneIterable, $strListIdentifier));
            }

            $strReturn .= $this->objToolkit->gridFooter();
        }

        $strReturn .= $arrPageViews["pageview"];

        return $strReturn;
    }


    /**
     * Renders a list of items, target is the common admin-list.
     * Please be aware, that the combination of paging and sortable-lists may result in unpredictable ordering.
     * As soon as the list is sortable, the page-size should be at least the same as the number of elements
     *
     * @param class_array_section_iterator $objArraySectionIterator
     * @param bool $bitSortable
     * @param string $strListIdentifier an internal identifier to check the current parent-list
     * @param bool $bitAllowTreeDrop
     * @param string $strPagerAddon
     *
     * @throws class_exception
     * @return string
     */
    protected function renderList(class_array_section_iterator $objArraySectionIterator, $bitSortable = false, $strListIdentifier = "", $bitAllowTreeDrop = false, $strPagerAddon = "") {
        $strReturn = "";
        $intI = 0;

        if($bitSortable && $objArraySectionIterator->getNrOfPages() > 1) {
            throw new class_exception("sortable lists with more than one page are not supported!", class_exception::$level_ERROR);
        }

        $strListId = generateSystemid();

        $arrPageViews = $this->objToolkit->getSimplePageview($objArraySectionIterator, $this->getArrModule("modul"), $this->getAction(), "&systemid=" . $this->getSystemid() . $this->strPeAddon . $strPagerAddon);
        $arrIterables = $arrPageViews["elements"];

        if(count($arrIterables) == 0)
            $strReturn .= $this->objToolkit->getTextRow($this->getLang("commons_list_empty"));

        if($bitSortable)
            $strReturn .= $this->objToolkit->dragableListHeader($strListId, false, $bitAllowTreeDrop);
        else
            $strReturn .= $this->objToolkit->listHeader();

        if($this->renderLevelUpAction($strListIdentifier) != "") {
            $strReturn .= $this->objToolkit->genericAdminList("", "", "", $this->objToolkit->listButton($this->renderLevelUpAction($strListIdentifier)), $intI++);
        }

        $arrMassActions = $this->getBatchActionHandlers($strListIdentifier);

        if(count($arrIterables) > 0) {

            /** @var $objOneIterable class_model|interface_model|interface_admin_listable */
            foreach($arrIterables as $objOneIterable) {

                if(!$objOneIterable->rightView())
                    continue;

                $strActions = $this->getActionIcons($objOneIterable, $strListIdentifier);
                $strReturn .= $this->objToolkit->simpleAdminList($objOneIterable, $strActions, $intI++, count($arrMassActions) > 0);
            }
        }


        $strNewActions = $this->mergeNewEntryActions($this->getNewEntryAction($strListIdentifier));
        $strBatchActions = "";

        if(count($arrMassActions) > 0)
            $strBatchActions .= $this->objToolkit->renderBatchActionHandlers($arrMassActions);

        if($strNewActions != "" || $strBatchActions != "")
            $strReturn .= $this->objToolkit->genericAdminList("batchActionSwitch", $strBatchActions, "", $strNewActions, $intI, "", "", $strBatchActions != "");

        if($bitSortable)
            $strReturn .= $this->objToolkit->dragableListFooter($strListId);
        else
            $strReturn .= $this->objToolkit->listFooter();




        $strReturn .= $arrPageViews["pageview"];

        return $strReturn;
    }

    /**
     * Wrapper rendering all action-icons for a given record. In most cases used to render a list-entry.
     *
     * @param class_model|interface_model|interface_admin_listable $objOneIterable
     * @param string $strListIdentifier
     *
     * @return string
     */
    public function getActionIcons($objOneIterable, $strListIdentifier = "") {
        $strActions = "";
        $strActions .= $this->renderUnlockAction($objOneIterable);
        $strActions .= $this->renderEditAction($objOneIterable);
        $arrAddons = $this->renderAdditionalActions($objOneIterable);
        if(is_array($arrAddons))
            $strActions .= implode("", $this->renderAdditionalActions($objOneIterable));
        $strActions .= $this->renderDeleteAction($objOneIterable);
        $strActions .= $this->renderCopyAction($objOneIterable);
        $strActions .= $this->renderStatusAction($objOneIterable);
        $strActions .= $this->renderTagAction($objOneIterable);
        $strActions .= $this->renderChangeHistoryAction($objOneIterable);
        $strActions .= $this->renderPermissionsAction($objOneIterable);

        return $strActions;
    }


    /**
     * Renders the action to jump a level upwards.
     * Overwrite this method if you want to provide such an action.
     *
     * @param $strListIdentifier
     * @return string
     */
    protected function renderLevelUpAction($strListIdentifier) {
        return "";
    }

    /**
     * For grid elements, an additional action may be defined when clicking the whole object.
     * This action is "in addition" to the action-toolbar at the bottom of the grid entry.
     * Make sure to pass a full eventhandler, e.g. onclick="document.location=''"
     * Overwrite this method if you want to provide such an action.
     *
     * @param $objOneIterable
     * @param $strListIdentifier
     *
     * @return string
     */
    protected function renderGridEntryClickAction($objOneIterable, $strListIdentifier) {
        return "";
    }


    /**
     * Renders the edit action button for the current record.
     *
     * @param class_model $objListEntry
     * @param bool $bitDialog opens the linked page in a js-based dialog
     *
     * @return string
     */
    protected function renderEditAction(class_model $objListEntry, $bitDialog = false) {
        if($objListEntry->rightEdit()) {

            $objLockmanager = $objListEntry->getLockManager();
            if(!$objLockmanager->isAccessibleForCurrentUser()) {
                return $this->objToolkit->listButton(class_adminskin_helper::getAdminImage("icon_editLocked", $this->getLang("commons_locked")));
            }

            if($bitDialog)
                return $this->objToolkit->listButton(
                    getLinkAdminDialog(
                        $objListEntry->getArrModule("modul"),
                        $this->getActionNameForClass("edit", $objListEntry),
                        "folderview=1&systemid=".$objListEntry->getSystemid().$this->strPeAddon,
                        $this->getLang("commons_list_edit"),
                        $this->getLang("commons_list_edit"),
                        "icon_edit"
                    )
                );
            else
                return $this->objToolkit->listButton(
                    getLinkAdmin(
                        $objListEntry->getArrModule("modul"),
                        $this->getActionNameForClass("edit", $objListEntry),
                        "&systemid=".$objListEntry->getSystemid().$this->strPeAddon,
                        $this->getLang("commons_list_edit"),
                        $this->getLang("commons_list_edit"),
                        "icon_edit"
                    )
                );
        }
        return "";
    }


    /**
     * Renders the unlock action button for the current record.
     * @param \class_model|\interface_model $objListEntry
     * @return string
     */
    protected function renderUnlockAction(interface_model $objListEntry) {

        $objLockmanager = $objListEntry->getLockManager();
        if(!$objLockmanager->isAccessibleForCurrentUser()) {
            if($objLockmanager->isUnlockableForCurrentUser() ) {
                return $this->objToolkit->listButton(
                    getLinkAdmin($objListEntry->getArrModule("modul"), $this->getActionNameForClass("list", $objListEntry), "&unlockid=".$objListEntry->getSystemid(), "", $this->getLang("commons_unlock"), "icon_lockerOpen")
                );
            }
        }
        return "";
    }


    /**
     * Renders the delete action button for the current record.
     * @param \class_model|\interface_model $objListEntry
     * @return string
     */
    protected function renderDeleteAction(interface_model $objListEntry) {
        if($objListEntry->rightDelete()) {

            $objLockmanager = $objListEntry->getLockManager();
            if(!$objLockmanager->isAccessibleForCurrentUser()) {
                return $this->objToolkit->listButton(class_adminskin_helper::getAdminImage("icon_deleteLocked", $this->getLang("commons_locked")));
            }

            return $this->objToolkit->listDeleteButton(
                strip_tags($objListEntry->getStrDisplayName()),
                $this->getLang($this->getObjLang()->stringToPlaceholder($this->getActionNameForClass("delete", $objListEntry)."_question"), $objListEntry->getArrModule("modul")),
                getLinkAdminHref($objListEntry->getArrModule("modul"), $this->getActionNameForClass("delete", $objListEntry), "&systemid=".$objListEntry->getSystemid().$this->strPeAddon)
            );
        }
        return "";
    }

    /**
     * Renders the status action button for the current record.
     * @param class_model $objListEntry
     * @return string
     */
    protected function renderStatusAction(class_model $objListEntry) {
        if($objListEntry->rightEdit() && $this->strPeAddon == "") {
            return $this->objToolkit->listStatusButton($objListEntry);
        }
        return "";
    }

    /**
     * Renders the permissions action button for the current record.
     * @param class_model|interface_model $objListEntry
     * @return string
     */
    protected function renderPermissionsAction(class_model $objListEntry) {
        if($objListEntry->rightRight() && $this->strPeAddon == "") {
            return $this->objToolkit->listButton(
                getLinkAdminDialog(
                    "right",
                    $this->getActionNameForClass("change", $objListEntry),
                    "&systemid=".$objListEntry->getSystemid().$this->strPeAddon,
                    "",
                    $this->getLang("commons_edit_permissions"),
                    getRightsImageAdminName($objListEntry->getSystemid()),
                    strip_tags($objListEntry->getStrDisplayName()),
                    true,
                    true
                )
            );
        }
        return "";
    }

    /**
     * Renders the icon to edit a records tags
     * @param class_model|interface_model $objListEntry
     * @return string
     */
    protected function renderTagAction(class_model $objListEntry) {
        if($objListEntry->rightEdit()) {

            //the tag list is more complex sind wrapped by a js-logic to load the tags by ajax afterwards

            // @codingStandardsIgnoreStart
            $strOnClick = "KAJONA.admin.folderview.dialog.setContentIFrame('".getLinkAdminHref("tags", "genericTagForm", "&systemid=".$objListEntry->getSystemid())."'); KAJONA.admin.folderview.dialog.setTitle('".strip_tags($objListEntry->getStrDisplayName())."'); KAJONA.admin.folderview.dialog.init(); return false;";
            $strLink = "<a href=\"#\" onclick=\"".$strOnClick."\" title=\"".$this->getLang("commons_edit_tags")."\" rel=\"tagtooltip\" data-systemid=\"".$objListEntry->getSystemid()."\">".class_adminskin_helper::getAdminImage("icon_tag", $this->getLang("commons_edit_tags"), true)."</a>";
            // @codingStandardsIgnoreEnd
            return $this->objToolkit->listButton($strLink);

        }
        return "";
    }


    /**
     * Renders the permissions action button for the current record.
     * @param class_model|interface_model $objListEntry
     * @return string
     */
    protected function renderCopyAction(class_model $objListEntry) {
        if($objListEntry->rightEdit() && $this->strPeAddon == "") {
            return $this->objToolkit->listButton(
                getLinkAdmin(
                    $objListEntry->getArrModule("modul"),
                    $this->getActionNameForClass("copyObject", $objListEntry),
                    "&systemid=".$objListEntry->getSystemid().$this->strPeAddon,
                    "",
                    $this->getLang("commons_edit_copy"),
                    "icon_copy"
                )
            );
        }
        return "";
    }

    /**
     * Returns an additional set of action-buttons rendered right after the edit-action.
     *
     * @param class_model $objListEntry
     * @return array
     */
    protected function renderAdditionalActions(class_model $objListEntry) {
        return array();
    }

    /**
     * Renders the action to add a new record to the end of the list.
     * Make sure you have the lang-key "module_action_new" in the modules' lang-file.
     * If you overwrite this method, you can either return a string containing the new-link or an array if you want to
     * provide multiple new-action.
     *
     * @param string $strListIdentifier an internal identifier to check the current parent-list
     * @param bool $bitDialog opens the linked pages in a dialog
     *
     * @return string|array
     */
    protected function getNewEntryAction($strListIdentifier, $bitDialog = false) {
        if($this->getObjModule()->rightEdit()) {
            if($bitDialog)
                return $this->objToolkit->listButton(
                    getLinkAdminDialog(
                        $this->getArrModule("modul"), $this->getActionNameForClass("new", null), "&systemid=".$this->getSystemid().$this->strPeAddon, $this->getLang("commons_list_new"), $this->getLang("commons_list_new"), "icon_new"
                    )
                );
            else
                return $this->objToolkit->listButton(
                    getLinkAdmin(
                        $this->getArrModule("modul"), $this->getActionNameForClass("new", null), "&systemid=".$this->getSystemid().$this->strPeAddon, $this->getLang("commons_list_new"), $this->getLang("commons_list_new"), "icon_new"
                    )
                );
        }
        return "";
    }

    /**
     * If multiple new actions are given, all buttons are merged into a new, single button.
     * The button itself is rendering the different buttons using a new dropdown-menu.
     * @param $arrActions
     * @return string
     */
    private function mergeNewEntryActions($arrActions) {
        if(!is_array($arrActions))
            return $arrActions;

        if(is_array($arrActions) && count($arrActions) == 0)
            return "";

        if(is_array($arrActions) && count($arrActions) == 1)
            return $arrActions[0];

        //create a menu and merge all buttons
        $arrActionMenuEntries = array();
        foreach($arrActions as $strOneAction) {
            $strOneAction = trim($strOneAction);
            //search for a title attribute
            $arrMatches = array();
            if(preg_match('/title=\"([a-zA-Z0-9\ \-\_]+)\"/i', $strOneAction, $arrMatches)) {
                if(uniSubstr($strOneAction, -11) == "</a></span>")
                    $strOneAction = uniSubstr($strOneAction, 0, -11).$arrMatches[1]."</a></span>";
                else
                    $strOneAction .= $arrMatches[1];
            }

            $arrActionMenuEntries[] = array("fullentry" => $strOneAction);
        }

        return $this->objToolkit->listButton(
            "<span class='dropdown pull-right'><a href='#' data-toggle='dropdown' role='button'>".class_adminskin_helper::getAdminImage("icon_new_multi")."</a>".$this->objToolkit->registerMenu(generateSystemid(), $arrActionMenuEntries)."</span>"
        );

    }

    /**
     * Overwrite this method if you want to provide a handler for a mass-action.
     * If one or more handler(s) are returned, the checkboxes to select a list of records
     * are rendered.
     *
     * @param $strListIdentifier
     *
     * @return class_admin_batchaction[]
     */
    protected function getBatchActionHandlers($strListIdentifier) {
        return array();
    }


    protected function getDefaultActionHandlers() {
        return array(
            new class_admin_batchaction(class_adminskin_helper::getAdminImage("icon_delete"), getLinkAdminXml("system", "delete", "&systemid=%systemid%"), $this->getLang("commons_batchaction_delete")),
            new class_admin_batchaction(class_adminskin_helper::getAdminImage("icon_enabled"), getLinkAdminXml("system", "setStatus", "&systemid=%systemid%&status=1"), $this->getLang("commons_batchaction_enable")),
            new class_admin_batchaction(class_adminskin_helper::getAdminImage("icon_disabled"), getLinkAdminXml("system", "setStatus", "&systemid=%systemid%&status=0"), $this->getLang("commons_batchaction_disable")),
        );
    }

    /**
     * Renders the button to open the records' change history. In most cases, this is done in a overlay.
     * To open the change-history, the permission "right3" on the system-module is required.
     *
     * @param class_model|interface_model $objListEntry
     *
     * @return string
     */
    protected function renderChangeHistoryAction(class_model $objListEntry) {
        if(_system_changehistory_enabled_ == "true" && $objListEntry instanceof interface_versionable && $objListEntry->rightEdit() && class_module_system_module::getModuleByName("system")->rightRight3()) {
            return $this->objToolkit->listButton(
                getLinkAdminDialog(
                    "system",
                    "genericChangelog",
                    "&systemid=".$objListEntry->getSystemid(),
                    $this->getLang("commons_edit_history"),
                    $this->getLang("commons_edit_history"),
                    "icon_history",
                    $objListEntry->getStrDisplayName()
                )
            );
        }
        return "";
    }

    public function setStrPeAddon($strPeAddon) {
        $this->strPeAddon = $strPeAddon;
    }

    public function getStrPeAddon() {
        return $this->strPeAddon;
    }



}

