<?php

error_reporting(E_ALL | E_STRICT);

require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';

if(!defined('PARM_CONFIG_GLOBAL'))
{
	define('PARM_CONFIG_GLOBAL','PARM_CONFIG_GLOBAL');
}

$GLOBALS[PARM_CONFIG_GLOBAL]['parm_namespaced_tests'] = new Parm\Database();
$GLOBALS[PARM_CONFIG_GLOBAL]['parm_namespaced_tests']->setMaster(new Parm\DatabaseNode($GLOBALS['db_namespaced_name'],$GLOBALS['db_namespaced_host'],$GLOBALS['db_namespaced_username'],$GLOBALS['db_namespaced_password']));

$GLOBALS[PARM_CONFIG_GLOBAL]['parm_global_tests'] = new Parm\Database();
$GLOBALS[PARM_CONFIG_GLOBAL]['parm_global_tests']->setMaster(new Parm\DatabaseNode($GLOBALS['db_global_name'],$GLOBALS['db_global_host'],$GLOBALS['db_global_username'],$GLOBALS['db_global_password']));

if(file_exists(dirname(__FILE__).'/dao'))
{
	system("rm -rf ".escapeshellarg(dirname(__FILE__).'/dao'));
}

if(file_exists(dirname(__FILE__).'/dao'))
{
	throw new Exception("Unable to clean old dao directory");
}

mkdir(dirname(__FILE__).'/dao');
chmod(dirname(__FILE__).'/dao', 0777);

$generator = new Parm\Generator\DatabaseGenerator($GLOBALS[PARM_CONFIG_GLOBAL]['parm_namespaced_tests']);
$generator->setDestinationDirectory(dirname(__FILE__).'/dao/namespaced');
$generator->setGeneratedNamespace("ParmTests\\Dao");
$generator->generate();

$generator = new Parm\Generator\DatabaseGenerator($GLOBALS[PARM_CONFIG_GLOBAL]['parm_global_tests']);
$generator->setDestinationDirectory(dirname(__FILE__).'/dao/global');
$generator->useGlobalNamespace();
$generator->generate();







$GLOBALS[PARM_CONFIG_GLOBAL]['parm_namespaced_tests'] = new Parm\Database();
$GLOBALS[PARM_CONFIG_GLOBAL]['parm_namespaced_tests']->setMaster(new Parm\DatabaseNode($GLOBALS['db_namespaced_name'],$GLOBALS['db_namespaced_host'],$GLOBALS['db_namespaced_username'],$GLOBALS['db_namespaced_password']));

$GLOBALS[PARM_CONFIG_GLOBAL]['parm_global_tests'] = new Parm\Database();
$GLOBALS[PARM_CONFIG_GLOBAL]['parm_global_tests']->setMaster(new Parm\DatabaseNode($GLOBALS['db_global_name'],$GLOBALS['db_global_host'],$GLOBALS['db_global_username'],$GLOBALS['db_global_password']));



require_once dirname(__FILE__).'/dao/namespaced/autoload.php';



?>