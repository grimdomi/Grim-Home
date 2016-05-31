<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: interface_statuschanged_listener.php 5409 2012-12-30 13:09:07Z sidler $                               *
********************************************************************************************************/


/**
 * Defines all methods a status-changed-listener should implement in order to react on those events
 *
 * @author sidler@mulchprod.de
 * @package module_system
 * @since 4.0
 */
interface interface_statuschanged_listener {

    /**
     * Called whenever a records' status was changed by the model.
     * Implement this method to be notified when a status is changed.
     * There's no need to register the listener, this is done automatically.
     *
     * Make sure to return a matching boolean-value, otherwise the transaction may be rolled back.
     *
     * @abstract
     * @param $strSystemid
     * @param $intNewStatus
     *
     * @return bool
     */
    public function handleStatusChangedEvent($strSystemid, $intNewStatus);

}
