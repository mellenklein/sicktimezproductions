<?php
 class CaseStudy extends Model {

	 public function __construct($keyfield='id') {
		 parent::__construct('case_studies', $keyfield); //default table
	 }


	 public function load($id, $keyfield = '') {
		 parent::load($id, $keyfield);
		 $id = (isset($this->data['id']) && !empty($this->data['id'])) ? $this->data['id'] : $id ;
		 //new index:
		 $this->data['team_members'] = $this->get_team_members($id);
		 $this->data['team_ids'] = $this->generate_team_ids($this->data['team_members']);

	 }


	 public function get_team_members($id) {
		 $team_members = $this->db->fetch('SELECT tm.* FROM case_x_members cxm, team tm WHERE cxm.case_study_id = ? AND cxm.team_member_id = tm.id', array($id));
		 return $team_members;
	 }


	 public function generate_team_ids($team_arr) {
		 $team_ids = array();
		 foreach($team_arr AS $tm) {
			 $team_ids[] = $tm['id'];
		 }
		 return $team_ids;
	 }

	 public function delete() {
		 $id = $this->id;
		 $affected = parent::delete();
		 if($affected != 0) {
			 $this->db->delete('DELETE FROM case_x_members WHERE case_study_id = '.$id);
		 }
		 return $affected;
	 }



	 public function get_items($args = array()) {
		 $items = parent::get_items($args);
		 //for each case study, get team members:
		 // giving $i a global scope - passing by ref
		 foreach($items AS &$i) {
			 $i['team_members'] = $this->get_team_members($i['id']);
			 $i['team_ids'] = $this->generate_team_ids($i['team_members']);
		 }
		 return $items;
		 //returns case study with team members as an array
	 }

 }
?>
