<?php
/* Copyright (C) 2013 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *      \file       test/phpunit/FactureRecTest.php
 *		\ingroup    test
 *      \brief      PHPUnit test
 *		\remarks	To run this script as CLI:  phpunit filename.php
 */

global $conf,$user,$langs,$db;
//define('TEST_DB_FORCE_TYPE','mysql');	// This is to force using mysql driver
//require_once 'PHPUnit/Autoload.php';
require_once dirname(__FILE__).'/../../htdocs/master.inc.php';
require_once dirname(__FILE__).'/../../htdocs/compta/facture/class/facture.class.php';
require_once dirname(__FILE__).'/../../htdocs/compta/facture/class/facture-rec.class.php';

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
class FactureRecTest extends PHPUnit\Framework\TestCase
{
	protected $savconf;
	protected $savuser;
	protected $savlangs;
	protected $savdb;

	/**
	 * Constructor
	 * We save global variables into local variables
	 *
	 * @return FactureTest
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
		$db->begin();	// This is to have all actions inside a transaction even if test launched without suite.

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
	 * @return	void
	 */
	protected function setUp(): void
	{
		global $conf,$user,$langs,$db;
		$conf=$this->savconf;
		$user=$this->savuser;
		$langs=$this->savlangs;
		$db=$this->savdb;

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
	 * testFactureRecCreate
	 *
	 * @return int
	 */
	public function testFactureRecCreate()
	{
		global $conf,$user,$langs,$db;

		$conf=$this->savconf;
		$user=$this->savuser;
		$langs=$this->savlangs;
		$db=$this->savdb;

		$localobjectinv=new Facture($db);
		$localobjectinv->initAsSpecimen();
		$result = $localobjectinv->create($user);

		print __METHOD__." result=".$result."\n";

		$localobject=new FactureRec($db);
		$localobject->initAsSpecimen();
		$result = $localobject->create($user, $localobjectinv->id);

		print __METHOD__." result=".$result."\n";
		$this->assertGreaterThan(0, $result, 'Create recurring invoice from common invoice');

		return $result;
	}

	/**
	 * testFactureRecFetch
	 *
	 * @param  int 	$id  	Id of created recuriing invoice
	 * @return int
	 *
	 * @depends testFactureRecCreate
	 * The depends says test is run only if previous is ok
	 */
	public function testFactureRecFetch($id)
	{
		global $conf,$user,$langs,$db;
		$conf=$this->savconf;
		$user=$this->savuser;
		$langs=$this->savlangs;
		$db=$this->savdb;

		$localobject=new FactureRec($db);
		$result = $localobject->fetch($id);

		print __METHOD__." result=".$result."\n";
		$this->assertGreaterThan(0, $result);
		return $result;
	}



	/**
	 * Edit an object to test updates
	 *
	 * @param 	FactureRec	$localobject		Object Facture rec
	 * @return	void
	 */
	public function changeProperties(&$localobject)
	{
		$localobject->note_private='New note';
		//$localobject->note='New note after update';
	}

	/**
	 * Compare all public properties values of 2 objects
	 *
	 * @param 	Object		$oA						Object operand 1
	 * @param 	Object		$oB						Object operand 2
	 * @param	boolean		$ignoretype				False will not report diff if type of value differs
	 * @param	array		$fieldstoignorearray	Array of fields to ignore in diff
	 * @return	array								Array with differences
	 */
	public function objCompare($oA, $oB, $ignoretype = true, $fieldstoignorearray = array('id'))
	{
		$retAr=array();

		if (get_class($oA) !== get_class($oB)) {
			$retAr[]="Supplied objects are not of same class.";
		} else {
			$oVarsA=get_object_vars($oA);
			$oVarsB=get_object_vars($oB);
			$aKeys=array_keys($oVarsA);
			foreach ($aKeys as $sKey) {
				if (in_array($sKey, $fieldstoignorearray)) {
					continue;
				}
				if (! $ignoretype && ($oVarsA[$sKey] !== $oVarsB[$sKey])) {
					$retAr[]=$sKey.' : '.(is_object($oVarsA[$sKey])?get_class($oVarsA[$sKey]):$oVarsA[$sKey]).' <> '.(is_object($oVarsB[$sKey])?get_class($oVarsB[$sKey]):$oVarsB[$sKey]);
				}
				if ($ignoretype && ($oVarsA[$sKey] != $oVarsB[$sKey])) {
					$retAr[]=$sKey.' : '.(is_object($oVarsA[$sKey])?get_class($oVarsA[$sKey]):$oVarsA[$sKey]).' <> '.(is_object($oVarsB[$sKey])?get_class($oVarsB[$sKey]):$oVarsB[$sKey]);
				}
			}
		}
		return $retAr;
	}
}
