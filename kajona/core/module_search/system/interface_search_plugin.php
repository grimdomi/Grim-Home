<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: interface_search_plugin.php 5409 2012-12-30 13:09:07Z sidler $                                  *
********************************************************************************************************/

/**
 * Interface for all portal search-plugins
 *
 * @package module_search
 * @author sidler@mulchprod.de
 */
interface interface_search_plugin {

    /**
     * Constructor, receiving the term to search for
     *
     * @param class_module_search_search $objSearch as db-query
     */
    public function __construct(class_module_search_search $objSearch);


    /**
     * This method is invoked from outside, starts to search for the passed term
     * and returns the results
     *
     * @return class_search_result[]
     */
    public function doSearch();

}
