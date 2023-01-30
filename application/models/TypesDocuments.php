<?php

class TypesDocuments extends CI_Model {

    private $tab_typesDocs = array();

    public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
                $this->load->model('typeDocument');
                $this->load->database();
        }

        public function queryToArray($query){
          $this->tab_typesDocs = array();

          if ($query->num_rows() > 0){
             foreach ($query->result('typeDocument') as $row){
              $typeDoc = new TypeDocument();
              $typeDoc->init($row->ID_DOC_TYPE,$row->NOM_DOC_TYPE);
              array_push($this->tab_typesDocs,$typeDoc);
             }
          }
          return $this->tab_typesDocs;
        }

        public function add($typeDoc){
          array_push($this->tab_typesDocs,$typeDoc);
        }

        public function getAllTypesDocuments(){
          $query = $this->db->query("SELECT ID_DOC_TYPE,NOM_DOC_TYPE FROM  doc_type order BY NOM_DOC_TYPE  COLLATE latin1_german2_ci");
          return $this->queryToArray($query);
        }


        public function save(){
          foreach ($this->tab_typesDocs as $key => $value) {
            $value->save();
          }
        }


}
