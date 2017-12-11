<?php

define('CSS_TOOLS_MODULE_LOADED', true);

class CSSBuilder {
	public function __construct() {
	}
	// ['name'=>'value', 'name'=>'value']
	public function build_rules($css_rules_arr) {
		$css_rules = [];
		foreach ($css_rules_arr as $name => $value) {
			$css_rules[] = "$name:$value";
		}
		return implode(";\n    ", $css_rules);
	}
	// ['selector'=>['name'=>'value', 'name'=>'value']]
    public function build_css($css_arr) {
    	$css = [];
    	foreach ($css_arr as $selector => $css_rules_arr) {
    		$css_rules = $this->build_rules($css_rules_arr);
    		$css[] = "$selector {\n    $css_rules\n}\n";
    	}
		return implode('', $css);
    }
}

class CSSManager extends CSSBuilder {
	public $tbl_main;

	public function __construct() {
		global $database;
		$this->database = $database;
        $this->tbl_main = '`'.TABLE_PREFIX.'mod2_css`';
	}

	public function get($mark) {
		$sql = "SELECT * FROM {$this->tbl_main} WHERE `css_mark`='$mark'";
		$result = $this->database->query($sql);
		if($this->database->is_error()) return $this->database->get_error();
		if ($result->numRows() == 0) return null;
		return json_decode($result->fetchRow()['css'], true);
	}

	public function update($mark, $css_arr) {
		$sql = "UPDATE {$this->tbl_main} SET `css`='".json_encode($css_arr)."' WHERE `css_mark`='$mark'";
		if(!$this->database->query($sql)) return $this->database->get_error();
		return true;
	}

	public function add($mark, $css_arr) {
		$sql = "INSERT INTO {$this->tbl_main} (`css_mark`, `css`) VALUES ('$mark', '".json_encode($css_arr)."')";
		if(!$this->database->query($sql)) return $this->database->get_error();
		return true;
	}

	public function show($mark, $default_css_arr, $is_return=false) {
		$_css_arr = $this->get($mark);
		if ($_css_arr === null) $_css_arr = [];
        foreach ($_css_arr as $name => $value) {
        	if(!in_array($name, array_keys($default_css_arr))) {$default_css_arr[$name] = $_css_arr[$name]; continue;}
        	foreach ($_css_arr[$name] as $pname => $pvalue) {
        		if ($pvalue != 'initial' && $pvalue != '') $default_css_arr[$name][$pname] = $pvalue;
        	}
        } unset($_css_arr);

/*		foreach ($default_css_arr as $default_name => $default_value) {
			if (!in_array($default_name, array_keys($_css_arr))) {continue;}
			foreach ($_css_arr[$default_name] as $pname => $pvalue) {
				$default_css_arr[$default_name][$default_pname] = $_css_arr[$default_name][$default_pname];
			}*/
/*			if (!in_array($default_name, array_keys($_css_arr))) {$_css_arr[$default_name] = $default_value; continue;}
			foreach ($_css_arr[$default_name] as $default_pname => $default_pvalue) {
				if (!in_array($default_pname, array_keys($_css_arr[$default_name]))) $_css_arr[$default_name][$default_pname] = $default_css_arr[$default_name][$default_pname];
			}
		}*/
		
		$css_arr = [];
		foreach ($default_css_arr as $name => $value) {
			#$css_arr[".".$mark."_".$name] = $value;
			if ($name=='main') $css_arr[".".$mark."_main"] = $value;
			else $css_arr[".".$mark."_main ".$name] = $value;
		}
		if($is_return) return $this->build_css($css_arr);
		else echo $this->build_css($css_arr);
	}
}

?>