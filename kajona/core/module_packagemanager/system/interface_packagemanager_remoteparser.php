<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: interface_packagemanager_remoteparser.php 5409 2012-12-30 13:09:07Z sidler $                                  *
********************************************************************************************************/


/**
 * A remote parser knows how to handle the result of a queried remote content provider.
 * It handles various versions of the remote API results.
 *
 * @author flo@mediaskills.org
 * @since 4.0
 * @package module_packagemanager
 */
interface interface_packagemanager_remoteparser {

    /**
     * Returns the array of packages within the boundary of start and end index.
     *
     * @abstract
     * @return array
     */
    public function getArrPackages();

    /**
     * Returns the code to navigate between existing pages.
     *
     * @return string
     */
    public function paginationFooter();
}