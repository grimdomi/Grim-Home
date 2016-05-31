<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_scriptlet_searchhighlight.php 5733 2013-07-12 15:21:12Z sidler $                               *
********************************************************************************************************/

/**
 * Replaces searched words with a highlighted background.
 * Calls the relevant script entries.
 *
 *
 * @package module_search
 * @since 4.0
 * @author sidler@mulchprod.de
 */
class class_scriptlet_searchhighlight implements interface_scriptlet {

    /**
     * Processes the content.
     * Make sure to return the string again, otherwise the output will remain blank.
     *
     * @param string $strContent
     *
     * @return string
     */
    public function processContent($strContent) {

        $strHighlight = trim(class_carrier::getInstance()->getParam("highlight"));
        if($strHighlight != "") {
            $strHighlight = strip_tags($strHighlight);
            $strJS = <<<JS
KAJONA.portal.loader.loadFile('/templates/default/js/jquery.highlight.js', function() { $("body div[class='contentRight']").highlight("{$strHighlight}"); });
JS;

            $strJS = "<script type=\"text/javascript\">".$strJS."</script>\n";

            $intBodyClose = uniStripos($strContent, "</body>");
            if($intBodyClose !== false)
                $strContent = uniSubstr($strContent, 0, $intBodyClose).$strJS.uniSubstr($strContent, $intBodyClose);

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
        return interface_scriptlet::BIT_CONTEXT_PORTAL_PAGE;
    }

}
