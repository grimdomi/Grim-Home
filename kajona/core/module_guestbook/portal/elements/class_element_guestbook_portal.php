<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_guestbook_portal.php 5903 2013-09-30 13:40:29Z sidler $                              *
********************************************************************************************************/

/**
 * Portal-part of the guestbook-element
 *
 * @package module_guestbook
 * @author sidler@mulchprod.de
 * @targetTable element_guestbook.content_id
 */
class class_element_guestbook_portal extends class_element_portal implements interface_portal_element {

    /**
     * Contructor
     *
     * @param $objElementData
     */
    public function __construct($objElementData) {
        parent::__construct($objElementData);

        if($this->getParam("action") == "saveGuestbook") {
            $this->setStrCacheAddon(generateSystemid());
        }
    }

    /**
     * Loads the guestbook-class and passes control
     *
     * @return string
     */
    public function loadData() {
        $strReturn = "";

        $objGBModule = class_module_system_module::getModuleByName("guestbook");
        if($objGBModule != null) {
            $objGuestbook = $objGBModule->getPortalInstanceOfConcreteModule($this->arrElementData);
            $strReturn = $objGuestbook->action();
        }

        return $strReturn;
    }

}
