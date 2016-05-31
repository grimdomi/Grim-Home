<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_installer_element_tagto.php 5925 2013-10-06 10:59:30Z sidler $                                 *
********************************************************************************************************/

/**
 * Installer to install a tagto-element to use in the portal
 *
 * @package element_tagto
 * @moduleId _pages_content_modul_id_
 */
class class_installer_element_tagto extends class_installer_base implements interface_installer {

	public function install() {
		$strReturn = "";

		//Register the element
		$strReturn .= "Registering tagto-element...\n";
		//check, if not already existing
		if(class_module_pages_element::getElement("tagto") == null) {
		    $objElement = new class_module_pages_element();
		    $objElement->setStrName("tagto");
		    $objElement->setStrClassAdmin("class_element_tagto_admin.php");
		    $objElement->setStrClassPortal("class_element_tagto_portal.php");
		    $objElement->setIntCachetime(3600*24*30);
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

        if(class_module_pages_element::getElement("tagto")->getStrVersion() == "3.4.2") {
            $strReturn .= "Updating element tagto to 3.4.9...\n";
            $this->updateElementVersion("tagto", "3.4.9");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("tagto")->getStrVersion() == "3.4.9") {
            $strReturn .= "Updating element tagto to 4.0...\n";
            $this->updateElementVersion("tagto", "4.0");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("tagto")->getStrVersion() == "4.0") {
            $strReturn .= "Updating element tagto to 4.1...\n";
            $this->updateElementVersion("tagto", "4.1");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("tagto")->getStrVersion() == "4.1") {
            $strReturn .= "Updating element tagto to 4.2...\n";
            $this->updateElementVersion("tagto", "4.2");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("tagto")->getStrVersion() == "4.2") {
            $strReturn .= "Updating element tagto to 4.3...\n";
            $this->updateElementVersion("tagto", "4.3");
            $this->objDB->flushQueryCache();
        }

        return $strReturn;
    }

}
