<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_formentry_yesno.php 5884 2013-09-29 01:21:57Z sidler $                               *
********************************************************************************************************/

/**
 * A yes-no field renders a dropdown containing one entry for yes and one for no.
 * 0 is no whereas 1 is rendered as yes.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_formgenerator
 */
class class_formentry_yesno extends class_formentry_base implements interface_formentry_printable {

    public function __construct($strFormName, $strSourceProperty, $objSourceObject) {
        parent::__construct($strFormName, $strSourceProperty, $objSourceObject);

        //set the default validator
        $this->setObjValidator(new class_text_validator());
   }

    /**
     * Renders the field itself.
     * In most cases, based on the current toolkit.
     *
     * @return string
     */
    public function renderField() {
        $objToolkit = class_carrier::getInstance()->getObjToolkit("admin");
        $objLang = class_carrier::getInstance()->getObjLang();
        $arrYesNo = array(
            0 => $objLang->getLang("commons_no", "system"), 1 => $objLang->getLang("commons_yes", "system")
        );
        $strReturn = "";
        if($this->getStrHint() != null)
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());
        $strReturn .=  $objToolkit->formInputDropdown($this->getStrEntryName(), $arrYesNo, $this->getStrLabel(), $this->getStrValue());
        return $strReturn;
    }

    /**
     * Returns a textual representation of the formentries' value.
     * May contain html, but should be stripped down to text-only.
     *
     * @return string
     */
    public function getValueAsText() {
        if($this->getStrValue())
            return class_carrier::getInstance()->getObjLang()->getLang("commons_yes", "system");
        else
            return class_carrier::getInstance()->getObjLang()->getLang("commons_no", "system");
    }

}
