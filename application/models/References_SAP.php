<?php

class References_SAP extends CI_Model {

    public $tab_references = array();

    public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
                $this->load->model('Reference_SAP');
                $this->load->database();
        }

        public function queryToArray($query){

          if ($query->num_rows() > 0){
             foreach ($query->result() as $row){
              $ref = new Reference_SAP();
              $ref->init($row->ID_REFERENCE_SAP,$row->ID_ARTICLE,$row->ID_SAP,$row->NOM_PROD);
              array_push($this->tab_references,$ref);
             }
          }
          return $this->tab_references;
        }

        public function add($ref){
          array_push($this->tab_references,$ref);
        }

        public function delete(){
          foreach ($this->tab_references as $key => $value) {
            $value->delete();
          }
        }

        public function save(){
          foreach ($this->tab_references as $key => $value) {
            $value->save();
          }
        }

        public function getRefByIdArticle($id){
          $query = $this->db->query("SELECT * FROM  reference_sap WHERE ID_ARTICLE ={$id}");
          return $this->queryToArray($query);
        }


}
