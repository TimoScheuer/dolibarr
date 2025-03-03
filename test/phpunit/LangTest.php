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
 *      \file       test/phpunit/LangTest.php
 *		\ingroup    test
 *      \brief      PHPUnit test
 *		\remarks	To run this script as CLI:  phpunit filename.php
 */

global $conf,$user,$langs,$db;
//define('TEST_DB_FORCE_TYPE','mysql');	// This is to force using mysql driver
//require_once 'PHPUnit/Autoload.php';
require_once dirname(__FILE__).'/../../htdocs/master.inc.php';
require_once dirname(__FILE__).'/../../htdocs/core/lib/security.lib.php';
require_once dirname(__FILE__).'/../../htdocs/core/lib/security2.lib.php';

if (! defined('NOREQUIREUSER')) {
	define('NOREQUIREUSER', '1');
}
if (! defined('NOREQUIREDB')) {
	define('NOREQUIREDB', '1');
}
if (! defined('NOREQUIRESOC')) {
	define('NOREQUIRESOC', '1');
}
if (! defined('NOREQUIRETRAN')) {
	define('NOREQUIRETRAN', '1');
}
if (! defined('NOCSRFCHECK')) {
	define('NOCSRFCHECK', '1');
}
if (! defined('NOTOKENRENEWAL')) {
	define('NOTOKENRENEWAL', '1');
}
if (! defined('NOREQUIREMENU')) {
	define('NOREQUIREMENU', '1'); // If there is no menu to show
}
if (! defined('NOREQUIREHTML')) {
	define('NOREQUIREHTML', '1'); // If we don't need to load the html.form.class.php
}
if (! defined('NOREQUIREAJAX')) {
	define('NOREQUIREAJAX', '1');
}
if (! defined("NOLOGIN")) {
	define("NOLOGIN", '1');       // If this page is public (can be called outside logged session)
}

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
class LangTest extends PHPUnit\Framework\TestCase
{
	protected $savconf;
	protected $savuser;
	protected $savlangs;
	protected $savdb;

	/**
	 * Constructor
	 * We save global variables into local variables
	 *
	 * @return SecurityTest
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
	 * testLang
	 *
	 * @return string
	 */
	public function testLang()
	{
		global $conf,$user,$langs,$db;
		$conf=$this->savconf;
		$user=$this->savuser;
		$langs=$this->savlangs;
		$db=$this->savdb;

		include_once DOL_DOCUMENT_ROOT.'/core/class/translate.class.php';

		$filesarray = scandir(DOL_DOCUMENT_ROOT.'/langs');
		foreach ($filesarray as $key => $code) {
			if (! preg_match('/^[a-z]+_[A-Z]+$/', $code)) {
				continue;
			}

			print 'Check language file for lang code='.$code."\n";
			$tmplangs=new Translate('', $conf);
			$langcode=$code;
			$tmplangs->setDefaultLang($langcode);
			$tmplangs->load("main");

			$result=$tmplangs->transnoentitiesnoconv("FONTFORPDF");
			print __METHOD__." FONTFORPDF=".$result."\n";
			$this->assertTrue(in_array($result, array('msungstdlight', 'stsongstdlight', 'helvetica', 'DejaVuSans', 'cid0jp', 'cid0kr', 'freemono', 'freeserif')), 'Error bad value '.$result.' for FONTFORPDF in main.lang file '.$code);

			$result=$tmplangs->transnoentitiesnoconv("DIRECTION");
			print __METHOD__." DIRECTION=".$result."\n";
			$this->assertTrue(in_array($result, array('rtl', 'ltr')), 'Error bad value for DIRECTION in main.lang file '.$code);

			$result=$tmplangs->transnoentitiesnoconv("SeparatorDecimal");
			print __METHOD__." SeparatorDecimal=".$result."\n";
			$this->assertContains($result, array('.',',','/',' ','','None'), 'Error on decimal separator for lang code '.$code);	// Note that ، that is coma for RTL languages is not supported

			$result=$tmplangs->transnoentitiesnoconv("SeparatorThousand");
			print __METHOD__." SeparatorThousand=".$result."\n";
			$this->assertContains($result, array('.',',','/',' ','','\'','None','Space'), 'Error on thousand separator for lang code '.$code);	// Note that ، that is coma for RTL languages is not supported

			// Test java string contains only d,M,y,/,-,. and not m,...
			$result=$tmplangs->transnoentitiesnoconv("FormatDateShortJava");
			print __METHOD__." FormatDateShortJava=".$result."\n";
			$this->assertRegExp('/^[dMy\/\-\.]+$/', $result, 'FormatDateShortJava KO for lang code '.$code);
			$result=$tmplangs->trans("FormatDateShortJavaInput");
			print __METHOD__." FormatDateShortJavaInput=".$result."\n";
			$this->assertRegExp('/^[dMy\/\-\.]+$/', $result, 'FormatDateShortJavaInput KO for lang code '.$code);

			unset($tmplangs);

			$filesarray2 = scandir(DOL_DOCUMENT_ROOT.'/langs/'.$code);
			foreach ($filesarray2 as $key => $file) {
				if (! preg_match('/\.lang$/', $file)) {
					continue;
				}

				print 'Check lang file '.$file."\n";
				$filecontent=file_get_contents(DOL_DOCUMENT_ROOT.'/langs/'.$code.'/'.$file);

				$result=preg_match('/=--$/m', $filecontent);	// A special % char we don't want. We want the common one.
				//print __METHOD__." Result for checking we don't have bad percent char = ".$result."\n";
				$this->assertTrue($result == 0, 'Found a translation KEY=-- into file '.$code.'/'.$file.'. We probably want Key=- instead.');

				$result=strpos($filecontent, '％');	// A special % char we don't want. We want the common one.
				//print __METHOD__." Result for checking we don't have bad percent char = ".$result."\n";
				$this->assertTrue($result === false, 'Found a bad percent char ％ instead of % into file '.$code.'/'.$file);

				$result=preg_match('/%n/m', $filecontent);	// A sequence of char we don't want
				//print __METHOD__." Result for checking we don't have bad percent char = ".$result."\n";
				$this->assertTrue($result == 0, 'Found a sequence %n into the translation file '.$code.'/'.$file.'. We probably want %s');

				$result=preg_match('/<<<<</m', $filecontent);	// A sequence of char we don't want
				//print __METHOD__." Result for checking we don't have bad percent char = ".$result."\n";
				$this->assertTrue($result == 0, 'Found a sequence <<<<< into the translation file '.$code.'/'.$file.'. Probably a bad merge of code were done.');
			}
		}

		return;
	}

	/**
	 * testTrans
	 *
	 * @return string
	 */
	public function testTrans()
	{
		global $conf,$user,$langs,$db;
		$conf=$this->savconf;
		$user=$this->savuser;
		$langs=$this->savlangs;
		$db=$this->savdb;

		$tmplangs=new Translate('', $conf);
		$langcode='en_US';
		$tmplangs->setDefaultLang($langcode);
		$tmplangs->load("main");

		$result = $tmplangs->trans("FilterOnInto", "<input autofocus onfocus='alert(1337)' <--!");
		print __METHOD__." result trans FilterOnInto = ".$result."\n";
		$this->assertEquals($result, "Search criteria '<b>&lt;input autofocus onfocus='alert(1337)' &lt;--!</b>' into fields ", 'Result of lang->trans must have original translation string with its original HTML tag, but inserted values must be fully encoded.');
	}
}
