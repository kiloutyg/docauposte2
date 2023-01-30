<?php

class Article extends CI_Model {

    public $id=-1;

    public $incident_qualite=NULL;
    public $information=NULL;
    public $delete=FALSE;
    public $tab_ref_SAP=NULL;


    public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
                $this->load->database();
                $this->load->model('incident_qualite');
                $this->load->model('information');
                $this->load->model('references_SAP');
                $this->tab_ref_SAP = new References_SAP();
        }

    public function init($id,$id_incident_qualite,$id_information,$delete=FALSE)
        {
                $this->id=$id;
                $this->delete=$delete;
                if($id_incident_qualite){
                  $incident = new Incident_qualite();
                  $incident->initById($id_incident_qualite);
                  $this->incident_qualite=$incident;
                }
                if($id_information){
                  $information = new Information();
                  $information->initById($id_information);


                  $query = $this->db->query("SELECT * FROM information WHERE ID_INFORMATION = {$id}");

                  $this->information=$information;

                  /*Supression de l'information apres 90 jours (Désactiver)

                 
				$today = new DateTime("now");
                $date = new DateTime($information->date);
				  
				  if($date->diff($today)->days>90){
                    $this->information->lien = '';
                    $this->save();
                  }
				*/
                }
                $this->tab_ref_SAP->getRefByIdArticle($id);
        }

        public function save(){
          if ($this->delete) {
            $this->tab_ref_SAP->delete();
            $this->delete();
            unset($this);
          }
          else {
            if($this->id == -1){
              $this->insert();

            }
            else{
              $this->update();
            }
            $this->tab_ref_SAP->save();
            return $this;
          }
        }

        public function addIncident($lien){
          $incident = new Incident_qualite();
          $incident->lien = $lien;
          $this->incident_qualite = $incident;
        }

        public function addInformation($lien){
          $this->load->helper('date');

          $information = new Information();
          $information->lien = $lien;
          $this->information = $information;

          $datestring = "%Y-%m-%d";
          $time = time();
          $information->date = mdate($datestring, $time);
        }

        private function insert(){
          $data = array(
               'ID_INCIDENT_QUALITE' => NULL,
               'ID_INFORMATION' => NULL
            );
          $query = $this->db->insert("article", $data);
            $this->id = $this->db->insert_id();
            foreach ($this->tab_ref_SAP->tab_references as $key => $value) {
              $value->id_article=$this->id;
            }
        }

        private function update(){


          $incident="ID_INCIDENT_QUALITE = NULL";
          $information="ID_INFORMATION = NULL";


        if($this->incident_qualite){
          if($this->incident_qualite->lien==''){
            $this->incident_qualite->delete=TRUE;
          }
          else{
            if($this->incident_qualite->id==-1){
              $this->incident_qualite->save();
            }
            $incident="ID_INCIDENT_QUALITE = {$this->incident_qualite->id}";
          }
        }



        if($this->information){
          if($this->information->lien==''){
            $this->information->delete=TRUE;

          }
          else{
            if($this->information->id==-1){
              $this->information->save();
            }
            $information="ID_INFORMATION = {$this->information->id}";
          }
        }

        if($incident || $information){
          $virgule=($incident && $information)?",":"";

          $query = $this->db->query("UPDATE article SET {$incident}{$virgule}{$information} WHERE ID_ARTICLE = {$this->id}");
        }

        if($this->incident_qualite){
          $this->incident_qualite->save();
        }

        if($this->information){
          $this->information->save();
        }

        }

        public function delete(){
          $query = $this->db->query("DELETE FROM article WHERE ID_ARTICLE = {$this->id}");
        }

        public function getArticleById($id){
          $query = $this->db->query("SELECT * FROM  article WHERE ID_ARTICLE = {$id}");
          $this->init($query->row("ID_ARTICLE"),$query->row("ID_INCIDENT_QUALITE"),$query->row("ID_INFORMATION"));
          return $this;
        }



}
