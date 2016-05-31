<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_system_aspect.php 5924 2013-10-05 18:08:38Z sidler $                          *
********************************************************************************************************/

/**
 * Model for a single aspect. An aspect is a filter-type that can be applied to the backend.
 * E.g. there could be different dashboard for each aspect or a module may be visible only for given
 * aspects.
 * Aspects should and will not replace the permissions! If a module was removed from an aspect, it may
 * still be accessible directly due to sufficient permissions.
 * This means aspects are rather some kind of view-filter then business-logic filters.
 *
 * @package module_system
 * @since 3.4
 * @author sidler@mulchprod.de
 * @targetTable aspects.aspect_id
 *
 * @module system
 * @moduleId _system_modul_id_
 */
class class_module_system_aspect extends class_model implements interface_model, interface_admin_listable {

    /**
     * @var string
     * @tableColumn aspect_name
     * @fieldType text
     * @fieldMandatory
     */
    private $strName = "";

    /**
     * @var bool
     * @tableColumn aspect_default
     * @fieldType yesno
     * @fieldMandatory
     */
    private $bitDefault = 0;

    private static $STR_SESSION_ASPECT_KEY = "STR_SESSION_ASPECT_KEY";
    private static $STR_SESSION_ASPECT_OBJECT = "STR_SESSION_ASPECT_OBJECT";



    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        //try to load the name from the lang-files
        $strLabel = $this->getLang("aspect_".$this->getStrName(), "system");
        if($strLabel != "!aspect_".$this->getStrName()."!")
            return $strLabel;
        else
            return $this->getStrName();
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon() {
        return "icon_aspect";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo() {
        return $this->getBitDefault() == 1 ? " (".$this->getLang("aspect_isDefault", "system").")" : "";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription() {
        return "";
    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    protected function updateStateToDb() {

        //if no other aspect exists, we have a new default aspect
        $arrObjAspects = class_module_system_aspect::getObjectList();
        if(count($arrObjAspects) == 0) {
            $this->setBitDefault(1);
        }

        if($this->getBitDefault() == 1)
            self::resetDefaultAspect();

        return parent::updateStateToDb();
    }


    /**
     * Returns an array of all aspects available
     *
     * @param bool $bitJustActive
     * @param bool|int $intStart
     * @param bool|int $intEnd
     *
     * @return class_module_system_aspect[]
     * @static
     */
    public static function getObjectList($bitJustActive = false, $intStart = null, $intEnd = null) {
        $strQuery = "SELECT system_id
                     FROM "._dbprefix_."aspects, "._dbprefix_."system
		             WHERE system_id = aspect_id
		             ".($bitJustActive ? "AND system_status != 0 " : "")."
		             ORDER BY system_sort ASC, aspect_name ASC";
        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array(), $intStart, $intEnd);
        $arrReturn = array();
        foreach($arrIds as $arrOneId)
            $arrReturn[] = new class_module_system_aspect($arrOneId["system_id"]);

        return $arrReturn;
    }


    /**
     * Returns the number of aspectss installed in the system
     *
     * @param bool $bitJustActive
     *
     * @return int
     */
    public static function getObjectCount($bitJustActive = false) {
        $strQuery = "SELECT COUNT(*)
                     FROM "._dbprefix_."aspects, "._dbprefix_."system
                     WHERE system_id = aspect_id
                     ".($bitJustActive ? "AND system_status != 0 " : "")."";
        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array());

        return (int)$arrRow["COUNT(*)"];

    }


    /**
     * Resets all default aspects.
     * Afterwards, no default aspect is available!
     *
     * @return bool
     */
    public static function resetDefaultAspect() {
        $strQuery = "UPDATE "._dbprefix_."aspects
                     SET aspect_default = 0";
        return class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array());
    }


    /**
     * Deletes the current object from the database
     *
     * @return bool
     */
    protected function deleteObjectInternal() {
        parent::deleteObjectInternal();

        //if we have just one aspect remaining, set this one as default
        $arrObjAspects = class_module_system_aspect::getObjectList();
        if(count($arrObjAspects) == 1) {
            $objOneLanguage = $arrObjAspects[0];
            $objOneLanguage->setBitDefault(1);
            $objOneLanguage->updateObjectToDb();
        }

        return true;
    }


    /**
     * Returns the default aspect, defined in the admin.
     * This takes permissions into account!
     *
     * @param bool $bitIgnorePermissions
     *
     * @return class_module_system_aspect null if no aspect is set up
     */
    public static function getDefaultAspect($bitIgnorePermissions = false) {
        //try to load the default language
        $strQuery = "SELECT system_id
                 FROM "._dbprefix_."aspects,
                      "._dbprefix_."system
	             WHERE system_id = aspect_id
	             AND aspect_default = 1
	             AND system_status = 1";
        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array());
        if(count($arrRow) > 0  && ($bitIgnorePermissions || class_carrier::getInstance()->getObjRights()->rightView($arrRow["system_id"]))) {
            return new class_module_system_aspect($arrRow["system_id"]);
        }
        else {
            if(count(class_module_system_aspect::getObjectList(true)) > 0) {
                $arrAspects = class_module_system_aspect::getObjectList(true);
                foreach($arrAspects as $objOneAspect)
                    if($objOneAspect->rightView())
                        return $objOneAspect;
            }

            return null;
        }
    }

    /**
     * Returns an aspect by name, ignoring the status
     *
     * @param string $strName
     *
     * @return class_module_system_aspect or null if not found
     */
    public static function getAspectByName($strName) {
        $strQuery = "SELECT system_id
                 FROM "._dbprefix_."aspects, "._dbprefix_."system
	             WHERE system_id = aspect_id
	             AND aspect_name = ?
	             ORDER BY system_sort ASC, system_comment ASC";
        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strName));
        if(count($arrRow) > 0) {
            return new class_module_system_aspect($arrRow["system_id"]);
        }
        else {
            return null;
        }
    }


    /**
     * Returns the aspect currently selected by the user.
     * If no aspect was selected before, the default aspect is returned instead.
     * In addition, the current params are processed in order to react on changes made
     * by the user / external sources.
     *
     * @return class_module_system_aspect null if no aspect is set up
     */
    public static function getCurrentAspect() {

        //process params maybe existing
        if(defined("_admin_") && _admin_ && getGet("aspect") != "" && validateSystemid(getGet("aspect"))) {
            self::setCurrentAspectId(getGet("aspect"));
        }

        //aspect registered in session?
        if(validateSystemid(class_carrier::getInstance()->getObjSession()->getSession(class_module_system_aspect::$STR_SESSION_ASPECT_KEY))) {
            if(class_carrier::getInstance()->getObjSession()->getSession(class_module_system_aspect::$STR_SESSION_ASPECT_OBJECT, class_session::$intScopeRequest) !== false) {
                return class_carrier::getInstance()->getObjSession()->getSession(class_module_system_aspect::$STR_SESSION_ASPECT_OBJECT, class_session::$intScopeRequest);
            }
            else {
                $objAspect = new class_module_system_aspect(class_carrier::getInstance()->getObjSession()->getSession(class_module_system_aspect::$STR_SESSION_ASPECT_KEY));
                class_carrier::getInstance()->getObjSession()->setSession(class_module_system_aspect::$STR_SESSION_ASPECT_OBJECT, $objAspect, class_session::$intScopeRequest);
                return $objAspect;
            }
        }
        else {
            $objAspect = class_module_system_aspect::getDefaultAspect();
            if($objAspect != null)
                self::setCurrentAspectId($objAspect->getSystemid());
            return $objAspect;
        }
    }

    /**
     * Wrapper to getCurrentAspect(), returning the ID of the aspect currently selected.
     * If no aspect is selected, an empty string is returned.
     *
     * @return string
     */
    public static function getCurrentAspectId() {
        $objAspect = class_module_system_aspect::getCurrentAspect();
        if($objAspect != null)
            return $objAspect->getSystemid();
        else
            return "";
    }

    /**
     * Saves an aspect id as the current active one - but only if the previous one was changed
     *
     * @param string $strAspectId
     */
    public static function setCurrentAspectId($strAspectId) {
        if(validateSystemid($strAspectId) && $strAspectId != class_carrier::getInstance()->getObjSession()->getSession(class_module_system_aspect::$STR_SESSION_ASPECT_KEY)) {
            class_carrier::getInstance()->getObjSession()->setSession(class_module_system_aspect::$STR_SESSION_ASPECT_KEY, $strAspectId);
            class_carrier::getInstance()->getObjSession()->setSession(class_module_system_aspect::$STR_SESSION_ASPECT_OBJECT, new class_module_system_aspect($strAspectId), class_session::$intScopeRequest);
        }
    }


    /**
     * @param $strName
     */
    public function setStrName($strName) {
        $this->strName = $strName;
    }

    public function setBitDefault($bitDefault) {
        $this->bitDefault = $bitDefault;
    }

    public function getStrName() {
        return $this->strName;
    }

    public function getBitDefault() {
        return $this->bitDefault;
    }

}
