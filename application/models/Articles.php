<?php

class Articles extends CI_Model {

    private $tab_articles = array();

    public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
                $this->load->model('article');
                $this->load->database();
        }

        public function queryToArray($query){

          if ($query->num_rows() > 0){
             foreach ($query->result() as $row){
              $article = new Article();
              $article->init($row->ID_ARTICLE,$row->ID_INCIDENT_QUALITE,$row->ID_INFORMATION);
              array_push($this->tab_articles,$article);
             }
          }
          return $this->tab_articles;
        }

        public function getAllArticles(){
          $query = $this->db->query("SELECT * FROM  article");
          return $this->queryToArray($query);
        }

        public function add($article){
          array_push($this->tab_articles,$article);
        }

        public function save(){
          foreach ($this->tab_articles as $key => $value) {
            $value->save();
          }
        }


}
