<?php

class Incident_qualite extends CI_Model {

    public $id=-1;

    public $lien;

    public $delete = FALSE;

    public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
                $this->load->database();

        }

    public function init($id,$lien,$delete=FALSE)
        {
                $this->id=$id;
                $this->lien=$lien;
                $this->delete=$delete;
        }

        public function save(){
          if ($this->delete) {
            $this->delete();
            unset($this);
            return NULL;
          }
          else {
            if($this->id == -1){
              $this->insert();

            }
            else{
              $this->update();
            }
            return $this;
          }
        }

        private function insert(){
          $data = array(
               'LIEN_INCIDENT_QUALITE' => $this->lien
            );
          $query = $this->db->insert("incident_qualite", $data);
            $this->id = $this->db->insert_id();
        }

        private function update(){
          $query = $this->db->query("UPDATE incident_qualite SET LIEN_INCIDENT_QUALITE = '{$this->lien}' WHERE ID_INCIDENT_QUALITE = {$this->id}");
        }

        public function delete(){
          $query = $this->db->query("DELETE FROM incident_qualite WHERE ID_INCIDENT_QUALITE = {$this->id}");
        }

        public function initById($id){
          $query = $this->db->query("SELECT * FROM incident_qualite WHERE ID_INCIDENT_QUALITE = {$id}");
          $this->id = $query->row("ID_INCIDENT_QUALITE");
          $this->lien = $query->row("LIEN_INCIDENT_QUALITE");
        }



}
