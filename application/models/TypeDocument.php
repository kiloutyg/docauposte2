<?php

class TypeDocument extends CI_Model {

    public $id=-1;

    public $label;

    public $delete = FALSE;



    public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
                $this->load->database();

        }

    public function init($id,$label,$delete = FALSE)
        {
                $this->id=$id;
                $this->label=$label;
                $this->delete=$delete;
        }


      public function getById($id)
          {
                  $query = $this->db->query("select NOM_DOC_TYPE FROM doc_type WHERE ID_DOC_TYPE = '{$id}'");
                  $res = $query->row();
                  if (isset($res)){
                          $this->label = $res->NOM_DOC_TYPE;
                          $this->id = $id;
                  }
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
      $query = $this->db->query("INSERT INTO  doc_type (NOM_DOC_TYPE) VALUES('{$this->label}')");
    }

    private function update(){
      $query = $this->db->query("UPDATE doc_type SET NOM_DOC_TYPE = '{$this->label}' WHERE ID_DOC_TYPE = {$this->id}");
    }

    private function delete(){
      $query = $this->db->query("DELETE FROM doc_type WHERE ID_DOC_TYPE = {$this->id}");
    }


}
