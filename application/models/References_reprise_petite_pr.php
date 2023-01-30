<?php

class References_reprise_petite_pr extends CI_Model {

    private $tab_moules = array();

    public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
                $this->load->model('reference_reprise_petite_pr');
                $this->load->database();
        }

        public function queryToArray($query){

          if ($query->num_rows() > 0){
             foreach ($query->result() as $row){
              $moule = new Reference_reprise_petite_pr();
              $moule->initMin($row->ID_REFERENCE_REPRISE_PETITE_PR,$row->ID_ARTICLE,$row->NUM_REFERENCE_REPRISE_PETITE_PR,$row->NOM_PROD_REFERENCE_REPRISE_PETITE_PR);
              array_push($this->tab_moules,$moule);
             }
          }
          return $this->tab_moules;
        }

        public function getAllReference(){
          $query = $this->db->query("SELECT * FROM  reference_reprise_petite_pr ORDER BY CONVERT(SUBSTRING_INDEX(NUM_REFERENCE_REPRISE_PETITE_PR,'-',1),UNSIGNED INTEGER),NUM_REFERENCE_REPRISE_PETITE_PR");
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
          $query = $this->db->query("SELECT NUM_REFERENCE_REPRISE_PETITE_PR FROM  reference_reprise_petite_pr WHERE NUM_REFERENCE_REPRISE_PETITE_PR like '%{$num}%'");
          $array = array();
          if ($query->num_rows() > 0){
             foreach ($query->result() as $row){
              array_push($array,$row->NUM_REFERENCE_REPRISE_PETITE_PR);
             }
          }
          return $array;
        }



}
