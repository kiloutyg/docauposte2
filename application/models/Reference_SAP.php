<?php

class Reference_SAP extends CI_Model {

    public $id=-1;

    public $id_article=-1;
    public $id_SAP=-1;
    public $nom_prod="";
    public $delete=FALSE;


    public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
                $this->load->database();

        }

    public function init($id,$id_article,$id_SAP,$nom_prod,$delete=FALSE)
        {
                $this->id=$id;
                $this->id_article=$id_article;
                $this->id_SAP=$id_SAP;
                $this->nom_prod=$nom_prod;
                $this->delete=$delete;

        }

        public function save(){
          if ($this->delete) {
            $this->delete();
            unset($this);
          }
          else {
            if($this->id == -1){
              $this->insert();
              return $this;
            }
            else{
              $this->update();
              return $this;
            }
          }

        }

        private function insert(){
          $data = array(
               'ID_ARTICLE' => $this->id_article,
               'ID_SAP' => $this->id_SAP,
               'NOM_PROD' => $this->nom_prod
            );
          $query = $this->db->insert("reference_sap", $data);
            $this->id = $this->db->insert_id();
        }

        private function update(){
          $query = $this->db->query("UPDATE reference_sap SET ID_SAP={$this->id_SAP},NOM_PROD='{$this->nom_prod}' WHERE ID_REFERENCE_SAP = {$this->id}");
        }

        public function delete(){
          $query = $this->db->query("DELETE FROM reference_sap WHERE ID_REFERENCE_SAP = {$this->id}");
        }
}
