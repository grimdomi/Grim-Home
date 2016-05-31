<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_portalupload_admin.php 5903 2013-09-30 13:40:29Z sidler $                               *
********************************************************************************************************/

/**
 * Class to handle the admin-stuff of the portalupload-element
 *
 * @package element_portalupload
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class class_element_portalupload_admin extends class_element_admin implements interface_admin_element {

    /**
     * @var string
     * @tableColumn element_universal.char1
     *
     * @fieldType template
     * @fieldLabel template
     *
     * @fieldTemplateDir /element_portalupload
     */
    private $strChar1;

    /**
     * @var string
     * @tableColumn element_universal.char2
     *
     * @fieldType dropdown
     * @fieldLabel portalupload_download
     * @fieldMandatory
     */
    private $strChar2;

    public function getAdminForm() {

        $arrDlArchives = class_module_mediamanager_repo::getObjectList();
        $arrDlDD = array();
        if(count($arrDlArchives) > 0) {
            foreach($arrDlArchives as $objOneArchive) {
                $arrDlDD[$objOneArchive->getSystemid()] = $objOneArchive->getStrDisplayName();
            }
        }

        $objForm = parent::getAdminForm();
        $objForm->getField("char2")->setArrKeyValues($arrDlDD);
        return $objForm;
    }

    /**
     * @param string $strChar2
     */
    public function setStrChar2($strChar2) {
        $this->strChar2 = $strChar2;
    }

    /**
     * @return string
     */
    public function getStrChar2() {
        return $this->strChar2;
    }

    /**
     * @param string $strChar1
     */
    public function setStrChar1($strChar1) {
        $this->strChar1 = $strChar1;
    }

    /**
     * @return string
     */
    public function getStrChar1() {
        return $this->strChar1;
    }




}
