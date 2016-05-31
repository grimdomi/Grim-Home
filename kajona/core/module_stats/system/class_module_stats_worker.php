<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_stats_worker.php 5924 2013-10-05 18:08:38Z sidler $                                 *
********************************************************************************************************/

/**
 * Model for a stats-worker
 *
 * @package module_stats
 * @author sidler@mulchprod.de
 *
 * @module stats
 * @moduleId _stats_modul_id_
 */
class class_module_stats_worker extends class_model implements interface_model {

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        return "";
    }

    public function updateObjectToDb($strPrevId = false) {
        return true;
    }


    /**
     * Loads all ips to update hostnames for the worker "hostnameLookup"
     *
     * @return array
     */
    public function hostnameLookupIpsToLookup() {
        $strQuery = "SELECT stats_ip
                       FROM " . _dbprefix_ . "stats_data
                      WHERE stats_hostname IS NULL
                            OR stats_hostname = ''
                        AND stats_hostname != 'na'
                      GROUP BY stats_ip";

        return $this->objDB->getPArray($strQuery, array());
    }

    /**
     * Updates an record in the statstable. saves the hostname for a given ip
     *
     * @param string $strHostname
     * @param string $strIP
     *
     * @return bool
     */
    public function hostnameLookupSaveHostname($strHostname, $strIP) {
        $strQuery = "UPDATE " . _dbprefix_ . "stats_data
                                    SET stats_hostname = ?
                                  WHERE stats_ip = ? ";
        return $this->objDB->_pQuery($strQuery, array($strHostname, $strIP));
    }

    /**
     * Resets all hostnames marked as not successfull resolved
     *
     * @return bool
     */
    public function hostnameLookupResetHostnames() {
        //Reset all na hostnames
        $strQuery = "UPDATE " . _dbprefix_ . "stats_data
                        SET stats_hostname = ''
                      WHERE stats_hostname = 'na'";
        return $this->objDB->_pQuery($strQuery, array());
    }

    /**
     * Creates a row in the stats-data table
     *
     * @param string $strIp
     * @param int $intDate
     * @param string $strPage
     * @param string $strReferer
     * @param string $strBrowser
     * @param string $strLanguage
     * @param string $strSession
     *
     * @return bool
     */
    public function createStatsEntry($strIp, $intDate, $strPage, $strReferer, $strBrowser, $strLanguage = "", $strSession = "") {
        if($strSession == "") {
            $strSession = $this->objSession->getSessionId();
        }
        $strQuery = "INSERT INTO " . _dbprefix_ . "stats_data
		            (stats_id, stats_ip, stats_date, stats_page, stats_referer, stats_browser, stats_session, stats_language) VALUES
		            (?, ?, ?, ?, ?, ?, ?, ?)";

        return $this->objDB->_pQuery(
            $strQuery,
            array(generateSystemid(), $strIp, $intDate, $strPage, $strReferer, $strBrowser, $strSession, $strLanguage)
        );
    }


    /**
     * Looks up the number of ips not yet resolved
     *
     * @return int
     */
    public function getNumberOfIpsToLookup() {
        $strQuery = "SELECT count(distinct stats_ip) as anzahl
                       FROM " . _dbprefix_ . "stats_data
                      WHERE stats_hostname IS NULL
                            OR stats_hostname = ''
                        AND stats_hostname != 'na'";

        $arrTemp = $this->objDB->getPRow($strQuery, array());
        return $arrTemp["anzahl"];
    }

    /**
     * Looks up the number of ip not yet resolved to a country
     *
     * @return array
     */
    public function getArrayOfIp2cLookups() {
        $strQuery = "SELECT distinct stats_ip
                       FROM " . _dbprefix_ . "stats_data
                            LEFT JOIN " . _dbprefix_ . "stats_ip2country
                                   ON (stats_ip = ip2c_ip)  
                      WHERE ip2c_name IS NULL
                       /*   OR ip2c_name = '' 
                        AND ip2c_name != 'na' */
                   GROUP BY stats_ip";

        return $this->objDB->getPArray($strQuery, array(), 0, 11);

    }

    /**
     * Looks up the number of ip not yet resolved to a country
     *
     * @return array
     */
    public function getNumberOfIp2cLookups() {
        $strQuery = "SELECT COUNT(*) as number FROM (SELECT distinct stats_ip
                       FROM " . _dbprefix_ . "stats_data
                            LEFT JOIN " . _dbprefix_ . "stats_ip2country
                                   ON (stats_ip = ip2c_ip)
                      WHERE ip2c_name IS NULL
                       /*   OR ip2c_name = ''
                        AND ip2c_name != 'na' */
                   GROUP BY stats_ip) as derived";

        $arrTemp = $this->objDB->getPRow($strQuery, array());
        return $arrTemp["number"];

    }

    /**
     * Saves a tuple of an ip and country to the cache-table
     *
     * @param string $strIp
     * @param string $strCountry
     *
     * @return bool
     */
    public function saveIp2CountryRecord($strIp, $strCountry) {
        $strQuery = "INSERT INTO " . _dbprefix_ . "stats_ip2country
    	               (ip2c_ip, ip2c_name) VALUES 
    	               (?, ?)";

        return $this->objDB->_pQuery($strQuery, array($strIp, $strCountry));
    }

}
