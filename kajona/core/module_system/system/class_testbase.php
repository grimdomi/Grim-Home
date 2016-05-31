<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_testbase.php 5783 2013-08-20 14:02:11Z sidler $                                             *
********************************************************************************************************/


require_once __DIR__."../../../bootstrap.php";


/**
 * The class_testbase is the common baseclass for all testcases.
 * Triggers the methods required to run proper PHPUnit tests such as starting the system-kernel
 *
 * @package module_system
 * @since 3.4
 * @author sidler@mulchprod.de
 */
abstract class class_testbase extends PHPUnit_Framework_TestCase {

    protected function setUp() {

        //echo "\n\nlogging test-setUp on ".get_class($this)." @ ".timeToString(time())."...\n";

        if(!defined("_block_config_db_loading_")) {
            define("_block_config_db_loading_", true);
        }

        if(!defined("_autotesting_")) {
            define("_autotesting_", true);
        }

        $objCarrier = class_carrier::getInstance();

        $strSQL = "UPDATE "._dbprefix_."system_config SET system_config_value = 'true'
                    WHERE system_config_name = '_system_changehistory_enabled_'";

        $objCarrier->getObjDB()->_query($strSQL);
        $objCarrier->getObjDB()->flushQueryCache();
        class_apc_cache::getInstance()->flushCache();

        class_config::getInstance()->loadConfigsDatabase(class_db::getInstance());

        //flush garbage collection, should avoid some segfaults on php 5.3.
        gc_collect_cycles();
        gc_disable();

        parent::setUp();
    }


    protected function tearDown() {

        //reenable garbage collection
        gc_enable();

        parent::tearDown();
    }

    protected function flushDBCache() {
        class_carrier::getInstance()->getObjDB()->flushQueryCache();
    }

}


