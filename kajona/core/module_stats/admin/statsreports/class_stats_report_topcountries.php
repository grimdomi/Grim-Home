<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_stats_report_topcountries.php 5957 2013-10-18 10:36:22Z sidler $                            *
********************************************************************************************************/

/**
 * This plugin creates a list of countries the visitors come from
 *
 * @package module_stats
 * @author sidler@mulchprod.de
 */
class class_stats_report_topcountries implements interface_admin_statsreports {

    //class vars
    private $intDateStart;
    private $intDateEnd;
    private $intInterval;

    private $objTexts;
    private $objToolkit;
    private $objDB;

    private $arrHits = null;

    /**
     * Constructor
     */
    public function __construct(class_db $objDB, class_toolkit_admin $objToolkit, class_lang $objTexts) {
        $this->objTexts = $objTexts;
        $this->objToolkit = $objToolkit;
        $this->objDB = $objDB;
    }

    public function registerPlugin(class_pluginmanager $objPluginamanger) {
        $objPluginamanger->registerPlugin($this);
    }

    public function setEndDate($intEndDate) {
        $this->intDateEnd = $intEndDate;
    }

    public function setStartDate($intStartDate) {
        $this->intDateStart = $intStartDate;
    }

    public function getTitle() {
        return $this->objTexts->getLang("topcountries", "stats");
    }

    public function getPluginCommand() {
        return "statsTopCountries";
    }

    public function isIntervalable() {
        return false;
    }

    public function setInterval($intInterval) {
        $this->intInterval = $intInterval;
    }

    public function getReport() {
        $strReturn = "";

        //Create Data-table
        $arrHeader = array();
        $arrValues = array();
        //Fetch data
        $arrStats = $this->getTopCountries();

        //calc a few values
        $intSum = 0;
        foreach($arrStats as $arrOneStat)
            $intSum += $arrOneStat;

        $intI = 0;
        foreach($arrStats as $strCountry => $arrOneStat) {
            //Escape?
            if($intI >= _stats_nrofrecords_)
                break;

            $arrValues[$intI] = array();
            $arrValues[$intI][] = $intI + 1;
            $arrValues[$intI][] = $strCountry;
            $arrValues[$intI][] = $arrOneStat;
            $arrValues[$intI][] = $this->objToolkit->percentBeam($arrOneStat / $intSum * 100);
            $intI++;
        }
        //HeaderRow
        $arrHeader[] = "#";
        $arrHeader[] = $this->objTexts->getLang("top_country_titel", "stats");
        $arrHeader[] = $this->objTexts->getLang("commons_hits_header", "stats");
        $arrHeader[] = $this->objTexts->getLang("anteil", "stats", "admin");

        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrValues);

        $strReturn .= $this->objToolkit->getTextRow($this->objTexts->getLang("stats_hint_task", "stats"));

        return $strReturn;
    }


    /**
     * Loads a list of systems accessed the page
     *
     * @return mixed
     */
    public function getTopCountries() {
        $arrReturn = array();

        if($this->arrHits != null)
            return $this->arrHits;

        $strQuery = "SELECT stats_ip, count(*) as anzahl
						FROM "._dbprefix_."stats_data
						WHERE stats_date > ?
						  AND stats_date <= ?
						GROUP BY stats_ip";

        $arrTemp = $this->objDB->getPArray($strQuery, array($this->intDateStart, $this->intDateEnd));

        $intCounter = 0;
        foreach($arrTemp as $arrOneRecord) {

            $strQuery = "SELECT ip2c_name as country_name
						   FROM "._dbprefix_."stats_ip2country
						  WHERE ip2c_ip = ?";

            $arrRow = $this->objDB->getPRow($strQuery, array($arrOneRecord["stats_ip"]));

            if(!isset($arrRow["country_name"]))
                $arrRow["country_name"] = "n.a.";

            if(isset($arrReturn[$arrRow["country_name"]]))
                $arrReturn[$arrRow["country_name"]] += $arrOneRecord["anzahl"];
            else
                $arrReturn[$arrRow["country_name"]] = $arrOneRecord["anzahl"];

            //flush query cache every 2000 hits
            if($intCounter++ >= 2000)
                $this->objDB->flushQueryCache();

        }

        arsort($arrReturn);
        $this->arrHits = $arrReturn;
        return $arrReturn;
    }

    public function getReportGraph() {
        $arrReturn = array();

        $arrData = $this->getTopCountries();

        $intSum = 0;
        foreach($arrData as $arrOneStat)
            $intSum += $arrOneStat;

        $arrKeyValues = array();
        //max 6 entries
        $intCount = 0;
        $floatPercentageSum = 0;
        $arrValues = array();
        $arrLabels = array();
        foreach($arrData as $strName => $intOneSystem) {
            if(++$intCount <= 6) {
                $floatPercentage = $intOneSystem / $intSum * 100;
                $floatPercentageSum += $floatPercentage;
                $arrKeyValues[$strName] = $floatPercentage;
                $arrValues[] = $floatPercentage;
                $arrLabels[] = $strName;
            }
            else {
                break;
            }
        }
        //add "others" part?
        if($floatPercentageSum < 99) {
            $arrKeyValues["others"] = 100 - $floatPercentageSum;
            $arrLabels[] = "others";
            $arrValues[] = 100 - $floatPercentageSum;
        }
        $objGraph = class_graph_factory::getGraphInstance();
        $objGraph->createPieChart($arrValues, $arrLabels);

        $arrReturn[] = $objGraph->renderGraph();


        return $arrReturn;
    }
}

