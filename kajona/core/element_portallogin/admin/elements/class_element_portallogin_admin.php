<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_portallogin_admin.php 5903 2013-09-30 13:40:29Z sidler $                                   *
********************************************************************************************************/

/**
 * Class to handle the admin-stuff of the portallogin-element
 *
 * @package element_portallogin
 * @author sidler@mulchprod.de
 *
 * @targetTable element_plogin.content_id
 */
class class_element_portallogin_admin extends class_element_admin implements interface_admin_element {

    /**
     * @var string
     * @tableColumn element_plogin.portallogin_template
     *
     * @fieldType template
     * @fieldLabel template
     *
     * @fieldTemplateDir /element_portallogin
     */
    private $strTemplate;

    /**
     * @var string
     * @tableColumn element_plogin.portallogin_error
     *
     * @fieldType page
     * @fieldLabel portallogin_error
     */
    private $strError;

    /**
     * @var string
     * @tableColumn element_plogin.portallogin_success
     *
     * @fieldType page
     * @fieldLabel commons_page_success
     */
    private $strSuccess;

    /**
     * @var string
     * @tableColumn element_plogin.portallogin_logout_success
     *
     * @fieldType page
     * @fieldLabel portallogin_logout_success
     */
    private $strLogout;

    /**
     * @var string
     * @tableColumn element_plogin.portallogin_profile
     *
     * @fieldType page
     * @fieldLabel portallogin_profile
     */
    private $strProfile;

    /**
     * @var string
     * @tableColumn element_plogin.portallogin_pwdforgot
     *
     * @fieldType page
     * @fieldLabel portallogin_pwdforgot
     * @fieldDDValues [0=>portallogin_editmode_0],[1=>portallogin_editmode_1]
     */
    private $strPwdForgot;

    /**
     * @var string
     * @tableColumn element_plogin.portallogin_editmode
     *
     * @fieldType dropdown
     * @fieldLabel portallogin_editmode
     * @fieldDDValues [0=>portallogin_editmode_0],[1=>portallogin_editmode_1]
     */
    private $strEditmode;




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
     * @param string $strPwdForgot
     */
    public function setStrPwdForgot($strPwdForgot) {
        $this->strPwdForgot = $strPwdForgot;
    }

    /**
     * @return string
     */
    public function getStrPwdForgot() {
        return $this->strPwdForgot;
    }

    /**
     * @param string $strProfile
     */
    public function setStrProfile($strProfile) {
        $this->strProfile = $strProfile;
    }

    /**
     * @return string
     */
    public function getStrProfile() {
        return $this->strProfile;
    }

    /**
     * @param string $strLogout
     */
    public function setStrLogout($strLogout) {
        $this->strLogout = $strLogout;
    }

    /**
     * @return string
     */
    public function getStrLogout() {
        return $this->strLogout;
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
     * @param string $strEditmode
     */
    public function setStrEditmode($strEditmode) {
        $this->strEditmode = $strEditmode;
    }

    /**
     * @return string
     */
    public function getStrEditmode() {
        return $this->strEditmode;
    }






}
