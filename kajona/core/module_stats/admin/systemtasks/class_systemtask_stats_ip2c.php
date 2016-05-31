<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: class_systemtask_stats_ip2c.php 5409 2012-12-30 13:09:07Z sidler $                                 *
********************************************************************************************************/

/**
 * Resolves the country for a given ip-adress
 *
 * @package module_stats
 */
class class_systemtask_stats_ip2c extends class_systemtask_base implements interface_admin_systemtask {

    private $strIp2cServer = "ip2c.kajona.de";


	/**
	 * contructor to call the base constructor
	 */
	public function __construct() {
		parent::__construct();
        $this->setStrTextBase("stats");
    }

    /**
     * @see interface_admin_systemtast::getGroupIdenitfier()
     * @return string
     */
    public function getGroupIdentifier() {
        return "stats";
    }

    /**
     * @see interface_admin_systemtast::getStrInternalTaskName()
     * @return string
     */
    public function getStrInternalTaskName() {
    	return "statsip2c";
    }

    /**
     * @see interface_admin_systemtast::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
    	return $this->getLang("systemtask_ip2c_name");
    }

    /**
     * @see interface_admin_systemtast::executeTask()
     * @return string
     */
    public function executeTask() {
        $strReturn = "";

        $objWorker = new class_module_stats_worker();

    	//determin the number of ips to lookup
        $arrIpToLookup = $objWorker->getArrayOfIp2cLookups();

        if(count($arrIpToLookup) == 0) {
            return $this->objToolkit->getTextRow($this->getLang("worker_lookup_end"));
        }

        //check, if we did anything before
        if($this->getParam("totalCount") == "")
            $this->setParam("totalCount", $objWorker->getNumberOfIp2cLookups());

        $strReturn .= $this->objToolkit->getTextRow($this->getLang("intro_worker_lookupip2c"). $this->getParam("totalCount"));

        //Lookup 10 Ips an load the page again
        for($intI = 0; $intI < 10; $intI++) {
            if(isset($arrIpToLookup[$intI])) {
                $strIP = $arrIpToLookup[$intI]["stats_ip"];

                try {
		            $objRemoteloader = new class_remoteloader();
		            $objRemoteloader->setStrHost($this->strIp2cServer);
		            $objRemoteloader->setStrQueryParams("/ip2c.php?ip=".urlencode($strIP)."&domain=".urlencode(_webpath_)."&checksum=".md5(urldecode(_webpath_).$strIP));
		            $strCountry = $objRemoteloader->getRemoteContent();
		        }
		        catch (class_exception $objExeption) {
		            $strCountry = "n.a.";
		        }

                $objWorker->saveIp2CountryRecord($strIP, $strCountry);

            }
        }

        //and Create a small progress-info
        $intTotal = $this->getParam("totalCount");
        $floatOnePercent = 100 / $intTotal;
        //and multiply it with the alredy looked up ips
        $intLookupsDone = ((int)$intTotal - $objWorker->getNumberOfIp2cLookups() ) * $floatOnePercent;
        $intLookupsDone = round($intLookupsDone, 2);
        if($intLookupsDone < 0)
            $intLookupsDone = 0;

        $this->setStrProgressInformation($strReturn);
        $this->setStrReloadParam("&totalCount=".$this->getParam("totalCount"));

        return $intLookupsDone;
    }

    /**
     * @see interface_admin_systemtast::getAdminForm()
     * @return string
     */
    public function getAdminForm() {
    	return "";
    }

}
