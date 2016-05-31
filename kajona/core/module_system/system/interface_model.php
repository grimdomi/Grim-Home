<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: interface_model.php 5924 2013-10-05 18:08:38Z sidler $                                          *
********************************************************************************************************/

/**
 * Interface for all model-classes
 *
 * @package module_system
 */
interface interface_model {

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     * @abstract
     * @return string
     */
    public function getStrDisplayName();




}
