<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_user_user.php 5924 2013-10-05 18:08:38Z sidler $                                   *
********************************************************************************************************/

/**
 * Model for a user
 * Note: Users do not use the classical system-id relation, so no entry in the system-table
 *
 * @package module_user
 * @author sidler@mulchprod.de
 *
 * @module user
 * @moduleId _user_modul_id_
 */
class class_module_user_user extends class_model implements interface_model, interface_admin_listable  {

    private $strSubsystem = "kajona";

    /**
     *
     * @var interface_usersources_user
     */
    private $objSourceUser;

    private $strUsername = "";

    private $intLogins = 0;
    private $intLastlogin = 0;
    private $intActive = 0;
    private $intAdmin = 0;
    private $intPortal = 0;
    private $strAdminskin = "";
    private $strAdminlanguage = "";
    private $strAuthcode = "";


    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     * @return string
     */
    public function getStrDisplayName() {
        $strReturn =  $this->getStrUsername();
        if($this->getStrName() != "")
            $strReturn .= " (".$this->getStrName().", ".$this->getStrForename().")";

        return $strReturn;
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon() {
        return "icon_user";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     * @return string
     */
    public function getStrAdditionalInfo() {
        if($this->rightRight1()) {
            return $this->getLang("user_logins", "user")." ".$this->getIntLogins()." ".$this->getLang("user_lastlogin", "user")." ".timeToString($this->getIntLastLogin(), false);
        }
        return "";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     * @return string
     */
    public function getStrLongDescription() {
        $objUsersources = new class_module_user_sourcefactory();
        if(count($objUsersources->getArrUsersources()) > 1) {
            return $this->getLang("user_list_source", "user")." ".$this->getStrSubsystem();
        }
        return "";
    }


    public function rightView() {
        return class_module_system_module::getModuleByName("user")->rightView();
    }

    public function rightEdit() {
        return class_module_system_module::getModuleByName("user")->rightEdit();
    }

    public function rightDelete() {
        return class_module_system_module::getModuleByName("user")->rightDelete();
    }

    public function rightRight1() {
        return class_module_system_module::getModuleByName("user")->rightRight1();
    }


    /**
     * Initialises the current object, if a systemid was given
     */
    protected function initObjectInternal() {
        $strQuery = "SELECT * FROM "._dbprefix_."user WHERE user_id=?";
        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));

        if(count($arrRow) > 0) {
            $this->setStrUsername($arrRow["user_username"]);
            $this->setStrSubsystem($arrRow["user_subsystem"]);
            $this->setIntLogins($arrRow["user_logins"]);
            $this->setIntLastLogin($arrRow["user_lastlogin"]);
            $this->setIntActive($arrRow["user_active"]);
            $this->setIntAdmin($arrRow["user_admin"]);
            $this->setIntPortal($arrRow["user_portal"]);
            $this->setStrAdminskin($arrRow["user_admin_skin"]);
            $this->setStrAdminlanguage($arrRow["user_admin_language"]);
            $this->setSystemid($arrRow["user_id"]);
            $this->setStrAuthcode($arrRow["user_authcode"]);
        }
    }

    /**
     * Updates the current object to the database
     * <b>ATTENTION</b> If you don't want to update the password, set it to "" before!
     *
     * @param bool $strPrevid
     * @return bool
     */
    public function updateObjectToDb($strPrevid = false) {

        if($this->getSystemid() == "") {
            $strUserid = generateSystemid();
            $this->setSystemid($strUserid);
            $strQuery = "INSERT INTO "._dbprefix_."user (
                        user_id, user_active,
                        user_admin, user_portal,
                        user_admin_skin, user_admin_language,
                        user_logins, user_lastlogin, user_authcode, user_subsystem, user_username

                        ) VALUES (?,?,?,?,?,?,?,?,?,?,?)";

            class_logger::getInstance(class_logger::USERSOURCES)->addLogRow("new user for subsystem ".$this->getStrSubsystem()." / ".$this->getStrUsername(), class_logger::$levelInfo);

            $bitReturn = $this->objDB->_pQuery(
                $strQuery,
                array(
                    $strUserid,
                    (int)$this->getIntActive(),
                    (int)$this->getIntAdmin(),
                    (int)$this->getIntPortal(),
                    $this->getStrAdminskin(),
                    $this->getStrAdminlanguage(),
                    0,
                    0,
                    $this->getStrAuthcode(),
                    $this->getStrSubsystem(),
                    $this->getStrUsername()
                )
            );

            //create the new instance on the remote-system
            $objSources = new class_module_user_sourcefactory();
            $objProvider = $objSources->getUsersource($this->getStrSubsystem());
            $objTargetUser = $objProvider->getNewUser();
            $objTargetUser->updateObjectToDb();
            $objTargetUser->setNewRecordId($this->getSystemid());
            $this->objDB->flushQueryCache();

            return $bitReturn;
        }
        else {

            $strQuery = "UPDATE "._dbprefix_."user SET
                    user_active=?, user_admin=?, user_portal=?, user_admin_skin=?, user_admin_language=?, user_logins = ?, user_lastlogin = ?, user_authcode = ?, user_subsystem = ?,
                    user_username =?
                    WHERE user_id = ?";

            $arrParams = array(
                    (int)$this->getIntActive(),
                    (int)$this->getIntAdmin(), (int)$this->getIntPortal(), $this->getStrAdminskin(), $this->getStrAdminlanguage(),
                    (int)$this->getIntLogins(), (int)$this->getIntLastLogin(), $this->getStrAuthcode(),
                    $this->getStrSubsystem(), $this->getStrUsername(),
                    $this->getSystemid()
               );


            class_logger::getInstance(class_logger::USERSOURCES)->addLogRow("updated user for subsystem ".$this->getStrSubsystem()." / ".$this->getStrUsername(), class_logger::$levelInfo);
            return $this->objDB->_pQuery($strQuery, $arrParams);
        }
    }

    /**
     * Called whenever a update-request was fired.
     * Use this method to synchronize yourselves with the database.
     * Use only updates, inserts are not required to be implemented.
     *
     * @return bool
     */
    protected function updateStateToDb() {
        return false;
    }


    /**
     * Fetches all available users an returns them in an array
     *
     * @param string $strUsernameFilter
     * @param bool|int $intStart
     * @param bool|int $intEnd
     * @return class_module_user_user[]
     */
    public static function getObjectList($strUsernameFilter = "", $intStart = null, $intEnd = null) {
        $strQuery = "SELECT user_id FROM " . _dbprefix_ . "user WHERE user_username LIKE ? ORDER BY user_username, user_subsystem ASC";

        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array("%" . $strUsernameFilter . "%"), $intStart, $intEnd);

        $arrReturn = array();
        foreach($arrIds as $arrOneId) {
            $arrReturn[] = new class_module_user_user($arrOneId["user_id"]);
        }

        return $arrReturn;
    }

    /**
     * Counts the number of users created
     *
     * @param string $strUsernameFilter
     * @return int
     */
    public static function getObjectCount($strUsernameFilter = "") {
        $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."user WHERE user_username LIKE ? ";
        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strUsernameFilter."%"));
        return $arrRow["COUNT(*)"];
    }

    /**
     * Fetches all available active users with the given username an returns them in an array
     *
     * @param string $strName
     * @return mixed
     */
    public static function getAllUsersByName($strName) {
        $objSubsystem = new class_module_user_sourcefactory();
        $objUser = $objSubsystem->getUserByUsername($strName);
        if($objUser != null)
            return array($objUser);
        else
            return null;
    }


    /**
     * Deletes a user from the systems
     *
     * @return bool
     */
    public function deleteObject() {
        class_logger::getInstance(class_logger::USERSOURCES)->addLogRow("deleted user with id ".$this->getSystemid(), class_logger::$levelInfo);
        $strQuery = "DELETE FROM "._dbprefix_."user WHERE user_id=?";
        //call other models that may be interested
        $this->getObjSourceUser()->deleteUser();
        $bitReturn = $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
        class_core_eventdispatcher::notifyRecordDeletedListeners($this->getSystemid(), get_class($this));

        return $bitReturn;
    }


    /**
     * Returns an array of group-ids the current user is assigned to
     * @return array string
     */
    public function getArrGroupIds() {
        $this->loadSourceObject();
        return $this->objSourceUser->getGroupIdsForUser();
    }

    public function getStrEmail() {
        $this->loadSourceObject();
        if($this->objSourceUser != null)
            return $this->objSourceUser->getStrEmail();
        else
            return "n.a.";
    }

    public function getStrForename() {
        $this->loadSourceObject();
        if($this->objSourceUser != null)
            return $this->objSourceUser->getStrForename();
        else
            return "n.a.";
    }

    public function getStrName() {
        $this->loadSourceObject();
        if($this->objSourceUser != null)
            return $this->objSourceUser->getStrName();
        else
            return "n.a.";
    }

    private function loadSourceObject() {
        if($this->objSourceUser == null) {
            $objUsersources = new class_module_user_sourcefactory();
            $this->setObjSourceUser($objUsersources->getSourceUser($this));
        }
    }




    // --- GETTERS / SETTERS --------------------------------------------------------------------------------

    public function getIntLogins() {
        return $this->intLogins;
    }
    public function getIntLastLogin() {
        return $this->intLastlogin;
    }
    public function getIntActive() {
        return $this->intActive;
    }
    public function getIntAdmin() {
        return $this->intAdmin;
    }
    public function getIntPortal() {
        return $this->intPortal;
    }
    public function getStrAdminskin() {
        return $this->strAdminskin;
    }
    public function getStrAdminlanguage() {
        return $this->strAdminlanguage;
    }

    public function getStrUsername() {
        return $this->strUsername;
    }

    public function setStrUsername($strUsername) {
        $this->strUsername = $strUsername;
    }

    public function setIntLogins($intLogins) {
        if($intLogins == "")
            $intLogins = 0;
        $this->intLogins = $intLogins;
    }
    public function setIntLastLogin($intLastLogin) {
        if($intLastLogin == "")
            $intLastLogin = 0;
        $this->intLastlogin = $intLastLogin;
    }
    public function setIntActive($intActive) {
        if($intActive == "")
            $intActive = 0;
        $this->intActive = $intActive;
    }
    public function setIntAdmin($intAdmin) {
        if($intAdmin == "")
            $intAdmin = 0;
        $this->intAdmin = $intAdmin;
    }
    public function setIntPortal($intPortal) {
        if($intPortal == "")
            $intPortal = 0;
        $this->intPortal = $intPortal;
    }
    public function setStrAdminskin($strAdminskin) {
        $this->strAdminskin = $strAdminskin;
    }
    public function setStrAdminlanguage($strAdminlanguage) {
        $this->strAdminlanguage = $strAdminlanguage;
    }

    public function getStrAuthcode() {
        return $this->strAuthcode;
    }

    public function setStrAuthcode($strAuthcode) {
        $this->strAuthcode = $strAuthcode;
    }

    public function getStrSubsystem() {
        return $this->strSubsystem;
    }

    public function setStrSubsystem($strSubsystem) {
        $this->strSubsystem = $strSubsystem;
    }

    /**
     *
     * @return interface_usersources_user
     */
    public function getObjSourceUser() {
        $this->loadSourceObject();
        return $this->objSourceUser;
    }

    public function setObjSourceUser($objSourceUser) {
        $this->objSourceUser = $objSourceUser;
    }

    public function getIntRecordStatus() {
        return $this->intActive;
    }

}
