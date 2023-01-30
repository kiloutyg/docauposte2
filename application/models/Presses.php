<?php

class Presses extends CI_Model {

    public $tab_presses = array();

    public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
                $this->load->model('presse');
                $this->load->database();
        }

        public function queryToArray($query){

          if ($query->num_rows() > 0){
             foreach ($query->result() as $row){
              $presse = new Presse();
              $presse->init($row->ID_PRESSE,$row->NUM_PRESSE);
              array_push($this->tab_presses,$presse);
             }
          }
          return $this->tab_presses;
        }

        public function getAllPresses(){
          $query = $this->db->query("SELECT * FROM  presse order BY NUM_PRESSE  COLLATE latin1_german2_ci");
          return $this->queryToArray($query);
        }

        public function add($presse){
          array_push($this->tab_presses,$presse);
        }

        public function save(){
          foreach ($this->tab_presses as $key => $value) {
            $value->save();
          }
        }


}
