<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_installer_element_gallery.php 5925 2013-10-06 10:59:30Z sidler $                                            *
********************************************************************************************************/

/**
 * Installer to install the mediamanager-module
 *
 * @package module_mediamanager
 * @moduleId _mediamanager_module_id_
 */
class class_installer_element_gallery extends class_installer_base implements interface_installer {

	public function __construct() {
		parent::__construct();
        $this->objMetadata->setStrTitle("gallery");
        $this->objMetadata->setStrType(class_module_packagemanager_manager::STR_TYPE_ELEMENT);
	}

	public function install() {
		$strReturn = "";

        if(class_module_system_module::getModuleByName("mediamanager") == null)
            return "Mediamanger not installed, skipping element\n";

		//Table for page-element
		$strReturn .= "Installing gallery-element table...\n";

		$arrFields = array();
		$arrFields["content_id"] 			= array("char20", false);
		$arrFields["gallery_id"] 			= array("char20", true);
		$arrFields["gallery_mode"] 			= array("int", true);
		$arrFields["gallery_template"] 		= array("char254", true);
		$arrFields["gallery_maxh_d"] 		= array("int", true);
		$arrFields["gallery_maxw_d"] 		= array("int", true);
		$arrFields["gallery_imagesperpage"] = array("int", true);
		$arrFields["gallery_text"] 			= array("char254", true);
		$arrFields["gallery_overlay"]    	= array("char254", true);
		$arrFields["gallery_text_x"] 		= array("int", true);
		$arrFields["gallery_text_y"] 		= array("int", true);

		if(!$this->objDB->createTable("element_gallery", $arrFields, array("content_id")))
			$strReturn .= "An error occured! ...\n";

		//Register the element
		$strReturn .= "Registering gallery-element...\n";
        $objElement = null;
		if(class_module_system_module::getModuleByName("pages") !== null && class_module_pages_element::getElement("gallery") == null) {
		    $objElement = new class_module_pages_element();
		    $objElement->setStrName("gallery");
		    $objElement->setStrClassAdmin("class_element_gallery_admin.php");
		    $objElement->setStrClassPortal("class_element_gallery_portal.php");
		    $objElement->setIntCachetime(3600);
		    $objElement->setIntRepeat(1);
            $objElement->setStrVersion($this->objMetadata->getStrVersion());
			$objElement->updateObjectToDb();
			$strReturn .= "Element registered...\n";
		}
		else {
			$strReturn .= "Element already installed or pages module not installed!...\n";
		}


		$strReturn .= "Registering galleryRandom-element...\n";
		if( class_module_system_module::getModuleByName("pages") !== null && class_module_pages_element::getElement("galleryRandom") == null) {
		    $objElement = new class_module_pages_element();
		    $objElement->setStrName("galleryRandom");
		    $objElement->setStrClassAdmin("class_element_galleryRandom_admin.php");
		    $objElement->setStrClassPortal("class_element_gallery_portal.php");
		    $objElement->setIntCachetime(-1);
		    $objElement->setIntRepeat(1);
            $objElement->setStrVersion($this->objMetadata->getStrVersion());
			$objElement->updateObjectToDb();
			$strReturn .= "Element registered...\n";
		}
		else {
			$strReturn .= "Element already installed or pages module not installed!...\n";
		}

		return $strReturn;
	}



    public function update() {
        $strReturn = "";
        if(class_module_pages_element::getElement("gallery")->getStrVersion() == "3.4.2") {
            $strReturn .= $this->update_342_349();
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("gallery")->getStrVersion() == "3.4.9"
            || class_module_pages_element::getElement("gallery")->getStrVersion() == "3.4.9.1"
            || class_module_pages_element::getElement("gallery")->getStrVersion() == "3.4.9.2"
            || class_module_pages_element::getElement("gallery")->getStrVersion() == "3.4.9.3"
        ) {
            $strReturn .= "Updating element gallery to 4.0...\n";
            $this->updateElementVersion("gallery", "4.0");
            $this->updateElementVersion("galleryRandom", "4.0");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("gallery")->getStrVersion() == "4.0") {
            $strReturn .= "Updating element gallery to 4.1...\n";
            $this->updateElementVersion("gallery", "4.1");
            $this->updateElementVersion("galleryRandom", "4.1");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("gallery")->getStrVersion() == "4.1") {
            $strReturn .= "Updating element gallery to 4.2...\n";
            $this->updateElementVersion("gallery", "4.2");
            $this->updateElementVersion("galleryRandom", "4.2");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("gallery")->getStrVersion() == "4.2") {
            $strReturn .= "Updating element gallery to 4.3...\n";
            $this->updateElementVersion("gallery", "4.3");
            $this->updateElementVersion("galleryRandom", "4.3");
            $this->objDB->flushQueryCache();
        }

        return $strReturn;
    }

    private function update_342_349() {
        $strReturn = "Updating element gallery to 3.4.9...\n";

        $strReturn .= "Migrating old gallery-element table...\n";

        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."element_gallery")."
                            DROP ".$this->objDB->encloseColumnName("gallery_maxh_p").",
                            DROP ".$this->objDB->encloseColumnName("gallery_maxw_p").",
                            DROP ".$this->objDB->encloseColumnName("gallery_maxh_m").",
                            DROP ".$this->objDB->encloseColumnName("gallery_maxw_m")."";
        if(!$this->objDB->_pQuery($strQuery, array()))
            $strReturn .= "An error occured! ...\n";

        $this->updateElementVersion("gallery", "3.4.9");
        $this->updateElementVersion("galleryRandom", "3.4.9");
        return $strReturn;
    }



}
