<?php

// https://fias.nalog.ru/

class WbsStorageSettlement {

    function __construct () {
        $this->tbl_settlement =                " `".TABLE_PREFIX."mod_wbs_core_settlement`";
        $this->tbl_settlement_any_settlement = " `".TABLE_PREFIX."mod_wbs_core_settlement_any_settlement`";
        $this->tbl_settlement_rayon =          " `".TABLE_PREFIX."mod_wbs_core_settlement_rayon`";
        $this->tbl_settlement_region =         " `".TABLE_PREFIX."mod_wbs_core_settlement_region`";
        $this->tbl_settlement_country =        " `".TABLE_PREFIX."mod_wbs_core_settlement_country`";
        $this->tbl_settlement_type =           " `".TABLE_PREFIX."mod_wbs_core_settlement_type`";
        $this->_tbl_settlement =               "".TABLE_PREFIX."mod_wbs_core_settlement";
    }
    
    public function getSettlements($sets, $only_count=null) {
        global $database;

        $keys = implode(',', [
            "{$this->tbl_settlement_country}.`name` AS country_name",
            "{$this->tbl_settlement_region}.`name` AS region_name",
            "{$this->tbl_settlement_rayon}.`name` AS rayon_name",
            "{$this->tbl_settlement_any_settlement}.`name` AS settlement_name",
            "{$this->tbl_settlement_type}.`name` AS type_name, {$this->tbl_settlement_type}.`short_name` AS type_short_name",
            "{$this->tbl_settlement_country}.`country_id` AS country_id",
            "{$this->tbl_settlement_region}.`region_id` AS region_id",
            "{$this->tbl_settlement_rayon}.`rayon_id` AS rayon_id",
            "{$this->tbl_settlement_any_settlement}.`any_settlement_id` AS any_settlement_id",
            "{$this->tbl_settlement}.`settlement_id`",
        ]);

        $tables = implode(',', [
            $this->tbl_settlement_country,
            $this->tbl_settlement_region,
            $this->tbl_settlement_rayon,
            $this->tbl_settlement_any_settlement,
            $this->tbl_settlement_type,
            $this->tbl_settlement,
        ]);
        
        $where = [
            "{$this->tbl_settlement_country}.`country_id` = {$this->tbl_settlement}.`country_id`",
            "{$this->tbl_settlement_region}.`region_id` = {$this->tbl_settlement}.`region_id`",
            "{$this->tbl_settlement_rayon}.`rayon_id` = {$this->tbl_settlement}.`rayon_id`",
            "{$this->tbl_settlement_any_settlement}.`any_settlement_id` = {$this->tbl_settlement}.`any_settlement_id`",
            "{$this->tbl_settlement_type}.`type_id` = {$this->tbl_settlement}.`settlement_type_id`",
        ];

        if (isset($sets['starts_with'])) $where[] = "{$this->tbl_settlement_any_settlement}.`name` LIKE '".$database->escapeString($sets['starts_with'])."%'";
        if (isset($sets['id'])) $where[] ='`settlement_id`='.process_value($sets['id']);

        $limit = build_limit(null, $sets['limit_count']);
        $where = implode(' AND ', $where);
        $sql = "SELECT $keys FROM $tables WHERE $where ORDER BY {$this->tbl_settlement_any_settlement}.`name` $limit";
        $res = $database->query($sql);
        if ($database->is_error()) return $database->get_error();
        return $res;
    }

    public function getSettlementByNames($sets) {
        global $database;

        $tables = [$this->tbl_settlement];
        $where = ["1=1"];

        $type_name = isset($sets['type_name'])        ? process_value($sets['type_name']) : null;
        $stype_name = isset($sets['stype_name'])      ? process_value($sets['stype_name']) : null;
        $settl_name = isset($sets['settl_name'])      ? process_value($sets['settl_name']) : null;
        $rayon_name = isset($sets['rayon_name'])      ? process_value($sets['rayon_name']) : null;
        $region_name = isset($sets['region_name'])    ? process_value($sets['region_name']) : null;
        $country_name = isset($sets['country_name'])  ? process_value($sets['country_name']) : null;

        $type_id = isset($sets['type_id'])       ? process_value($sets['type_id']) : null;
        $settl_id = isset($sets['settl_id'])     ? process_value($sets['settl_id']) : null;
        $rayon_id = isset($sets['rayon_id'])     ? process_value($sets['rayon_id']) : null;
        $region_id = isset($sets['region_id'])   ? process_value($sets['region_id']) : null;
        $country_id = isset($sets['country_id']) ? process_value($sets['country_id']) : null;

        if ($country_name !== null || $country_id !== null)   $tables[] = $this->tbl_settlement_country;
        if ($region_name !== null || $region_id !== null)     $tables[] = $this->tbl_settlement_region;
        if ($rayon_name !== null || $rayon_id !== null)       $tables[] = $this->tbl_settlement_rayon;
        if ($settl_name !== null || $settl_id !== null)       $tables[] = $this->tbl_settlement_any_settlement;
        if ($type_name !== null || $stype_name !== null || $type_id !== null)       $tables[] = $this->tbl_settlement_type;

        if ($country_name !== null || $country_id !== null)    $where[] = "$this->tbl_settlement.`country_id`=          $this->tbl_settlement_country.`country_id`";
        if ($region_name !== null || $region_id !== null)      $where[] = "$this->tbl_settlement.`region_id`=           $this->tbl_settlement_region.`region_id`";
        if ($rayon_name !== null || $rayon_id !== null)        $where[] = "$this->tbl_settlement.`rayon_id`=            $this->tbl_settlement_rayon.`rayon_id`";
        if ($settl_name !== null || $settl_id !== null)        $where[] = "$this->tbl_settlement.`any_settlement_id`=   $this->tbl_settlement_any_settlement.`any_settlement_id`";
        if ($type_name !== null || $stype_name !== null || $type_id !== null)        $where[] = "$this->tbl_settlement.`settlement_type_id`=  $this->tbl_settlement_type.`type_id`";

        if ($country_name !== null)    $where[] = "$this->tbl_settlement_country.`name`='$country_name'";
        if ($region_name !== null)     $where[] = "$this->tbl_settlement_region.`name`='$region_name'";
        if ($rayon_name !== null)      $where[] = "$this->tbl_settlement_rayon.`name`='$rayon_name'";
        if ($settl_name !== null)      $where[] = "$this->tbl_settlement_any_settlement.`name`='$settl_name'";
        if ($type_name !== null)       $where[] = "$this->tbl_settlement_type.`name`='$type_name'";
        if ($stype_name !== null)      $where[] = "$this->tbl_settlement_type.`short_name`='$stype_name'";

        if ($country_id !== null)   $where[] = "$this->tbl_settlement_country.`country_id`='$country_id'";
        if ($region_id !== null)    $where[] = "$this->tbl_settlement_region.`region_id`='$region_id'";
        if ($rayon_id !== null)     $where[] = "$this->tbl_settlement_rayon.`rayon_id`='$rayon_id'";
        if ($settlt_id !== null)    $where[] = "$this->tbl_settlement_any_settlement.`any_settlement_id`='$settl_id'";
        if ($type_id !== null)      $where[] = "$this->tbl_settlement_type.`type_id`='$type_id'";

        $tables = implode(', ', $tables);
        $sql = "SELECT * FROM $tables WHERE ".implode("\n        AND ", $where);

        $r = $database->query($sql);
        if (gettype($r) == 'string') return $r;
        if ($r->numRows() == 0) return null;
        return $r;
    }

    public function getRegions($sets) {
        global $database;

        $keys = implode(', ', [
            "{$this->tbl_settlement_country}.`name` AS country_name",
            "{$this->tbl_settlement_region}.`name` AS region_name",
            "{$this->tbl_settlement_country}.`country_id` AS country_id",
            "{$this->tbl_settlement_region}.`region_id` AS region_id",
        ]);

        $tables = "{$this->tbl_settlement_country}, {$this->tbl_settlement_region}, {$this->tbl_settlement} ";

        $where = [
            "{$this->tbl_settlement_country}.`country_id` = {$this->tbl_settlement}.`country_id`",
            "{$this->tbl_settlement_region}.`region_id` = {$this->tbl_settlement}.`region_id`",
        ];

        if($sets['starts_with'] != '') $where[] = "{$this->tbl_settlement_region}.`name` LIKE '".$database->escapeString($sets['starts_with'])."%'";
        if ($sets['id'] !== null) $where[] ="{$this->tbl_settlement_region}.`region_id`=".process_value($sets['id']);

        $limit = build_limit(null, $sets['limit_count']);
        $where = implode(' AND ', $where);
        $sql = "SELECT DISTINCT $keys FROM $tables WHERE $where ORDER BY {$this->tbl_settlement_region}.`name` $limit";

        $res = $database->query($sql);
        if ($database->is_error()) return $database->get_error();        
        return $res;
    }

    /* for AJAX */

    /*public function getSuggestion($text, $count, $level) {
        $count = preg_replace("/[^0-9]+/", '', $count);
        if ($level == 'settlement') $res = $this->getSettlements(['limit_count'=>$count, 'starts_with'=>$text]);
        else if ($level == 'region') $res = $this->getRegions(null, $count, $text);
        if (gettype($res) == 'string') return $res;
        $answer = [];
        while ($r = $res->fetchRow()) {
            $answer[] = $r;
        }
        return $answer;
    }*/
    /*public function getSettlementName($id, $level) {
        $id = preg_replace("/[^0-9]+/", '', $id);
        if ($level == 'settlement') $res = $this->getSettlements(['id'=>$id]);
        else if ($level == 'region') $res = $this->getRegions($id, $count, $text);
        if (gettype($res) == 'string') return $res;
        return $res->fetchRow();
    }*/


}


?>
