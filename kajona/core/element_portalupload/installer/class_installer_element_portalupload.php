<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_installer_element_portalupload.php 5925 2013-10-06 10:59:30Z sidler $                          *
********************************************************************************************************/

/**
 * Installer to install a login-element to use in the portal
 *
 * @package element_portalupload
 * @moduleId _pages_content_modul_id_
 */
class class_installer_element_portalupload extends class_installer_base implements interface_installer {

    public function install() {
		$strReturn = "";

		//Register the element
        $strReturn .= "Registering portalupload-element...\n";
        //check, if not already existing
        if(class_module_pages_element::getElement("portalupload") == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("portalupload");
            $objElement->setStrClassAdmin("class_element_portalupload_admin.php");
            $objElement->setStrClassPortal("class_element_portalupload_portal.php");
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

        if(class_module_pages_element::getElement($this->objMetadata->getStrTitle())->getStrVersion() == "3.4.2") {
            $strReturn .= "Updating element portalupload to 3.4.9...\n";
            $this->updateElementVersion("portalupload", "3.4.9");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement($this->objMetadata->getStrTitle())->getStrVersion() == "3.4.9") {
            $strReturn .= "Updating element portalupload to 4.0...\n";
            $this->updateElementVersion("portalupload", "4.0");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement($this->objMetadata->getStrTitle())->getStrVersion() == "4.0") {
            $strReturn .= "Updating element portalupload to 4.1...\n";
            $this->updateElementVersion("portalupload", "4.1");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement($this->objMetadata->getStrTitle())->getStrVersion() == "4.1") {
            $strReturn .= "Updating element portalupload to 4.2...\n";
            $this->updateElementVersion("portalupload", "4.2");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement($this->objMetadata->getStrTitle())->getStrVersion() == "4.2") {
            $strReturn .= "Updating element portalupload to 4.2...\n";
            $this->updateElementVersion("portalupload", "4.3");
            $this->objDB->flushQueryCache();
        }

        return $strReturn;
    }


}
