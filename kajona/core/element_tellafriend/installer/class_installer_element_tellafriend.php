<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_installer_element_tellafriend.php 5925 2013-10-06 10:59:30Z sidler $                          *
********************************************************************************************************/

/**
 * Installer to install a tellafriend-element to use in the portal
 *
 * @package element_tellafriend
 * @author sidler@mulchprod.de
 * @moduleId _pages_content_modul_id_
 */
class class_installer_element_tellafriend extends class_installer_base implements interface_installer {

	public function install() {
		$strReturn = "";

		//Table for page-element
		$strReturn .= "Installing tellafriend-element table...\n";

		$arrFields = array();
		$arrFields["content_id"] 			= array("char20", false);
		$arrFields["tellafriend_template"] 	= array("char254", true);
		$arrFields["tellafriend_error"] 	= array("char254", true);
		$arrFields["tellafriend_success"] 	= array("char254", true);

		if(!$this->objDB->createTable("element_tellafriend", $arrFields, array("content_id")))
			$strReturn .= "An error occured! ...\n";

		//Register the element
		$strReturn .= "Registering tellafriend-element...\n";
		//check, if not already existing
		if(class_module_pages_element::getElement("tellafriend") == null) {
		    $objElement = new class_module_pages_element();
		    $objElement->setStrName("tellafriend");
		    $objElement->setStrClassAdmin("class_element_tellafriend_admin.php");
		    $objElement->setStrClassPortal("class_element_tellafriend_portal.php");
		    $objElement->setIntCachetime(-1);
		    $objElement->setIntRepeat(0);
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
        if(class_module_pages_element::getElement("tellafriend")->getStrVersion() == "3.4.2") {
            $strReturn .= "Updating element tellafriend to 3.4.9...\n";
            $this->updateElementVersion("tellafriend", "3.4.9");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("tellafriend")->getStrVersion() == "3.4.9") {
            $strReturn .= "Updating element tellafriend to 4.0...\n";
            $this->updateElementVersion("tellafriend", "4.0");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("tellafriend")->getStrVersion() == "4.0") {
            $strReturn .= "Updating element tellafriend to 4.1...\n";
            $this->updateElementVersion("tellafriend", "4.1");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("tellafriend")->getStrVersion() == "4.1") {
            $strReturn .= "Updating element tellafriend to 4.2...\n";
            $this->updateElementVersion("tellafriend", "4.2");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("tellafriend")->getStrVersion() == "4.2") {
            $strReturn .= "Updating element tellafriend to 4.3...\n";
            $this->updateElementVersion("tellafriend", "4.3");
            $this->objDB->flushQueryCache();
        }

        return $strReturn;
    }


}
