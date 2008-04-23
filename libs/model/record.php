<?php

# Result record.  Maps to a parent model
# TODO: Figure out how to not use the _parent field, just for readibility
class Record {
	
	# The parent model for this record
	var $_parent;
	
	# Constructor
	function record($model, $in_model = false) {
		$this->_parent = $in_model ? $in_model : $model;
	}
	
	# Member missing?  Try the parent!
	function __get($member) {
		$model =& new $this->_parent;
		$model->_record = get_object_vars($this);
		
		if (!empty($model->$member)) {
			return $model->$member();
		}
		else if (method_exists($this->_parent, $member)) {
			return $model->$member();
		}
		else {
	    return false;
		}
	}
	
	# Method missing?  Try calling the parents'
	function __call($func, $args) {
		if (method_exists($this->_parent, $func)) {
			$model[_record] = get_object_vars($this);
			$model =& new $this->_parent;
			return $model->$func();
		}
		return false;
	}
	
}

?>