<?php

class Information extends CI_Model {

    public $id=-1;

    public $lien;

    public $date;

    public $delete = FALSE;

    public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
                $this->load->database();

        }

    public function init($id,$lien,$date,$delete=FALSE)
        {
                $this->id=$id;
                $this->lien=$lien;
                $this->date=$date;
                $this->delete=$delete;
        }

        public function save(){
          if ($this->delete) {
            $this->delete();
            unset($myInstance);
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
               'LIEN_INFORMATION' => $this->lien,
               'DATE_INFORMATION' => $this->date
            );
          $query = $this->db->insert("information", $data);
            $this->id =  $this->db->insert_id();
        }

        private function update(){
          $query = $this->db->query("UPDATE information SET LIEN_INFORMATION = '{$this->lien}',DATE_INFORMATION = '{$this->date}' WHERE ID_INFORMATION = {$this->id}");
        }

        public function delete(){
          $query = $this->db->query("DELETE FROM information WHERE ID_INFORMATION = {$this->id}");
        }

        public function initById($id){

          $query = $this->db->query("SELECT * FROM information WHERE ID_INFORMATION = {$id}");

          $this->id = $query->row("ID_INFORMATION");
          $this->lien = $query->row("LIEN_INFORMATION");
          $this->date =$query->row("DATE_INFORMATION");



        }


}
