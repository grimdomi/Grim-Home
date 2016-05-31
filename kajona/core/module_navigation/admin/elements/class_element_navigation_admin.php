<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: class_element_navigation_admin.php 5903 2013-09-30 13:40:29Z sidler $                                 *
********************************************************************************************************/

/**
 * Admin class of the navigation element
 *
 * @package module_navigation
 * @author sidler@mulchprod.de
 *
 * @targetTable element_navigation.content_id
 */
class class_element_navigation_admin extends class_element_admin implements interface_admin_element {


    /**
     * @var string
     * @tableColumn element_navigation.navigation_id
     * @fieldType dropdown
     * @fieldLabel commons_name
     */
    private $strRepo;

    /**
     * @var string
     * @tableColumn element_navigation.navigation_template
     * @fieldType template
     * @fieldLabel template
     * @fieldTemplateDir /module_navigation
     */
    private $strTemplate;

    /**
     * @var int
     * @tableColumn element_navigation.navigation_foreign
     * @fieldType yesno
     * @fieldLabel navigation_foreign
     */
    private $intForeign;


    public function getAdminForm() {
        $objForm = parent::getAdminForm();

        $arrNavigationsDropdown = array();
        foreach(class_module_navigation_tree::getObjectList() as $objOneNavigation)
            $arrNavigationsDropdown[$objOneNavigation->getSystemid()] = $objOneNavigation->getStrDisplayName();
        $objForm->getField("repo")->setArrKeyValues($arrNavigationsDropdown);

        return $objForm;
    }

    /**
     * @param string $strTemplate
     */
    public function setStrTemplate($strTemplate) {
        $this->strTemplate = $strTemplate;
    }

    /**
     * @return string
     */
    public function getStrTemplate() {
        return $this->strTemplate;
    }

    /**
     * @param string $strRepo
     */
    public function setStrRepo($strRepo) {
        $this->strRepo = $strRepo;
    }

    /**
     * @return string
     */
    public function getStrRepo() {
        return $this->strRepo;
    }

    /**
     * @param int $intForeign
     */
    public function setIntForeign($intForeign) {
        $this->intForeign = $intForeign;
    }

    /**
     * @return int
     */
    public function getIntForeign() {
        return $this->intForeign;
    }





}
