<?php
// Must include code to stop this file being access directly
if(defined('WB_PATH') == false) { die("Cannot access this file directly"); }

// for including js and css
function wbs_core_include($file_names) {
        foreach ($file_names as $i => $file_name) {

                $const = strtoupper(str_replace('.', '_', $file_name));
                if (defined($const)) continue;
                else define($const, true);

                $extension = pathinfo($file_name)['extension'];
                $link = WB_URL.'/modules/wbs_core/core_client/'.$file_name;
                if ($extension == 'css') {
                        echo "<link href='$link' rel='stylesheet'>";
                } else if ($extension == 'js') {
                        echo "<script src='$link'></script>";
                }
        }
}
?>