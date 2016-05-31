<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: interface_module_rating_algo.php 5409 2012-12-30 13:09:07Z sidler $                             *
********************************************************************************************************/

/**
 * Interface to be implemented by all rating-algorithms designed to calculate ratings
 *
 * @package module_rating
 */
interface interface_module_rating_algo {

    /**
     * Calculates the new rating
     *
     * @param class_module_rating_rate $objSourceRate  The rating-record to update
     * @param float                   $floatNewRating The rating fired by the user
     *
     * @return float the new rating
     */
    public function doRating(class_module_rating_rate $objSourceRate, $floatNewRating);

}
