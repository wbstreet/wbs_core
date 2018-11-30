<?php

/* // NEW
if (!defined('WB_URL')) require(__DIR__.'/../../config.php'); // для инициализации сессии

$oReg = WbAdaptor::getInstance();

if (!defined('SQL_TOOLS_MODULE_LOADED')) include($oReg->AppPath.'modules/wbs_core/core/functions.sql_tools.php');
if (!class_exists('FilterData')) include($oReg->AppPath.'modules/wbs_core/core/class.filter_data.php');
if (!class_exists('Addon')) include($oReg->AppPath.'modules/wbs_core/core/class.wb_module.php');
if (!defined('CUSTOM_FUNCTIONS_LOADED')) include($oReg->AppPath.'modules/wbs_core/core/functions.php');
if (!class_exists('Agreement')) include($oReg->AppPath.'modules/wbs_core/core/class.agreement.php');

$clsAgreemnt = new Agreement($oReg->Db);
$clsFilter = new FilterData();

*/

if (!defined('WB_URL')) require(__DIR__.'/../../config.php'); // для инициализации сессии

if (!defined('FUNCTIONS_FILE_LOADED')) include(__DIR__.'/../../framework/functions.php');

$sTmp = dirname(__FILE__).'/include/Sensio/Twig/lib/Twig/Autoloader.php';
if (!class_exists('Twig_Autoloader') && is_readable($sTmp)){
    include $sTmp;
    Twig_Autoloader::register();
}

include(WB_PATH.'/modules/wbs_core/include.php');

if (!defined('SQL_TOOLS_MODULE_LOADED')) include(WB_PATH.'/modules/wbs_core/core/functions.sql_tools.php');
if (!class_exists('FilterData')) include(WB_PATH.'/modules/wbs_core/core/class.filter_data.php');
if (!class_exists('Addon')) include(WB_PATH.'/modules/wbs_core/core/class.wb_module.php');
if (!defined('CUSTOM_FUNCTIONS_LOADED')) include(WB_PATH.'/modules/wbs_core/core/functions.php');
if (!class_exists('Agreement')) include(WB_PATH.'/modules/wbs_core/core/class.agreement.php');
if (!class_exists('WbsEmail')) include(WB_PATH.'/modules/wbs_core/core/class.email.php');
if (!class_exists('WbsStorageImg')) include(WB_PATH.'/modules/wbs_core/core/class.storage_img.php');
if (!class_exists('WbsStorageVisitor')) include(WB_PATH.'/modules/wbs_core/core/class.storage_visitor.php');
if (!class_exists('WbsStorageSettlement')) include(WB_PATH.'/modules/wbs_core/core/class.storage_settlement.php');
if (!class_exists('WbsYML')) include(WB_PATH.'/modules/wbs_core/core/class.yml.php');
if (!class_exists('WbsTwig')) include(WB_PATH.'/modules/wbs_core/core/class.twig.php');

$clsAgreemnt = new Agreement($database);
$clsFilter = new FilterData();
$clsStorageImg = new WbsStorageImg();
$clsStorageVisitor = new WbsStorageVisitor();
$clsStorageSettlement = new WbsStorageSettlement();

if (!class_exists('wb')) include(WB_PATH.'/framework/class.wb.php');
if (!isset($wb) || !($wb instanceof wb)) { $wb = new wb(); }
$clsEmail = new WbsEmail($wb);