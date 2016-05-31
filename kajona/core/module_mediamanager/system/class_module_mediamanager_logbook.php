<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_mediamanager_logbook.php 5924 2013-10-05 18:08:38Z sidler $                              *
********************************************************************************************************/

/**
 * Model for the downloads-logbook
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 *
 * @module mediamanager
 * @moduleId _mediamanager_module_id_
 */
class class_module_mediamanager_logbook extends class_model implements interface_model {

    /**
     * Generates an entry in the logbook an increases the hits-counter
     *
     * @param \class_module_mediamanager_file $objFile
     */
    public static function generateDlLog(class_module_mediamanager_file $objFile) {
        $objDB = class_carrier::getInstance()->getObjDB();
        $strQuery = "INSERT INTO "._dbprefix_."mediamanager_dllog
	                   (downloads_log_id, downloads_log_date, downloads_log_file, downloads_log_user, downloads_log_ip) VALUES
	                   (?, ?, ?, ?, ?)";

        $objDB->_pQuery($strQuery, array(generateSystemid(), (int)time(), basename($objFile->getStrFilename()),
            class_carrier::getInstance()->getObjSession()->getUsername(), getServer("REMOTE_ADDR")));

        $objFile->increaseHits();
    }

    /**
     * Loads the records of the dl-logbook
     *
     * @static
     *
     * @param null $intStart
     * @param null $intEnd
     *
     * @return mixed AS ARRAY
     */
    public static function getLogbookData($intStart = null, $intEnd = null) {
        $strQuery = "SELECT *
					  FROM "._dbprefix_."mediamanager_dllog
					  ORDER BY downloads_log_date DESC";
        return class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array(), $intStart, $intEnd);

    }

    /**
     * Counts the number of logs available
     *
     * @return int
     */
    public function getLogbookDataCount() {
        $strQuery = "SELECT COUNT(*)
					  FROM "._dbprefix_."mediamanager_dllog";
        $arrTemp = $this->objDB->getPRow($strQuery, array());
        return $arrTemp["COUNT(*)"];

    }


    /**
     * Deletes logrecords from db older than the passed timestamp
     *
     * @static
     *
     * @param $intOlderDate
     *
     * @return bool
     */
    public static function deleteFromLogs($intOlderDate) {
        $strSql = "DELETE FROM "._dbprefix_."mediamanager_dllog
			           WHERE downloads_log_date < ?";

        return class_carrier::getInstance()->getObjDB()->_pQuery($strSql, array((int)$intOlderDate));
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        return "";
    }
}

