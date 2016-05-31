<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: interface_usersources_user.php 5418 2013-01-06 17:51:24Z sidler $                              *
********************************************************************************************************/


/**
 * Interface for all users. Defines the common methods for user-objects.
 *
 * @author sidler
 * @since 3.4.1
 * @package module_usersource
 */
interface interface_usersources_user {

    /**
     * Returns the list of group-ids the current user is assigned to
     *
     * @return array
     */
    public function getGroupIdsForUser();

    /**
     * Deletes the current user from the system - if possible
     *
     * @return bool
     */
    public function deleteUser();

    /**
     * Indicates if the current user is editable or read-only
     *
     * @return bool
     */
    public function isEditable();


    /**
     * Returns the forename
     *
     * @return string
     */
    public function getStrForename();

    /**
     * Returns the family-name
     *
     * @return string
     */
    public function getStrName();

    /**
     * Returns the email adress of the current user
     */
    public function getStrEmail();

    /**
     * Passes a new system-id to the object.
     * This id has to be used for newly created objects,
     * otherwise the mapping of kajona-users to users in the
     * subsystem may fail.
     *
     * @param string $strId
     *
     * @return void
     */
    public function setNewRecordId($strId);

    /**
     * Indicates if the current users' password may be reset, e.g. via a password-forgotten mail
     */
    public function isPasswordResettable();
}
