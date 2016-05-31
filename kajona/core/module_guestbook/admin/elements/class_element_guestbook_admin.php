<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_guestbook_admin.php 5903 2013-09-30 13:40:29Z sidler $                              *
********************************************************************************************************/


/**
 * Class representing the admin-part of the guestbook element
 *
 * @package module_guestbook
 * @author sidler@mulchprod.de
 * @targetTable element_guestbook.content_id
 */
class class_element_guestbook_admin extends class_element_admin implements interface_admin_element {

    /**
     * @var string
     * @tableColumn element_guestbook.guestbook_id
     *
     * @fieldType dropdown
     * @fieldLabel guestbook_id
     */
    private $strGuestbook;

    /**
     * @var string
     * @tableColumn element_guestbook.guestbook_template
     *
     * @fieldType template
     * @fieldLabel template
     *
     * @fieldTemplateDir /module_guestbook
     */
    private $strTemplate;

    /**
     * @var int
     * @tableColumn element_guestbook.guestbook_amount
     *
     * @fieldType text
     * @fieldLabel guestbook_amount
     */
    private $intAmount;


    public function getAdminForm() {
        $objForm = parent::getAdminForm();

        $objGuestbooks = class_module_guestbook_guestbook::getObjectList();
        $arrGuestbooks = array();
        foreach ($objGuestbooks as $objOneGuestbook)
            $arrGuestbooks[$objOneGuestbook->getSystemid()] = $objOneGuestbook->getStrDisplayName();

        $objForm->getField("guestbook")->setArrKeyValues($arrGuestbooks);
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
     * @param string $strGuestbook
     */
    public function setStrGuestbook($strGuestbook) {
        $this->strGuestbook = $strGuestbook;
    }

    /**
     * @return string
     */
    public function getStrGuestbook() {
        return $this->strGuestbook;
    }

    /**
     * @param int $intAmount
     */
    public function setIntAmount($intAmount) {
        $this->intAmount = $intAmount;
    }

    /**
     * @return int
     */
    public function getIntAmount() {
        return $this->intAmount;
    }




}
