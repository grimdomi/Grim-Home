<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_formular_admin.php 5903 2013-09-30 13:40:29Z sidler $                                   *
********************************************************************************************************/

/**
 * Class to handle the admin-stuff of the formular-element
 *
 * @package element_formular
 * @author sidler@mulchprod.de
 *
 * @targetTable element_formular.content_id
 */
class class_element_formular_admin extends class_element_admin implements interface_admin_element {

    /**
     * @var string
     * @tableColumn element_formular.formular_class
     *
     * @fieldType dropdown
     * @fieldLabel formular_class
     * @fieldMandatory
     *
     * @elementContentTitle
     */
    private $strClass;

    /**
     * @var string
     * @tableColumn element_formular.formular_email
     *
     * @fieldType text
     * @fieldLabel formular_email
     * @fieldValidator email
     * @fieldMandatory
     */
    private $strEmail;

    /**
     * @var string
     * @tableColumn element_formular.formular_success
     *
     * @fieldType text
     * @fieldLabel formular_success
     */
    private $strSuccess;

    /**
     * @var string
     * @tableColumn element_formular.formular_error
     *
     * @fieldType text
     * @fieldLabel formular_error
     */
    private $strError;

    /**
     * @var string
     * @tableColumn element_formular.formular_template
     *
     * @fieldType template
     * @fieldLabel template
     *
     * @fieldTemplateDir /element_form
     */
    private $strTemplate;

    public function getAdminForm() {
        $objForm = parent::getAdminForm();

        $arrClassesDD = array();
        foreach(class_resourceloader::getInstance()->getFolderContent("/portal/forms", array(".php")) as $strClass) {
            $arrClassesDD[$strClass] = $strClass;
        }

        $objForm->getField("class")->setArrKeyValues($arrClassesDD);
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
     * @param string $strSuccess
     */
    public function setStrSuccess($strSuccess) {
        $this->strSuccess = $strSuccess;
    }

    /**
     * @return string
     */
    public function getStrSuccess() {
        return $this->strSuccess;
    }

    /**
     * @param string $strError
     */
    public function setStrError($strError) {
        $this->strError = $strError;
    }

    /**
     * @return string
     */
    public function getStrError() {
        return $this->strError;
    }

    /**
     * @param string $strEmail
     */
    public function setStrEmail($strEmail) {
        $this->strEmail = $strEmail;
    }

    /**
     * @return string
     */
    public function getStrEmail() {
        return $this->strEmail;
    }

    /**
     * @param string $strClass
     */
    public function setStrClass($strClass) {
        $this->strClass = $strClass;
    }

    /**
     * @return string
     */
    public function getStrClass() {
        return $this->strClass;
    }





}
