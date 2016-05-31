<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_formentry_headline.php 5831 2013-09-17 15:43:24Z sidler $                               *
********************************************************************************************************/

/**
 * A simple headline-element
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_formgenerator
 */
class class_formentry_headline extends class_formentry_base implements interface_formentry_printable {

    public function __construct($strName = "") {
        if($strName == "")
            $strName = generateSystemid();
        parent::__construct("", $strName);

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
        return $objToolkit->formHeadline($this->getStrValue());
    }

    public function updateLabel($strKey = "") {
        return "";
    }

    /**
     * Returns a textual representation of the formentries' value.
     * May contain html, but should be stripped down to text-only.
     *
     * @return string
     */
    public function getValueAsText() {
        return class_carrier::getInstance()->getObjToolkit("admin")->formHeadline($this->getStrValue());
    }


}
