<?php
namespace TMDDS;

final class Database {
	private $db = null;

	public function __construct($db = null) {
#		$this->db = $db ?: new \mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
		if ($this->db->connect_errno) {
			throw new \Exception($this->db->connect_error, $this->db->connect_errno);
		}
	}

	function __destruct() {
		$this->db->close();
	}

	private function check($result) {
		if (!$result) {
			throw new \Exception($this->db->error, $this->db->errno);
		}
		return $result;
	}

	private function prepare_stmt($sql) {
		// Note that the following line is calling prepare on the $db instance var, not on $this.
		$stmt = $this->check($this->db->prepare($sql));
		$values = array();
		$types = "";
		for($p = 1; $p < func_num_args(); ++$p) {
			$value = func_get_arg($p);
			$type = substr(strtolower(gettype($value)), 0, 1);
			if (!in_array($type, array("s", "i", "d", "n"))) {
				throw new \Exception("Unknown parameter type {$type}.");
			}
			$types .= $type == "n" ? "s" : $type;
			array_push($values, $value);
		}
		if (count($values) > 0) {
			$args = array($types);
			foreach ($values as $k => &$value) {
				$args[$k + 1] = &$value;
			}
			$this->check(call_user_func_array(array($stmt, "bind_param"), $args));
		}
		return $stmt;
	}

	// Expects an array in which $args[0] is the SQL statement and the rest of the args,
	// if any, are parameters.
	private function prepare($args) {
		return call_user_func_array(array($this, "prepare_stmt"), $args);
	}

	// Expects an array in which $args[0] is the SQL statement and the rest of the args,
	// if any, are parameters. The $fetch parameter is a function which takes a statement
	// and is expected to extract the result from it, which is then returned from run().
	private function run($args, $fetch) {
		$stmt = null;
		try {
			$stmt = $this->prepare($args);
			$this->check($stmt->execute());
                        return $fetch($stmt);
		} finally {
			if ($stmt) $stmt->close();
		}
	}

	public function prime($array, $key) {
		$result = array();
		foreach($array as $value) {
			$result[$value[$key]] = $value;
		}
		return $result;
	}

	// Don't use this function. Deprecated. Use parameter binding instead.
	public function escape($string) {
		return $this->db->real_escape_string($string);
	}
	
 	// Additional arguments are bound parameters
	public function exec($sql) {
		return $this->run(func_get_args(), function($stmt) {
			return $this->db->affected_rows;
		});
	}

 	// Additional arguments are bound parameters
	public function query($sql) {
		return $this->run(func_get_args(), function($stmt) {
			$res = $this->check($stmt->get_result());
			$result = array();
			while ($row = $res->fetch_assoc()) {
				array_push($result, $row);
			}
			return $result;
		});
	}

 	// Additional arguments are bound parameters
	public function query_one($sql) {
		return $this->run(func_get_args(), function($stmt) {
			$res = $this->check($stmt->get_result());
			return $res->fetch_assoc();	
		});
	}

 	// Additional arguments are bound parameters
	public function query_value($sql) {
		return $this->run(func_get_args(), function($stmt) {
			$value = null;
			$res = $this->check($stmt->get_result());
			$one = $res->fetch_row();
			if ($one) {
				$value = $one[0];
			}
			return $value;
		});
	}

	public function insert_id() {
		return $db->insert_id;
	}
}
?>
