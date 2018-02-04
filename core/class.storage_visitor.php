<?php

class WbsStorageVisitor {

    function __construct() {
        $this->tbl_visitors = "`".TABLE_PREFIX."mod_wbs_core_visitor`";
        $this->tbl_visitor_browser = "`".TABLE_PREFIX."mod_wbs_core_visitor_browser`";
        $this->tbl_visitor_refer = "`".TABLE_PREFIX."mod_wbs_core_visitor_refer`";
    }

    function write_browser($browser_name, $is_bot=0) {
        $fields = ['browser_name'=>$browser_name, 'browser_is_bot'=>$is_bot];
        return insert_row($this->tbl_visitor_browser, $fields);
    }

    function get_browser($fields) {
        return select_row($this->tbl_visitor_browser, "*",  glue_fields($fields, ' AND '));
    }

    function browser2id($name) {
        $r = $this->get_browser(['browser_name'=>$name]);
        if (gettype($r) == 'string') return $r;

        if ($r === null) {

            $r = $this->write_browser($name);
            if (gettype($r) === 'string') return $r;

            $r = $this->get_browser(['browser_name'=>$name]);
            if (gettype($r) === 'string') return $r;
            if ($r === null) return 'Ваш браузер не найден';
        }
        
        $r = $r->fetchRow()['browser_id'];
        return (int)$r;
    }

    function write_refer($refer_url) {
        $fields = ['refer_url'=>$refer_url];
        return insert_row($this->tbl_visitor_refer, $fields);
    }

    function get_refer($fields) {
        return select_row($this->tbl_visitor_refer, "*", glue_fields($fields, ' AND '));
    }

    function refer2id($url) {
        $r = $this->get_refer(['refer_url'=>$url]);
        if (gettype($r) == 'string') return "1. ".$r;

        if ($r === null) {

            $r = $this->write_refer($url);
            if (gettype($r) == 'string') return "2. ".$r;

            $r = $this->get_refer(['refer_url'=>$url]);
            if (gettype($r) == 'string') return "3. ".$r;
            if ($r === null) return 'Ваш рефер не найден';
        }
        
        $r = $r->fetchRow()['refer_id'];
        return (int)$r;
    }
        
    function write() {
        global $database, $admin;
        //SERVER_PROTOCOL
        //REQUEST_METHOD

        // Опрелделяем пользователя 
        if (isset($admin) && $admin !== null && $admin->is_authenticated()) $user_id = $admin->get_user_id();
        else $user_id = null;
        
        // сохраняем браузер

        $browser_id = $this->browser2id($_SERVER['HTTP_USER_AGENT']);
        if (gettype($browser_id) == 'string') { return $browser_id."- ";}

        // сохраняем рефера

        //$referer = defined(ORG_REFERER) ? (ORG_REFERER !== null ? ORG_REFERER: '') : ($_SERVER['HTTP_REFERER'] ?? '');
        $referer = ORG_REFERER !== null ? ORG_REFERER: '';
        list($referer, $is_error) = idn_decode($referer);
        $refer_id = $this->refer2id($referer);
        if (gettype($refer_id) == 'string') { return " -".$refer_id;}

        // добавляем основную запись        
        $fields = [
            'page_id'=>PAGE_ID,
            'refer'=>$refer_id,
            'browser'=>$browser_id,
            'ip'=>$_SERVER['REMOTE_ADDR'],
        ];
        if ($user_id !== null) $fields['user_id'] = $user_id;
        return insert_row($this->tbl_visitors, $fields);
    }

    function get_count($sets) {
        global $database, $sql_builder;
        /*$page_id = isset($sets['page_id']) ? $sets['page_id'] : null; // NULL или число
        $user_id = isset($sets['user_id']) ? $sets['user_id'] : null; // NULL или число

        $sql_builder->clear();

        if ( $page_id !== null ) $sql_builder->add_raw_where("{$this->tbl_visitors}.`page_id`={$page_id} ");
        if ( $user_id !== null ) $sql_builder->add_raw_where("{$this->tbl_visitors}.`user_id`={$page_id} ");
        $sql_builder->add_raw_where("`date` > CURDATE()");
        
        $sql = "SELECT DISTINCT(ip), `browser` FROM `rf_mod2_visitors` WHERE ".$sql_builder->build_where();;
        $r = $database->query($sql);
        if ($database->is_error()) return $database->get_error();
        
        return $r;
        */
                
    } 
}

// INSERT IGNORE INTO `rf_mod2_visitor_refer` (`refer_url`) SELECT `refer` FROM `rf_mod2_visitors`
// UPDATE `rf_mod2_visitors` SET `refer`=(SELECT `refer_id` FROM `rf_mod2_visitor_refer` WHERE `rf_mod2_visitor_refer`.`refer_url`=`rf_mod2_visitors`.`refer`)

// UPDATE `rf_mod2_visitors` SET `browser`=(SELECT `browser_id` FROM `rf_mod2_visitor_browser` WHERE `rf_mod2_visitor_browser`.`browser_name`=`rf_mod2_visitors`.`browser`)

/*

<?php
    include(WB_PATH.'/modules/wbs_core/include_all.php');
    $r = $clsStorageVisitor->write();
    if ($r !== true) echo $r;
?>

*/

?>