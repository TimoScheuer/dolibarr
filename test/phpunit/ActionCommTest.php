<?php
/* Copyright (C) 2018 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *      \file       test/phpunit/ActionCommTest.php
 *      \ingroup    test
 *      \brief      PHPUnit test
 *      \remarks    To run this script as CLI:  phpunit filename.php
 */

global $conf,$user,$langs,$db;
//define('TEST_DB_FORCE_TYPE','mysql');	// This is to force using mysql driver
//require_once 'PHPUnit/Autoload.php';
require_once dirname(__FILE__).'/../../htdocs/master.inc.php';
require_once dirname(__FILE__).'/../../htdocs/comm/action/class/actioncomm.class.php';

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
class ActionCommTest extends PHPUnit\Framework\TestCase
{
	protected $savconf;
	protected $savuser;
	protected $savlangs;
	protected $savdb;

	/**
	 * Constructor
	 * We save global variables into local variables
	 *
	 * @return ActionCommTest
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

		if (!isModEnabled('agenda')) {
			print __METHOD__." module agenda must be enabled.\n"; die(1);
		}

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

		print __METHOD__."\n";
		//print $db->getVersion()."\n";
	}

	/**
	 * End phpunit tests
	 *
	 * @return  void
	 */
	protected function tearDown(): void
	{
		print __METHOD__."\n";
	}

	/**
	 * testActionCommCreate
	 *
	 * @return  int		Id of created object
	 */
	public function testActionCommCreate()
	{
		global $conf,$user,$langs,$db;
		$conf=$this->savconf;
		$user=$this->savuser;
		$langs=$this->savlangs;
		$db=$this->savdb;

		$now = dol_now();

		$localobject=new ActionComm($db);

		$localobject->type_code   = 'AC_OTH_AUTO';		// Type of event ('AC_OTH', 'AC_OTH_AUTO', 'AC_XXX'...)
		$localobject->code        = 'AC_PHPUNITTEST';
		$localobject->label       = 'This is a description';
		$localobject->note_private = 'This is note';
		$localobject->fk_project  = 0;
		$localobject->datep       = $now;
		$localobject->datef       = $now;
		$localobject->percentage  = -1;   // Not applicable
		$localobject->socid       = 0;
		$localobject->contactid   = 0;
		$localobject->authorid    = $user->id;   // User saving action
		$localobject->userownerid = $user->id;	// Owner of action
		// Fields when action is en email (content should be added into note)
		/*$localobject->email_msgid = $object->email_msgid;
		 $localobject->email_from  = $object->email_from;
		 $localobject->email_sender= $object->email_sender;
		 $localobject->email_to    = $object->email_to;
		 $localobject->email_tocc  = $object->email_tocc;
		 $localobject->email_tobcc = $object->email_tobcc;
		 $localobject->email_subject = $object->email_subject;
		 $localobject->errors_to   = $object->errors_to;*/
		//$localobject->fk_element  = $invoice->id;
		//$localobject->elementtype = $invoice->element;
		$localobject->extraparams = 'Extra params';

		$result = $localobject->create($user);

		$this->assertLessThan($result, 0);
		print __METHOD__." result=".$result."\n";
		return $result;
	}

	/**
	 * testActionCommFetch
	 *
	 * @param   int $id     Id action comm
	 * @return  ActionComm
	 *
	 * @depends	testActionCommCreate
	 * The depends says test is run only if previous is ok
	 */
	public function testActionCommFetch($id)
	{
		global $conf,$user,$langs,$db;
		$conf=$this->savconf;
		$user=$this->savuser;
		$langs=$this->savlangs;
		$db=$this->savdb;

		$localobject=new ActionComm($db);
		$result=$localobject->fetch($id);

		$this->assertLessThan($result, 0);
		print __METHOD__." id=".$id." result=".$result."\n";
		return $localobject;
	}

	/**
	 * testActionCommUpdate
	 *
	 * @param	ActionComm		$localobject	ActionComm
	 * @return	int							Id action comm updated
	 *
	 * @depends	testActionCommFetch
	 * The depends says test is run only if previous is ok
	 */
	public function testActionCommUpdate($localobject)
	{
		global $conf,$user,$langs,$db;
		$conf=$this->savconf;
		$user=$this->savuser;
		$langs=$this->savlangs;
		$db=$this->savdb;

		$localobject->label='New label';
		$result=$localobject->update($user);

		$this->assertLessThan($result, 0);
		print __METHOD__." id=".$localobject->id." result=".$result."\n";
		return $localobject->id;
	}

	/**
	 * testActionCommDelete
	 *
	 * @param   int $id         Id of action comm
	 * @return  int				Result of delete
	 *
	 * @depends testActionCommUpdate
	 * The depends says test is run only if previous is ok
	 */
	public function testActionCommDelete($id)
	{
		global $conf,$user,$langs,$db;
		$conf=$this->savconf;
		$user=$this->savuser;
		$langs=$this->savlangs;
		$db=$this->savdb;

		$localobject=new ActionComm($db);
		$result=$localobject->fetch($id);
		$result=$localobject->delete($user);

		print __METHOD__." id=".$id." result=".$result."\n";
		$this->assertLessThan($result, 0);
		return $result;
	}
}
