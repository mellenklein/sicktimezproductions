<?php 
	class SortModel extends Model {

		public function __construct($table='', $keyfield='id') {
			parent::__construct($table, $keyfield);
		}
		
		public function sort($sort) {
			$i = 1;
			foreach($sort as $id){
				$sql = "UPDATE ".$this->table." SET sort=".$i." WHERE id=".$id." LIMIT 1";
				$affected = $this->db->update($sql);
				$i++;
			}
			return $sql;
		}
	
	}
?>