<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: class_element_paragraph_admin.php 5891 2013-09-29 11:19:10Z sidler $                           *
********************************************************************************************************/

/**
 * Admin class to handle the paragraphs
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 *
 * @targetTable element_paragraph.content_id
 */
class class_element_paragraph_admin extends class_element_admin implements interface_admin_element {

    /**
     * @var string
     * @tableColumn element_paragraph.paragraph_title
     *
     * @fieldType text
     * @fieldLabel commons_title
     *
     * @elementContentTitle
     */
    private $strTitle = "";

    /**
     * @var string
     * @tableColumn element_paragraph.paragraph_content
     * @blockEscaping
     *
     * @fieldType wysiwyg
     * @fieldLabel paragraph_content
     *
     * @elementContentTitle
     */
    private $strTextContent = "";

    /**
     * @var string
     * @tableColumn element_paragraph.paragraph_link
     *
     * @fieldType page
     * @fieldLabel paragraph_link
     *
     * @elementContentTitle
     */
    private $strLink = "";

    /**
     * @var string
     * @tableColumn element_paragraph.paragraph_image
     *
     * @fieldType image
     * @fieldLabel commons_image
     *
     * @elementContentTitle
     */
    private $strImage = "";

    /**
     * @var string
     * @tableColumn element_paragraph.paragraph_template
     *
     * @fieldType template
     * @fieldLabel template
     * @fieldTemplateDir /element_paragraph
     *
     * @elementContentTitle
     */
    private $strTemplate = "";


    /**
     * Returns an abstract of the current element
     *
     * @return string
     */
    public function getContentTitle() {
        $this->loadElementData();

        if($this->getStrTitle() != "") {
            return htmlStripTags($this->getStrTitle());
        }
        else if($this->getStrTextContent() != "") {
            return uniStrTrim(htmlStripTags($this->getStrTextContent()), 120);
        }
        else
            return parent::getContentTitle();
    }



    /**
     * @param string $strContent
     */
    public function setStrTextContent($strContent) {
        $this->strTextContent = $strContent;
    }

    /**
     * @return string
     */
    public function getStrTextContent() {
        return $this->strTextContent;
    }

    /**
     * @param string $strImage
     */
    public function setStrImage($strImage) {
        $this->strImage = $strImage;
    }

    /**
     * @return string
     */
    public function getStrImage() {
        return $this->strImage;
    }

    /**
     * @param string $strLink
     */
    public function setStrLink($strLink) {
        $this->strLink = $strLink;
    }

    /**
     * @return string
     */
    public function getStrLink() {
        return $this->strLink;
    }

    /**
     * @param string $strTemplate
     */
    public function setStrTemplate($strTemplate) {
        $this->strTemplate = $strTemplate;
    }

    /**
     * @return string
     */
    public function getStrTemplate() {
        return $this->strTemplate;
    }

    /**
     * @param string $strTitle
     */
    public function setStrTitle($strTitle) {
        $this->strTitle = $strTitle;
    }

    /**
     * @return string
     */
    public function getStrTitle() {
        return $this->strTitle;
    }


}
