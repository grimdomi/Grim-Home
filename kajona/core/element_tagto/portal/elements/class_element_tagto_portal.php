<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_tagto_portal.php 5903 2013-09-30 13:40:29Z sidler $                                     *
********************************************************************************************************/

/**
 * Loads the last-modified date of the current page and prepares it for output
 *
 * @package element_tagto
 * @author sidler@mulchprod.de
 * @targetTable element_universal.content_id
 */
class class_element_tagto_portal extends class_element_portal implements interface_portal_element {


    /**
     * Looks up the last modified-date of the current page
     *
     * @return string the prepared html-output
     */
    public function loadData() {
        //actions or systemids passed? pagename?
        $strSystemid = $this->getParam("systemid");
        $strActions = $this->getParam("action");
        $strPagenme = $this->getPagename();

        //load the template
        $strTemplateID = $this->objTemplate->readTemplate("/element_tagto/".$this->arrElementData["char1"], "tagtos");
        $strLink = getLinkPortalHref($strPagenme, "", $strActions, "", $strSystemid);
        $strReturn = $this->fillTemplate(array("pageurl" => $strLink), $strTemplateID);

        return $strReturn;
    }

}
