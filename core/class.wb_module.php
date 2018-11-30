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
        
        $this->twig = new WbsTwig($this->pathTemplates, [
            'url_api'=>"url:'{$this->urlAPI}'",
            'wb_url'=>WB_URL,
            'section_id'=>$this->section_id,

            'page_id'=>$this->page_id,
        ]);

        #$loader = new Twig_Loader_Filesystem([$this->pathTemplates, WB_PATH.'/modules/wbs_core/templates/']);

        #$this->loader_chain = new Twig_Loader_Chain([$loader]);

        #$this->_twig = new Twig_Environment($this->loader_chain);
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

    /* Functions for Twig */
    
    function render($file_name, $fields, $is_ret=false) {
        return $this->twig->render($file_name, $fields, $is_ret);
        /*$fields = array_merge($fields, [
            'url_api'=>"url:'{$this->urlAPI}'",
            'wb_url'=>WB_URL,
            'section_id'=>$this->section_id,

            'page_id'=>$this->page_id,
        ]);

        $res = $this->_twig->render($file_name, $fields);

        if ($is_ret) return $res;
        echo $res;*/
    }
    
    function add_loader($type, $data) {
        $this->twig->add_loader($type, $data);
        //$loader = null;
        //if ($type=="filesystem") $loader = new Twig_Loader_Filesystem($data);
        //else if ($type=="array") $loader = new Twig_Loader_Array($data);
        //$this->loader_chain->addLoader($loader);

    }

}

?>