<?php

if(defined('WB_PATH'))
{
    // create tables from sql dump file
    if (is_readable(__DIR__.'/install-struct.sql')) {
        $database->SqlImport(__DIR__.'/install-struct.sql', TABLE_PREFIX, __FILE__ );
    }
}

if(defined('WB_PATH'))
{
    // create tables from sql dump file
    if (is_readable(__DIR__.'/install-data.sql')) {
        $database->SqlImport(__DIR__.'/install-data.sql', TABLE_PREFIX, __FILE__ );
    }
}

