<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_search_portal_xml.php 5924 2013-10-05 18:08:38Z sidler $						    *
********************************************************************************************************/

/**
 * Portal-class of the search.
 * Serves xml-requests, e.g. generates search results
 *
 * @package module_search
 * @author sidler@mulchprod.de
 *
 * @module search
 * @moduleId _search_module_id_
 */
class class_module_search_portal_xml extends class_portal implements interface_xml_portal {

    private static $INT_MAX_NR_OF_RESULTS = 30;

    /**
     * Searches for a passed string
     *
     * @return string
     * @permissions view
     */
    protected function actionDoSearch() {
        $strReturn = "";

        $strSearchterm = "";
        if($this->getParam("searchterm") != "") {
            $strSearchterm = htmlToString(urldecode($this->getParam("searchterm")), true);
        }

        $arrResult = array();
        $objSearchCommons = new class_module_search_commons();
        if($strSearchterm != "") {
            $arrResult = $objSearchCommons->doPortalSearch($strSearchterm);
        }

        $strReturn .= $this->createSearchXML($strSearchterm, $arrResult);

        return $strReturn;
    }


    /**
     * @param $strSearchterm
     * @param class_search_result[] $arrResults
     *
     * @return string
     */
    private function createSearchXML($strSearchterm, $arrResults) {
        $strReturn = "";

        $strReturn .=
            "<search>\n"
                ."    <searchterm>".xmlSafeString($strSearchterm)."</searchterm>\n"
                ."    <nrofresults>".count($arrResults)."</nrofresults>\n";


        //And now all results
        $intI = 0;
        $strReturn .= "    <resultset>\n";
        foreach($arrResults as $objOneResult) {

            $objPage = class_module_pages_page::getPageByName($objOneResult->getStrPagename());
            if($objPage === null || !$objPage->rightView() || $objPage->getIntRecordStatus() != 1)
                continue;


            if(++$intI > self::$INT_MAX_NR_OF_RESULTS)
                break;

            //create a correct link
            if($objOneResult->getStrPagelink() == "")
                $objOneResult->setStrPagelink(getLinkPortal($objOneResult->getStrPagename(), "", "_self", $objOneResult->getStrPagename(), "", "&highlight=".$strSearchterm."#".$strSearchterm));

            $strReturn .=
                "        <item>\n"
                    ."            <pagename>".$objOneResult->getStrPagename()."</pagename>\n"
                    ."            <pagelink>".$objOneResult->getStrPagelink()."</pagelink>\n"
                    ."            <score>".$objOneResult->getIntHits()."</score>\n"
                    ."            <description>".xmlSafeString(uniStrTrim($objOneResult->getStrDescription(), 200))."</description>\n"
                    ."        </item>\n";
        }

        $strReturn .= "    </resultset>\n";
        $strReturn .= "</search>";
        return $strReturn;
    }
}
