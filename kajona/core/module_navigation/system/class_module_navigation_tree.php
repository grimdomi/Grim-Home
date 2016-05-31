<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_navigation_tree.php 5924 2013-10-05 18:08:38Z sidler $                              *
********************************************************************************************************/

/**
 * Model for a navigation tree itself
 *
 * @package module_navigation
 * @author sidler@mulchprod.de
 * @targetTable navigation.navigation_id
 *
 * @module navigation
 * @moduleId _navigation_modul_id_
 */
class class_module_navigation_tree extends class_model implements interface_model, interface_admin_listable {

    /**
     * @var string
     * @tableColumn navigation_name
     * @listOrder
     * @fieldMandatory
     * @fieldLabel commons_title
     */
    private $strName = "";

    /**
     * @var string
     * @tableColumn navigation_folder_i
     */
    private $strFolderId = "";


    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrName();
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon() {
        return "icon_treeRoot";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo() {
        return "";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription() {
        return "";
    }

    /**
     * Returns an array of all navigation-trees available
     *
     * @param string $strPrevid
     * @param bool|int $intStart
     * @param bool|int $intEnd
     *
     * @return class_module_navigation_tree[]
     * @static
     */
    public static function getObjectList($strPrevid = "", $intStart = false, $intEnd = false) {
        return parent::getObjectList(class_module_system_module::getModuleIdByNr(_navigation_modul_id_), $intStart, $intEnd);
    }


    /**
     * Looks up a navigation by its name
     *
     * @param string $strName
     *
     * @return class_module_navigation_tree
     * @static
     */
    public static function getNavigationByName($strName) {
        $strQuery = "SELECT system_id
                     FROM " . _dbprefix_ . "navigation, " . _dbprefix_ . "system
                     WHERE system_id = navigation_id
                     AND system_prev_id = ?
                     AND navigation_name = ?
                     AND system_module_nr = ?
                     ORDER BY system_sort ASC, system_comment ASC";
        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array(class_module_system_module::getModuleIdByNr(_navigation_modul_id_), $strName, _navigation_modul_id_));
        if(isset($arrRow["system_id"])) {
            return new class_module_navigation_tree($arrRow["system_id"]);
        }
        else {
            return null;
        }

    }

    /**
     * Loads al nodes of a navigation, skipping inactive and non-viewable ones.
     * Includes transformed page-nodes!
     *
     * @return array
     */
    public function getCompleteNaviStructure() {
        $arrReturn = array();
        $arrReturn["node"] = null;
        $arrReturn["subnodes"] = $this->loadSingleLevel($this->getSystemid());
        return $arrReturn;
    }

    /**
     * Loads a singe level of nodes, internal recursion helper
     *
     * @param string $strParentNode
     *
     * @return array
     */
    private function loadSingleLevel($strParentNode) {
        $arrReturn = array();

        $arrCurLevel = class_module_navigation_point::getDynamicNaviLayer($strParentNode);

        if(isset($arrCurLevel["node"]) && isset($arrCurLevel["subnodes"])) {
            //switch between added nodes and "real" nodes
            $arrTemp = array();
            $arrTemp["node"] = $arrCurLevel["node"];
            $arrTemp["subnodes"] = $arrCurLevel["subnodes"];

            $arrReturn[] = $arrCurLevel;

        }

        /** @var class_module_navigation_point $objOneNode */
        foreach($arrCurLevel as $strKey => $objOneNode) {

            if($strKey !== "node" && $strKey !== "subnodes") {

                if($objOneNode->getStatus() == 1 && $objOneNode->rightView()) {
                    $arrTemp = array();
                    $arrTemp["node"] = $objOneNode;
                    $arrTemp["subnodes"] = $this->loadSingleLevel($objOneNode->getSystemid());

                    $arrReturn[] = $arrTemp;
                }
            }
        }

        return $arrReturn;
    }


    public function getStrName() {
        return $this->strName;
    }

    public function setStrName($strName) {
        $this->strName = $strName;
    }

    public function getStrFolderId() {
        return $this->strFolderId;
    }

    public function setStrFolderId($strFolderId) {
        $this->strFolderId = $strFolderId;
    }

}
