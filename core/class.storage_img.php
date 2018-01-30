<?php
/*
Author: Polyakov Konstantin
Date: 2018-01-30
*/


#http://hilocomod.blogspot.ru/2010/03/linux.html
# http://php.net/manual/en/image.constants.php

class WbsStorageImg() {
    function __construct() {
        $this->tbl_img = "`".TABLE_PREFIX."mod_wbs_core_img`";
        
        $this->aLimits = [
            'exts'=>['jpg', 'png'],
            'maxsize'=>2*1024, // Kb
            'minsize'='0' // Kb
        ],

        $this->path = WB_PATH."/media/wbs_core/storage_img";
    }

    function get_img_path($sSize, $md5, $ext) {
        $sDir = $this->path."/{$sSize}/".substr($md5, 0, 3)."/".substr($md5, 4, 3);
        make_dir($sDir);
        return $sDir."/".substr($md5, 7).".".$ext;
    }
    
    function transform_size($sOldPath, $aSize, $sNewPath=null) {
        $sNewPath = $sNewPath == null ? $sOldPath: $sNewPath;
        list($w, $h) = $aSize;

        $image = new Imagick($image_name);
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();

        if ($width / $height >= $w/$h) { $image->thumbnailImage(0, $h);}
        else {$image->thumbnailImage($w, 0);}

        $image->cropImage($w, $h, 0, 0);
        $image->writeImage($sNewPath);
    }

    function get($iId, $sSize='origin') {
        global $database;

        // Вынимаем информацию о картинке
        
        $r = $database->query("SELECT * FROM {$this->tbl_img} WHERE `img_id`=".process_value($id));
        if ($database->is_error()) return $database->get_error();
        if ($r->numRows() === 0) return "Изображение не найдено!";
        $aImg = $r->fetchRow();

        // Формируем путь к изображению
        
        $sPath = $this->get_img_path($sSize, $aImg['md5'], $aImg['ext']);

        // Проверяем существование изображения
        
        if ($sSize === 'origin') {
        
            if (!file_exists($sPath)) return "Изображение не найдено!"; // вернуть путь к картинке "Ошибка сервера: картинка не найдена"

        } else {

            if (!file_exists($sPath)) {
            
                // трансформируем картинку
                $this->transform_size($this->get_img_path('origin', $aImg['md5'], $aImg['ext']), explode('x', $sSize), $sPath);
                if (!file_exists($sPath)) return "Изображение не найдено!";
            }
        }

        return $sPath;
        
    }
   
    function save($sTmpPath, $aLimits=null) {
    
        if ($aLimits === null) $aLimits = $this->aLimits;
        // здесь бы дополнить пользовательский массив массивом по умолчанию ( limits.update(this->limits) )
        
        if (!file_exists($sTmpPath)) return 'Изображение не существует!';
        //$aTmpPath = pathinfo($sTmpPath);

        // определяем некоторые характеристики изображения
        
        $md5 = md5($sTmpPath);
        
        $aImgType = getimagesize($sTmpPath, false);
        $ext = image_type_to_extension($aImgType[2]);
        
        // проверяем изображение на соответсвие правилам
        
        if (!in_array($ext, $aLimits['exts'])) return "Изображение имеет неразрешённый формат";
        $size = filesize($sTmpPath);
        if ($size === false || $size > $aLimits['maxsize']) return "Изображение имеет недопустимый размер - до {$aLimits['maxsize']} Kb!";
        if ($size === false || $size < $aLimits['minsize']) return "Изображение имеет недопустимый размер!";
        
        // проверяем на наличие такого же изображения
        
        $r = $database->query("SELECT * FROM {$this->tbl_img} WHERE `md5`=".process_value($md5));
        if ($database->is_error()) return $database->get_error();
        if ($r->numRows() > 0) {
            $aImg = $r->fetchRow();
            return (integer)$aImg['img_id'];
        }

        // перемещаем изображение
        
        $sPath = $this->get_img_path('origin', $md5, $ext])
        if (!move_uploaded_file($sTmpPath, $sPath)) return "Не удалось переместить файл!"

        // добавляем запись в базу
        
        $r = insert_row($this->tbl_img, [
            'md5'=>$md5,
            'ext'=>$ext,
            'user_id'=$admin->get_user_id()
        ]);
        if ($r !== true) return $r;

        return $database->getLastInsertId();
    }
    
    function save_many($aTmpPaths, $aLimits=null) {
        $aIds = [];
        $aErrors = [];
        
        foreach($aTmpPaths as $i => $sTmpPath) {
            $r = $this->save($sTmpPath, $aLimits);
            if (gettype($r) !== 'string') $aErrors[$sTmpPath] = $r;
            else $aIds[$sTmpPath] = $r;
        }

        return [$aIds, $aErrors];
    }
}

?>