<?php

if (!defined('WB_URL')) require(__DIR__.'/../../config.php'); // для инициализации сессии

if(defined('WB_PATH'))
{
require_once(WB_PATH.'/framework/functions.php');
    // delete tables from sql dump file
    if (is_readable(__DIR__.'/uninstall-struct.sql')) {
        $database->SqlImport(__DIR__.'/uninstall-struct.sql', TABLE_PREFIX, __FILE__ );
    }
}