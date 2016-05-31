<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: class_systemtask_dbdump.php 5409 2012-12-30 13:09:07Z sidler $                                        *
********************************************************************************************************/


/**
 * Dumps the database to the filesystem using the current db-driver
 *
 * @package module_system
 */
class class_systemtask_dbdump extends class_systemtask_base implements interface_admin_systemtask {

    private $arrTablesToExlucde = array(
        "stats_data", "stats_ip2country", "cache"
    );

    /**
     * contructor to call the base constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * @see interface_admin_systemtask::getGroupIdenitfier()
     * @return string
     */
    public function getGroupIdentifier() {
        return "database";
    }

    /**
     * @see interface_admin_systemtask::getStrInternalTaskName()
     * @return string
     */
    public function getStrInternalTaskName() {
        return "dbdump";
    }

    /**
     * @see interface_admin_systemtask::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
        return $this->getLang("systemtask_dbexport_name");
    }

    /**
     * @see interface_admin_systemtask::executeTask()
     * @return string
     */
    public function executeTask() {

        $arrToExclude = array();
        if($this->getParam("excludeTables") == "1")
            $arrToExclude = $this->arrTablesToExlucde;

        if(class_carrier::getInstance()->getObjDB()->dumpDb($arrToExclude))
            return $this->objToolkit->getTextRow($this->getLang("systemtask_dbexport_success"));
        else
            return $this->objToolkit->getTextRow($this->getLang("systemtask_dbexport_error"));
    }

    /**
     * @see interface_admin_systemtask::getAdminForm()
     * @return string
     */
    public function getAdminForm() {
        $strReturn = "";
        $strReturn .= $this->objToolkit->formTextRow($this->getLang("systemtask_dbexport_exclude_intro"));
        $strReturn .= $this->objToolkit->formInputDropdown("dbExcludeTables", array(0 => $this->getLang("commons_no"), 1 => $this->getLang("commons_yes")), $this->getLang("systemtask_dbexport_excludetitle"));
        return $strReturn;
    }

    /**
     * @see interface_admin_systemtask::getSubmitParams()
     * @return string
     */
    public function getSubmitParams() {
        return "&excludeTables=".$this->getParam("dbExcludeTables");
    }

}
