<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_installer_element_portallogin.php 5925 2013-10-06 10:59:30Z sidler $                               *
********************************************************************************************************/

/**
 * Installer to install a login-element to use in the portal
 *
 * @package element_portallogin
 * @author sidler@mulchprod.de
 * @moduleId _pages_content_modul_id_
 */
class class_installer_element_portallogin extends class_installer_base implements interface_installer {

	public function install() {
		$strReturn = "";

       	//Table for page-element
		$strReturn .= "Installing element_plogin-element table...\n";

		$arrFields = array();
		$arrFields["content_id"] 				= array("char20", false);
		$arrFields["portallogin_template"] 		= array("char254", true);
		$arrFields["portallogin_error"] 		= array("char254", true);
		$arrFields["portallogin_success"] 		= array("char254", true);
		$arrFields["portallogin_logout_success"]= array("char254", true);
        $arrFields["portallogin_profile"]       = array("char254", true);
        $arrFields["portallogin_pwdforgot"]     = array("char254", true);
        $arrFields["portallogin_editmode"]      = array("int", true);

		if(!$this->objDB->createTable("element_plogin", $arrFields, array("content_id")))
			$strReturn .= "An error occured! ...\n";

		//Register the element
		$strReturn .= "Registering portallogin-element...\n";
		//check, if not already existing
		if(class_module_pages_element::getElement("portallogin") == null) {
		    $objElement = new class_module_pages_element();
		    $objElement->setStrName("portallogin");
		    $objElement->setStrClassAdmin("class_element_portallogin_admin.php");
		    $objElement->setStrClassPortal("class_element_portallogin_portal.php");
		    $objElement->setIntCachetime(-1);
		    $objElement->setIntRepeat(1);
            $objElement->setStrVersion($this->objMetadata->getStrVersion());
			$objElement->updateObjectToDb();
			$strReturn .= "Element registered...\n";
		}
		else {
			$strReturn .= "Element already installed!...\n";
		}

		return $strReturn;
	}


	public function update() {
        $strReturn = "";

        if(class_module_pages_element::getElement("portallogin")->getStrVersion() == "3.4.2") {
            $strReturn .= "Updating element portallogin to 3.4.9...\n";
            $this->updateElementVersion("portallogin", "3.4.9");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("portallogin")->getStrVersion() == "3.4.9") {
            $strReturn .= "Updating element portallogin to 4.0...\n";
            $this->updateElementVersion("portallogin", "4.0");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("portallogin")->getStrVersion() == "4.0") {
            $strReturn .= "Updating element portallogin to 4.1...\n";
            $this->updateElementVersion("portallogin", "4.1");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("portallogin")->getStrVersion() == "4.1") {
            $strReturn .= "Updating element portallogin to 4.2...\n";
            $this->updateElementVersion("portallogin", "4.2");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("portallogin")->getStrVersion() == "4.2") {
            $strReturn .= "Updating element portallogin to 4.3...\n";
            $this->updateElementVersion("portallogin", "4.3");
            $this->objDB->flushQueryCache();
        }

        return $strReturn;
    }


}
