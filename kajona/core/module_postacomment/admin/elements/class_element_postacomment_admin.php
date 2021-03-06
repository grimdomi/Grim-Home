<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_postacomment_admin.php 5903 2013-09-30 13:40:29Z sidler $                              *
********************************************************************************************************/


/**
 * Class representing the admin-part of the postacomment element
 *
 * @package module_postacomment
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class class_element_postacomment_admin extends class_element_admin implements interface_admin_element {


    /**
     * @var string
     * @tableColumn element_universal.char1
     *
     * @fieldType template
     * @fieldLabel template
     *
     * @fieldTemplateDir /module_postacomment
     */
    private $strChar1;

    /**
     * @var string
     * @tableColumn element_universal.char2
     *
     * @fieldType text
     * @fieldLabel postacomment_actionfilter
     */
    private $strChar2 = "";

    /**
     * @var int
     * @tableColumn element_universal.int1
     *
     * @fieldType text
     * @fieldLabel postacomment_numberofposts
     */
    private $intInt1;

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

    /**
     * @param int $intInt1
     */
    public function setIntInt1($intInt1) {
        $this->intInt1 = $intInt1;
    }

    /**
     * @return int
     */
    public function getIntInt1() {
        return $this->intInt1;
    }







}
