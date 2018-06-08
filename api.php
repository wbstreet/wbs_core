<?php

// Здесь только публичное API, доступное без авторизации! 

require_once(dirname(__FILE__).'/include_all.php');

$action = $_POST['action'];

if ($action=='get_agreement') {

        $text = $clsAgreemnt->get_text_from_page();
        if ($clsAgreemnt->is_error()) $text = $clsAgreemnt->get_error();
        print_success($text, ['title'=>'Пользовательское соглашение']);

} else if ($action=='send_feedback') {
    
    //print_error(json_encode($_SESSION['captcha']).' - '.json_encode($_POST['captcha']));

    $arrFields = [
        'fio'=>'ФИО',
        'phone'=>'Телефон',
        'zayavka'=>'Текст',
    ];
    
    foreach($arrFields as $lat_name => $rus_name) {
        $clsFilter->f($lat_name, [['1', "Введите $rus_name!"]], 'append');
    }
    $clsFilter->f('captcha', [['1', "Введите Защитный код!"], ['variants', "Введите Защитный код!", [$_SESSION['captcha']]]], 'append', '');
    $clsFilter->f('i_agree', [['variants', "Вы должны согласитиься с пользовательским соглашением!", ['true']]], 'append', '');
    if ($clsFilter->is_error()) $clsFilter->print_error();

    // TODO здесь нужно изменить капчу

    // Определяем сайт
    list($url, $is_true) = idn_decode(WB_URL);

    if (defined('CUSTOMSETTINGS_FEEDBACK_EMAIL') && trim(CUSTOMSETTINGS_FEEDBACK_EMAIL) != '') {
        $email = CUSTOMSETTINGS_FEEDBACK_EMAIL;
    } else {
        $email = "wbstreet@mail.ru";
    }

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

} else if ($action == 'get_settlement') {

    $id = preg_replace("/[^0-9]+/", '', $_POST['id']);
    $level = $_POST['level'];

    if ($level == 'settlement') $res = $clsStorageSettlement->getSettlements(['id'=>$id]);
    else if ($level == 'region') $res = $clsStorageSettlement->getRegions(['id'=>$id, 'count_limit'=>$count]);

    if (gettype($res) === 'string') print_error($res);
    print_success('', ['data'=>$res->fetchRow()]);

} else if ($action == 'get_suggestion') {

    $level = $_POST['level'];
    $text = $_POST['text'];
    $count = preg_replace("/[^0-9]+/", '', $_POST['count']);

    if ($level == 'settlement') $res = $clsStorageSettlement->getSettlements(['limit_count'=>$count, 'starts_with'=>$text]);
    else if ($level == 'region') $res = $clsStorageSettlement->getRegions(['text'=>$text, 'count_limit'=>$count]);

    if (gettype($res) == 'string') print_error($res);
    $answer = [];
    while ($r = $res->fetchRow()) {
        $answer[] = $r;
    }

    print_success('', ['data'=>$answer]);
    
} else {print_error('неверный api name');}

?>