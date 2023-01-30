<?php

class Moules_injection extends CI_Model {

    private $tab_moules = array();

    public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
                $this->load->model('moule_injection');
                $this->load->database();
        }

        public function queryToArray($query){

          if ($query->num_rows() > 0){
             foreach ($query->result() as $row){
              $moule = new Moule_injection();
              $moule->initMin($row->ID_MOULE_INJECTION,$row->ID_ARTICLE,$row->NUM_MOULE_INJECTION,$row->NOM_PROD_MOULE_INJECTION,$row->NUM_PRESSE);
              array_push($this->tab_moules,$moule);
             }
          }
          return $this->tab_moules;
        }

        public function getAllmoules(){
          $query = $this->db->query("SELECT * FROM  moule_injection ORDER BY CONVERT(SUBSTRING_INDEX(NUM_MOULE_INJECTION,'-',1),UNSIGNED INTEGER),NUM_MOULE_INJECTION");
          return $this->queryToArray($query);
        }

        public function getMoulesByPresse($id){
          $query = $this->db->query("SELECT * FROM  moule_injection m, moule_presse mp, presse p WHERE p.id_presse = {$id} AND p.id_presse=mp.id_presse AND mp.id_moule_injection=m.id_moule_injection ORDER BY CONVERT(SUBSTRING_INDEX(NUM_MOULE_INJECTION,'-',1),UNSIGNED INTEGER),NUM_MOULE_INJECTION");
          return $this->queryToArray($query);
        }

        public function getAllNum($num=""){
          $query = $this->db->query("SELECT NUM_MOULE_INJECTION FROM  moule_injection WHERE NUM_MOULE_INJECTION like '%{$num}%'");
          $array = array();
          if ($query->num_rows() > 0){
             foreach ($query->result() as $row){
              array_push($array,$row->NUM_MOULE_INJECTION);
             }
          }
          return $array;
        }



        public function add($moule){
          array_push($this->tab_moules,$moule);
        }

        public function save(){
          foreach ($this->tab_moules as $key => $value) {
            $value->save();
          }
        }



}
