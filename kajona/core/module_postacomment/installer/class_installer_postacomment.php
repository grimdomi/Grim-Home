<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_installer_postacomment.php 5925 2013-10-06 10:59:30Z sidler $                                *
********************************************************************************************************/

/**
 * Class providing an install for the postacomment module
 *
 * @package module_postacomment
 * @moduleId _postacomment_modul_id_
 */
class class_installer_postacomment extends class_installer_base implements interface_installer {

    public function install() {
		$strReturn = "";

		$strReturn .= "Installing table postacomment...\n";

		$arrFields = array();
		$arrFields["postacomment_id"] 		= array("char20", false);
		$arrFields["postacomment_date"] 	= array("int", true);
		$arrFields["postacomment_page"] 	= array("char254", true);
		$arrFields["postacomment_language"] = array("char20", true);
		$arrFields["postacomment_systemid"] = array("char20", true);
		$arrFields["postacomment_username"] = array("char254", true);
		$arrFields["postacomment_title"] 	= array("char254", true);
		$arrFields["postacomment_comment"] 	= array("text", true);

		if(!$this->objDB->createTable("postacomment", $arrFields, array("postacomment_id")))
			$strReturn .= "An error occured! ...\n";


		//register the module
		$strSystemID = $this->registerModule(
            "postacomment",
		    _postacomment_modul_id_,
		    "class_module_postacomment_portal.php",
		    "class_module_postacomment_admin.php",
            $this->objMetadata->getStrVersion(),
		    true,
		    "class_module_postacomment_portal_xml.php");

		//modify default rights to allow guests to post
		$strReturn .= "Modifying modules' rights node...\n";
		$this->objRights->addGroupToRight(_guests_group_id_, $strSystemID, "right1");
		$this->objRights->addGroupToRight(_guests_group_id_, $strSystemID, "right2");


        $strReturn .= "Registering postacomment-element...\n";
        //check, if not already existing
        $objElement = class_module_pages_element::getElement("postacomment");
        if($objElement == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("postacomment");
            $objElement->setStrClassAdmin("class_element_postacomment_admin.php");
            $objElement->setStrClassPortal("class_element_postacomment_portal.php");
            $objElement->setIntCachetime(-1);
            $objElement->setIntRepeat(0);
            $objElement->setStrVersion($this->objMetadata->getStrVersion());
            $objElement->updateObjectToDb();
            $strReturn .= "Element registered...\n";
        }
        else {
            $strReturn .= "Element already installed!...\n";
        }

        $strReturn .= "Setting aspect assignments...\n";
        if(class_module_system_aspect::getAspectByName("content") != null) {
            $objModule = class_module_system_module::getModuleByName($this->objMetadata->getStrTitle());
            $objModule->setStrAspect(class_module_system_aspect::getAspectByName("content")->getSystemid());
            $objModule->updateObjectToDb();
        }

		return $strReturn;

	}



	public function update() {
	    $strReturn = "";
        //check installed version and to which version we can update
        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);

        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";


        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.2") {
            $strReturn .= $this->update_342_349();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.9") {
            $strReturn .= $this->update_349_40();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.0") {
            $strReturn .= $this->update_40_41();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.1") {
            $strReturn = "Updating 4.1 to 4.2...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.2");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("postacomment", "4.2");
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.2") {
            $strReturn = "Updating 4.2 to 4.3...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.3");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("postacomment", "4.3");
        }

        return $strReturn."\n\n";
	}




    private function update_342_349() {
        $strReturn = "Updating 3.4.1.1 to 3.4.9...\n";

        $strReturn .= "Adding classes for existing records...\n";


        $strReturn .= "Postacomment\n";
        $arrRows = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."postacomment, "._dbprefix_."system WHERE system_id = postacomment_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_postacomment_post', $arrOneRow["system_id"] ) );
        }

        $strReturn .= "Removing old notify-constant\n";
        $strQuery = "DELETE FROM "._dbprefix_."system_config WHERE system_config_name = ? ";
        $this->objDB->_pQuery($strQuery, array("_postacomment_notify_mail_"));

        $strReturn .= "Setting aspect assignments...\n";
        if(class_module_system_aspect::getAspectByName("content") != null) {
            $objModule = class_module_system_module::getModuleByName($this->objMetadata->getStrTitle());
            $objModule->setStrAspect(class_module_system_aspect::getAspectByName("content")->getSystemid());
            $objModule->updateObjectToDb();
        }

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "3.4.9");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("postacomment", "3.4.9");

        return $strReturn;
    }

    private function update_349_40() {
        $strReturn = "Updating 3.4.9 to 4.0...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.0");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("postacomment", "4.0");
        return $strReturn;
    }

    private function update_40_41() {
        $strReturn = "Updating 4.0 to 4.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("postacomment", "4.1");
        return $strReturn;
    }

}
