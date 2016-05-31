<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_scriptlet_qrcode.php 5528 2013-02-20 15:39:52Z sidler $                               *
********************************************************************************************************/

/**
 * The qrcode scriptlet generates a qrcode based on the passed url.
 * The second, optional param is a size-value, ranging from 1 to 3
 * The syntax is
 *  [qrcode,url(,size)]
 *
 *
 * @package module_qrcode
 * @since 4.0
 * @author sidler@mulchprod.de
 */
class class_scriptlet_qrcode implements interface_scriptlet {

    /**
     * Processes the content.
     * Make sure to return the string again, otherwise the output will remain blank.
     *
     * @param string $strContent
     *
     * @return string
     */
    public function processContent($strContent) {

        $arrTemp = array();
        preg_match_all("#\[qrcode,([ \?\&\-=:+%;A-Za-z0-9_\./\\\]+)(,[1-3])\]#i", $strContent, $arrTemp);

        foreach($arrTemp[0] as $intKey => $strSearchString) {

            $intSize = 1;
            $strSubstr = isset($arrTemp[2][$intKey]) ? (int)substr($arrTemp[2][$intKey], 1) : 1;
            if($strSubstr >= 1 && $strSubstr <=3 ) {
                $intSize = $strSubstr;
            }

            $objQrCode = new class_qrcode();
            $objQrCode->setIntSize($intSize);
            $strImage = $objQrCode->getImageForString($arrTemp[1][$intKey]);

            $strContent = uniStrReplace(
                $strSearchString,
                _webpath_."/".$strImage,
                $strContent
            );

        }

        return $strContent;
    }

    /**
     * Define the context the scriptlet is applied to.
     * A combination of contexts is allowed using an or-concatenation.
     * Examples:
     *   return interface_scriptlet::BIT_CONTEXT_ADMIN
     *   return interface_scriptlet::BIT_CONTEXT_ADMIN | BIT_CONTEXT_ADMIN::BIT_CONTEXT_PORTAL_ELEMENT
     *
     * @return mixed
     */
    public function getProcessingContext() {
        return interface_scriptlet::BIT_CONTEXT_PORTAL_ELEMENT;
    }

}
