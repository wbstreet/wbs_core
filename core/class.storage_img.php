<?php
/*
Author: Polyakov Konstantin
Date: 2018-01-30
*/


#http://hilocomod.blogspot.ru/2010/03/linux.html
# http://php.net/manual/en/image.constants.php

class WbsStorageImg {
    function __construct() {
        $this->tbl_img = "`".TABLE_PREFIX."mod_wbs_core_img`";
        
        $this->aLimits = [
            'exts'=>['jpeg', 'jpg', 'png'],
            'maxsize'=>2*1024, // Kb
            'minsize'=>'0' // Kb
        ];

        $this->path = WB_PATH."/media/mod_wbs_core/storage_img";
    }
    
    function get_default($sSize) {
        $sPath = $this->path."/{$sSize}";
        make_dir($sPath);
        $sPath = $sPath."/default.png";
        
        if ($sSize !== 'origin' && !file_exists($sPath)) {
            if (file_exists($this->path."/origin/default.png")) $this->transform_size($this->path."/origin/default.png", explode('x', $sSize), $sPath);
        }
        
        return str_replace(WB_PATH, WB_URL, $sPath);
    }

    function get_img_path($sSize, $md5, $ext) {
        $sDir = $this->path."/{$sSize}/".substr($md5, 0, 3)."/".substr($md5, 4, 3);
        make_dir($sDir);
        return $sDir."/".substr($md5, 7).".".$ext;
    }
    
    function transform_size($sOldPath, $aSize, $sNewPath=null) {
        $sNewPath = $sNewPath == null ? $sOldPath: $sNewPath;
        list($w, $h) = $aSize;

        /*$image = new Imagick($sOldPath);
        
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();

        if ($width / $height >= $w/$h) { $image->thumbnailImage(0, $h);}
        else {$image->thumbnailImage($w, 0);}

        $image->cropImage($w, $h, 0, 0);
        $image->writeImage($sNewPath);*/

        $image = new Imagick($sOldPath);
        $image = $image->coalesceImages(); 
        
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();

        foreach ($image as $frame) {
            if ($width / $height >= $w/$h) { $frame->thumbnailImage(0, $h);}
            else {$frame->thumbnailImage($w, 0);}
            
            $frame->setImagePage($w, $h, 0, 0); 
            $frame->cropImage($w, $h, 0, 0);
        }
        $image = $image->deconstructImages();
        $image->stripImage(); // удаляем exif
        $image->writeImages($sNewPath, true);

    }

    function get($iId, $sSize='origin') {
        global $database;

        if ($iId === null || $iId === '') return $this->get_default($sSize);

        // Вынимаем информацию о картинке
        
        $r = $database->query("SELECT * FROM {$this->tbl_img} WHERE `img_id`=".process_value($iId));
        if ($database->is_error()) return $this->get_default($sSize);
        if ($r->numRows() === 0) $this->get_default($sSize);
        $aImg = $r->fetchRow();

        // Формируем путь к изображению
        
        $sPath = $this->get_img_path($sSize, $aImg['md5'], $aImg['ext']);

        // Проверяем существование изображения
        
        if ($sSize === 'origin') {
        
            if (!file_exists($sPath)) return $this->get_default($sSize);

        } else {

            if (!file_exists($sPath)) {
            
                // трансформируем картинку
                $this->transform_size($this->get_img_path('origin', $aImg['md5'], $aImg['ext']), explode('x', $sSize), $sPath);
                if (!file_exists($sPath)) return $this->get_default($sSize);
            }
        }

        return str_replace(WB_PATH, WB_URL, $sPath);
        
    }

    function get_without_db($sMd5, $sExt, $sSize='origin') {
        global $database;

        // Формируем путь к изображению
        
        $sPath = $this->get_img_path($sSize, $sMd5, $sExt);

        // Проверяем существование изображения
        
        if ($sSize === 'origin') {
        
            if (!file_exists($sPath)) return $this->get_default($sSize);

        } else {

            if (!file_exists($sPath)) {
            
                // трансформируем картинку
                $this->transform_size($this->get_img_path('origin', $sMd5, $sExt), explode('x', $sSize), $sPath);
                if (!file_exists($sPath)) return $this->get_default($sSize);
            }
        }

        return str_replace(WB_PATH, WB_URL, $sPath);
        
    }

    function save($sTmpPath, $aLimits=null) {
        global $database, $admin;
    
        if ($aLimits === null) $aLimits = $this->aLimits;
        else $aLimits = array_merge($this->aLimits, $aLimits);
        
        if (!file_exists($sTmpPath)) return 'Изображение не существует!';
        //$aTmpPath = pathinfo($sTmpPath);

        // определяем некоторые характеристики изображения
        
        $md5 = md5_file($sTmpPath);
        
        $aImgType = getimagesize($sTmpPath);
        $ext = image_type_to_extension($aImgType[2], false);
        
        // проверяем изображение на соответсвие правилам
        
        if (!in_array($ext, $aLimits['exts'])) return "Изображение имеет неразрешённый формат: {$ext}";
        $size = (int)(filesize($sTmpPath) / 1024);
        if ($size === false || $size > $aLimits['maxsize']) return "Изображение имеет недопустимый размер - $size Kb. Разрешено до {$aLimits['maxsize']} Kb!";
        if ($size === false || $size < $aLimits['minsize']) return "Изображение имеет недопустимый размер!";
        
        // проверяем на наличие такого же изображения
        
        $r = $database->query("SELECT * FROM {$this->tbl_img} WHERE `md5`=".process_value($md5));
        if ($database->is_error()) return $database->get_error();
        if ($r->numRows() > 0) {
            $aImg = $r->fetchRow();
            return (integer)$aImg['img_id'];
        }

        // добавляем запись в базу
        
        $r = insert_row($this->tbl_img, [
            'md5'=>$md5,
            'ext'=>$ext,
            'user_id'=>$admin->get_user_id()
        ]);
        if ($r !== true) return $r;

        // перемещаем изображение
        
        $sPath = $this->get_img_path('origin', $md5, $ext);
        if (!move_uploaded_file($sTmpPath, $sPath)) return "Не удалось переместить файл!";
        
        return $database->getLastInsertId();
    }
    
    function save_many($aTmpPaths, $aLimits=null) {
        $aIds = [];
        $aErrors = [];
        
        foreach($aTmpPaths as $i => $sTmpPath) {
            $r = $this->save($sTmpPath, $aLimits);
            if (gettype($r) !== 'string') $aIds[$sTmpPath] = $r;
            else $aErrors[$sTmpPath] = $r;
        }

        return [$aIds, $aErrors];
    }
}

?>