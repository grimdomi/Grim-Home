<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: installer.php 5919 2013-10-03 14:14:13Z sidler $                                           *
********************************************************************************************************/

/**
 * Class representing a graphical installer.
 * Loads all sub-installers
 *
 * @author sidler@mulchprod.de
 * @package module_system
 */
class class_installer {


    private $STR_ORIG_CONFIG_FILE = "";
    private $STR_PROJECT_CONFIG_FILE = "";

    /**
     * @var class_module_packagemanager_metadata[]
     */
    private $arrMetadata;
    private $strOutput = "";
    private $strLogfile = "";
    private $strForwardLink = "";
    private $strBackwardLink = "";

    private $strVersion = "V 4.3";

    /**
     * Instance of template-engine
     *
     * @var class_template
     */
    private $objTemplates;

    /**
     * text-object
     *
     * @var class_lang
     */
    private $objLang;

    /**
     * session
     *
     * @var class_session
     */
    private $objSession;


    public function __construct() {
        //start up system
        class_carrier::getInstance();
        $this->objTemplates = class_carrier::getInstance()->getObjTemplate();
        $this->objLang = class_carrier::getInstance()->getObjLang();
        //init session-support
        $this->objSession = class_carrier::getInstance()->getObjSession();

        //set a different language?
        if(issetGet("language")) {
            if(in_array(getGet("language"), explode(",", class_carrier::getInstance()->getObjConfig()->getConfig("adminlangs")))) {
                $this->objLang->setStrTextLanguage(getGet("language"));
                //and save to a cookie
                $objCookie = new class_cookie();
                $objCookie->setCookie("adminlanguage", getGet("language"));

            }
        }
        else {
            //init correct text-file handling as in admins
            $this->objLang->setStrTextLanguage($this->objSession->getAdminLanguage(true));
        }

        $this->STR_ORIG_CONFIG_FILE = _corepath_."/module_system/system/config/config.php";
        $this->STR_PROJECT_CONFIG_FILE = _realpath_."/project/system/config/config.php";
    }


    /**
     * Action block to control the behaviour

     */
    public function action() {

        //check if needed values are given
        if(!$this->checkDefaultValues())
            $this->configWizard();

        //load a list of available installers
        $this->loadInstaller();

        //step one: needed php-values
        if(!isset($_GET["step"]))
            $this->checkPHPSetting();


        elseif($_GET["step"] == "config" || !$this->checkDefaultValues()) {
            $this->configWizard();
        }

        elseif($_GET["step"] == "loginData") {
            $this->adminLoginData();
        }

        elseif($_GET["step"] == "modeSelect") {
            $this->modeSelect();
        }

        elseif($_GET["step"] == "install") {
            $this->createModuleInstalls();
        }

        elseif($_GET["step"] == "samplecontent") {
            $this->installSamplecontent();
        }

        elseif($_GET["step"] == "finish") {
            $this->finish();
        }
    }

    /**
     * Makes a few checks on files and settings for a correct webserver

     */
    public function checkPHPSetting() {
        $strReturn = "";

        $arrFilesAndFolders = array(
            "/project/system/config",
            "/project/dbdumps",
            "/project/log",
            "/project/temp",
            "/files/cache",
            "/files/images",
            "/files/public",
            "/files/downloads",
            "/core",
            "/templates/default"
        );

        $arrModules = array(
            "mbstring",
            "gd",
            "xml",
            "zip"
        );

        $strReturn .= $this->getLang("installer_phpcheck_intro");
        $strReturn .= $this->getLang("installer_phpcheck_lang");

        //link to different languages
        $arrLangs = explode(",", class_carrier::getInstance()->getObjConfig()->getConfig("adminlangs"));
        $intLangCount = 1;
        foreach($arrLangs as $strOneLang) {
            $strReturn .= "<a href=\""._webpath_."/installer.php?language=".$strOneLang."\">".class_carrier::getInstance()->getObjLang()->getLang("lang_".$strOneLang, "user")."</a>";
            if($intLangCount++ < count($arrLangs)) {
                $strReturn .= " | ";
            }
        }

        $strReturn .= "<br />".$this->getLang("installer_phpcheck_intro2")."<ul>";

        foreach($arrFilesAndFolders as $strOneFile) {
            $strReturn .= "<li>".$this->getLang("installer_phpcheck_folder").$strOneFile." ";
            if(is_writable(_realpath_.$strOneFile))
                $strReturn .= "<span class=\"label label-success\">".$this->getLang("installer_given")."</span>.";
            else
                $strReturn .= "<span class=\"label label-important\">".$this->getLang("installer_missing")."</span>!";
            $strReturn .= "</li>";
        }

        foreach($arrModules as $strOneModule) {
            $strReturn .= "<li>".$this->getLang("installer_phpcheck_module").$strOneModule." ";
            if(in_array($strOneModule, get_loaded_extensions()))
                $strReturn .= " <span class=\"label label-success\">".$this->getLang("installer_loaded")."</span>.";
            else
                $strReturn .= " <span class=\"label label-important\">".$this->getLang("installer_nloaded")."</span>!";

            $strReturn .= "</li>";
        }

        $strReturn .= "</ul>";
        $this->strForwardLink = $this->getForwardLink(_webpath_."/installer.php?step=config");
        $this->strBackwardLink = "";
        $this->strOutput = $strReturn;
    }

    /**
     * Shows a form to write the values to the config files

     */
    public function configWizard() {
        $strReturn = "";

        if($this->checkDefaultValues())
            header("Location: "._webpath_."/installer.php?step=loginData");

        $bitCxCheck = true;

        if(isset($_POST["write"]) && $_POST["write"] == "true") {


            //try to validate the data passed
            $bitCxCheck = class_carrier::getInstance()->getObjDB()->validateDbCxData(
                $_POST["driver"],
                $_POST["hostname"],
                $_POST["username"],
                $_POST["password"],
                $_POST["dbname"],
                $_POST["port"]
            );


            if($bitCxCheck) {
                $strFileContent = "<?php\n";
                $strFileContent .= "/*\n Kajona V4 config-file.\n If you want to overwrite additional settings, copy them from /core/module_system/system/config/config.php into this file.\n*/";
                $strFileContent .= "\n";
                $strFileContent .= "  \$config['dbhost']               = '".$_POST["hostname"]."';                   //Server name \n";
                $strFileContent .= "  \$config['dbusername']           = '".$_POST["username"]."';                   //Username \n";
                $strFileContent .= "  \$config['dbpassword']           = '".$_POST["password"]."';                   //Password \n";
                $strFileContent .= "  \$config['dbname']               = '".$_POST["dbname"]."';                     //Database name \n";
                $strFileContent .= "  \$config['dbdriver']             = '".$_POST["driver"]."';                     //DB-Driver \n";
                $strFileContent .= "  \$config['dbprefix']             = '".$_POST["dbprefix"]."';                   //Table-prefix \n";
                $strFileContent .= "  \$config['dbport']               = '".$_POST["port"]."';                       //Database port \n";

                $strFileContent .= "\n";
                //and save to file
                file_put_contents($this->STR_PROJECT_CONFIG_FILE, $strFileContent);
                // and reload
                header("Location: "._webpath_."/installer.php?step=loginData");
                $this->strOutput = $strReturn;
                return;
            }
        }


        //check for available modules
        $strMysqliInfo = "";
        $strSqlite3Info = "";
        $strPostgresInfo = "";
        $strOci8Info = "";
        if(!in_array("mysqli", get_loaded_extensions())) {
            $strMysqliInfo = "<div class=\"alert alert-error\">".$this->getLang("installer_dbdriver_na")." mysqli</div>";
        }
        if(!in_array("pgsql", get_loaded_extensions())) {
            $strPostgresInfo = "<div class=\"alert alert-error\">".$this->getLang("installer_dbdriver_na")." postgres</div>";
        }
        if(in_array("sqlite3", get_loaded_extensions())) {
            $strSqlite3Info = "<div class=\"alert alert-info\">".$this->getLang("installer_dbdriver_sqlite3")."</div>";
        }
        else {
            $strSqlite3Info = "<div class=\"alert alert-error\">".$this->getLang("installer_dbdriver_na")." sqlite3</div>";
        }
        if(in_array("oci8", get_loaded_extensions())) {
            $strOci8Info = "<div class=\"alert alert-info\">".$this->getLang("installer_dbdriver_oci8")."</div>";
        }
        else {
            $strOci8Info = "<div class=\"alert alert-error\">".$this->getLang("installer_dbdriver_na")." oci8</div>";
        }

        $strCxWarning = "";
        if(!$bitCxCheck) {
            $strCxWarning = "<div class=\"alert alert-error\">".$this->getLang("installer_dbcx_error")."</div>";
        }

        //configwizard_form
        $strTemplateID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "configwizard_form", true);
        $strReturn .= $this->objTemplates->fillTemplate(
            array(
                "mysqliInfo"       => $strMysqliInfo,
                "sqlite3Info"      => $strSqlite3Info,
                "postgresInfo"     => $strPostgresInfo,
                "oci8Info"         => $strOci8Info,
                "cxWarning"        => $strCxWarning,
                "postHostname"     => isset($_POST["hostname"]) ? $_POST["hostname"] : "",
                "postUsername"     => isset($_POST["username"]) ? $_POST["username"] : "",
                "postDbname"       => isset($_POST["dbname"]) ? $_POST["dbname"] : "",
                "postDbport"       => isset($_POST["port"]) ? $_POST["port"] : "",
                "postDbdriver"     => isset($_POST["driver"]) ? $_POST["driver"] : "",
                "postPrefix"       => isset($_POST["dbprefix"]) != "" ? $_POST["dbprefix"] : "kajona_"
            ),
            $strTemplateID
        );
        $this->strBackwardLink = $this->getBackwardLink(_webpath_."/installer.php");


        $this->strOutput = $strReturn;
    }

    /**
     * Collects the data required to create a valid admin-login
     */
    public function adminLoginData() {
        $bitUserInstalled = false;
        $bitShowForm = true;
        $this->strOutput .= $this->getLang("installer_login_intro");

        //if user-module is already installed, skip this step
        try {
            $objUser = class_module_system_module::getModuleByName("user");
            if($objUser != null) {
                $bitUserInstalled = true;
            }
        }
        catch(class_exception $objE) {
        }


        if($bitUserInstalled) {
            $bitShowForm = false;
            $this->strOutput .= "<span class=\"green\">".$this->getLang("installer_login_installed")."</span>";
        }
        if(isset($_POST["write"]) && $_POST["write"] == "true") {
            $strUsername = $_POST["username"];
            $strPassword = $_POST["password"];
            $strEmail = $_POST["email"];
            //save to session
            if($strUsername != "" && $strPassword != "" && checkEmailaddress($strEmail)) {
                $bitShowForm = false;
                $this->objSession->setSession("install_username", $strUsername);
                $this->objSession->setSession("install_password", $strPassword);
                $this->objSession->setSession("install_email", $strEmail);
                header("Location: "._webpath_."/installer.php?step=modeSelect");
            }
        }

        if($bitShowForm) {
            $strTemplateID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "loginwizard_form", true);
            $this->strOutput .= $this->objTemplates->fillTemplate(array(), $strTemplateID);
        }

        $this->strBackwardLink = $this->getBackwardLink(_webpath_."/installer.php");
        if($bitUserInstalled)
            $this->strForwardLink = $this->getForwardLink(_webpath_."/installer.php?step=modeSelect");
    }

    /**
     * The form to select the installer mode - everything automatically or a manual selection
     */
    public function modeSelect() {

        $strTemplateID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "modeselect_content", true);
        $this->strOutput .= $this->objTemplates->fillTemplate(
            array(
                "link_autoinstall" => _webpath_."/installer.php?step=finish&autoInstall=true",
                "link_manualinstall" => _webpath_."/installer.php?step=install"
            ),
            $strTemplateID
        );

        $this->strBackwardLink = $this->getBackwardLink(_webpath_."/installer.php");

    }

    /**
     * Loads all installers available to this->arrInstaller

     */
    public function loadInstaller() {

        $objManager = new class_module_packagemanager_manager();
        $arrModules = $objManager->getAvailablePackages();

        $this->arrMetadata = array();
        foreach($arrModules as $objOneModule)
            if($objOneModule->getBitProvidesInstaller())
                $this->arrMetadata[] = $objOneModule;

        $this->arrMetadata = $objManager->sortPackages($this->arrMetadata, true);

    }

    /**
     * Loads all installers and requests a install / update link, if available

     */
    public function createModuleInstalls() {
        $strReturn = "";
        $strInstallLog = "";

        $objManager = new class_module_packagemanager_manager();

        //module-installs to loop?
        if(isset($_POST["moduleInstallBox"]) && is_array($_POST["moduleInstallBox"])) {
            $arrModulesToInstall = $_POST["moduleInstallBox"];
            foreach($arrModulesToInstall as $strOneModule => $strValue) {

                //search the matching modules
                foreach($this->arrMetadata as $objOneMetadata) {
                    if($strOneModule == "installer_".$objOneMetadata->getStrTitle()) {
                        $objHandler = $objManager->getPackageManagerForPath($objOneMetadata->getStrPath());
                        $strInstallLog .= $objHandler->installOrUpdate();
                    }
                }

            }

        }

        class_objectfactory::getInstance()->flushCache();
        class_carrier::getInstance()->getObjDB()->flushQueryCache();
        class_module_system_module::flushCache();
        $this->loadInstaller();


        $this->strLogfile = $strInstallLog;
        $strReturn .= $this->getLang("installer_modules_found");

        $strRows = "";
        $strTemplateID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "installer_modules_row", true);
        $strTemplateIDInstallable = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "installer_modules_row_installable", true);

        //Loading each installer

        foreach($this->arrMetadata as $objOneMetadata) {

            //skip samplecontent
            if($objOneMetadata->getStrTitle() == "samplecontent")
                continue;

            $objHandler = $objManager->getPackageManagerForPath($objOneMetadata->getStrPath());

            $arrTemplate = array();
            $arrTemplate["module_name"] = $objHandler->getObjMetadata()->getStrTitle();
            $arrTemplate["module_nameShort"] = $objHandler->getObjMetadata()->getStrTitle();
            $arrTemplate["module_version"] = $objHandler->getObjMetadata()->getStrVersion();

            //generate the hint
            $arrTemplate["module_hint"] = "";

            if($objHandler->getVersionInstalled() !== null) {
                $arrTemplate["module_hint"] .= $this->getLang("installer_versioninstalled", "system").$objHandler->getVersionInstalled()."<br />";
            }

            //check missing modules
            $arrModules = $objHandler->getObjMetadata()->getArrRequiredModules();
            foreach($arrModules as $strOneModule => $strVersion) {
                if(trim($strOneModule) != "" && class_module_system_module::getModuleByName(trim($strOneModule)) === null) {

                    //check if a corresponding module is available
                    $objPackagemanager = new class_module_packagemanager_manager();
                    $objPackage = $objPackagemanager->getPackage($strOneModule);

                    if($objPackage === null || $objPackage->getBitProvidesInstaller() || version_compare($strVersion, $objPackage->getStrVersion(), ">")) {
                        $arrTemplate["module_hint"] .= $this->getLang("installer_systemversion_needed", "system").$strOneModule." >= ".$strVersion."<br />";
                    }

                }

                else if(version_compare($strVersion, class_module_system_module::getModuleByName(trim($strOneModule))->getStrVersion(), ">")) {
                    $arrTemplate["module_hint"] .= $this->getLang("installer_systemversion_needed", "system").$strOneModule." >= ".$strVersion."<br />";
                }
            }




            if($objHandler->isInstallable()) {
                $strRows .= $this->objTemplates->fillTemplate($arrTemplate, $strTemplateIDInstallable);
            }
            else {
                $strRows .= $this->objTemplates->fillTemplate($arrTemplate, $strTemplateID);
            }

        }

        //wrap in form
        $strTemplateID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "installer_modules_form", true);
        $strReturn .= $this->objTemplates->fillTemplate(array("module_rows" => $strRows), $strTemplateID);

        $this->strOutput .= $strReturn;
        $this->strBackwardLink = $this->getBackwardLink(_webpath_."/installer.php?step=modeSelect");
        $this->strForwardLink = $this->getForwardLink(_webpath_."/installer.php?step=samplecontent");
    }


    /**
     * Installs, if available, the samplecontent

     */
    public function installSamplecontent() {
        $strReturn = "";
        $strInstallLog = "";

        $objManager = new class_module_packagemanager_manager();

        //Is there a module to be installed or updated?
        if(isset($_GET["update"])) {
            foreach($this->arrMetadata as $objOneMetadata) {
                if($objOneMetadata->getStrTitle() != "samplecontent")
                    continue;

                $objHandler = $objManager->getPackageManagerForPath($objOneMetadata->getStrPath());
                $strInstallLog .= $objHandler->installOrUpdate();
            }
        }

        //module-installs to loop?
        if(isset($_POST["moduleInstallBox"]) && is_array($_POST["moduleInstallBox"])) {
            foreach($this->arrMetadata as $objOneMetadata) {
                if($objOneMetadata->getStrTitle() != "samplecontent")
                    continue;

                $objHandler = $objManager->getPackageManagerForPath($objOneMetadata->getStrPath());
                $strInstallLog .= $objHandler->installOrUpdate();
            }
        }

        $this->strLogfile = $strInstallLog;
        $strReturn .= $this->getLang("installer_samplecontent");

        //Loading each installer
        $strRows = "";
        $strTemplateID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "installer_modules_row", true);
        $strTemplateIDInstallable = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "installer_modules_row_installable", true);

        $bitInstallerFound = false;
        foreach($this->arrMetadata as $objOneMetadata) {

            if($objOneMetadata->getStrTitle() != "samplecontent")
                continue;

            $bitInstallerFound = true;

            $objHandler = $objManager->getPackageManagerForPath($objOneMetadata->getStrPath());

            $arrTemplate = array();
            $arrTemplate["module_nameShort"] = $objOneMetadata->getStrTitle();
            $arrTemplate["module_name"] = $objOneMetadata->getStrTitle();
            $arrTemplate["module_version"] = $objOneMetadata->getStrVersion();

            //generate the hint
            $arrTemplate["module_hint"] = "";

            if($objHandler->getVersionInstalled() !== null) {
                $arrTemplate["module_hint"] = $this->getLang("installer_versioninstalled", "system").$objHandler->getVersionInstalled();
            }
            else {
                //check missing modules
                $strRequired = "";
                $arrModules = $objHandler->getObjMetadata()->getArrRequiredModules();
                foreach($arrModules as $strOneModule => $strVersion) {
                    if(trim($strOneModule) != "" && class_module_system_module::getModuleByName(trim($strOneModule)) === null)
                        $strRequired .= $strOneModule.", ";
                }

                if(trim($strRequired) != "")
                    $arrTemplate["module_hint"] = $this->getLang("installer_modules_needed", "system").substr($strRequired, 0, -2);
            }

            if($objHandler->isInstallable())
                $strRows .= $this->objTemplates->fillTemplate($arrTemplate, $strTemplateIDInstallable);
            else
                $strRows .= $this->objTemplates->fillTemplate($arrTemplate, $strTemplateID);

        }

        if(!$bitInstallerFound)
            header("Location: "._webpath_."/installer.php?step=finish");

        //wrap in form
        $strTemplateID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "installer_samplecontent_form", true);
        $strReturn .= $this->objTemplates->fillTemplate(array("module_rows" => $strRows), $strTemplateID);

        $this->strOutput .= $strReturn;
        $this->strBackwardLink = $this->getBackwardLink(_webpath_."/installer.php?step=install");
        $this->strForwardLink = $this->getForwardLink(_webpath_."/installer.php?step=finish");
    }

    /**
     * The last page of the installer, showing a few infos and links how to go on

     */
    public function finish() {
        $strReturn = "";

        if(isset($_GET["autoInstall"]) && $_GET["autoInstall"] == "true") {
            $this->strLogfile = $this->processAutoInstall();
        }


        $this->objSession->sessionUnset("install_username");
        $this->objSession->sessionUnset("install_password");

        $strReturn .= $this->getLang("installer_finish_intro");
        $strReturn .= $this->getLang("installer_finish_hints");
        $strReturn .= $this->getLang("installer_finish_hints_update");
        $strReturn .= $this->getLang("installer_finish_closer");

        $this->strOutput = $strReturn;
        $this->strBackwardLink = $this->getBackwardLink(_webpath_."/installer.php?step=samplecontent");
    }


    private function processAutoInstall() {
        $strReturn = "";

        $strReturn .= "Searching for packages to be installed...\n";
        $objManager = new class_module_packagemanager_manager();
        $arrPackageMetadata = $objManager->getAvailablePackages();

        $arrPackagesToInstall = array();
        foreach($arrPackageMetadata as $objOneMetadata) {
            if(!in_array($objOneMetadata->getStrTitle(), array("samplecontent")))
                $arrPackagesToInstall[] = $objOneMetadata;
        }

        $strReturn .= "Number of packages found: ".count($arrPackagesToInstall)."\n";
        $strReturn .= "\n\n";

        $intMaxLoops = 0;
        $strReturn .= "starting installations...\n";
        while(count($arrPackagesToInstall) > 0 && ++$intMaxLoops < 100) {
            foreach($arrPackagesToInstall as $intKey => $objOneMetadata) {

                $strReturn .= "------------------------------\n";

                if(!$objOneMetadata->getBitProvidesInstaller()) {
                    $strReturn .= "skipping ".$objOneMetadata->getStrTitle().", no installer provided...\n";
                    unset($arrPackagesToInstall[$intKey]);
                    continue;
                }

                $strReturn .= "Installing ".$objOneMetadata->getStrTitle()."...\n";
                $objHandler = $objManager->getPackageManagerForPath($objOneMetadata->getStrPath());

                if(!$objHandler->isInstallable()) {
                    $strReturn .= "skipping ".$objOneMetadata->getStrTitle()." due to unresolved requirements\n";
                    continue;
                }

                $strReturn .= $objHandler->installOrUpdate();
                unset($arrPackagesToInstall[$intKey]);
                $strReturn .= "\n";
            }
        }


        $strReturn .= "Installing samplecontent...\n";
        try {
            $objHandler = $objManager->getPackageManagerForPath("/core/module_samplecontent");
        }
        catch(class_exception $objEx) {
            $objHandler = null;
        }
        if($objHandler != null && $objHandler->isInstallable())
            $strReturn .= $objHandler->installOrUpdate();


        return $strReturn;
    }


    /**
     * Generates the surrounding layout and embeds the installer-output
     *
     * @return string
     */
    public function getOutput() {
        if($this->strLogfile != "") {
            $strTemplateID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "installer_log", true);
            $this->strLogfile = $this->objTemplates->fillTemplate(
                array(
                    "log_content" => $this->strLogfile,
                    "systemlog"   => $this->getLang("installer_systemlog")
                ), $strTemplateID
            );
        }


        //build the progress-entries
        $strCurrentCommand = (isset($_GET["step"]) ? $_GET["step"] : "");
        if($strCurrentCommand == "")
            $strCurrentCommand = "phpsettings";

        $arrProgressEntries = array(
            "phpsettings"   => $this->getLang("installer_step_phpsettings"),
            "config"        => $this->getLang("installer_step_dbsettings"),
            "loginData"     => $this->getLang("installer_step_adminsettings"),
            "modeSelect"     => $this->getLang("installer_step_modeselect"),
            "install"       => $this->getLang("installer_step_modules"),
            "samplecontent" => $this->getLang("installer_step_samplecontent"),
            "finish"        => $this->getLang("installer_step_finish"),
        );

        $strProgress = "";
        $strTemplateEntryTodoID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "installer_progress_entry", true);
        $strTemplateEntryCurrentID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "installer_progress_entry_current", true);
        $strTemplateEntryDoneID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "installer_progress_entry_done", true);

        $strTemplateEntryID = $strTemplateEntryDoneID;
        foreach($arrProgressEntries as $strKey => $strValue) {
            $arrTemplateEntry = array();
            $arrTemplateEntry["entry_name"] = $strValue;

            //choose the correct template section
            if($strCurrentCommand == $strKey) {
                $strProgress .= $this->objTemplates->fillTemplate($arrTemplateEntry, $strTemplateEntryCurrentID, true);
                $strTemplateEntryID = $strTemplateEntryTodoID;
            }
            else
                $strProgress .= $this->objTemplates->fillTemplate($arrTemplateEntry, $strTemplateEntryID, true);

        }
        $arrTemplate = array();
        $arrTemplate["installer_progress"] = $strProgress;
        $arrTemplate["installer_version"] = $this->strVersion;
        $arrTemplate["installer_output"] = $this->strOutput;
        $arrTemplate["installer_forward"] = $this->strForwardLink;
        $arrTemplate["installer_backward"] = $this->strBackwardLink;
        $arrTemplate["installer_logfile"] = $this->strLogfile;
        $strTemplateID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "installer_main", true);

        $strReturn = $this->objTemplates->fillTemplate($arrTemplate, $strTemplateID);
        $strReturn = $this->callScriptlets($strReturn);
        $this->objTemplates->setTemplate($strReturn);
        $this->objTemplates->deletePlaceholder();
        $strReturn = $this->objTemplates->getTemplate();
        return $strReturn;
    }


    /**
     * Calls the scriptlets in order to process additional tags and in order to enrich the content.
     *
     * @param $strContent
     *
     * @return string
     */
    private function callScriptlets($strContent) {
        $objHelper = new class_scriptlet_helper();
        return $objHelper->processString($strContent);
    }


    /**
     * Checks, if the config-file was filled with correct values
     *
     * @return bool
     */
    public function checkDefaultValues() {
        return is_file($this->STR_PROJECT_CONFIG_FILE);
    }

    /**
     * Creates a forward-link
     *
     * @param string $strHref
     *
     * @return string
     */
    public function getForwardLink($strHref) {
        $strTemplateID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "installer_forward_link", true);
        return $this->objTemplates->fillTemplate(array("href" => $strHref, "text" => $this->getLang("installer_next")), $strTemplateID);
    }

    /**
     * Creates backward-link
     *
     * @param string $strHref
     *
     * @return string
     */
    public function getBackwardLink($strHref) {
        $strTemplateID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "installer_backward_link", true);
        return $this->objTemplates->fillTemplate(array("href" => $strHref, "text" => $this->getLang("installer_prev")), $strTemplateID);
    }

    /**
     * Loads a text
     *
     * @param string $strKey
     *
     * @return string
     */
    public function getLang($strKey) {
        return $this->objLang->getLang($strKey, "installer");
    }
}


//set admin to false
define("_admin_", false);

//Creating the Installer-Object
$objInstaller = new class_installer();
$objInstaller->action();
echo $objInstaller->getOutput();

