<?php
/*
 * Author: Polyakov Konstantin 
 */

define('CUSTOM_FUNCTIONS_LOADED', true);

function echo_creator($link_style='', $image_style='', $sign_style='') {
        ?>

    <a style='border: 1px solid #fff;padding:12px;<?=$link_style?>' target="_blank" href="https://vk.com/id275575214">
            <nobr>
                        <img style='<?=$image_style?>' height="30px" src="https://pp.userapi.com/c639418/v639418616/5ba06/6JVBrCdTehI.jpg">
                        <span style='<?=$sign_style?>'>&nbsp;&nbsp;Дизайн и разработка</span>
                </nobr>
        </a>

    <?php
}

function echoImageLoader($name, $image_url, $w, $h, $is_ret=false) {
   $s = '';
   $s .=  '<div style="width:'.$w.'; height:'.$h.'; background-image:url('.$image_url.'?a='.time().'); background-size: contain; background-repeat: no-repeat; border: 2px solid #C7D8EA;position:relative;">';
   //$s .= '<input style="opacity:0; cursor:pointer; width:'.$w.'; height:'.$h.';" name="'.$name.'" type="file" onchange="show_pic(this.files[0], function(image_data, el) {el.parentElement.style.backgroundImage = \'url(\'+image_data+\')\'}, this)">'';
   $s .= '<input style="opacity:0; cursor:pointer; width:'.$w.'; height:'.$h.';" name="'.$name.'" type="file" onchange="show_image(this, this.parentElement)">';
   $s .= '<input style="position:absolute;bottom:5px;" type="button" value="Отменить" onclick="this.parentElement.style.backgroundImage=this.parentElement.dataset.url; this.parentElement.querySelector(\'input\').value = \'\'">';
   $s .= '</div>';
 
   if ($is_ret) return $s;
   echo $s;
}

function show_editor($content, $script_file, $height='100%', $width='400') {
        global $admin;
    //$old = $_SERVER['SCRIPT_NAME']; $_SERVER['SCRIPT_NAME'] = preg_replace("/.*\/public_html/", '', $script_file);
    //$old2 = $_SERVER['SCRIPT_FILENAME']; $_SERVER['SCRIPT_FILENAME'] = $script_file;
        require(WB_PATH.'/modules/ckeditor/include.php');
    echo $admin->getFTAN()."\n";
    show_wysiwyg_editor($name='content', $id='content', $content, $height, $width);
    //$_SERVER['SCRIPT_NAME'] = $old;
    //$_SERVER['SCRIPT_FILENAME'] = $old2;
}

function generate_image_name($len=15, $registr='both') {
        $salt = "0123456789";
        if (in_array($registr, ['both', 'up'])) $salt .= "ABCHEFGHJKMNPQRSTUVWXYZ";
        if (in_array($registr, ['both', 'low'])) $salt .= "abchefghjkmnpqrstuvwxyz";
        $name = '';
        srand((double)microtime()*1000000);
        $i = 0;
        while ($i <= $len) {
                $num = rand() % strlen($salt);
                $tmp = substr($salt, $num, 1);
                $name = $name . $tmp;
                $i++;
        }
        return $name;

}

// http://php.net/manual/ru/reserved.variables.files.php#109958
function diverse_array($vector) { 
    $result = array(); 
    foreach($vector as $key1 => $value1) 
        foreach($value1 as $key2 => $value2) 
            $result[$key2][$key1] = $value2; 
    return $result; 
}


/*
   печатают сообщения (уведомительные или ошибки). Для работы функций требуется создать пустой массив $res
*/
function print_message($message, $options, $type) {
    global $admin;
    if (!isset($options['return_url'])) $options['return_url'] = 'index.php';
    if (!isset($options['data'])) $options['data'] = [];
    $format =  isset($options['format']) ? $options['format'] : 'json';
    if ($format == 'html') {
        $admin->print_header();
        if ($type == 'error') $admin->print_error($message, $options['return_url']);
        else if ($type == 'success') $admin->print_success($message, $options['return_url']);
        die;
    } else if ($format == 'json') {
        $res = [];
        if ($type == 'error') $res['success'] = '0';
        else if ($type == 'success') $res['success'] = '1';
        $res['message'] = $message;
        if (isset($options['data']))          $res['data'] = $options['data'];
        if (isset($options['absent_fields'])) $res['absent_fields'] = $options['absent_fields'];
        if (isset($options['timeout']))       $res['timeout'] = $options['timeout'];
        if (isset($options['location']))      $res['location'] = $options['location'];
        if (isset($options['title']))          $res['title'] = $options['title'];
        echo json_encode($res);
        die();
    } else if ($format=='js') {
        echo "<script>console.log(`{$message}`);</script>";
    }
}
function print_error($message, $options=[]) { print_message($message, $options, 'error'); }
function print_success($message, $options=[]) { print_message($message, $options, 'success'); }

// http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php

function startsWith($haystack, $needle) {
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle) {
   $length = strlen($needle);
   if ($length == 0) { return true; }
   return (substr($haystack, -$length) === $needle);
}

function idn_decode($url) {
    $class_path = WB_PATH.'/include/idna_convert/idna_convert.class.php';
    if (!class_exists('idna_convert')) {
        if (file_exists($class_path)) require_once($class_path);
    }
    if (class_exists('idna_convert')) {
        $IDN = new idna_convert();
        return [$IDN->decode($url), true];
    }
    return [$url, false];
}

function len_base64($str, $kilo='B') {
        $kilos = ['B'=>1, 'KB'=>1024, 'MB'=>1024*1024];
        return strlen($str) * 6 / 8 / $kilos[$kilo];
}

/* Проверка прав  */

function check_permission($rules) {
    global $admin;
    foreach ($rules as $i => $rule) {
        if (!$admin->get_permission($rule)) print_error('Нет доступа');
    }
}

function check_page_permission($page_id) {
    global $admin;
    if (!$admin->get_page_permission($page_id)) print_error('Нет доступа к странице');
}

function check_all_permission($page_id, $rules) {
    check_page_permission($page_id);
    check_permission($rules);
}

function check_auth() {
    global $admin;
    if (!$admin->is_authenticated()) print_error('Доступ разрешён только зарегистрированным пользователям!');    
}

// https://vk.com/dev/widgets_for_sites
class VKTools {
        function __construct() {
                
        }
        
        function echo_widget_js($widgets) {
                if (in_array('share', $widgets)) {
                echo '<!-- Put this script tag to the <head> of your page -->
                  <script type="text/javascript" src="https://vk.com/js/api/share.js?94" charset="windows-1251"></script>';
                }
        }

        function echo_share_button($text='Поделиться') {
                echo "
                     <div style='display:inline-block;vertical-align:bottom;'>
                             <!-- Put this script tag to the place, where the Share button will be -->
                     <script type='text/javascript'><!--
                         document.write(VK.Share.button(false,{type: 'round', text: '$text'}));
                     --></script>
             </div>";
        }

}

// https://apiok.ru/ext/like
class OKTools {
        function __construct() {
                
        }
        
        
        function echo_share_button() {
                ?>
        <div style='display:inline-block;vertical-align:super;'>
                        <div id="ok_shareWidget"></div>
                        <script>
                        !function (d, id, did, st, title, description, image) {
                          var js = d.createElement("script");
                          js.src = "https://connect.ok.ru/connect.js";
                          js.onload = js.onreadystatechange = function () {
                          if (!this.readyState || this.readyState == "loaded" || this.readyState == "complete") {
                            if (!this.executed) {
                              this.executed = true;
                              setTimeout(function () {
                                OK.CONNECT.insertShareWidget(id,did,st, title, description, image);
                              }, 0);
                            }
                          }};
                          d.documentElement.appendChild(js);
                        }(document,"ok_shareWidget",document.URL,'{"sz":20,"st":"rounded","ck":1}',"","","");
                        </script>
                </div>
    
    <?php
        }
}

function share_page_link() {
    $clsVKTools = new VKTools;
    $clsOKTools = new OKTools;
    
    $clsVKTools->echo_widget_js(['share']);
    echo '<div style="width:100%;text-align:right;">';
        $clsVKTools->echo_share_button();
        $clsOKTools->echo_share_button();
    echo '</div>';
}

function calc_paginator($page_num, $page_total) {
        
        $ON_SIDE = 3;
    $divs = [];

        if ($page_num!=1) $divs[] = ['1', 'url'];
        if ($page_num-$ON_SIDE > 2) $divs[] = ['...', 'text'];
        
    for ($i=$ON_SIDE; $i>0; $i--) {
        if ($page_num-$i > 1) $divs[] = [$page_num-$i, 'url'];
    }

        $divs[] = [$page_num, 'text'];

        for ($i=$page_num+1; $i<=$page_num+$ON_SIDE; $i++) {
            if ($i < $page_total) $divs[] = [$i, 'url'];
        }

        if ($page_num+$ON_SIDE < $page_total-1) $divs[] = ['...', 'text'];
        if ($page_num!=$page_total) $divs[] = [$page_total, 'url'];
        
        return $divs;
}

/*
page_total - всего страниц
obj_per_page - объектов на странице
page_num - номер текущей страницы
0bj_total - всего объектов
*/
function calc_paginator_and_limit($args, &$fields, $obj_total) {
    $fields['limit_count'] = $args['obj_per_page'];
    $fields['limit_offset'] = $args['obj_per_page'] * ($args['page_num'] - 1);
    
    return calc_paginator($args['page_num'], (int)($obj_total / $args['obj_per_page'])+1);
}

?>