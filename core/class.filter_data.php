<?php

class _FilterData {

	public $nulls = ['', null];

    function __construct() {
   		$this->obj = $_POST;
    }
	
	public function filter_1($value) {
		if (in_array($value, $this->nulls)) return false;
		return true;
	}
	
	public function filter_array_integer($value) {
	    foreach($value as $i => $v) {
	    	$v = get_number($v);
	    	if ($v == '') return false;
	    }
	    return true;
	}
	
	public function filter_integer($value) {
		if (gettype($value) == 'integer') return true;
		if (preg_match("/[^-0-9]+/", $value) || strlen($value) == 0) return false;
		return true;
	}

	public function filter_float($value) {
		if (gettype($value) == 'float') return true;
		if (preg_match("/[^-0-9.,]+/", $value) || strlen($value) == 0) return false;
		return true;
	}

	public function filter_boolean($value) {
		if ($value === true || $value === false) return true;
		return false;
	}

    // $args[0] - min
    // $args[1] - max
	public function _filter_count($count, $args) {
		if ($args[1] !== null) {
			if ($count>$args[1]) return false;
		}
		if ($args[0] !== null) {
			if ($count<$args[0]) return false;
		}
		return true;
	}    

	public function filter_arrCount($value, $args) {
		if (gettype($value) != 'array') return false;
		$count = count($value);
		return $this->_filter_count($count, $args);
	}

	public function filter_strCount($value, $args) {
		if (gettype($value) != 'string') return false;
		$count = strlen($value);
		print_error($count);
		return $this->_filter_count($count, $args);
	}

	public function filter_mb_strCount($value, $args) {
		if (gettype($value) != 'string') return false;
		$count = mb_strlen($value, 'utf-8');
		return $this->_filter_count($count, $args);
	}

	public function filter_variants($value, $args) {
		if (in_array($value, $args[0])) return true;
		return false;
	}

	public function get_filter_result($filter_name, $value, $args=[]) {
	    $filter_name = 'filter_'.$filter_name;
		if ($args) return $this->$filter_name($value, $args);
		else return $this->$filter_name($value);
	}
}

class FilterData extends _FilterData {

	public $errs = [];

    // new

	public function set_obj($obj) {$this->obj = $obj;}
        public function get_obj() {return $this->obj;}
    public function get_error($options=null) {
    	if ($options === null) $options = [];
    	if (!isset($options['sep'])) $options['sep'] = '<br>';
    	if (!isset($options['ret_entity'])) $options['ret_entity'] = 'errors';

        if ($options['ret_entity'] == 'errors') {
        	$errs = [];
        	foreach($this->errs as $prop_name => $_errs) $errs = array_merge($errs, $_errs);
        	return implode($options['sep'], $errs);
        } else if ($options['ret_entity'] == 'fields') {
        	return array_keys($this->errs);
        } else if ($options['ret_entity'] == 'raw') {
        	return $this->errs;
        }

    }
    public function add_error($err_text, $prop_name) {
    	if (!in_array($prop_name, array_keys($this->errs))) $this->errs[$prop_name] = [];
    	$this->errs[$prop_name][] = $err_text;
    }
    public function clear_error() { $this->errs= []; }
    public function is_error() {return count($this->errs) > 0 ? true : false;}

    // вызываетс€ в случае не валидного значени€
    public function do_by_type($type, $prop_name, $err_msg, $default=null) {
        $err_msg = $err_msg === null ? "Ќеверное значение $name" : $err_msg;
    	if ($type=='error') print_error($err_msg);
    	else if ($type='default') return $default;
        else if ($type='append') $this->errs[] = $err_msg;
    }

    public function f_simple($value, $filter) {
    	$filter_name = $filter[0];
    	$err_msg = $filter[1];
    	$args = array_slice($filter, 2);

	    $filter_name = 'filter_'.$filter_name;
		return $this->$filter_name($value);
    }

    /*
        $value = f('name', [], 'default', 'default_value') - если такого свойства нет, то возвращаем значение по умолчанию
        $value = f('name', [['filter', $err_msg, $args]], 'default') - пропускаем значение через фильтр. 
    */	
	public function f($prop_name, $filters, $type, $default=null) {
		// извлекаем значение у объекта
		$obj = $this->obj;
    	if      (gettype($obj) === 'array'  && isset($obj[$prop_name]))           $value = $obj[$prop_name];
		else if (gettype($obj) === 'object' && property_exists($obj, $prop_name)) $value = $obj->$prop_name;
		else $value = $default;//$this->do_by_type($type, $prop_name, $err_msg, $default);
		
		// фильтруем значение
		foreach ($filters as $i => $filter) {
			
	    	$filter_name = $filter[0];
	    	$err_msg = $filter[1];
	    	$args = array_slice($filter, 2);

			if (!$this->get_filter_result($filter_name, $value, $args)) {
        		// в случае невалидности значени€: выводим ошибку, возвращаем значение по умолчанию или накапливаем ошибки
		        $err_msg = $err_msg === null ? "Ќеверное значение $name" : $err_msg;
		    	if ($type=='error' || $type=='fatal') print_error($err_msg);
		    	else if ($type=='default') return $default;
		        else if ($type=='append') $this->add_error($err_msg, $prop_name);//$this->errs[] = $err_msg;				
			}
		}
		
		return $value;
	}

	public function f2($obj, $prop_name, $filters, $type, $default=null) {
                $temp = $this->get_obj();
                $this->set_obj($obj);
                $r = $this->f($prop_name, $filters, $type, $default);
                $this->set_obj($temp);
                return $r;
	}
	
	
	function print_error($message='', $options=[]) {

		$options['absent_fields'] = $this->get_error(['ret_entity'=>'fields']);

		$message .= $this->get_error();

	    print_error($message, $options);

	}
}

?>