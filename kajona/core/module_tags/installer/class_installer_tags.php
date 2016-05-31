<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_installer_tags.php 5925 2013-10-06 10:59:30Z sidler $                                *
********************************************************************************************************/

/**
 * Class providing an install for the tags module
 *
 * @package module_tags
 * @author sidler@mulchprod.de
 * @moduleId _tags_modul_id_
 */
class class_installer_tags extends class_installer_base implements interface_installer {

    public function install() {
		$strReturn = "";

		//tags_tag --------------------------------------------------------------------------------------
		$strReturn .= "Installing table tags_tag...\n";

		$arrFields = array();
		$arrFields["tags_tag_id"] 		= array("char20", false);
		$arrFields["tags_tag_name"] 	= array("char254", true);
		$arrFields["tags_tag_private"] 	= array("int", true);

		if(!$this->objDB->createTable("tags_tag", $arrFields, array("tags_tag_id")))
			$strReturn .= "An error occured! ...\n";

        //tags_member --------------------------------------------------------------------------------------
		$strReturn .= "Installing table tags_member...\n";

        $arrFields = array();
		$arrFields["tags_memberid"]     = array("char20", false);
		$arrFields["tags_systemid"] 	= array("char20", false);
		$arrFields["tags_tagid"]        = array("char20", false);
		$arrFields["tags_attribute"]    = array("char254", true);
		$arrFields["tags_owner"]        = array("char20", true);

		if(!$this->objDB->createTable("tags_member", $arrFields, array("tags_memberid"), array("tags_systemid", "tags_tagid", "tags_attribute", "tags_owner")))
			$strReturn .= "An error occured! ...\n";



        //tags_favorite ---------------------------------------------------------------------------------
        $strReturn .= "Installing table tags_favorite...\n";

        $arrFields = array();
        $arrFields["tags_fav_id"] 	        = array("char20", false);
        $arrFields["tags_fav_tagid"]        = array("char20", true);
        $arrFields["tags_fav_userid"]       = array("char20", true);

        if(!$this->objDB->createTable("tags_favorite", $arrFields, array("tags_fav_id")))
            $strReturn .= "An error occured! ...\n";

		//register the module
		$this->registerModule(
            "tags",
            _tags_modul_id_,
            "",
            "class_module_tags_admin.php",
            $this->objMetadata->getStrVersion(),
            true,
            "",
            "class_module_tags_admin_xml.php"
        );

		$strReturn .= "Registering system-constants...\n";
        $this->registerConstant("_tags_defaultprivate_", "false", class_module_system_setting::$int_TYPE_BOOL, _tags_modul_id_);

        //Register the element
        $strReturn .= "Registering tags-element...\n";

        //check, if not already existing
        if(class_module_system_module::getModuleByName("pages") !== null && class_module_pages_element::getElement("tags") == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("tags");
            $objElement->setStrClassAdmin("class_element_tags_admin.php");
            $objElement->setStrClassPortal("class_element_tags_portal.php");
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
        //check installed version and to which version we can update
        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.2") {
            $strReturn .= $this->update_342_349();
            $this->objDB->flushQueryCache();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.9") {
            $strReturn .= $this->update_349_3491();
            $this->objDB->flushQueryCache();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.9.1") {
            $strReturn .= $this->update_3491_3492();
            $this->objDB->flushQueryCache();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.9.2") {
            $strReturn .= $this->update_3492_40();
            $this->objDB->flushQueryCache();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.0") {
            $strReturn .= $this->update_40_41();
            $this->objDB->flushQueryCache();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.1") {
            $strReturn .= $this->update_41_42();
            $this->objDB->flushQueryCache();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.2") {
            $strReturn .= "Updating 4.2 to 4.3...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.3");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("tags", "4.3");
            $this->objDB->flushQueryCache();
        }

        return $strReturn."\n\n";
	}



    private function update_342_349() {
        $strReturn = "Updating 3.4.2 to 3.4.9...\n";

        $strReturn .= "Adding classes for existing records...\n";

        $strReturn .= "Tags\n";
        $arrRows = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."tags_tag, "._dbprefix_."system WHERE system_id = tags_tag_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_tags_tag', $arrOneRow["system_id"] ) );
        }

        $strReturn .= "Installing table tags_favorite...\n";

        $arrFields = array();
        $arrFields["tags_fav_id"] 	        = array("char20", false);
        $arrFields["tags_fav_tagid"]        = array("char20", true);
        $arrFields["tags_fav_userid"]       = array("char20", true);

        if(!$this->objDB->createTable("tags_favorite", $arrFields, array("tags_fav_id")))
            $strReturn .= "An error occurred! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "3.4.9");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("tags", "3.4.9");

        return $strReturn;
    }

    private function update_349_3491() {
        $strReturn = "Updating 3.4.9 to 3.4.9.1...\n";

        $strReturn .= "Adding columns for private tag assignments...\n";

        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."tags_member")."
                    ADD ".$this->objDB->encloseColumnName("tags_owner")." ".$this->objDB->getDatatype("char20")." NULL";

        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occurred! ...\n";

        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."tags_tag")."
                    ADD ".$this->objDB->encloseColumnName("tags_tag_private")." ".$this->objDB->getDatatype("int")." NULL";

        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occurred! ...\n";

        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."tags_member")."
                      DROP PRIMARY KEY,
                      ADD PRIMARY KEY(tags_systemid, tags_tagid, tags_owner)";

        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occurred! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "3.4.9.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("tags", "3.4.9.1");

        return $strReturn;
    }

    private function update_3491_3492() {
        $strReturn = "Updating 3.4.9.1 to 3.4.9.2...\n";

        $strReturn .= "Updating tag assignment table...\n";

        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."tags_member")."
                    ADD ".$this->objDB->encloseColumnName("tags_memberid")." ".$this->objDB->getDatatype("char20")." ";

        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occurred! ...\n";

        //fill default ids
        $strQuery = "SELECT * FROM ".$this->objDB->encloseTableName(_dbprefix_."tags_member")." WHERE tags_memberid = '' OR tags_memberid IS NULL";
        $arrRows = $this->objDB->getPArray($strQuery, array());
        foreach($arrRows as $arrSingleRow) {
            $strQuery = "UPDATE ".$this->objDB->encloseTableName(_dbprefix_."tags_member")."
                           SET tags_memberid = ?
                         WHERE tags_systemid = ?
                           AND tags_tagid = ?
                           AND tags_attribute = ?
                           AND tags_owner = ?";

            $this->objDB->_pQuery($strQuery, array(
                generateSystemid(),
                $arrSingleRow["tags_systemid"],
                $arrSingleRow["tags_tagid"],
                $arrSingleRow["tags_attribute"],
                $arrSingleRow["tags_owner"]
            ));
        }



        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."tags_member")."
                      DROP PRIMARY KEY,
                      ADD PRIMARY KEY(tags_memberid)";

        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occurred! ...\n";

        $strReturn .= "Adding index to table tags_member\n";
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."tags_member")." ADD INDEX ( ".$this->objDB->encloseColumnName("tags_systemid")." ) ", array());
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."tags_member")." ADD INDEX ( ".$this->objDB->encloseColumnName("tags_tagid")." ) ", array());
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."tags_member")." ADD INDEX ( ".$this->objDB->encloseColumnName("tags_attribute")." ) ", array());
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."tags_member")." ADD INDEX ( ".$this->objDB->encloseColumnName("tags_owner")." ) ", array());


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "3.4.9.2");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("tags", "3.4.9.2");

        return $strReturn;
    }

    private function update_3492_40() {
        $strReturn = "Updating 3.4.9.2 to 4.0...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.0");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("tags", "4.0");
        return $strReturn;
    }

    private function update_40_41() {
        $strReturn = "Updating 4.0 to 4.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("tags", "4.1");
        return $strReturn;
    }

    private function update_41_42() {
        $strReturn = "Updating 4.1 to 4.2...\n";

        $strReturn .= "Registering tags private mode setting\n";
        $this->registerConstant("_tags_defaultprivate_", "false", class_module_system_setting::$int_TYPE_BOOL, _tags_modul_id_);

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.2");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("tags", "4.2");
        return $strReturn;
    }

}
