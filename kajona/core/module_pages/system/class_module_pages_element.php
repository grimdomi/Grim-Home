<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_pages_element.php 5924 2013-10-05 18:08:38Z sidler $                                *
********************************************************************************************************/

/**
 * Model for a element. This is the "raw"-element, not the element on a page
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 * @targetTable element.element_id
 *
 * @module pages_content
 * @moduleId _pages_content_modul_id_
 */
class class_module_pages_element extends class_model implements interface_model, interface_admin_listable {

    /**
     * @var string
     * @tableColumn element_name
     * @listOrder
     *
     * @fieldMandatory
     * @fieldType text
     * @fieldLabel commons_name
     */
    private $strName = "";

    /**
     * @var string
     * @tableColumn element_class_portal
     *
     * @fieldType dropdown
     */
    private $strClassPortal = "";

    /**
     * @var string
     * @tableColumn element_class_admin
     *
     * @fieldType dropdown
     */
    private $strClassAdmin = "";

    /**
     * @var int
     * @tableColumn element_repeat
     *
     * @fieldType yesno
     */
    private $intRepeat = "";

    /**
     * @var int
     * @tableColumn element_cachetime
     *
     * @fieldMandatory
     * @fieldValidator numeric
     * @fieldType text
     */
    private $intCachetime = "";

    /**
     * @var string
     * @tableColumn element_version
     */
    private $strVersion = "";

    /**
     * @var string
     * @tableColumn element_config1
     */
    private $strConfigVal1 = "";

    /**
     * @var string
     * @tableColumn element_config2
     */
    private $strConfigVal2 = "";

    /**
     * @var string
     * @tableColumn element_config3
     */
    private $strConfigVal3 = "";

    protected function deleteObjectInternal() {

        //delete elements in the database
        $arrElements = $this->objDB->getPArray("SELECT page_element_id FROM "._dbprefix_."page_element WHERE page_element_ph_element = ?", array($this->getStrName()));
        foreach($arrElements as $arrOneRow) {
            $objElement = new class_module_pages_pageelement($arrOneRow["page_element_id"]);
            $objElement->deleteObject();
        }

        return parent::deleteObjectInternal();
    }


    public function rightView() {
        return true;
    }

    public function rightEdit() {
        return true;
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        $strName = class_carrier::getInstance()->getObjLang()->getLang("element_".$this->getStrName()."_name", "elements");
        if($strName == "!element_".$this->getStrName()."_name!")
            $strName = $this->getStrName();
        else
            $strName .= " (".$this->getStrName().")";
        return $strName;
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon() {
        return "icon_dot";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo() {
        return " V ".$this->getStrVersion()." (".($this->getIntCachetime() == "-1" ? "<b>".$this->getIntCachetime()."</b>" : $this->getIntCachetime()).")";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription() {
        $objAdminInstance = $this->getAdminElementInstance();
        return $objAdminInstance->getElementDescription();
    }

    /**
     * Returns the element using the given element-name
     *
     * @param string $strName
     *
     * @return class_module_pages_element
     */
    public static function getElement($strName) {
        $strQuery = "SELECT element_id FROM "._dbprefix_."element WHERE element_name=?";
        $arrId = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strName));
        if(isset($arrId["element_id"]))
            return new class_module_pages_element($arrId["element_id"]);
        else
            return null;
    }


    /**
     * Factory method, creates an instance of the admin-element represented by this page-element.
     * The admin-element won't get initialized by a systemid, so you shouldn't retrieve
     * it for further usings.
     *
     * @throws class_exception
     * @return interface_admin_element|class_element_admin An instance of the admin-class linked by the current element
     */
    public function getAdminElementInstance() {
        //Build the class-name
        $strElementClass = str_replace(".php", "", $this->getStrClassAdmin());
        //and finally create the object
        if(class_exists($strElementClass)) {
            $objElement = new $strElementClass();
            return $objElement;
        }
        else {
            throw new class_exception("element class ".$strElementClass." not existing", class_exception::$level_FATALERROR);
        }
    }


    public function getStrName() {
        return $this->strName;
    }

    public function getStrClassPortal() {
        return $this->strClassPortal;
    }

    public function getStrClassAdmin() {
        return $this->strClassAdmin;
    }

    public function getIntRepeat() {
        return (int)$this->intRepeat;
    }

    public function getIntCachetime() {
        return $this->intCachetime;
    }

    /**
     * Returns a readable representation of the current elements' name.
     * Searches the lang-file for an entry element_NAME_name.
     *
     * @return string
     * @deprecated use getStrDisplayName()
     * @fixme remove me
     */
    public function getStrReadableName() {
        $strName = class_carrier::getInstance()->getObjLang()->getLang("element_".$this->getStrName()."_name", "elemente");
        if($strName == "!element_".$this->getStrName()."_name!")
            $strName = $this->getStrName();
        return $strName;
    }


    public function setStrName($strName) {
        $this->strName = $strName;
    }

    public function setStrClassPortal($strClassPortal) {
        $this->strClassPortal = $strClassPortal;
    }

    public function setStrClassAdmin($strClassAdmin) {
        $this->strClassAdmin = $strClassAdmin;
    }

    public function setIntRepeat($intRepeat) {
        $this->intRepeat = $intRepeat;
    }

    public function setIntCachetime($intCachetime) {
        $this->intCachetime = $intCachetime;
    }

    public function getStrVersion() {
        return $this->strVersion;
    }

    public function setStrVersion($strVersion) {
        $this->strVersion = $strVersion;
    }

    /**
     * @param string $strConfigVal1
     */
    public function setStrConfigVal1($strConfigVal1) {
        $this->strConfigVal1 = $strConfigVal1;
    }

    /**
     * @return string
     */
    public function getStrConfigVal1() {
        return $this->strConfigVal1;
    }

    /**
     * @param string $strConfigVal2
     */
    public function setStrConfigVal2($strConfigVal2) {
        $this->strConfigVal2 = $strConfigVal2;
    }

    /**
     * @return string
     */
    public function getStrConfigVal2() {
        return $this->strConfigVal2;
    }

    /**
     * @param string $strConfigVal3
     */
    public function setStrConfigVal3($strConfigVal3) {
        $this->strConfigVal3 = $strConfigVal3;
    }

    /**
     * @return string
     */
    public function getStrConfigVal3() {
        return $this->strConfigVal3;
    }

}
