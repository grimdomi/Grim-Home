<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_installer_element_languageswitch.php 5925 2013-10-06 10:59:30Z sidler $                                   *
********************************************************************************************************/


/**
 * Installer for the languageswitch element
 *
 * @package element_languageswitch
 * @author sidler@mulchprod.de
 * @moduleId _pages_content_modul_id_
 */
class class_installer_element_languageswitch extends class_installer_base implements interface_installer {

    public function install() {

        //Register the element
        $strReturn = "Registering languageswitch-element...\n";

        //check, if not already existing
        $objElement = class_module_pages_element::getElement("languageswitch");
        if($objElement == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("languageswitch");
            $objElement->setStrClassAdmin("class_element_languageswitch_admin.php");
            $objElement->setStrClassPortal("class_element_languageswitch_portal.php");
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

        if(class_module_pages_element::getElement("languageswitch")->getStrVersion() == "3.4.2") {
            $strReturn .= "Updating 3.4.2 to 3.4.9...\n";
            $this->updateElementVersion("languageswitch", "3.4.9");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("languageswitch")->getStrVersion() == "3.4.9") {
            $strReturn .= "Updating 3.4.9 to 4.0...\n";
            $this->updateElementVersion("languageswitch", "4.0");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("languageswitch")->getStrVersion() == "4.0") {
            $strReturn .= "Updating 4.0 to 4.1...\n";
            $this->updateElementVersion("languageswitch", "4.1");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("languageswitch")->getStrVersion() == "4.1") {
            $strReturn .= "Updating 4.1 to 4.2...\n";
            $this->updateElementVersion("languageswitch", "4.2");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("languageswitch")->getStrVersion() == "4.2") {
            $strReturn .= "Updating 4.2 to 4.3...\n";
            $this->updateElementVersion("languageswitch", "4.3");
            $this->objDB->flushQueryCache();
        }

        return $strReturn."\n\n";
    }


}
