<?php

class Document extends CI_Model {

    public $id=-1;

    public $lien;
    public $id_article;
    public $doc_type;


    public $delete = FALSE;

    public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
                $this->load->database();

        }

    public function init($id,$lien,$id_article,$id_doc_type,$delete=FALSE)
        {
          $this->load->model('typeDocument');
                $this->id=$id;
                $this->lien=$lien;
                $this->id_article=$id_article;
                $type = new TypeDocument();
                $type->getById($id_doc_type);
                $this->doc_type=$type;
                $this->delete=$delete;
        }

        public function save(){
          if ($this->delete) {
            $this->delete();
          }
          else {
            if($this->id == -1){
              $this->insert();
            }
            else{
              $this->update();
            }
          }

        }

        private function insert(){
          $query = $this->db->query("INSERT INTO  document (LIEN_DOCUMENT,ID_ARTICLE,ID_DOC_TYPE) VALUES('{$this->lien}',{$this->id_article},{$this->doc_type->id})");
        }

        private function update(){
          $data = array(
               'LIEN_DOCUMENT' => $this->lien,
               'ID_DOC_TYPE' => $this->doc_type->id
            );

$this->db->where('ID_DOCUMENT', $this->id);
$this->db->update('document', $data);

        }

        public function delete(){
          $query = $this->db->query("DELETE FROM document WHERE ID_DOCUMENT = {$this->id}");
        }



}
