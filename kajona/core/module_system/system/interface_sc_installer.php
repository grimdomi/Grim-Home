<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: interface_sc_installer.php 5409 2012-12-30 13:09:07Z sidler $                                     *
********************************************************************************************************/

/**
 * Interface for all installers
 *
 * @package module_system
 */
interface interface_sc_installer {


    /**
     * Does the hard work: installs the module and registers needed constants
     *
     * @return String
     */
    public function install();

    /**
     * Passes the db object
     *
     * @param class_db $objDb
     */
    public function setObjDb($objDb);

    /**
     * Passes the portal language
     *
     * @param string $strContentlanguage
     */
    public function setStrContentlanguage($strContentlanguage);

    /**
     * Returns the assigned module
     *
     * @return string
     */
    public function getCorrespondingModule();


}
