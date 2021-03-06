<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * tests for PhpMyAdmin\CheckUserPrivileges
 *
 * @package PhpMyAdmin-test
 */
namespace PhpMyAdmin\Tests;

use PhpMyAdmin\CheckUserPrivileges;
use PHPUnit_Framework_TestCase as TestCase;

/*
 * Include to test.
 */
$GLOBALS['server'] = 1;
$GLOBALS['cfg']['Server']['DisableIS'] = false;

/**
 * tests for PhpMyAdmin\CheckUserPrivileges
 *
 * @package PhpMyAdmin-test
 */
class CheckUserPrivilegesTest extends TestCase
{
    /**
     * prepares environment for tests
     *
     * @return void
     */
    public function setUp()
    {
        $GLOBALS['col_priv'] = false;
        $GLOBALS['db_priv'] = false;
        $GLOBALS['proc_priv'] = false;
        $GLOBALS['table_priv'] = false;
        $GLOBALS['is_reload_priv'] = false;
    }


    public function testGetItemsFromShowGrantsRow()
    {
        // TEST CASE 1
        $show_grants_full_row = "GRANT ALL PRIVILEGES ON *.* "
            . "TO 'root'@'localhost' WITH GRANT OPTION";

        list(
            $show_grants_str,
            $show_grants_dbname,
            $show_grants_tblname
        ) = CheckUserPrivileges::getItemsFromShowGrantsRow(
            $show_grants_full_row
        );

        $this->assertEquals(
            "ALL PRIVILEGES",
            $show_grants_str
        );

        $this->assertEquals(
            "*",
            $show_grants_dbname
        );

        $this->assertEquals(
            "*",
            $show_grants_tblname
        );

        // TEST CASE 2
        $show_grants_full_row = "GRANT ALL PRIVILEGES ON `mysql`.* TO "
            . "'root'@'localhost' WITH GRANT OPTION";

        list(
            $show_grants_str,
            $show_grants_dbname,
            $show_grants_tblname
        ) = CheckUserPrivileges::getItemsFromShowGrantsRow(
            $show_grants_full_row
        );

        $this->assertEquals(
            "ALL PRIVILEGES",
            $show_grants_str
        );

        $this->assertEquals(
            "mysql",
            $show_grants_dbname
        );

        $this->assertEquals(
            "*",
            $show_grants_tblname
        );

        // TEST CASE 3
        $show_grants_full_row = "GRANT SELECT, INSERT, UPDATE, DELETE "
            . "ON `mysql`.`columns_priv` TO 'root'@'localhost'";

        list(
            $show_grants_str,
            $show_grants_dbname,
            $show_grants_tblname
        ) = CheckUserPrivileges::getItemsFromShowGrantsRow(
            $show_grants_full_row
        );

        $this->assertEquals(
            "SELECT, INSERT, UPDATE, DELETE",
            $show_grants_str
        );

        $this->assertEquals(
            "mysql",
            $show_grants_dbname
        );

        $this->assertEquals(
            "columns_priv",
            $show_grants_tblname
        );

    }


    /**
     * Test for CheckUserPrivileges::checkRequiredPrivilegesForAdjust
     *
     * @return void
     */
    public function testCheckRequiredPrivilegesForAdjust()
    {
        // TEST CASE 1
        $show_grants_full_row = "GRANT ALL PRIVILEGES ON *.* "
            . "TO 'root'@'localhost' WITH GRANT OPTION";
        list(
            $show_grants_str,
            $show_grants_dbname,
            $show_grants_tblname
        ) = CheckUserPrivileges::getItemsFromShowGrantsRow(
            $show_grants_full_row
        );

        // call the to-be-tested function
        CheckUserPrivileges::checkRequiredPrivilegesForAdjust(
            $show_grants_str,
            $show_grants_dbname,
            $show_grants_tblname
        );

        $this->assertEquals(
            true,
            $GLOBALS['col_priv']
        );

        $this->assertEquals(
            true,
            $GLOBALS['db_priv']
        );

        $this->assertEquals(
            true,
            $GLOBALS['proc_priv']
        );

        $this->assertEquals(
            true,
            $GLOBALS['table_priv']
        );

        // re-initialise the privileges
        $this->setUp();

        // TEST CASE 2
        $show_grants_full_row = "GRANT ALL PRIVILEGES ON `mysql`.* TO "
            . "'root'@'localhost' WITH GRANT OPTION";
        list(
            $show_grants_str,
            $show_grants_dbname,
            $show_grants_tblname
        ) = CheckUserPrivileges::getItemsFromShowGrantsRow(
            $show_grants_full_row
        );

        // call the to-be-tested function
        CheckUserPrivileges::checkRequiredPrivilegesForAdjust(
            $show_grants_str,
            $show_grants_dbname,
            $show_grants_tblname
        );

        $this->assertEquals(
            true,
            $GLOBALS['col_priv']
        );

        $this->assertEquals(
            true,
            $GLOBALS['db_priv']
        );

        $this->assertEquals(
            true,
            $GLOBALS['proc_priv']
        );

        $this->assertEquals(
            true,
            $GLOBALS['table_priv']
        );

        // re-initialise the privileges
        $this->setUp();

        // TEST CASE 3
        $show_grants_full_row = "GRANT SELECT, INSERT, UPDATE, DELETE ON "
            . "`mysql`.* TO 'root'@'localhost'";
        list(
            $show_grants_str,
            $show_grants_dbname,
            $show_grants_tblname
        ) = CheckUserPrivileges::getItemsFromShowGrantsRow(
            $show_grants_full_row
        );

        // call the to-be-tested function
        CheckUserPrivileges::checkRequiredPrivilegesForAdjust(
            $show_grants_str,
            $show_grants_dbname,
            $show_grants_tblname
        );

        $this->assertEquals(
            true,
            $GLOBALS['col_priv']
        );

        $this->assertEquals(
            true,
            $GLOBALS['db_priv']
        );

        $this->assertEquals(
            true,
            $GLOBALS['proc_priv']
        );

        $this->assertEquals(
            true,
            $GLOBALS['table_priv']
        );

        // re-initialise the privileges
        $this->setUp();

        // TEST CASE 4
        $show_grants_full_row = "GRANT SELECT, INSERT, UPDATE, DELETE ON "
            . "`mysql`.`db` TO 'root'@'localhost'";
        list(
            $show_grants_str,
            $show_grants_dbname,
            $show_grants_tblname
        ) = CheckUserPrivileges::getItemsFromShowGrantsRow(
            $show_grants_full_row
        );

        // call the to-be-tested function
        CheckUserPrivileges::checkRequiredPrivilegesForAdjust(
            $show_grants_str,
            $show_grants_dbname,
            $show_grants_tblname
        );

        $this->assertEquals(
            false,
            $GLOBALS['col_priv']
        );

        $this->assertEquals(
            true,
            $GLOBALS['db_priv']
        );

        $this->assertEquals(
            false,
            $GLOBALS['proc_priv']
        );

        $this->assertEquals(
            false,
            $GLOBALS['table_priv']
        );
    }
}
