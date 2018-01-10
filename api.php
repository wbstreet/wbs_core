<?php

// Здесь только публичное API, доступное без авторизации! 

require_once(dirname(__FILE__).'/include_all.php');

$action = $_POST['action'];

if ($action=='get_agreement') {

        $text = $clsAgreemnt->get_text_from_page();
        if ($clsAgreemnt->is_error()) $text = $clsAgreemnt->get_error();
        print_success($text, ['title'=>'Пользовательское соглашение']);

} else if ($action=='send_feedback') {

    // проверка капчи
    if (!isset($_SESSION['captcha']) || !isset($_POST['captcha']) || (string)$_SESSION['captcha'] != (string)$_POST['captcha']) {
        print_error("Капча введена неверно! Повторите попытку. ");
    }

    // проверка согласия пользователя
    if (!isset($clsAgreemnt) || !$clsAgreemnt->does_visitor_agree()) {
        print_error("Вам необходимо согласиться с соглашением! ");
    }
    
    // Определяем сайт
    if (file_exists(__DIR__.'/include/idna_convert/idna_convert.class.php')) require_once(__DIR__.'/include/idna_convert/idna_convert.class.php');
    if (class_exists('idna_convert')) {
        $IDN = new idna_convert();
        $url = $IDN->decode(WB_URL);
    } else { $url = WB_URL; }

    if (defined('CUSTOMSETTINGS_FEEDBACK_EMAIL') && trim(CUSTOMSETTINGS_FEEDBACK_EMAIL) != '') {
        $email = CUSTOMSETTINGS_FEEDBACK_EMAIL;
    } else {
        $email = "wbstreet@mail.ru";
    }

    $arrFields = [
        'fio'=>'ФИО',
        'phone'=>'Телефон',
        'zayavka'=>'Текст',
    ];

    // склеивание полей
    $strFields = '';
    foreach($arrFields as $lat_name => $rus_name) {
        $strFields .= "\n\n{$rus_name}: {$_POST[$lat_name]}";
    }

    $r = $clsEmail->send(
        $email,
        "Письмо с Вашего сайта: \n{$strFields}",
        "Письмо с Вашего сайта ".$url,
        0, false
    );
    if ($r[0] !== true) print_error('Письмо не отправлено! ');

    print_success('Письмо успешно отправлено!');

} else {print_error('неверный api name');}

?>