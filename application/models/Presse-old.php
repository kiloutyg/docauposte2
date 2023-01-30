<?php

class Presse extends CI_Model {

    public $id=-1;

    public $num;

    public $delete = FALSE;

    public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
                $this->load->database();

        }

    public function init($id,$num,$delete=FALSE)
        {
                $this->id=$id;
                $this->num=$num;
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
          $query = $this->db->query("INSERT INTO  presse (NUM_PRESSE) VALUES('{$this->num}')");
        }

        private function update(){
          $query = $this->db->query("UPDATE presse SET NUM_PRESSE = '{$this->num}' WHERE ID_PRESSE = {$this->id}");
        }

        public function delete(){
          $query = $this->db->query("DELETE FROM presse WHERE ID_PRESSE = {$this->id}");
        }

        public function getPresseByNum($num){
          $query = $this->db->query("SELECT * FROM  presse WHERE NUM_PRESSE = {$num}");
          $this->init($query->row("ID_PRESSE"),$query->row("NUM_PRESSE"));
          return $this;
        }

        public function getPresseById($id){
          $query = $this->db->query("SELECT * FROM  presse WHERE ID_PRESSE = {$id}");
          $this->init($query->row("ID_PRESSE"),$query->row("NUM_PRESSE"));
          return $this;
        }



}
