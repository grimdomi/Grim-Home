<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_stats_report_toppages.php 5957 2013-10-18 10:36:22Z sidler $                              *
********************************************************************************************************/

/**
 * This plugin creates a view common numbers, such as "user online" oder "total pagehits"
 *
 * @package module_stats
 * @author sidler@mulchprod.de
 */
class class_stats_report_toppages implements interface_admin_statsreports {

    //class vars
    private $intDateStart;
    private $intDateEnd;
    private $intInterval;

    private $objTexts;
    private $objToolkit;
    private $objDB;


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
        return $this->objTexts->getLang("topseiten", "stats");
    }

    public function getPluginCommand() {
        return "statsTopPages";
    }

    public function isIntervalable() {
        return true;
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
        $arrPages = $this->getTopPages();

        //calc a few values
        $intSum = 0;
        foreach($arrPages as $arrOnePage)
            $intSum += $arrOnePage["anzahl"];

        $intI = 0;
        foreach($arrPages as $arrOnePage) {
            //Escape?
            if($intI >= _stats_nrofrecords_)
                break;
            $arrValues[$intI] = array();
            $arrValues[$intI][] = $intI + 1;
            $arrValues[$intI][] = $arrOnePage["name"];
            $arrValues[$intI][] = $arrOnePage["language"];
            $arrValues[$intI][] = $arrOnePage["anzahl"];
            $arrValues[$intI][] = $this->objToolkit->percentBeam($arrOnePage["anzahl"] / $intSum * 100);
            $intI++;
        }

        //HeaderRow
        $arrHeader[] = "#";
        $arrHeader[] = $this->objTexts->getLang("top_seiten_titel", "stats");
        $arrHeader[] = $this->objTexts->getLang("commons_language", "stats");
        $arrHeader[] = $this->objTexts->getLang("commons_hits_header", "stats");
        $arrHeader[] = $this->objTexts->getLang("anteil", "stats", "admin");

        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrValues);

        return $strReturn;
    }

    /**
     * Returns the pages and their hits
     *
     * @return mixed
     */
    public function getTopPages() {
        $strQuery = "SELECT stats_page as name, count(*) as anzahl, stats_language as language
						FROM "._dbprefix_."stats_data
						WHERE stats_date > ?
								AND stats_date <= ?
						GROUP BY stats_page, stats_language
							ORDER BY anzahl desc";

        return $this->objDB->getPArray($strQuery, array($this->intDateStart, $this->intDateEnd), 0, _stats_nrofrecords_ - 1);
    }

    public function getReportGraph() {
        $arrReturn = array();
        //collect data
        $arrPages = $this->getTopPages();

        $arrGraphData = array();
        $arrPlots = array();
        $arrLabels = array();
        $intCount = 1;
        foreach($arrPages as $arrOnePage) {
            $arrGraphData[] = $arrOnePage["anzahl"];
            $arrLabels[] = $intCount;
            if($intCount <= 6) {
                $arrPlots[$arrOnePage["name"]] = array();
            }

            if($intCount++ >= 9)
                break;
        }

        if(count($arrGraphData) > 1) {
            //generate a bar-chart
            $objGraph = class_graph_factory::getGraphInstance();
            $objGraph->setArrXAxisTickLabels($arrLabels);
            $objGraph->addBarChartSet($arrGraphData, $this->objTexts->getLang("top_seiten_titel", "stats"));
            $objGraph->setStrXAxisTitle($this->objTexts->getLang("top_seiten_titel", "stats"));
            $objGraph->setStrYAxisTitle($this->objTexts->getLang("commons_hits_header", "stats"));
            $objGraph->setBitRenderLegend(false);
            $arrReturn[] = $objGraph->renderGraph();

            //--- XY-Plot -----------------------------------------------------------------------------------
            //calc number of plots

            $arrTickLabels = array();

            $intGlobalEnd = $this->intDateEnd;
            $intGlobalStart = $this->intDateStart;

            $this->intDateEnd = $this->intDateStart + 60 * 60 * 24 * $this->intInterval;

            $intCount = 0;
            while($this->intDateStart <= $intGlobalEnd) {
                $arrPagesData = $this->getTopPages();
                //init plot array for this period
                $arrTickLabels[] = date("d.m.", $this->intDateStart);
                foreach($arrPlots as $strPage => &$arrOnePlot) {
                    $arrOnePlot[$intCount] = 0;
                    foreach($arrPagesData as $arrOnePage) {
                        if($arrOnePage["name"] == $strPage) {
                            $arrOnePlot[$intCount] += $arrOnePage["anzahl"];
                        }
                    }
                }
                //increase start & end-date
                $this->intDateStart = $this->intDateEnd;
                $this->intDateEnd = $this->intDateStart + 60 * 60 * 24 * $this->intInterval;
                $intCount++;
            }
            //create graph
            if($intCount > 1) {
                $objGraph = class_graph_factory::getGraphInstance();
                $objGraph->setArrXAxisTickLabels($arrTickLabels);

                foreach($arrPlots as $arrPlotName => $arrPlotData) {
                    $objGraph->addLinePlot($arrPlotData, $arrPlotName);
                }
                $arrReturn[] = $objGraph->renderGraph();
            }
            //reset global dates
            $this->intDateEnd = $intGlobalEnd;
            $this->intDateStart = $intGlobalStart;

            return $arrReturn;
        }
        else
            return "";
    }

}

