<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: packagemanager.php 5409 2012-12-30 13:09:07Z sidler $                                  *
********************************************************************************************************/

/**
 * Config-file for the package-manager. Contains the list of manager available
 * @package module_packagemanager
 */

$config = array();

//comma-separated list of registered content-providers
$config["contentproviders"] = "class_module_packagemanager_contentprovider_kajona,";
$config["contentproviders"] .= "class_module_packagemanager_contentprovider_kajonabase,";
$config["contentproviders"] .= "class_module_packagemanager_contentprovider_local";