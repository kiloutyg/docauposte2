<?php

class Moule_presse extends CI_Model {

    public $id=-1;

    public $moule = NULL;

    public $presse = NULL;

    public $tab_docs = NULL;

    public $delete = FALSE;

    public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
                $this->load->database();
                $this->load->model('moule_injection');
                $this->load->model('presse');
                $this->load->model('docs_moule_presse');
        }

    public function init($id,$id_moule,$id_presse,$delete = FALSE)
        {

                $this->id=$id;
                $this->moule = new Moule_injection();

                $this->moule->getMouleByIdWithoutMP($id_moule);

                $this->presse = new Presse();
                $this->presse->getPresseById($id_presse);
                $this->delete=$delete;
                $this->tab_docs= new Docs_moule_presse();
                $this->tab_docs->getDocumentsByIdMoulePresse($id);
        }

        public function create($moule,$id_presse)
            {

                    $this->id=-1;
                    $this->moule = new Moule_injection();

                    $this->moule->getMouleByIdWithoutMP($moule->id);

                    $this->presse = new Presse();
                    $this->presse->getPresseById($id_presse);
                    $this->tab_docs= new Docs_moule_presse();
                    $this->delete=FALSE;
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
            $this->tab_docs->save();
            }
          }

        }


        private function insert(){
          //$this->article->save();
          //$this->moule->save();

          $data = array(
               'ID_MOULE_INJECTION' => $this->moule->id,
               'ID_PRESSE' =>$this->presse->id
            );
          $query = $this->db->insert("moule_presse", $data);
            $this->id = $this->db->insert_id();
        }


        public function delete(){
          $this->tab_docs->delete();
          $query = $this->db->query("DELETE FROM moule_presse WHERE ID_MOULE_PRESSE = {$this->id}");
        }

        public function getIdMoulePresseByIdMouleIdPresse($idM,$idP){
          $query = $this->db->query("SELECT mp.ID_MOULE_PRESSE FROM  moule_presse mp WHERE mp.ID_MOULE_INJECTION = {$idM} AND mp.ID_PRESSE = {$idP}");
          return $query->row('ID_MOULE_PRESSE');
        }
}
