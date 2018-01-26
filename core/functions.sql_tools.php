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
	if ($offset === '') $offset = null;
	if ($count === '') $count = null;
	
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
        	$values = "(".glue_keys($values).")";
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

function check_update($sql) {
	global $database;

	if ($database->query($sql)) return true;
	//if ($database->is_error()) return "update_row() '$sql' :: ".$database->get_error();
	if ($database->is_error()) error_log("update_row() '$sql' :: ".$database->get_error());

	return false;
}
function check_insert($sql) { 
        global $database;

        if ($database->query($sql)) return true;
        //if ($database->is_error()) return "update_row() '$sql' :: ".$database->get_error();
        if ($database->is_error()) error_log("insert_row() '$sql' :: ".$database->get_error());

        return false;
}
function check_delete($sql) {
        global $database;

        if ($database->query($sql)) return true;
        //if ($database->is_error()) return "update_row() '$sql' :: ".$database->get_error();
        if ($database->is_error()) error_log("delete_row() '$sql' :: ".$database->get_error());

        return false;
}

/**
 * 
 */
function check_select($sql) {
	global $database;

	$r = $database->query($sql);
	//if ($database->is_error()) return "select_rows() '$sql' :: ".$database->get_error();
	if ($database->is_error()) { error_log("select_rows() '$sql' :: ".$database->get_error()); return false; }
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

?>