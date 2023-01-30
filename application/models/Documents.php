<?php

class Documents extends CI_Model {

    public $tab_documents = array();

    public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
                $this->load->model('document');
                $this->load->database();
        }

        public function queryToArray($query){

          if ($query->num_rows() > 0){
             foreach ($query->result() as $row){
              $document = new Document();
              $document->init($row->ID_DOCUMENT,$row->LIEN_DOCUMENT,$row->ID_ARTICLE,$row->ID_DOC_TYPE);
              array_push($this->tab_documents,$document);
             }
          }
          return $this->tab_documents;
        }

        public function delete(){
          foreach ($this->tab_documents as $key => $value) {
            $value->delete();
          }
        }
        public function save(){
          foreach ($this->tab_documents as $key => $value) {
            $value->save();
          }
        }

        public function getAllDocuments(){
          $query = $this->db->query("SELECT * FROM  document");
          return $this->queryToArray($query);
        }

        public function getDocumentsByIdArticle($id){
          $query = $this->db->query("SELECT * FROM  document WHERE ID_ARTICLE = {$id}");
          return $this->queryToArray($query);
        }

        public function add($document){
          array_push($this->tab_documents,$document);
        }


}
