<?php

// Здесь только публичное API, доступное без авторизации! 

require_once(dirname(__FILE__).'/include_all.php');

$action = $_POST['action'];

if ($action=='get_agreement') {

        $text = $clsAgreemnt->get_text_from_page();
        if ($clsAgreemnt->is_error()) $text = $clsAgreemnt->get_error();
        print_success($text, ['title'=>'Пользовательское соглашение']);

} else {print_error('неверный api name');}

?>