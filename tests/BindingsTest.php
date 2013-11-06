<?php

require dirname(__FILE__) . '/test.inc.php';

class BindingsTest extends PHPUnit_Framework_TestCase
{
	function testStringBinding()
	{
		$f = new ParmTests\Dao\PeopleDaoFactory();
		
		$binding = new \Parm\Binding\StringBinding("people.people_id = 1");
		$this->assertEquals('people.people_id = 1', $binding->getSQL($f));
		
	}
	
	function testBindingEscaping()
	{
		$f = new ParmTests\Dao\PeopleDaoFactory();
		
		$binding = new \Parm\Binding\ContainsBinding("last_name","Parmo's");
		$this->assertEquals("last_name LIKE '%Parmo\'s%'", $binding->getSQL($f));
		
		$binding = new \Parm\Binding\EqualsBinding("last_name","Parmo's");
		$this->assertEquals("last_name = 'Parmo\'s'", $binding->getSQL($f));
		
		$binding = new \Parm\Binding\EqualsBinding("last_name","Parmo\'\'\"s");
		$this->assertEquals("last_name = 'Parmo\\\\\'\\\\\'\\\"s'", $binding->getSQL($f));
	}
	
	function testCaseSensitiveEqualsBinding()
	{
		$f = new ParmTests\Dao\PeopleDaoFactory();
		
		$binding = new \Parm\Binding\CaseSensitiveEqualsBinding("last_name","Parmo");
		$this->assertEquals("last_name COLLATE utf8_bin LIKE 'Parmo'", $binding->getSQL($f));
	}
	
	
	function testContainsBinding()
	{
		$f = new ParmTests\Dao\PeopleDaoFactory();
		
		$binding = new \Parm\Binding\ContainsBinding("last_name","Parmo");
		$this->assertEquals("last_name LIKE '%Parmo%'", $binding->getSQL($f));
	}
	
	
	function testForeignKeyObjectBinding()
	{
		$sharon = ParmTests\Dao\ZipcodesDaoObject::findId(1445);
		
		$f = new ParmTests\Dao\PeopleDaoFactory();
		$binding = new \Parm\Binding\ForeignKeyObjectBinding($sharon);
		$this->assertEquals("zipcode_id = '1445'", $binding->getSQL($f));
	}
	
	function testEqualsBinding()
	{
		$f = new ParmTests\Dao\PeopleDaoFactory();
		
		$binding = new \Parm\Binding\EqualsBinding("people_id",1);
		$this->assertEquals("people_id = '1'", $binding->getSQL($f));
		
		$binding = new \Parm\Binding\EqualsBinding("last_name","Montoya");
		$this->assertEquals("last_name = 'Montoya'", $binding->getSQL($f));
		
		$binding = new \Parm\Binding\EqualsBinding("last_name",null);
		$this->assertEquals("last_name = NULL", $binding->getSQL($f));
		
		$binding = new \Parm\Binding\EqualsBinding("last_name","");
		$this->assertEquals("last_name = ''", $binding->getSQL($f));
		
		$binding = new \Parm\Binding\EqualsBinding("last_name","κόσμε");
		$this->assertEquals("last_name = 'κόσμε'", $binding->getSQL($f));
	}
	
	function testInBinding()
	{
		$f = new ParmTests\Dao\PeopleDaoFactory();
		
		$binding = new Parm\Binding\InBinding("zipcode_id",array(1,2,3,4));
		$this->assertEquals("zipcode_id IN (1,2,3,4)", $binding->getSQL($f));
		
		$f = new ParmTests\Dao\PeopleDaoFactory();
		$binding = new Parm\Binding\InBinding("zipcode_id",array("1","2","3","4"));
		$this->assertEquals("zipcode_id IN (1,2,3,4)", $binding->getSQL($f));
		
		$f = new ParmTests\Dao\PeopleDaoFactory();
		$binding = new Parm\Binding\InBinding("zipcode_id",array("3","2","1","contact"));
		$this->assertEquals("zipcode_id IN (3,2,1,'contact')", $binding->getSQL($f));
		
		$f = new ParmTests\Dao\PeopleDaoFactory();
		$binding = new Parm\Binding\InBinding("zipcode_id",array("apple","orange","dumptruck"));
		$this->assertEquals("zipcode_id IN ('apple','orange','dumptruck')", $binding->getSQL($f));
		
		$f = new ParmTests\Dao\PeopleDaoFactory();
		$binding = new Parm\Binding\InBinding("zipcode_id",array(null,"",''));
		$this->assertEquals("zipcode_id IN ('','','')", $binding->getSQL($f));
	}
	
	
}

?>