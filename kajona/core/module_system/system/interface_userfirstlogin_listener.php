<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: interface_userfirstlogin_listener.php 5409 2012-12-30 13:09:07Z sidler $                               *
********************************************************************************************************/


/**
 * Interface to be implemented by listeners on users' first logins
 *
 * @author sidler@mulchprod.de
 * @package module_system
 * @since 4.0
 */
interface interface_userfirstlogin_listener {

    /**
     *
     * Callback method, triggered each time a user logs into the system for the very first time.
     * May be used to trigger actions or initial setups for the user.
     *
     * @param $strUserid
     *
     * @return bool
     */
    public function handleUserFirstLoginEvent($strUserid);

}
