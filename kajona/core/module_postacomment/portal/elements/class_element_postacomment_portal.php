<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_postacomment_portal.php 5903 2013-09-30 13:40:29Z sidler $						        *
********************************************************************************************************/

/**
 * Portal-part of the postacomment-element
 *
 * @package module_postacomment
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class class_element_postacomment_portal extends class_element_portal implements interface_portal_element {

    /**
     * Constructor
     * @param $objElementData
     */
    public function __construct($objElementData) {
        parent::__construct($objElementData);

        //we support ratings, so add cache-busters
        $objRatingModule = class_module_system_module::getModuleByName("rating");
        if($objRatingModule != null) {
            $this->setStrCacheAddon(getCookie(class_module_rating_rate::RATING_COOKIE));
        }
    }


    /**
     * Loads the postacomment-class and passes control
     *
     * @return string
     */
    public function loadData() {
        $strReturn = "";
        //Load the data
        $objPostacommentModule = class_module_system_module::getModuleByName("postacomment");
        if($objPostacommentModule != null) {

            //action-filter set within the element?
            if(trim($this->arrElementData["char2"]) != "") {
                if($this->getParam("action") != $this->arrElementData["char2"]) {
                    return "";
                }
            }

            $objPostacomment = $objPostacommentModule->getPortalInstanceOfConcreteModule($this->arrElementData);
            $strReturn = $objPostacomment->action();
        }
        return $strReturn;
    }

}
