<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_tags_admin.php 5903 2013-09-30 13:40:29Z sidler $                                  *
********************************************************************************************************/


/**
 * Class to handle the admin-stuff of the tags-element
 *
 * @package module_tags
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class class_element_tags_admin extends class_element_admin implements interface_admin_element {


    /**
     * @var string
     * @tableColumn element_universal.char1
     *
     * @fieldType template
     * @fieldLabel template
     *
     * @fieldTemplateDir /element_tags
     */
    private $strChar1;

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
