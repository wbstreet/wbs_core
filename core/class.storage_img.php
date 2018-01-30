<?php
/*
Author: Polyakov Konstantin
Date: 2018-01-30
*/


#http://hilocomod.blogspot.ru/2010/03/linux.html 

class WbsStorageImg() {
    function __construct() {
        $this->tbl_img = "`".TABLE_PREFIX."mod_wbs_core_img`";
        
        $this->limits = [
            'format'=>'jpg,png',
            'maxsize'=>2048, // Kb
            'minsize'='0' // Kb
        ],
    }
    
    function transform_size() {
    }
    
    function get($id, $size='origin') {
        return $path;
    }
   
    function save($tmp_path, $limits=null) {
    
        if ($limits === null) $limits = $this->limits;
        // здесь бы дополнить пользовательский массив массивом по умолчанию ( limits.update(this->limits) )
        
        if (!file_exists($tmp_path)) return 'Файл не существует!';
        $info_tmp_path = pathinfo($tmp_path);

        return (integer)$id;
    }
    
    function save_many($tmp_paths, $limits=null) {
        $ids = [];
        $errors = [];
        
        foreach($tmp_paths as $i => $tmp_path) {
            $r = $this->save($tmp_path, $limits);
            if (gettype($r) !== 'string') $errors[$tmp_path] = $r;
            else $id[$tmp_path] = $r;
        }

        return [$ids, $errors];
    }
}

?>