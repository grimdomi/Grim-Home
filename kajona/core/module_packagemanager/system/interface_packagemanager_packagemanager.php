<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: interface_packagemanager_packagemanager.php 5737 2013-07-13 14:40:24Z sidler $                                  *
********************************************************************************************************/


/**
 * A packagemanager handles a single package or a list of packages installed locally.
 * It provides common methods to query installed packages and triggers updates or installs.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_packagemanager
 */
interface interface_packagemanager_packagemanager {

    /**
     * Returns a list of installed packages, so a single metadata-entry
     * for each package.
     *
     * @abstract
     * @return class_module_packagemanager_metadata[]
     */
    public function getInstalledPackages();

    /**
     * Moves the extracted(!) package from the temp-folder
     * to the target-folder.
     * In most cases, this is either located at /core or at /templates.
     *
     * @abstract
     */
    public function move2Filesystem();

    /**
     * Invokes the installer, if given.
     * The installer itself is capable of detecting whether an update or a plain installation is required.
     *
     * @abstract
     */
    public function installOrUpdate();

    /**
     * Returns the metadata currently set.
     *
     * @abstract
     * @return class_module_packagemanager_metadata
     */
    public function getObjMetadata();

    /**
     * Validates, whether the current package is installable or not.
     * In nearly all cases
     *
     * @abstract
     * @return bool
     */
    public function isInstallable();

    /**
     * Gets the version of the package currently installed.
     * If not installed, null should be returned instead.
     *
     * @abstract
     * @return string|null
     */
    public function getVersionInstalled();

    /**
     * Queries the packagemanager for the resolved target path, so the folder to package will be located at
     * after installation (or is already located at since it's already installed.
     *
     * @abstract
     * @return mixed
     */
    public function getStrTargetPath();


    /**
     * This method is called during the installation of a package.
     * Depending on the current manager, the default-template may be updated.
     * @return bool
     */
    public function updateDefaultTemplate();

}