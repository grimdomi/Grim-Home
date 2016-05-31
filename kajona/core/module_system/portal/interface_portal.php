<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: interface_portal.php 5409 2012-12-30 13:09:07Z sidler $                                         *
********************************************************************************************************/

/**
 * Interface for all portal-classes (modules)
 * Ensures, that all needed methods are being implemented
 *
 * @package module_system
 */
interface interface_portal {

    /**
     * Contstructor accepting Element-Data. Passed to the base-class
     *
     * @param mixed $arrElementData
     */
    public function __construct($arrElementData);

	/**
	 * This method is being called from the element and controls all other actions
	 * If given, the action passed in the GET-Array is being passed by param
	 *
	 * The method returns the content of the xml file, NOT the headers
	 */
	public function action();


}
