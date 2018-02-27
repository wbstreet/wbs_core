<?php

class Addon {
    function __construct($name, $page_id, $section_id) {
        $this->name = $name;
        $this->section_id = $section_id;
        $this->page_id = $page_id;

        $this->urlRet = ADMIN_URL.'/pages/modify.php?page_id='.$this->page_id."#wb_".$this->section_id;
        $this->urlMod = WB_URL."/modules/{$this->name}/";
        $this->pathMod = WB_PATH."/modules/{$this->name}/";
        $this->urlMedia = WB_URL.MEDIA_DIRECTORY."/mod_{$this->name}/";
        $this->pathMedia = WB_PATH.MEDIA_DIRECTORY."/mod_{$this->name}/";
        $this->pathTemplates = WB_PATH."/modules/{$this->name}/templates/";
        $this->urlAPI = WB_URL."/modules/{$this->name}/api.php";
        if (! is_dir($this->pathMedia)) {mkdir($this->pathMedia, 0777, true);}

        $loader = new Twig_Loader_Filesystem([$this->pathTemplates, WB_PATH.'/modules/wbs_core/templates/']);
        $this->_twig = new Twig_Environment($loader);
        
    }
    
    function getUrlAction($action) {
        return WB_URL."/modules/{$this->name}/save.php?action={$action}&section_id={$this->section_id}&page_id={$this->page_id}";
    }
   
    function print_error($message, $options=[]) {

        if (!isset($options['format'])) $options['format'] = 'js';

        $message = "Модуль: {$this->name}; page_id: {$this->page_id}; section_id: {$this->section_id}\n".$message;

        print_error(htmlentities($message, ENT_QUOTES), $options);
    }
    
    function install() {
    }

    function uninstall() {
    }
    
    function _import_sql($filepath, $name) {
        global $database;
        // create tables from sql dump file
        $dirpath = dirname($filepath);
        $filename = $dirpath.'/install-'.$name.'.sql';
        if (file_exists($filename) && is_readable($filename)) {
            $r = $database->SqlImport($filename, TABLE_PREFIX, $filepath);
            if ($database->is_error()) {
                return $database->get_error();
            }
            return true;
        }
    }

    function render($file_name, $fields, $is_ret=false) {
        $fields = array_merge($fields, [
            'url_api'=>"url:'{$this->urlAPI}'",
            'wb_url'=>WB_URL,
        ]);

        $res = $this->_twig->render($file_name, $fields);

        if ($is_ret) return $res;
        echo $res;
    }

    function _getobj_order_limit($sets, $glue=true) {
        $order = build_order($sets['order_by'] ?? null, $sets['order_dir'] ?? null);

        if (isset($sets['limit_offset'])) $limit_offset = (integer)($sets['limit_offset']); else $limit_offset = null;
        if (isset($sets['limit_count'])) $limit_count = (integer)($sets['limit_count']); else $limit_count = null;
        $limit = build_limit($limit_offset, $limit_count);
        
        return $glue ? $order.' '.$limit : [$order, $limit];
    }

    function _getobj_return($sql, $only_count) {
        global $database;

        $r = $database->query($sql);
        if ($database->is_error()) return $database->get_error();

        if ($only_count) {
            $count = $r->fetchRow()['count'];
            return (integer)$count;
        } else {
            if ($r->numRows() === 0) return null;
            return $r;
        }

    }
    
}

?>