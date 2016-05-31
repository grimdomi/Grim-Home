<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: interface_module_rating_sortalgo.php 5409 2012-12-30 13:09:07Z sidler $                         *
********************************************************************************************************/

/**
 * Interface to be implemented by all rating-sort-algorithms designed to calculate the lists 
 *
 * @package module_rating
 */
interface interface_module_rating_sortalgo {

	/**
     * Sets an array of elements to be sorted.
     * Elements have to be an instance of interface_sortable_rating.
     *
     * @param array $arrElements
     * @return void
     */
    public function setElementsArray($arrElements);
    
    /**
     * Does the sorting and returns the sorted array of elements.
     *
     * @return array
     */
    public function doSorting();
		
    
}
