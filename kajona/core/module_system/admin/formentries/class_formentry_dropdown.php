<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_formentry_dropdown.php 5937 2013-10-11 13:39:45Z sidler $                               *
********************************************************************************************************/

/**
 * A yes-no field renders a dropdown containing a list of entries.
 * Make sure to pass the list of possible entries before rendering the form.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_formgenerator
 */
class class_formentry_dropdown extends class_formentry_base implements interface_formentry_printable {

    /**
     * a list of [key=>value],[key=>value] pairs, resolved from the language-files
     */
    const STR_DDVALUES_ANNOTATION = "@fieldDDValues";


    private $arrKeyValues = array();
    private $strAddons = "";

    public function __construct($strFormName, $strSourceProperty, $objSourceObject = null) {
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
        $strReturn = "";
        if($this->getStrHint() != null)
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());
        $strReturn .=  $objToolkit->formInputDropdown($this->getStrEntryName(), $this->arrKeyValues, $this->getStrLabel(), $this->getStrValue(), "", !$this->getBitReadonly(), $this->getStrAddons());
        return $strReturn;
    }

    /**
     * Overwritten in order to load key-value pairs declared by annotations
     */
    protected function updateValue() {
        parent::updateValue();

        if($this->getObjSourceObject() != null && $this->getStrSourceProperty() != "") {
            $objReflection = new class_reflection($this->getObjSourceObject());

            //try to find the matching source property
            $arrProperties = $objReflection->getPropertiesWithAnnotation(self::STR_DDVALUES_ANNOTATION);
            $strSourceProperty = null;
            foreach($arrProperties as $strPropertyName => $strValue) {
                if(uniSubstr(uniStrtolower($strPropertyName), (uniStrlen($this->getStrSourceProperty()))*-1) == $this->getStrSourceProperty())
                    $strSourceProperty = $strPropertyName;
            }

            if($strSourceProperty == null)
                return;

            $strDDValues = $objReflection->getAnnotationValueForProperty($strSourceProperty, self::STR_DDVALUES_ANNOTATION);
            if($strDDValues !== null && $strDDValues != "") {
                $arrDDValues = array();
                foreach(explode(",", $strDDValues) as $strOneKeyVal) {
                    $strOneKeyVal = uniSubstr(trim($strOneKeyVal), 1, -1);
                    $arrOneKeyValue = explode("=>", $strOneKeyVal);

                    $strKey = trim($arrOneKeyValue[0]) == "" ? " " : trim($arrOneKeyValue[0]);
                    if(count($arrOneKeyValue) == 2) {
                        $strValue = class_carrier::getInstance()->getObjLang()->getLang(trim($arrOneKeyValue[1]), $this->getObjSourceObject()->getArrModule("modul"));
                        if($strValue == "!".trim($arrOneKeyValue[1])."!")
                            $strValue = $arrOneKeyValue[1];
                        $arrDDValues[$strKey] = $strValue;
                    }
                }
                $this->setArrKeyValues($arrDDValues);
            }
        }
    }


    /**
     * Returns a textual representation of the formentries' value.
     * May contain html, but should be stripped down to text-only.
     *
     * @return string
     */
    public function getValueAsText() {
        return isset($this->arrKeyValues[$this->getStrValue()]) ? $this->arrKeyValues[$this->getStrValue()] : "";
    }

    /**
     * @param $arrKeyValues
     * @return class_formentry_dropdown
     */
    public function setArrKeyValues($arrKeyValues) {
        $this->arrKeyValues = $arrKeyValues;
        return $this;
    }

    public function getArrKeyValues() {
        return $this->arrKeyValues;
    }

    /**
     * @param string $strAddons
     * @return $this
     */
    public function setStrAddons($strAddons) {
        $this->strAddons = $strAddons;
        return $this;
    }

    /**
     * @return string
     */
    public function getStrAddons() {
        return $this->strAddons;
    }



}
