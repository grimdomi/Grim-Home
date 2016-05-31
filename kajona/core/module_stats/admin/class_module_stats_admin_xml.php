<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_stats_admin_xml.php 5957 2013-10-18 10:36:22Z sidler $                             *
********************************************************************************************************/


/**
 * Admin class of the stats-module - xml based.
 * Triggers the report-generation
 *
 * @package module_stats
 * @author sidler@mulchprod.de
 * @module stats
 * @moduleId _stats_modul_id_
 */
class class_module_stats_admin_xml extends class_admin implements interface_xml_admin {

    /**
     * @var class_date
     */
    private $objDateStart;
    /**
     * @var class_date
     */
    private $objDateEnd;
    private $intInterval;


    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();

        $intDateStart = class_carrier::getInstance()->getObjSession()->getSession(class_module_stats_admin::$STR_SESSION_KEY_DATE_START);
        //Start: first day of current month
        $this->objDateStart = new class_date();
        $this->objDateStart->setTimeInOldStyle($intDateStart);

        //End: Current Day of month
        $intDateEnd = class_carrier::getInstance()->getObjSession()->getSession(class_module_stats_admin::$STR_SESSION_KEY_DATE_END);
        $this->objDateEnd = new class_date();
        $this->objDateEnd->setTimeInOldStyle($intDateEnd);

        $this->intInterval = class_carrier::getInstance()->getObjSession()->getSession(class_module_stats_admin::$STR_SESSION_KEY_INTERVAL);
    }


    /**
     * Triggers the "real" creation of the report and wraps the code inline into a xml-structure
     *
     * @return string
     * @permissions view
     */
    protected function actionGetReport() {
        $strPlugin = $this->getParam("plugin");
        $strReturn = "";

        $objPluginManager = new class_pluginmanager();
        $objPluginManager->loadPluginsFiltered("/admin/statsreports/", class_module_stats_admin::$STR_PLUGIN_EXTENSION_POINT);

        /** @var $objPlugin interface_admin_statsreports|interface_admin_plugin */
        $objPlugin = $objPluginManager->getPluginObject(class_module_stats_admin::$STR_PLUGIN_EXTENSION_POINT, $strPlugin);

        if($objPlugin->getPluginCommand() == $strPlugin && $objPlugin instanceof interface_admin_statsreports) {
            //get date-params as ints
            $intStartDate = mktime(0, 0, 0, $this->objDateStart->getIntMonth(), $this->objDateStart->getIntDay(), $this->objDateStart->getIntYear());
            $intEndDate = mktime(0, 0, 0, $this->objDateEnd->getIntMonth(), $this->objDateEnd->getIntDay(), $this->objDateEnd->getIntYear());
            $objPlugin->setEndDate($intEndDate);
            $objPlugin->setStartDate($intStartDate);
            $objPlugin->setInterval($this->intInterval);

            $arrImage = $objPlugin->getReportGraph();

            if(!is_array($arrImage)) {
                $arrImage = array($arrImage);
            }

            foreach($arrImage as $strImage) {
                if($strImage != "") {
                    $strReturn .= $this->objToolkit->getGraphContainer($strImage);
                }
            }


            $strReturn .= $objPlugin->getReport();
            $strReturn = "<content><![CDATA[".$strReturn."]]></content>";
        }

        return $strReturn;
    }

}

