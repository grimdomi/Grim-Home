<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_adminwidget.php 5571 2013-04-04 15:10:29Z sidler $	                                    *
********************************************************************************************************/

/**
 * Base class to be extended by all adminwidgets.
 * Holds a few methods to create a framework-like behaviour
 *
 * @package module_dashboard
 * @author sidler@mulchprod.de
 */
abstract class class_adminwidget {

    private $arrFields = array();
    private $arrPersistenceKeys = array();
    private $strSystemid = "";

    /**
     * instance of class_db
     *
     * @var class_db
     */
    private $objDb;

    /**
     * instance of class_toolkit
     *
     * @var class_toolkit_admin
     */
    protected $objToolkit;

    /**
     * instance of class_lang
     *
     * @var class_lang
     */
    private $objLang;


    private $bitBlockSessionClose = false;

    public function __construct() {

        $this->objDb = class_carrier::getInstance()->getObjDB();
        $this->objToolkit = class_carrier::getInstance()->getObjToolkit("admin");
        $this->objLang = class_carrier::getInstance()->getObjLang();

    }

    /**
     * Use this method to tell the widgets whicht keys of the $arrFields should
     * be loaded from and be persitsted to the database
     *
     * @param array $arrKeys
     */
    protected final function setPersistenceKeys($arrKeys) {
        $this->arrPersistenceKeys = $arrKeys;
    }

    /**
     * This method invokes the rendering of the widget. Calls
     * the implementing class.
     *
     * @return string
     */
    public final function generateWidgetOutput() {
        return $this->getWidgetOutput();
    }

    /**
     * Overwrite this method!
     *
     * @return string
     * @see interface_adminwidget::getWidgetOutput()
     */
    public function getWidgetOutput() {
        return "";
    }

    /**
     * Returns the current fields as a serialized array.
     *
     * @return string
     */
    public final function getFieldsAsString() {
        $arrFieldsToPersist = array();
        foreach($this->arrPersistenceKeys as $strOneKey) {
            $arrFieldsToPersist[$strOneKey] = $this->getFieldValue($strOneKey);
        }

        $strArraySerialized = serialize($arrFieldsToPersist);
        return $strArraySerialized;
    }

    /**
     * Takes the current fields serialized and retransforms the contents
     *
     * @param string $strContent
     */
    public final function setFieldsAsString($strContent) {
        $arrFieldsToLoad = unserialize(stripslashes($strContent));
        foreach($this->arrPersistenceKeys as $strOneKey) {
            if(isset($arrFieldsToLoad[$strOneKey])) {
                $this->setFieldValue($strOneKey, $arrFieldsToLoad[$strOneKey]);
            }
        }
    }

    /**
     * Pass an array of values. The method looks for fields to be loaded into
     * the internal arrays.
     *
     * @param array $arrFields
     */
    public final function loadFieldsFromArray($arrFields) {
        foreach($this->arrPersistenceKeys as $strOneKey) {
            if(isset($arrFields[$strOneKey])) {
                $this->setFieldValue($strOneKey, $arrFields[$strOneKey]);
            }
            else {
                $this->setFieldValue($strOneKey, "");
            }
        }
    }

    /**
     * Loads a text-fragement from the textfiles
     *
     * @param string $strKey
     *
     * @return string
     */
    public final function getLang($strKey) {
        return $this->objLang->getLang($strKey, "adminwidget");
    }

    /**
     * Looks up a value in the fields-array
     *
     * @param string $strFieldName
     *
     * @return mixed
     */
    protected final function getFieldValue($strFieldName) {
        if(isset($this->arrFields[$strFieldName])) {
            return $this->arrFields[$strFieldName];
        }
        else {
            return "";
        }
    }

    /**
     * Sets the value of a given field
     *
     * @param string $strFieldName
     * @param mixed $mixedValue
     */
    protected final function setFieldValue($strFieldName, $mixedValue) {
        $this->arrFields[$strFieldName] = $mixedValue;
    }

    /**
     * Sets the systemid of the current widget
     *
     * @param string $strSystemid
     */
    public final function setSystemid($strSystemid) {
        $this->strSystemid = $strSystemid;
    }

    /**
     * Returns the systemid of the current widget
     *
     * @return string
     */
    public final function getSystemid() {
        return $this->strSystemid;
    }

    /**
     * This method controls the elements-section used by the toolkit to render
     * the outer parts of the widget.
     * Overwrite this method in cases you need some special layouting - in most cases this shouldn't be
     * necessary.
     *
     * @return string
     */
    public function getLayoutSection() {
        return "adminwidget_widget";
    }

    //--- Layout/Content functions --------------------------------------------------------------------------

    /**
     * Use this method to place a formatted text in the widget
     *
     * @param string $strText
     *
     * @return string
     */
    protected final function widgetText($strText) {
        return $this->objToolkit->adminwidgetText($strText);
    }

    /**
     * Use this method to generate a separator / divider to split up
     * the widget in logical sections.
     *
     * @return string
     */
    protected final function widgetSeparator() {
        return $this->objToolkit->adminwidgetSeparator();
    }

    public function setBitBlockSessionClose($bitBlockSessionClose) {
        $this->bitBlockSessionClose = $bitBlockSessionClose;
    }

    public function getBitBlockSessionClose() {
        return $this->bitBlockSessionClose;
    }

}


