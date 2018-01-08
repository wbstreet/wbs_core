<?php
// Must include code to stop this file being access directly
if(defined('WB_PATH') == false) { die("Cannot access this file directly"); }

if (!function_exists('wbs_core_include')) {
    // for including js and css
    function wbs_core_include($file_names, $is_vendor=false) {
        
        if ($is_vendor) $source_path = 'include_client';
        else $source_path = 'core_client';
        
        foreach ($file_names as $i => $file_name) {
    
            $const = strtoupper(str_replace('.', '_', $file_name));
            $const = strtoupper(str_replace('/', '_', $file_name));
            if (defined($const)) continue;
            else define($const, true);                    

            $extension = pathinfo($file_name)['extension'];
            $link = WB_URL."/modules/wbs_core/$source_path/$file_name";
            if ($extension == 'css') {
                echo "<link href='$link' rel='stylesheet' type='text/css'>";
            } else if ($extension == 'js') {
                echo "<script src='$link'></script>";
            }
       }
    }
}
?>