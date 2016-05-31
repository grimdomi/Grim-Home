<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_packagemanager_remoteparser_v3.php 5409 2012-12-30 13:09:07Z sidler $             *
********************************************************************************************************/

/**
 * A remote parser which handles results of Kajona's package manager API provided by version 3.x.
 *
 * @package module_packagemanager
 * @author flo@mediaskills.org
 * @since 4.0
 * @deprecated
 */
class class_module_packagemanager_remoteparser_v3 implements interface_packagemanager_remoteparser {

    private $arrPackages;

    function __construct($arrRemoteResponse) {
        $this->arrPackages = $arrRemoteResponse;
    }

    public function getArrPackages() {
        return $this->arrPackages;
    }

    public function paginationFooter() {
        return "";
    }


}