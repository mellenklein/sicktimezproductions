<?php
 class Member extends Model {

	 public function __construct($keyfield='id') {
		 parent::__construct('team', $keyfield);
	 }

	 public function load($id, $keyfield = '') {
		 parent::load($id, $keyfield);
		 $id = (isset($this->data['id']) && !empty($this->data['id'])) ? $this->data['id'] : $id ;
		 $this->data['case_studies'] = $this->get_models($id);
		 $this->data['case_ids'] = $this->generate_models_ids($this->data['case_studies']);
	 }

	//  public function get_models($id) {
	// 	 $case_studies = $this->db->fetch('SELECT cxm.*, cs.title AS m_title FROM case_x_members cxm, case_studies cs WHERE cxm.case_study_id = ? AND cxm.team_member_id = cs.id', array($id));
	// 	 return $case_studies;
	//  }
	 public function get_models($id) {
		 $case_studies = $this->db->fetch('SELECT cxm.*, cs.title AS m_title, cs.id AS m_id FROM case_x_members cxm, case_studies cs, team tm WHERE cxm.case_study_id = ? AND cxm.team_member_id = tm.id', array($id));
		 return $case_studies;
	 }
	 public function generate_models_ids($case_arr) {
		 $case_ids = array();
		 foreach($case_arr AS $m) {
			 $case_ids[] = $m['case_study_id'];
		 }
		 return $case_ids;
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
		 foreach($items AS &$i) {
			 $i['case_studies'] = $this->get_models($i['id']);
			 $i['case_ids'] = $this->generate_models_ids($i['case_studies']);
		 }
		 return $items;
	 }

 }
?>
