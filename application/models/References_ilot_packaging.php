<?php

class References_ilot_packaging extends CI_Model {

    private $tab_moules = array();

    public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
                $this->load->model('reference_ilot_packaging');
                $this->load->database();
        }

        public function queryToArray($query){

          if ($query->num_rows() > 0){
             foreach ($query->result() as $row){
              $moule = new Reference_ilot_packaging();
              $moule->initMin($row->ID_REFERENCE_ILOT_PACKAGING,$row->ID_ARTICLE,$row->NUM_REFERENCE_ILOT_PACKAGING,$row->NOM_PROD_REFERENCE_ILOT_PACKAGING);
              array_push($this->tab_moules,$moule);
             }
          }
          return $this->tab_moules;
        }

        public function getAllReference(){
          $query = $this->db->query("SELECT * FROM  reference_ilot_packaging ORDER BY CONVERT(SUBSTRING_INDEX(NUM_REFERENCE_ILOT_PACKAGING,'-',1),UNSIGNED INTEGER),NUM_REFERENCE_ILOT_PACKAGING");
          return $this->queryToArray($query);
        }



        public function add($moule){
          array_push($this->tab_moules,$moule);
        }

        public function save(){
          foreach ($this->tab_moules as $key => $value) {
            $value->save();
          }
        }

        public function getAllNum($num=""){
          $query = $this->db->query("SELECT NUM_REFERENCE_ILOT_PACKAGING FROM  reference_ilot_packaging WHERE NUM_REFERENCE_ILOT_PACKAGING like '%{$num}%'");
          $array = array();
          if ($query->num_rows() > 0){
             foreach ($query->result() as $row){
              array_push($array,$row->NUM_REFERENCE_ILOT_PACKAGING);
             }
          }
          return $array;
        }



}
