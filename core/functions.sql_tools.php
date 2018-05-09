<?php

/**
* @author: Polyakov Konstantin <shyzik93@mail.ru>
* @licenece: GNU General Public Licence
* @date: 2016-2017
* 
* 2017-02-07 - Добавлены функции glue_fields, check_insert, check_delete. Функции prepare2update, prepare2select не рекомендованы 
* 2017-04-11 - Функция prepare2insert нерекомендована
* 2017-04-12 - добавлена константа загрузки модуля.
*/

// функции, которые должны лечь в основу класса, унаследованного от database

/**
* Объект "значение":
*     [
*         'type'=> 'string'
*         'value'=> $strValue || null || $intValue
*
*         'type'=>'function'
*         'value'=> $strSQLFunctionName
*     ]
* 
* Объект "Имя таблицы":
*     [
*         'name' => $strName
*         'alias' => $strAliasName
*     ]
*/

define('SQL_TOOLS_MODULE_LOADED', true);

function process_value($value, $type_value='string') {
        global $database;
        if (gettype($value) == 'array') {
                $type_value = isset($value['type']) ? $value['type'] : 'string';
                $value = isset($value['value']) ? $value['value'] : null;
        }
        
        if ($type_value === 'string') {
                if (gettype($value) == 'string') return '"'.$database->escapeString($value).'"';
                else if ($value === null) return 'NULL';
                else return '"'.$value.'"'; // numbers (float or integer)
        } else if ($type_value == 'function') {
                return $value;
        }
}

function process_key($key) {
        if (gettype($key) == 'number') return (string)$key;
        if (substr($key, 0, 1) != '`') $key = "`{$key}`";
        return $key;
}

function process_table($arrTable) {
    if (gettype($arrTable)=='string')  return $arrTable;

    $arrTable['name'] = process_key($arrTable['name']);
        
    if (isset($arrTable['alias'])) return $arrTable['name']." AS ".$arrTable['alias'];
    return $arrTable['name'];

}

function process_tables($arrTables) {
        if (gettype($arrTables) == 'string') return $arrTables;
        foreach($arrTables as $i => $arrTable) $arrTables[$i] = process_table($arrTable);
    return implode(',', $arrTables);
}

function process_where($where) {
    if ($where === null || $where === '') $where = "1=1";
    return $where;

}

/** 
* 
* Склеивает имена через запятую. Ключами могут быть как строки, так и числа.
* Строки заключаются в одинарные наклонные кавычки
* 
* @param arra $keys ['key1', 'key2', 'key3']
*/
function glue_keys($keys) {
        if (gettype($keys) == 'string') $keys = [$keys];
        foreach($keys as $i => $key) {
                $keys[$i] = process_key($key);
        }
        return implode(',', $keys);
}
/** 
* @param arra $values ['value1', 'value2', 'value3']
*/
function glue_values($values) {
        if (gettype($values) == 'string') $values = [$values];
        foreach($values as $i => $value) {
                $values[$i] = process_value($value);
        }
        return implode(',', $values);
}

/** 
* @param arra $fields ['key1'=>value1', 'key2'=>['value2', 'value4'], 'key3'=>'value3']
*/
function glue_fields($fields, $sep, $sep2='=') {
    $_fields = array();
    foreach ($fields as $key => $value) {
        if (gettype($value) == 'array') $value = "(".glue_value($value).")";
        else $value = process_value($value);
        $key = process_key($key);
        $_fields[] = $key.' '.$sep2.' '.$value;
    }
    return implode($sep, $_fields);
}

//$condition = ['name'=>'name1', 'value'=>['value'=>'value1', 'type'=>'function'], 'operator'=>'='];
//$condition = ['name'=>'name1', 'value'=>[['value'=>'value1', 'type'=>'function'],['value'=>'value1', 'type'=>'function']], 'operator'=>'in'];
function glue_condition($condition) {
        $key = "`{$condition['key']}`";
        $operator = $condition['operator'];
        if (in_array($condition['operator'], ['=', '!=', '>', '<'])) {
                $value = process_value($condition['value']);
        } else if (in_array($condition['operator'], ['in', 'not in'])) {
                $value = "(".glue_values($condition['value']).")";
        }
        
        return $key." ".$operator." ".$value;
}

// не рекомендавана. Используйте glue_keys(array_keys($fields)) и glue_values(array_values($fields))
//function prepare2insert($fields) {

// не рекомендавана. Используйте glue_fields($fields, ',')
//function prepare2update($fields) {

// не рекомендавана. Используйте glue_fields($fields, ' AND ')
//function prepare2select($fields) {

/* ----------- Промежуточный уровень: построение запроса ----------- */ 

function build_order($keys=null, $direction=null) {
        if ($keys === null) return '';

        if (!in_array($direction, ['ASC', 'DESC'])) $direction = '';

        return " ORDER BY ".glue_keys($keys)." $direction ";
}

function build_limit($offset=null, $count=null) {
    if (gettype($offset) === 'string') $offset = (integer)(preg_replace('/[^0-9]/', '', $offset));
    else if (gettype($offset) !== 'integer') $offset = null;

    if (gettype($count) === 'string') $count = (integer)(preg_replace('/[^0-9]/', '', $count));
    else if (gettype($count) !== 'integer') $count = null;
        
    if ($offset === null && $count === null) return '';

    if ($offset === null) $offset = 0;
    if ($count === null) $count = 0;

    return " LIMIT $offset,$count ";
}

function build_update($table, $fields, $where=null) {
    $fields = glue_fields($fields, ',');
    $table = process_tables($table);
    $where = process_where($where);
    $sql = "UPDATE $table SET $fields WHERE $where";
    return $sql;
}

/**
* @param string $table Name of table
* @param mixed $keys Array of field names or raw string
*/
function build_select($table, $keys, $where=null) {
    if (gettype($keys) == 'array') $keys = glue_keys($keys);
    $table = process_tables($table);
    $where = process_where($where);
    $sql = "SELECT $keys FROM $table WHERE $where";
    return $sql;
    
}

function build_delete($table, $where=null) {
    $table = process_tables($table);
    $where = process_where($where);
    $sql = "DELETE FROM $table WHERE $where";
    return $sql;
}

/** Variant 1:
*  array  $fields ['name1'=>'value1', name2=>'value2', name2=>'value3']
*  Variant 2:
*  array  $fields ['name1', name2, name2]
* array $value_lines [['value1', 'value2', 'value3'], ['value1', 'value2', 'value3']]
*/
function build_insert($table, $fields, $value_lines=false) {
        $table = process_tables($table);
    if ($value_lines) {
        $keys = glue_keys($fields);
                $_value_lines = [];
        foreach($value_lines as $values) {
                $_value_lines[] = "(".glue_values($values).")";
        }
        $value_lines = implode(',', $_value_lines);
    } else {
                //$fields = prepare2insert($fields);
                //$keys = $fields['keys'];
                //$value_lines = "(".$fields['values'].")";
                $keys = glue_keys(array_keys($fields));
                $value_lines = "(".glue_values(array_values($fields)).")";
    }
        return "INSERT INTO $table ($keys) VALUES $value_lines";    
}

/* ----------- Промежуточный уровень: проверяем результат ----------- */

function db_get_err($sql, $type) {
    global $database;
        $err = "{$type}_row() '$sql' :: ".$database->get_error();
    error_log($err);
    return $err;
}

function check_update($sql) {
        global $database;

        if ($database->query($sql)) return true;
        //if ($database->is_error()) return "update_row() '$sql' :: ".$database->get_error();
        if ($database->is_error()) return db_get_err($sql, 'update');

        return false;
}
function check_insert($sql) { 
        global $database;

        if ($database->query($sql)) return true;
        //if ($database->is_error()) return "update_row() '$sql' :: ".$database->get_error();
        if ($database->is_error()) return db_get_err($sql, 'insert');

        return false;
}
function check_delete($sql) {
        global $database;

        if ($database->query($sql)) return true;
        //if ($database->is_error()) return "update_row() '$sql' :: ".$database->get_error();
        if ($database->is_error()) return db_get_err($sql, 'delete');

        return false;
}

/**
* 
*/
function check_select($sql) {
        global $database;

        $r = $database->query($sql);
        //if ($database->is_error()) return "select_rows() '$sql' :: ".$database->get_error();
        if ($database->is_error()) { return db_get_err($sql, 'select'); }
        if ($r->numRows() == 0) return null;
        return $r;
}

/* ----------- Высший уровень: строим, делаем запрос, проверяем результат ----------- */ 

function update_row($table, $fields, $where=null) {
    global $database;
    $sql = build_update($table, $fields, $where);
    return check_update($sql);
}

function delete_row($table, $where=null) {
    global $database;
    $sql = build_delete($table, $where);
    return check_delete($sql);
}

function select_rows($table, $keys, $where=null) {
    global $database;
    $sql = build_select($table, $keys, $where);
    return check_select($sql);
}
function select_row($table, $keys, $where=null) { return select_rows($table, $keys, $where); }

function insert_rows($table, $fields, $value_lines=false) {
    global $database;
    $sql = build_insert($table, $fields, $value_lines);
    return check_insert($sql);
}
function insert_row($table, $fields, $value_lines=false) { return insert_rows($table, $fields, $value_lines); }

/* ----------- Комбинация запросов ----------- */

function insert_row_uniq($table, $fields, $keys_uniq=false, $key_ret=false) {
    global $database;

    if ($keys_uniq === false) $keys_uniq = array_keys($fields);
    if (gettype($keys_uniq) === 'string') $keys_uniq = [$keys_uniq];

    $select = $key_ret === false ? $keys_uniq : array_merge($keys_uniq, [$key_ret]);

    $where = [];
    foreach($keys_uniq as $key) $where[$key] = $fields[str_replace('`', '', $key)];

    $r = select_row($table, glue_keys($select), glue_fields($where, ' AND '));
    if (gettype($r) === 'string') return [$r, null];
    else if ($r === null) {
        $r = insert_row($table, $fields);
        if (gettype($r) === 'string') return [$r, null];
        return [(integer)($database->getLastInsertId()), true];
    }
    
    if ($key_ret !== false) {
        $fields = $r->fetchRow();
        return [(integer)($fields[$key_ret]), false];
    }
}

/**
* 
* mixed $table - таблица или список таблиц
* array $fields - поля и значения для вставки. Включая те, для которых в базе указаны значения по умолчанию. Кроме двух нижеуказанных полей
* mixed $keys_uniq - поля, которые должныв быть уникальными. Если false, то будут использованы ключи $fields 
* string $key_ret - поле с автоинкрементом
* 
* У таблицы обязательно должны быть поле с автоинкрементом ($key_ret) и поле `is_deleted`
**/
function insert_row_uniq_deletable($table, $fields, $keys_uniq, $key_ret) {
    global $database;

    // проверяем наличие дубля

    if ($keys_uniq !== null) {
        if ($keys_uniq === false) $keys_uniq = array_keys($fields);
        if (gettype($keys_uniq) === 'string') $keys_uniq = [$keys_uniq];

        $where = ["`is_deleted`"=>'0'];
        foreach($keys_uniq as $key) $where[$key] = $fields[$key];

        $r = select_row($table, process_key($key_ret), glue_fields($where, ' AND ')." LIMIT 1");
        if (gettype($r) === 'string') return $r;
        else if ($r !== null) return 'Уже существует!';
    }

    // Если дублей нет, то проверяем наличие удалённых записей

    $r = select_row($table, process_key($key_ret), "`is_deleted`=1 LIMIT 1");
    if (gettype($r) === 'string') return $r;

    if ($r === null) { // если нет удалённых записей, то вставляем новую
        
        $r = insert_row($table, $fields);
        if (gettype($r) === 'string') return $r;
        $id = $database->getLastInsertId();

    } else { // если есть удалённые, то обновляем

        $id = $r->fetchRow()[$key_ret];
        $fields['is_deleted'] = '0';
        
        $r = update_row($table, $fields, process_key($key_ret)."=".process_value($id));
        if (gettype($r) === 'string') return $r;

    }
    
    return (integer)$id;
}

/* функции для конструирования функций-извлектаелей данных */

function getobj_order_limit($sets, $glue=true) {
    $order = build_order($sets['order_by'] ?? null, $sets['order_dir'] ?? null);

    if (isset($sets['limit_offset'])) $limit_offset = (integer)($sets['limit_offset']); else $limit_offset = null;
    if (isset($sets['limit_count'])) $limit_count = (integer)($sets['limit_count']); else $limit_count = null;
    $limit = build_limit($limit_offset, $limit_count);
    
    return $glue ? $order.' '.$limit : [$order, $limit];
}

function getobj_return($sql, $only_count) {
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

function getobj_search($sets, $keys) {
    global $database;
    
    if (!isset($sets['find_str']) || $sets['find_str'] === null) return null;

    $s = str_replace('%', '\%', $database->escapeString($sets['find_str']));
    $s_in = isset($sets['find_in']) && $sets['find_in'] ? explode(',', $sets['find_in']) : [];
    if (count($s_in) === 0) $s_in = array_keys($keys);

    $where_find = [];
    foreach($s_in as $i => $key) {
        if (!isset($keys[$key])) continue;
        $where_find[] = $keys[$key]." LIKE '%$s%'";
    }
    
    return '('.implode(' OR ', $where_find).')';
}

/*
 * array $tables - tables
 * array $where - strings of where expressions (they will be concatenate with 'AND')
 * assoc array $where_opts - 
*/
function get_obj($tables, $where, $where_opts, $where_find=[], $sets=[], $only_count=false) {
        global $database;
        
        $where_find = getobj_search($sets, $where_find);
        if ($where_find) $where[] = $where_find;
        
        foreach($where_opts as $opt=>$key) {
                if (isset($sets[$opt])) $where[] = $key."=".process_value($sets[$opt]);
        }

        $select = $only_count ? "COUNT(*) AS count" : "*";
        $tables = implode(',', $tables);
        $where = implode(' AND ', $where);
        $order_limit = getobj_order_limit($sets);
        
        $sql = "SELECT $select FROM $tables WHERE $where $order_limit";
        
        //echo "<script>console.log(`".htmlentities($sql)."`);</script>";
        
        return getobj_return($sql, $only_count);
}

?>