<?php

class Doc_moule_presse extends CI_Model {

    public $id=-1;

    public $lien;
    public $id_moule_presse;
    public $doc_type;


    public $delete = FALSE;

    public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
                $this->load->database();

        }

    public function init($id,$lien,$id_moule_presse,$id_doc_type,$delete=FALSE)
        {
          $this->load->model('typeDocument');
                $this->id=$id;
                $this->lien=$lien;
                $this->id_moule_presse=$id_moule_presse;
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
          $query = $this->db->query("INSERT INTO  doc_moule_presse (LIEN_MOULE_PRESSE,ID_MOULE_PRESSE,ID_DOC_TYPE) VALUES('{$this->lien}',{$this->id_moule_presse},{$this->doc_type->id})");
        }

        private function update(){
          $data = array(
               'LIEN_MOULE_PRESSE' => $this->lien,
               'ID_DOC_TYPE' => $this->doc_type->id
            );

$this->db->where('ID_DOC_MOULE_PRESSE', $this->id);
$this->db->update('doc_moule_presse', $data);

        }

        public function delete(){
          $query = $this->db->query("DELETE FROM doc_moule_presse WHERE ID_DOC_MOULE_PRESSE = {$this->id}");
        }



}
