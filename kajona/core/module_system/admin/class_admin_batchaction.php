<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_admin_batchaction.php 5409 2012-12-30 13:09:07Z sidler $	                                            *
********************************************************************************************************/


/**
 * A massaction is a single, descriptive object to be rendered by the admin-toolkit.
 * Each action may be called iterative for a set of systemid.
 * The action is triggered by an ajax-request, the target-url is specified by the respective property.
 * The target-url should provide a %systemid% element, being replaced before the triggering of the request.
 *
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_system
 */
class class_admin_batchaction {
    private $strIcon;
    private $strTitle;
    private $strTargetUrl;

    function __construct($strIcon, $strTargetUrl, $strTitle) {
        $this->strIcon = $strIcon;
        $this->strTargetUrl = $strTargetUrl;
        $this->strTitle = $strTitle;
    }

    public function setStrIcon($strIcon) {
        $this->strIcon = $strIcon;
    }

    public function getStrIcon() {
        return $this->strIcon;
    }

    public function setStrTargetUrl($strTargetUrl) {
        $this->strTargetUrl = $strTargetUrl;
    }

    public function getStrTargetUrl() {
        return $this->strTargetUrl;
    }

    public function setStrTitle($strTitle) {
        $this->strTitle = $strTitle;
    }

    public function getStrTitle() {
        return $this->strTitle;
    }



}
