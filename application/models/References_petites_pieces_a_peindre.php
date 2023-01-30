<?php

class References_petites_pieces_a_peindre extends CI_Model {

    private $tab_moules = array();

    public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
                $this->load->model('reference_petites_pieces_a_peindre');
                $this->load->database();
        }

        public function queryToArray($query){

          if ($query->num_rows() > 0){
             foreach ($query->result() as $row){
              $moule = new Reference_petites_pieces_a_peindre();
              $moule->initMin($row->ID_REFERENCE_PETITES_PIECES_A_PEINDRE,$row->ID_ARTICLE,$row->NUM_REFERENCE_PETITES_PIECES_A_PEINDRE,$row->NOM_PROD_REFERENCE_PETITES_PIECES_A_PEINDRE);
              array_push($this->tab_moules,$moule);
             }
          }
          return $this->tab_moules;
        }

        public function getAllReference(){
          $query = $this->db->query("SELECT * FROM  reference_petites_pieces_a_peindre ORDER BY CONVERT(SUBSTRING_INDEX(NUM_REFERENCE_PETITES_PIECES_A_PEINDRE,'-',1),UNSIGNED INTEGER),NUM_REFERENCE_PETITES_PIECES_A_PEINDRE");
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
          $query = $this->db->query("SELECT NUM_REFERENCE_PETITES_PIECES_A_PEINDRE FROM  reference_petites_pieces_a_peindre WHERE NUM_REFERENCE_PETITES_PIECES_A_PEINDRE like '%{$num}%'");
          $array = array();
          if ($query->num_rows() > 0){
             foreach ($query->result() as $row){
              array_push($array,$row->NUM_REFERENCE_PETITES_PIECES_A_PEINDRE);
             }
          }
          return $array;
        }



}
