<?php

class WbsEmail {
	function __construct() {
		$this->tbl_templates_of_letter = "`".TABLE_PREFIX."mod_wbscore_templates_of_letter`";
		$this->tbl_templates_of_letter_sended = "`".TABLE_PREFIX."mod_wbscore_templates_of_letter_sended`";
	}
	
	protected function _send($to, $body, $subject) {
		    /*if(IT_IS_ORIGINAL_PORTAL === false) {
		    	$body = "******************************<br>".htmlentities($to)
		    	        ." - в нетестовом режиме портала письмо было бы отправлено на данный электронный адрес <br>"
		    	        ."******************************<br><br>$body";
		    	$to = PORTAL_TEST_EMAIL;
		    }*/

		    /*$mail = new PHPMailer();

		    $mail->IsSMTP();
		    $mail->Host = PORTAL_SMTP_HOST;
		    //$mail->SMTPDebug = 2;
		    $mail->SMTPAuth = true;
		    $mail->Port = PORTAL_SMTP_PORT;
		    $mail->Username = PORTAL_SMTP_USERNAME;
		    $mail->Password = PORTAL_SMTP_PASSWORD;
		    $mail->AddRepplyTo = PORTAL_SMTP_ADD_REPLY_TO;
		    $mail->From = PORTAL_SMTP_FROM;
		    $mail->FromName = PORTAL_SMTP_FROM_NAME;
		
		    //$mail->IsMail();
		    $mail->IsHtml(true);
		    $mail->CharSet = "utf-8";
		    $mail->Subject = $subject;
		
		    $mail->AddAddress($to);
		    $mail->Body = $body;
		
		    if(!$mail->Send()) {
		    	return 'Ошибка отправки письма! Пожалуйста, обратитесь в службу поддержки. '.$mail->ErrorInfo;
		    }*/
		    
		    if (!mail($to, $subject, $body)){
                        return 'Ошибка отправки письма! Пожалуйста, обратитесь в службу поддержки.';
                    }
		
		    return true;
	}

    function backup_letter($to, $body, $subject, $template_id, $sender_user_id=null, $send_from_page_id=null) {
    	global $database;
    	$fields = [
    		'letter_email'=>$to,
    		'letter_body'=>$body,
    		'letter_subject'=>$subject,
    		'letter_template_id'=>$template_id,
    		'sender_user_id'=>$sender_user_id,
    		'send_from_page_id'=>$send_from_page_id,
    		];
    	$sql = build_insert($this->tbl_templates_of_letter_sended, $fields);
    	if (!$database->query($sql)) return $database->get_error();
    	return $database->getLastInsertId();
    }
    
    function backup_is_sended($backuped_letter_id) {
    	return update_row($this->tbl_templates_of_letter_sended, ['is_sended'=>1], "`sended_letter_id`=$backuped_letter_id");
    }

    function get_templates($sets=[]) {
	global $database, $sql_builder;
	if (isset($sets['letter_id'])) $letter_id = get_number($sets['letter_id']); else $letter_id = null;
	if (isset($sets['letter_name'])) $letter_name = mysql_escape_string($sets['letter_name']); else $letter_name = null;

        $sql_builder->clear();

        $sql_builder->add_raw_where("1=1");
        if ($letter_id !== null) $sql_builder->add_raw_where("`letter_template_id`=$letter_id");
        if ($letter_name !== null) $sql_builder->add_raw_where("`letter_template_name`='$letter_name'");

		$sql = "SELECT * FROM {$this->tbl_templates_of_letter} WHERE ".$sql_builder->build_where();
		$result = $database->query($sql);
		if ($database->is_error()) return 'email->get_templates: '.$database->get_error();
		if ($result->numRows() == 0) return null;
		return $result;
    }

    function update_template($fields, $template_id) {
    	$template_id = get_number($template_id);
    	return update_row($this->tbl_templates_of_letter, $fields, "`letter_template_id`=$template_id");
    }

    function create_template($name, $subject, $body) {
    	global $database;
    	$fields = [
    		'letter_template_name'=>$name,
    		'letter_template_subject'=>$subject,
    		'letter_template_body'=>$body,
    		];
    	$sql = build_insert($this->tbl_templates_of_letter, $fields);
    	if (!$database->query($sql)) return $database->get_error();
    	return true;
    }

    function get_letters($sets=[]) {
		global $database, $sql_builder;
		if (isset($sets['letter_id'])) $letter_id = get_number($sets['letter_id']); else $letter_id = null;

        $sql_builder->clear();

        $sql_builder->add_raw_where("1=1");
        if ($letter_id !== null) $sql_builder->add_raw_where("`sended_letter_id`=$letter_id");

		$sql = "SELECT * FROM {$this->tbl_templates_of_letter_sended} WHERE ".$sql_builder->build_where()." ORDER BY `date` DESC";
		$result = $database->query($sql);
		if ($database->is_error()) return 'email->get_templates: '.$database->get_error();
		if ($result->numRows() == 0) return null;
		return $result;
    }


    function send($to, $body, $subject, $template_id=0, $save_backup=true) {
        if ($save_backup) {
            // сохраняем копию письма
            $admin = new admin('Start', '', false, false);
       	    if ($admin->is_authenticated()) $sender_user_id = $admin->get_user_id();
       	    else $sender_user_id = null;
       	    // константу PAGE_ID необходимо объявлять вручную из API.php
       	    if (defined('PAGE_ID')) $send_from_page_id = PAGE_ID;
       	    else $send_from_page_id = null;
            $backuped_letter_id = $this->backup_letter($to, $body, $subject, $template_id, $sender_user_id, $send_from_page_id);
            if (gettype($backuped_letter_id) == 'string') return $backuped_letter_id;

            // отправляем письмо
	    $r = $this->_send($to, $body, $subject);
	    if ($r !== true) return $r;

            // помечаем копию как отправленная
	    $r = $this->backup_is_sended($backuped_letter_id);
	 } else {
            // отправляем письмо
            $r = $this->_send($to, $body, $subject);
            $backuped_letter_id = null;
	 }

	return [$r, $backuped_letter_id];
    }
	
    function send_template($to, $template_name, $vars, $save_backup=true) {
        $r = $this->get_templates(['letter_name'=>$template_name]);
	if (gettype($r) === 'string') return $r;
	if ($r === null) return 'Шаблон письма не найден';
	$r = $r->fetchRow();

	$_vars = [];
	foreach ($vars as $name => $value) {
		$_vars['{{'.strtoupper($name).'}}'] = $value;
	}
	$body = str_replace(array_keys($_vars), array_values($_vars), $r['letter_template_body']);

        // отправляем письмо
	return $this->send($to, $body, $r['letter_template_subject'], $r['letter_template_id'], $save_backup);
    }
	
}

?>