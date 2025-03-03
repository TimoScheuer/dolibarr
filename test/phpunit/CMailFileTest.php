<?php
/* Copyright (C) 2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2023 Alexandre Janniaux   <alexandre.janniaux@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 * or see https://www.gnu.org/
 */

/**
 *      \file       test/phpunit/CMailFileTest.php
 *      \ingroup    test
 *      \brief      PHPUnit test
 *      \remarks    To run this script as CLI:  phpunit filename.php
 */

global $conf,$user,$langs,$db;
//define('TEST_DB_FORCE_TYPE','mysql');	// This is to force using mysql driver
//require_once 'PHPUnit/Autoload.php';
require_once dirname(__FILE__).'/../../htdocs/master.inc.php';
require_once dirname(__FILE__).'/../../htdocs/core/class/CMailFile.class.php';

if (empty($user->id)) {
	print "Load permissions for admin user nb 1\n";
	$user->fetch(1);
	$user->getrights();
}
$conf->global->MAIN_DISABLE_ALL_MAILS=1;


/**
 * Class for PHPUnit tests
 *
 * @backupGlobals disabled
 * @backupStaticAttributes enabled
 * @remarks	backupGlobals must be disabled to have db,conf,user and lang not erased.
 */
class CMailFileTest extends PHPUnit\Framework\TestCase
{
	protected $savconf;
	protected $savuser;
	protected $savlangs;
	protected $savdb;

	/**
	 * Constructor
	 * We save global variables into local variables
	 *
	 * @return CMailFile
	 */
	public function __construct($name = '')
	{
		parent::__construct($name);

		//$this->sharedFixture
		global $conf,$user,$langs,$db;
		$this->savconf=$conf;
		$this->savuser=$user;
		$this->savlangs=$langs;
		$this->savdb=$db;

		print __METHOD__." db->type=".$db->type." user->id=".$user->id;
		//print " - db ".$db->db;
		print "\n";
	}

	/**
	 * setUpBeforeClass
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void
	{
		global $conf,$user,$langs,$db;
		$db->begin(); // This is to have all actions inside a transaction even if test launched without suite.

		print __METHOD__."\n";
	}

	/**
	 * tearDownAfterClass
	 *
	 * @return	void
	 */
	public static function tearDownAfterClass(): void
	{
		global $conf,$user,$langs,$db;
		$db->rollback();

		print __METHOD__."\n";
	}

	/**
	 * Init phpunit tests
	 *
	 * @return  void
	 */
	protected function setUp(): void
	{
		global $conf,$user,$langs,$db;
		$conf=$this->savconf;
		$user=$this->savuser;
		$langs=$this->savlangs;
		$db=$this->savdb;

		$conf->global->MAIN_DISABLE_ALL_MAILS=1;    // If I comment/remove this lien, unit test still works alone but failed when ran from AllTest. Don't know why.

		print __METHOD__."\n";
	}
	/**
	 * End phpunit tests
	 *
	 * @return	void
	 */
	protected function tearDown(): void
	{
		print __METHOD__."\n";
	}

	/**
	 * testCMailFileText
	 *
	 * @return void
	 */
	public function testCMailFileText()
	{
		global $conf,$user,$langs,$db;
		$conf=$this->savconf;
		$user=$this->savuser;
		$langs=$this->savlangs;
		$db=$this->savdb;

		$localobject=new CMailFile('Test', 'test@test.com', 'from@from.com', 'Message txt', array(), array(), array(), '', '', 1, 0);

		$result=$localobject->sendfile();
		print __METHOD__." result=".$result."\n";
		$this->assertFalse($result);   // False because mail send disabled

		return $result;
	}

	/**
	 * testCMailFileStatic
	 *
	 * @return string
	 */
	public function testCMailFileStatic()
	{
		global $conf,$user,$langs,$db;
		$conf=$this->savconf;
		$user=$this->savuser;
		$langs=$this->savlangs;
		$db=$this->savdb;

		$localobject=new CMailFile('', '', '', '');

		$src='John Doe <john@doe.com>';
		$result=$localobject->getValidAddress($src, 0);
		print __METHOD__." result=".$result."\n";
		$this->assertEquals($result, 'John Doe <john@doe.com>');

		$src='John Doe <john@doe.com>';
		$result=$localobject->getValidAddress($src, 1);
		print __METHOD__." result=".$result."\n";
		$this->assertEquals($result, '<john@doe.com>');

		$src='John Doe <john@doe.com>';
		$result=$localobject->getValidAddress($src, 2);
		print __METHOD__." result=".$result."\n";
		$this->assertEquals($result, 'john@doe.com');

		$src='John Doe <john@doe.com>';
		$result=$localobject->getValidAddress($src, 3, 0);
		print __METHOD__." result=".$result."\n";
		$this->assertEquals($result, '"John Doe" <john@doe.com>');

		$src='John Doe <john@doe.com>';
		$result=$localobject->getValidAddress($src, 3, 1);
		print __METHOD__." result=".$result."\n";
		$this->assertEquals($result, '"=?UTF-8?B?Sm9obiBEb2U=?=" <john@doe.com>');

		$src='John Doe <john@doe.com>';
		$result=$localobject->getValidAddress($src, 4);
		print __METHOD__." result=".$result."\n";
		$this->assertEquals($result, 'John Doe');

		$src='John Doe <john@doe.com>, John Doe2 <john@doe3.com>, John Doe3 <john@doe2.com>';
		$result=$localobject->getValidAddress($src, 4);
		print __METHOD__." result=".$result."\n";
		$this->assertEquals($result, 'John Doe,John Doe2,John Doe3');

		$src='John Doe <john@doe.com>, John Doe2 <john@doe3.com>, John Doe3 <john@doe2.com>';
		$result=$localobject->getValidAddress($src, 4, 0, 2);
		print __METHOD__." result=".$result."\n";
		$this->assertEquals($result, 'John Doe,John Doe2...');

		return $result;
	}
}
