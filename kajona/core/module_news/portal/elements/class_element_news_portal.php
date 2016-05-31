<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_news_portal.php 5903 2013-09-30 13:40:29Z sidler $						               	*
********************************************************************************************************/

/**
 * Portal-part of the news-element
 *
 * @package module_news
 * @author sidler@mulchprod.de
 * @targetTable element_news.content_id
 */
class class_element_news_portal extends class_element_portal implements interface_portal_element {


    /**
     * Loads the news-class and passes control
     *
     * @return string
     */
    public function loadData() {
        $strReturn = "";
        //Load the data
        $objNewsModule = class_module_system_module::getModuleByName("news");
        if($objNewsModule != null) {
            $objNews = $objNewsModule->getPortalInstanceOfConcreteModule($this->arrElementData);
            $strReturn = $objNews->action();
        }
        return $strReturn;
    }


    /**
     * Overwrite this method if you'd like to perform special actions if as soon as content
     * was loaded from the cache.
     * Make sure to return a proper boolean value, otherwise the cached entry may get invalid.
     *
     * @return boolean
     */
    public function onLoadFromCache() {
        //update the news shown, if in details mode
        if($this->getParam("action") == "newsDetail") {
            $objNews = new class_module_news_news($this->getParam("systemid"));
            $objNews->increaseHits();
        }

        return true;
    }

}
