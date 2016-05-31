<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_installer_rating.php 5925 2013-10-06 10:59:30Z sidler $                                        *
********************************************************************************************************/

/**
 * Class providing an installer for the rating module
 *
 * @package module_rating
 * @moduleId _rating_modul_id_
 */
class class_installer_rating extends class_installer_base implements interface_installer {

    public function install() {
		$strReturn = "";
		//Tabellen anlegen

		//rating ----------------------------------------------------------------------------------
		$strReturn .= "Installing table rating...\n";

		$arrFields = array();
		$arrFields["rating_id"] 		= array("char20", false);
		$arrFields["rating_systemid"] 	= array("char20", true);
		$arrFields["rating_checksum"] 	= array("char254", true);
		$arrFields["rating_rate"]       = array("double", true);
		$arrFields["rating_hits"]       = array("int", true);

		if(!$this->objDB->createTable("rating", $arrFields, array("rating_id")))
			$strReturn .= "An error occured! ...\n";


		$strReturn .= "Installing table rating_history...\n";

        $arrFields = array();
        $arrFields["rating_history_id"]     = array("char20", false);
        $arrFields["rating_history_rating"] = array("char20", true);
        $arrFields["rating_history_user"]   = array("char20", true);
        $arrFields["rating_history_timestamp"]= array("int", true);
        $arrFields["rating_history_value"]  = array("double", true);

        if(!$this->objDB->createTable("rating_history", $arrFields, array("rating_history_id")))
            $strReturn .= "An error occured! ...\n";


		//register the module
		$strSystemID = $this->registerModule(
            "rating",
             _rating_modul_id_,
             "class_module_rating_portal.php",
             "",
            $this->objMetadata->getStrVersion(),
             false,
             "class_module_rating_portal_xml.php"
        );

        $strReturn .= "Module registered. Module-ID: ".$strSystemID." \n";

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
            $strReturn .= "Updating 3.4.9 to 4.0...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("rating", "4.0");
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.0") {
            $strReturn .= "Updating 4.0 to 4.1...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("rating", "4.1");
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.1") {
            $strReturn .= "Updating 4.1 to 4.2...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("rating", "4.2");
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.2") {
            $strReturn .= "Updating 4.2 to 4.3...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("rating", "4.3");
        }

        return $strReturn."\n\n";
	}



    private function update_342_349() {
        $strReturn = "Updating 3.4.2 to 3.4.9...\n";
        $strReturn .= "Updating module definitions...\n";
        $objModule = class_module_system_module::getModuleByName("rating");
        $objModule->setStrNamePortal("class_module_rating_portal.php");
        $objModule->updateObjectToDb();


        $strReturn .= "Adding classes for existing records...\n";
        $arrRows = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."rating, "._dbprefix_."system WHERE system_id = rating_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_rating_rate', $arrOneRow["system_id"] ) );
        }

        $strReturn .= "Setting aspect assignments...\n";
        if(class_module_system_aspect::getAspectByName("content") != null) {
            $objModule = class_module_system_module::getModuleByName($this->objMetadata->getStrTitle());
            $objModule->setStrAspect(class_module_system_aspect::getAspectByName("content")->getSystemid());
            $objModule->updateObjectToDb();
        }

        $strReturn .= "Updating module-versions...\n";

        $this->updateModuleVersion("rating", "3.4.9");
        return $strReturn;
    }



}
