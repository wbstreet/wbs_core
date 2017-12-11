<?php
/*
 * Author: Polyakov Konstantin 
 */

define('CUSTOM_FUNCTIONS_LOADED', true);

function echo_creator($link_style='', $image_style='', $sign_style='') {
	?>

    <a style='border: 1px solid #fff;padding:12px;<?=$link_style?>' target="_blank" href="http://вашсайт.инфо-рф.рф">
	    <nobr>
			<img style='<?=$image_style?>' height="30px" src="https://инфо-рф.рф/media/img/informer.png">
			<span style='<?=$sign_style?>'>&nbsp;&nbsp;Дизайн и разработка</span>
		</nobr>
	</a>

    <?php
}

function echoImageLoader($name, $image_url, $w, $h, $is_ret=false) {
   $s = '';
   $s .=  '<div style="width:'.$w.'; height:'.$h.'; background-image:url('.$image_url.'?a='.time().'); background-size: contain; background-repeat: no-repeat; border: 2px solid #C7D8EA;">';
   $s .= '<input style="opacity:0; cursor:pointer; width:'.$w.'; height:'.$h.';" name="'.$name.'" type="file" onchange="show_pic(this.files[0], function(image_data, el) {el.parentElement.style.backgroundImage = \'url(\'+image_data+\')\'}, this)">';
   $s .= '</div>';
   
   if ($is_ret) return $s;
   echo $s;
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
	}
}
function print_error($message, $options=[]) { print_message($message, $options, 'error');
}
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
?>