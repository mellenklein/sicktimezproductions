<?php
	class DynamicContent extends SortModel {

		public function __construct($dc_type='b', $keyfield='id') {
			parent::__construct('dynamic_content', $keyfield);
			$this->dc_type = $dc_type;
		}
		
		public function load($id, $keyfield = '') {
			if ($keyfield == '') {
				$keyfield = $this->keyfield;
			}
			$sql = "SELECT * FROM ".$this->table." WHERE dc_type = ? AND ".$keyfield."=? LIMIT 1";
			$rows = $this->db->fetch($sql, array($this->dc_type, $id));
			if(!empty($rows)) {
				$this->data = $rows[0];
				$this->id = $rows[0][$keyfield];
				$this->data['authors'] = $this->get_authors($this->data['id']);
			} else {
				$this->data = array();
				$this->id = 0;
			}
		}
		
		public function get_authors($id) {
			$team_members = $this->db->fetch('SELECT t.* FROM team t, content_x_team cxt WHERE cxt.dc_id = ? AND cxt.team_id = t.id', array($id));
			return $team_members;
		}
		
		public function delete() {
			$id = $this->id;
			$affected = parent::delete();
			if($affected != 0) {
				$this->db->delete("DELETE FROM content_x_team WHERE dc_id = ".$id." AND dc_type = '".$this->dc_type ."'");
				$this->db->delete("DELETE FROM content_x_categories WHERE child_id = ".$id." AND child_table = 'dynamic_content'");
			}
			return $affected;
		}
		
		public function get_items($args = array()) {
			if(isset($args['where'])) {
				$args['where'] .= ' AND dc_type = "'.$this->dc_type .'"';
			} else {
				$args['where'] = 'dc_type = "'.$this->dc_type .'"';
			}
			$items = parent::get_items($args);
			foreach($items AS &$i) {
				$i['authors'] = $this->get_authors($i['id']);
			}
			return $items;
		}
	
	}
?>