<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_folderview_admin.php 5930 2013-10-08 07:22:07Z sidler $                            *
********************************************************************************************************/


/**
 * This class provides a list-view of the folders created in the database / filesystem.
 * Since Kajona 3.4.1 this class is deprecated. All methods have been moved to the appropriate source-modules.
 * It only remains as a switch between different browsers.
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 * @deprecated
 *
 * @module mediamanager
 * @moduleId _mediamanager_module_id_
 */
class class_module_folderview_admin extends class_admin implements interface_admin {

    /**
     * Constructor, doing nothing but a few inits
     */
    public function __construct() {
        $this->setArrModuleEntry("template", "/folderview.tpl");
        parent::__construct();
        $this->setStrLangBase("mediamanager");

    }


    protected function getOutputModuleTitle() {
        return $this->getLang("moduleFolderviewTitle");
    }

    /**
     * @return string
     * @autoTestable
     * @permissions view
     */
    protected function actionBrowserChooser() {
        $strReturn = "";

        if($this->getParam("CKEditorFuncNum") != "") {
            $strReturn .= "<script type=\"text/javascript\">window.opener.KAJONA.admin.folderview.selectCallbackCKEditorFuncNum = " . (int)$this->getParam("CKEditorFuncNum") . ";</script>";
        }

        $intCounter = 1;
        $strReturn .= $this->objToolkit->listHeader();

        if(class_module_system_module::getModuleByName("pages") !== null) {
            $strAction = $this->objToolkit->listButton(
                getLinkAdmin(
                    "pages",
                    "pagesFolderBrowser",
                    "&pages=1&form_element=" . $this->getParam("form_element") . "&bit_link=1",
                    $this->getLang("wysiwygPagesBrowser"),
                    $this->getLang("wysiwygPagesBrowser"),
                    "icon_folderActionOpen"
                )
            );
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("wysiwygPagesBrowser"), "", $strAction, $intCounter++);
        }

        if(validateSystemid(_mediamanager_default_filesrepoid_) && class_module_system_module::getModuleByName("mediamanager") !== null) {
            $strAction = $this->objToolkit->listButton(
                getLinkAdmin(
                    "mediamanager",
                    "folderContentFolderviewMode",
                    "&systemid=" . _mediamanager_default_filesrepoid_ . "&form_element=" . $this->getParam("form_element") . "&bit_link=1",
                    $this->getLang("wysiwygFilesBrowser"),
                    $this->getLang("wysiwygFilesBrowser"),
                    "icon_folderActionOpen"
                )
            );
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("wysiwygFilesBrowser"), "", $strAction, $intCounter++);
        }

        if(validateSystemid(_mediamanager_default_imagesrepoid_) && class_module_system_module::getModuleByName("mediamanager") !== null) {
            $strAction = $this->objToolkit->listButton(
                getLinkAdmin(
                    "mediamanager",
                    "folderContentFolderviewMode",
                    "&systemid=" . _mediamanager_default_imagesrepoid_ . "&form_element=" . $this->getParam("form_element") . "&bit_link=1",
                    $this->getLang("wysiwygImagesBrowser"),
                    $this->getLang("wysiwygImagesBrowser"),
                    "icon_folderActionOpen"
                )
            );
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("wysiwygImagesBrowser"), "", $strAction, $intCounter++);
        }

        if(class_module_system_module::getModuleByName("mediamanager") !== null) {
            $strAction = $this->objToolkit->listButton(
                getLinkAdmin(
                    "mediamanager",
                    "folderContentFolderviewMode",
                    "&form_element=" . $this->getParam("form_element") . "&bit_link=1",
                    $this->getLang("wysiwygRepoBrowser"),
                    $this->getLang("wysiwygRepoBrowser"),
                    "icon_folderActionOpen"
                )
            );
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("wysiwygRepoBrowser"), "", $strAction, $intCounter++);
        }

        $strReturn .= $this->objToolkit->listFooter();
        return $strReturn;
    }

}
