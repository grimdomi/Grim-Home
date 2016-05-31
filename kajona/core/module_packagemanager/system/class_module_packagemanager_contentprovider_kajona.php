<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_packagemanager_contentprovider_kajona.php 5409 2012-12-30 13:09:07Z sidler $                                  *
********************************************************************************************************/

/**
 * A simple content-provider used to upload archives from the official kajona-repo.
 * Provides both, a search and a download-part.
 *
 * @package module_packagemanager
 * @author sidler@mulchprod.de
 * @author flo@mediaskills.org
 * @since 4.0
 */
class class_module_packagemanager_contentprovider_kajona extends class_module_packagemanager_contentprovider_remote_base {

    function __construct() {
        parent::__construct(
            "provider_kajona",
            "www.kajona.de",
            "/xml.php?module=packageserver&action=list",
            "/download.php",
            __CLASS__
        );
    }
}