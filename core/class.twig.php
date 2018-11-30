<?php

class WbsTwig {
    function __construct($templates, $default_fields) {
        $this->pathTemplates = $templates;
        $this->default_fields = $default_fields;

        $loader = new Twig_Loader_Filesystem([$this->pathTemplates, WB_PATH.'/modules/wbs_core/templates/']);

        $this->loader_chain = new Twig_Loader_Chain([$loader]);

        $this->_twig = new Twig_Environment($this->loader_chain);
    }
    
    /* Functions for Twig */
    
    function render($file_name, $fields, $is_ret=false) {
        $fields = array_merge($fields, $this->default_fields);

        $res = $this->_twig->render($file_name, $fields);

        if ($is_ret) return $res;
        echo $res;
    }
    
    function add_loader($type, $data) {
        $loader = null;
        if ($type=="filesystem") $loader = new Twig_Loader_Filesystem($data);
        else if ($type=="array") $loader = new Twig_Loader_Array($data);
        $this->loader_chain->addLoader($loader);

    }

}

?> 
