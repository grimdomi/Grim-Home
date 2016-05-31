<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_tags_admin.php 5924 2013-10-05 18:08:38Z sidler $                                  *
********************************************************************************************************/

/**
 * Admin-Part of the tags.
 * No classical functionality, rather a list of helper-methods, e.g. in order to
 * create the form to tag content.
 *
 * @package module_tags
 * @author sidler@mulchprod.de
 *
 * @objectList class_module_tags_tag
 * @objectEdit class_module_tags_tag
 *
 * @autoTestable list
 *
 * @module tags
 * @moduleId _tags_modul_id_
 */
class class_module_tags_admin extends class_admin_evensimpler implements interface_admin {

    public function getOutputModuleNavi() {
        $arrReturn = array();
        $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        $arrReturn[] = array("right1", getLinkAdmin($this->arrModule["modul"], "listFavorites", "", $this->getLang("action_list_favorites"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=" . $this->arrModule["modul"], $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));

        return $arrReturn;
    }



    protected function getNewEntryAction($strListIdentifier, $bitDialog = false) {
        return "";
    }

    protected function renderAdditionalActions(class_model $objListEntry) {
        if($objListEntry instanceof class_module_tags_tag) {
            $arrButtons = array();
            $arrButtons[] = $this->objToolkit->listButton(
                getLinkAdmin(
                    $this->getArrModule("modul"),
                    "showAssignedRecords",
                    "&systemid=" . $objListEntry->getSystemid(),
                    $this->getLang("action_show_assigned_records"),
                    $this->getLang("action_show_assigned_records"),
                    "icon_folderActionOpen"
                )
            );

            if($objListEntry->rightRight1()) {
                $arrButtons[] = $this->objToolkit->listButton(
                    getLinkAdmin(
                        $this->getArrModule("modul"),
                        "addToFavorites",
                        "&systemid=" . $objListEntry->getSystemid(),
                        $this->getLang("action_add_to_favorites"),
                        $this->getLang("action_add_to_favorites"),
                        "icon_favorite"
                    )
                );
            }

            return $arrButtons;

        }
        else {
            return array();
        }
    }


    /**
     * @param interface_model|class_model $objListEntry
     *
     * @return string
     */
    protected function renderDeleteAction(interface_model $objListEntry) {
        if($objListEntry instanceof class_module_tags_favorite) {
            if($objListEntry->rightDelete()) {
                return $this->objToolkit->listDeleteButton(
                    $objListEntry->getStrDisplayName(),
                    $this->getLang("delete_question_fav", $objListEntry->getArrModule("modul")),
                    getLinkAdminHref($objListEntry->getArrModule("modul"), "delete", "&systemid=" . $objListEntry->getSystemid())
                );
            }
        }
        else
            return parent::renderDeleteAction($objListEntry);


        return "";
    }


    /**
     * @permissions edit
     * @return string
     */
    protected function actionShowAssignedRecords() {
        //load tag
        $objTag = new class_module_tags_tag($this->getSystemid());
        //get assigned record-ids

        $objArraySectionIterator = new class_array_section_iterator($objTag->getIntAssignments());
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection($objTag->getArrAssignedRecords($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        return $this->renderList($objArraySectionIterator, false, "assignedTagList");
    }

    public function getActionIcons($objOneIterable, $strListIdentifier = "") {
        if($strListIdentifier == "assignedTagList") {
            //call the original module to render the action-icons
            $objAdminInstance = class_module_system_module::getModuleByName($objOneIterable->getArrModule("modul"))->getAdminInstanceOfConcreteModule();
            if($objAdminInstance != null && $objAdminInstance instanceof class_admin_simple) {
                return $objAdminInstance->getActionIcons($objOneIterable);
            }
        }

        return parent::getActionIcons($objOneIterable, $strListIdentifier);
    }

    /**
     * Renders the generic tag-form, in case to be embedded from external.
     * Therefore, two params are evaluated:
     *  - the param "systemid"
     *  - the param "attribute"
     *
     * @return string
     * @permissions edit
     */
    protected function actionGenericTagForm() {
        $this->setArrModuleEntry("template", "/folderview.tpl");
        return $this->getTagForm($this->getSystemid(), $this->getParam("attribute"));
    }

    /**
     * Generates a form to add tags to the passed systemid.
     * Since all functionality is performed using ajax, there's no page-reload when adding or removing tags.
     * Therefore the form-handling of existing forms can remain as is
     *
     * @param string $strTargetSystemid the systemid to tag
     * @param string $strAttribute additional info used to differ between tag-sets for a single systemid
     *
     * @return string
     * @permissions edit
     */
    public function getTagForm($strTargetSystemid, $strAttribute = null) {
        $strTagContent = "";

        $strTagsWrapperId = generateSystemid();

        $strTagContent .= $this->objToolkit->formHeader(
            getLinkAdminHref($this->arrModule["modul"], "saveTags"), "", "", "KAJONA.admin.tags.saveTag(document.getElementById('tagname').value+'', '" . $strTargetSystemid . "', '" . $strAttribute . "');return false;"
        );
        $strTagContent .= $this->objToolkit->formTextRow($this->getLang("tag_name_hint"));
        $strTagContent .= $this->objToolkit->formInputTagSelector("tagname", $this->getLang("form_tags_name"));
        $strTagContent .= $this->objToolkit->formInputSubmit($this->getLang("button_add"), $this->getLang("button_add"), "");
        $strTagContent .= $this->objToolkit->formClose();

        $strTagContent .= $this->objToolkit->getTaglistWrapper($strTagsWrapperId, $strTargetSystemid, $strAttribute);

        return $strTagContent;
    }

    protected function getOutputNaviEntry(interface_model $objInstance) {
        if($objInstance instanceof class_module_tags_tag)
            return getLinkAdmin($this->getArrModule("modul"), "showAssignedRecords", "&systemid=" . $objInstance->getSystemid(), $objInstance->getStrName());

        return null;
    }


    /**
     * Renders the list of favorites created by the current user
     *
     * @return string
     * @autoTestable
     * @permissions right1
     */
    protected function actionListFavorites() {

        $objArraySectionIterator = new class_array_section_iterator(class_module_tags_favorite::getNumberOfFavoritesForUser($this->objSession->getUserID()));
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(class_module_tags_favorite::getAllFavoritesForUser($this->objSession->getUserID(), $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        return $this->renderList($objArraySectionIterator);
    }

    /**
     * Adds a single tag to a users list of favorites
     *
     * @permissons right1
     */
    protected function actionAddToFavorites() {
        if(count(class_module_tags_favorite::getAllFavoritesForUserAndTag($this->objSession->getUserID(), $this->getSystemid())) == 0) {
            $objFavorite = new class_module_tags_favorite();
            $objFavorite->setStrUserId($this->objSession->getUserID());
            $objFavorite->setStrTagId($this->getSystemid());

            $objFavorite->updateObjectToDb();
        }

        $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "listFavorites"));
    }
}
