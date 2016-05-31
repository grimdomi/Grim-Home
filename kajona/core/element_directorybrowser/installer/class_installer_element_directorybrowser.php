<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_installer_element_directorybrowser.php 5925 2013-10-06 10:59:30Z sidler $                               *
********************************************************************************************************/

/**
 * Installer to install a directorybrowser-element to use in the portal
 *
 * @package element_directorybrowser
 * @moduleId _pages_content_modul_id_
 */
class class_installer_element_directorybrowser extends class_installer_base implements interface_installer {

	public function install() {
        $strReturn = "";

        //Register the element
        $strReturn .= "Registering directorybrowser-element...\n";
        //check, if not already existing
        $objElement = class_module_pages_element::getElement($this->objMetadata->getStrTitle());
        if($objElement == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName($this->objMetadata->getStrTitle());
            $objElement->setStrClassAdmin("class_element_directorybrowser_admin.php");
            $objElement->setStrClassPortal("class_element_directorybrowser_portal.php");
            $objElement->setIntCachetime(3600);
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

        if(class_module_pages_element::getElement($this->objMetadata->getStrTitle())->getStrVersion() == "1.0") {
            $strReturn = "Updating 1.0 to 1.1...\n";
            $this->updateElementVersion($this->objMetadata->getStrTitle(), "1.1");
            $this->objDB->flushQueryCache();
        }
        if(class_module_pages_element::getElement($this->objMetadata->getStrTitle())->getStrVersion() == "1.1") {
            $strReturn = "Updating 1.1 to 1.2...\n";
            $this->updateElementVersion($this->objMetadata->getStrTitle(), "1.2");
            $this->objDB->flushQueryCache();
        }

        return $strReturn."\n\n";
	}



}
