<?php

class Agreement {
	
	function __construct($db) {
		$this->db = $db;
		$this->error = null;
	}
	
	function set_error($error) {
		$this->error = $error;
	}
	function get_error() {
		return $this->error;
	}
	function is_error() {
		return ! ($this->error === null);
	}

	
	function get_text_from_page($page_link='/soglashenie') {
		#$sql = "SELECT * FROM `".TABLE_PREFIX."pages` AS p, `".TABLE_PREFIX."sections` AS s, `".TABLE_PREFIX."mod_wysiwyg` AS w WHERE p.`link`='{$page_link}' AND p.page_id=s.page_id AND s.section_id=w.section_id";
		$sql = "SELECT * FROM `".TABLE_PREFIX."pages` AS p, `".TABLE_PREFIX."mod_wysiwyg` AS w WHERE p.`link`='{$page_link}' AND p.page_id=w.page_id";
        $r = $this->db->query($sql);

        if ($this->db->is_error()) {
        	$this->set_error($this->db->get_error());
        	return null;
        }
        
        if ($r->numRows() == 0) {
        	$this->set_error('Текст соглашения не найден');
        	return null;
        }

        return $r->fetchRow()['content'];        
        
	}
	
	function is_active() {
		
	}
	
	function does_visitor_agree($field_name='i_agree', $true_value='on', $object=null) {
		if ($object == null) $object = $_POST;

		if (gettype($object) == 'object') {
			if (!has_class_property($object, $field_name)) return false;
			$value = class_value($object, $field_name);
		} else if (gettype($object) == 'array') {
			if (!isset($object[$field_name])) return false;
    		$value = $object[$field_name];
		}
		
		if ($value === $true_value) return true;
		return false;
	}
}

#$clsAgreemnt = new Agreement($database);


#$text = $clsAgreemnt->get_text_from_page();
#if ($clsAgreemnt->is_error()) $text = $clsAgreemnt->get_error();
#echo "<script>console.log('{$text}')</script>";

?>