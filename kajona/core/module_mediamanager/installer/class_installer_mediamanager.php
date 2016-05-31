<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_installer_mediamanager.php 5930 2013-10-08 07:22:07Z sidler $                                            *
********************************************************************************************************/

/**
 * Installer to install the mediamanager-module
 *
 * @package module_mediamanager
 * @moduleId _mediamanager_module_id_
 */
class class_installer_mediamanager extends class_installer_base implements interface_installer {

    public function install() {

		$strReturn = "Installing ".$this->objMetadata->getStrTitle()."...\n";
		//Tabellen anlegen

		$strReturn .= "Installing table mediamanager_repo...\n";

		$arrFields = array();
		$arrFields["repo_id"] 	            = array("char20", false);
		$arrFields["repo_path"]             = array("char254", true);
		$arrFields["repo_title"]            = array("char254", true);
		$arrFields["repo_upload_filter"]    = array("char254", true);
		$arrFields["repo_view_filter"]      = array("char254", true);

		if(!$this->objDB->createTable("mediamanager_repo", $arrFields, array("repo_id")))
			$strReturn .= "An error occured! ...\n";

		$strReturn .= "Installing table mediamanager_file...\n";

		$arrFields = array();
		$arrFields["file_id"] 			    = array("char20", false);
		$arrFields["file_name"] 			= array("char254", true);
		$arrFields["file_filename"] 		= array("char254", true);
		$arrFields["file_description"] 	    = array("text", true);
		$arrFields["file_subtitle"] 		= array("char254", true);
		$arrFields["file_hits"] 			= array("int", true);
		$arrFields["file_type"] 			= array("int", true);
		$arrFields["file_cat"]  			= array("char254", true);
		$arrFields["file_screen1"]      	= array("char254", true);
		$arrFields["file_screen2"]      	= array("char254", true);
		$arrFields["file_screen3"]      	= array("char254", true);
		$arrFields["file_ispackage"]    	= array("int", true);

		if(!$this->objDB->createTable("mediamanager_file", $arrFields, array("file_id")))
			$strReturn .= "An error occured! ...\n";


        $strReturn .= "Installing table mediamanager_dllog...\n";

        $arrFields = array();
        $arrFields["downloads_log_id"] 		= array("char20", false);
        $arrFields["downloads_log_date"] 	= array("int", true);
        $arrFields["downloads_log_file"] 	= array("char254", true);
        $arrFields["downloads_log_user"] 	= array("char20", true);
        $arrFields["downloads_log_ip"] 		= array("char20", true);

        if(!$this->objDB->createTable("mediamanager_dllog", $arrFields, array("downloads_log_id")))
            $strReturn .= "An error occured! ...\n";


		//register the module
		$this->registerModule(
            "mediamanager",
            _mediamanager_module_id_,
            "class_module_mediamanager_portal.php",
            "class_module_mediamanager_admin.php",
            $this->objMetadata->getStrVersion(),
            true, "",
            "class_module_mediamanager_admin_xml.php");

        //The folderview
        $this->registerModule("folderview", _mediamanager_folderview_modul_id_, "", "class_module_folderview_admin.php", $this->objMetadata->getStrVersion() , false);

        $this->registerConstant("_mediamanager_default_imagesrepoid_", "", class_module_system_setting::$int_TYPE_STRING, _mediamanager_module_id_);
        $this->registerConstant("_mediamanager_default_filesrepoid_", "", class_module_system_setting::$int_TYPE_STRING, _mediamanager_module_id_);

        $strReturn .= "Trying to copy the *.root files to top-level...\n";
        if(!file_exists(_realpath_."/download.php")) {
            if(!copy(_realpath_."/core/module_mediamanager/download.php.root", _realpath_."/download.php"))
                $strReturn .= "<b>Copying the download.php.root to top level failed!!!</b>";
        }



		return $strReturn;

	}


	public function update() {
        $strReturn = "";
        //check installed version and to which version we can update
        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);

        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.9") {
            $strReturn .= $this->update_349_3491();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.9.1") {
            $strReturn .= $this->update_3491_3492();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.9.2") {
            $strReturn .= $this->update_3492_3493();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.9.3") {
            $strReturn .= $this->update_3493_40();
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
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.2") {
            $strReturn = "Updating 4.2 to 4.3...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.3");
        }

        return $strReturn."\n\n";
	}


    private function update_349_3491() {
        $strReturn = "Updating 3.4.9 to 3.4.9.1...\n";

        $strReturn .= "Altering element-table...\n";

        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."mediamanager_file")."
                    ADD ".$this->objDB->encloseColumnName("file_ispackage")." ".$this->objDB->getDatatype("int")." NULL";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "3.4.9.1");
        return $strReturn;
    }

    private function update_3491_3492() {
        $strReturn = "Updating 3.4.9.1 to 3.4.9.2...\n";

        $strReturn .= "Altering element-table...\n";

        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."mediamanager_file")."
                    ADD ".$this->objDB->encloseColumnName("file_cat")." ".$this->objDB->getDatatype("int")." NULL,
                    ADD ".$this->objDB->encloseColumnName("file_screen1")." ".$this->objDB->getDatatype("char254")." NULL,
                    ADD ".$this->objDB->encloseColumnName("file_screen2")." ".$this->objDB->getDatatype("char254")." NULL,
                    ADD ".$this->objDB->encloseColumnName("file_screen3")." ".$this->objDB->getDatatype("char254")." NULL";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "3.4.9.2");
        return $strReturn;
    }


    private function update_3492_3493() {
        $strReturn = "Updating 3.4.9.2 to 3.4.9.3...\n";

        $strReturn .= "Altering element-table...\n";

        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."mediamanager_file")."
                    CHANGE ".$this->objDB->encloseColumnName("file_cat")." ".$this->objDB->encloseColumnName("file_cat")." ".$this->objDB->getDatatype("char254")." NULL";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "3.4.9.3");
        return $strReturn;
    }

    private function update_3493_40() {
        $strReturn = "Updating 3.4.9.3 to 4.0...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.0");
        return $strReturn;
    }

    private function update_40_41() {
        $strReturn = "Updating 4.0 to 4.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.1");
        return $strReturn;
    }


}
