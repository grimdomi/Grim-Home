<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_search_portal.php 5903 2013-09-30 13:40:29Z sidler $	                                    *
********************************************************************************************************/

/**
 * Portal element of the search-module
 *
 * @package module_search
 * @author sidler@mulchprod.de
 *
 * @targetTable element_search.content_id
 */
class class_element_search_portal extends class_element_portal implements interface_portal_element {


    /**
     * @param class_module_pages_pageelement|mixed $objElementData
     */
    public function __construct($objElementData) {
        parent::__construct($objElementData);
        $this->setStrCacheAddon(getPost("searchterm").getGet("searchterm"));
    }

    /**
     * Loads the search-class and passes control
     *
     * @return string
     */
    public function loadData() {
        $strReturn = "";
        //Load the data
        $objSearchModule = class_module_system_module::getModuleByName("search");
        if($objSearchModule != null) {
            $objSearch = $objSearchModule->getPortalInstanceOfConcreteModule($this->arrElementData);
            $strReturn = $objSearch->action();
        }
        return $strReturn;
    }

}
