<?php

class Moules_micro_chaine extends CI_Model {

    private $tab_moules = array();

    public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
                $this->load->model('moule_micro_chaine');
                $this->load->database();
        }

        public function queryToArray($query){

          if ($query->num_rows() > 0){
             foreach ($query->result() as $row){
              $moule = new Moule_micro_chaine();
              $moule->initMin($row->ID_MOULE_MICRO_CHAINE,$row->ID_ARTICLE,$row->NUM_MOULE_MICRO_CHAINE,$row->NOM_PROD_MOULE_MICRO_CHAINE,$row->PROG_FLAMMAGE);
              array_push($this->tab_moules,$moule);
             }
          }
          return $this->tab_moules;
        }

        public function getAllmoules(){
          $query = $this->db->query("SELECT * FROM  moule_micro_chaine ORDER BY CONVERT(SUBSTRING_INDEX(NUM_MOULE_MICRO_CHAINE,'-',1),UNSIGNED INTEGER),NUM_MOULE_MICRO_CHAINE");
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
          $query = $this->db->query("SELECT NUM_MOULE_MICRO_CHAINE FROM  moule_micro_chaine WHERE NUM_MOULE_MICRO_CHAINE like '%{$num}%'");
          $array = array();
          if ($query->num_rows() > 0){
             foreach ($query->result() as $row){
              array_push($array,$row->NUM_MOULE_MICRO_CHAINE);
             }
          }
          return $array;
        }


}
