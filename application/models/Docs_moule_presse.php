<?php

class Docs_moule_presse extends CI_Model {

    public $tab_documents = array();

    public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
                $this->load->model('doc_moule_presse');
                $this->load->database();
        }

        public function queryToArray($query){

          if ($query->num_rows() > 0){
             foreach ($query->result() as $row){
              $document = new Doc_moule_presse();
              $document->init($row->ID_DOC_MOULE_PRESSE,$row->LIEN_MOULE_PRESSE,$row->ID_MOULE_PRESSE,$row->ID_DOC_TYPE);
              $this->tab_documents[$row->ID_DOC_MOULE_PRESSE]=$document;
             }
          }
          return $this->tab_documents;
        }

        public function save(){
          foreach ($this->tab_documents as $key => $value) {
            $value->save();
          }
        }

        public function delete(){
          foreach ($this->tab_documents as $key => $value) {
            $value->delete();
          }
        }

        public function getAllDocuments(){
          $query = $this->db->query("SELECT * FROM  doc_moule_presse");
          return $this->queryToArray($query);
        }

        public function getDocumentsByIdMoulePresse($id){
          $query = $this->db->query("SELECT * FROM  doc_moule_presse WHERE ID_MOULE_PRESSE = {$id}");
          return $this->queryToArray($query);
        }

        public function add($document){
          array_push($this->tab_documents,$document);
        }


}
