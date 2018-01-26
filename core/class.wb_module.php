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
        if (! is_dir($this->pathMedia)) {mkdir($this->pathMedia, 0777, true);}
    }

    function getUrlAction($action) {
        return WB_URL."/modules/{$this->name}/save.php?action={$action}&section_id={$this->section_id}&page_id={$this->page_id}";
    }

    function print_error($message, $options=[]) {
        if (!isset($options['format'])) $options['format'] = 'js';
        $message = "Модуль: {$this->name}; page_id: {$this->page_id}; section_id: {$this->section_id}\n".$message;
        print_error(htmlentities($message, ENT_QUOTES), $options);
    }
    
}

?>