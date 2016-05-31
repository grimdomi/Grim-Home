<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_formentry_divider.php 5695 2013-07-04 13:27:37Z sidler $                               *
********************************************************************************************************/

/**
 * A hidden field
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_formgenerator
 */
class class_formentry_divider extends class_formentry_base implements interface_formentry {

    public function __construct() {
        parent::__construct("", generateSystemid());

        //set the default validator
        $this->setObjValidator(new class_dummy_validator());
    }

    /**
     * Renders the field itself.
     * In most cases, based on the current toolkit.
     *
     * @return string
     */
    public function renderField() {
        $objToolkit = class_carrier::getInstance()->getObjToolkit("admin");
        return $objToolkit->divider();
    }

    public function updateLabel($strKey = "") {
        return "";
    }

    public function setValueToObject() {
        return true;
    }


}
