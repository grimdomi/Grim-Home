<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_eventmanager_portal.php 5903 2013-09-30 13:40:29Z sidler $						               	*
********************************************************************************************************/

/**
 * Portal-part of the eventmanager-element
 *
 * @package module_eventmanager
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class class_element_eventmanager_portal extends class_element_portal implements interface_portal_element {


    /**
     * Loads the eventmanager-class and passes control
     *
     * @return string
     */
    public function loadData() {
        $strReturn = "";
        //Load the data
        $objEventmanagerModule = class_module_system_module::getModuleByName("eventmanager");
        if($objEventmanagerModule != null) {
            $objEventmanager = $objEventmanagerModule->getPortalInstanceOfConcreteModule($this->arrElementData);
            $strReturn = $objEventmanager->action();
        }
        return $strReturn;
    }

}
