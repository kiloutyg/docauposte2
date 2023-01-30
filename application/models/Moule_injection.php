<?php

class Moule_injection extends CI_Model {

    public $id=-1;

    public $num;

    public $article = NULL;

    public $tab_docs = NULL;

    public $tab_moules_presses = NULL;

    public $delete = FALSE;

    public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
                $this->load->database();
                $this->load->model('article');
                $this->load->model('documents');
                $this->load->model('moules_presses');

        }

    public function init($id,$id_article,$num,$delete = FALSE)
        {


                $this->id=$id;
                $this->num=$num;
                $this->delete=$delete;
                $this->article = new Article();
                $this->article->getArticleById($id_article);
                $docs = new Documents();
                $docs->getDocumentsByIdArticle($this->article->id);
                $this->tab_docs= $docs;
                $mp = new Moules_presses();
                $mp->getAllByNumMoule($num);
                $this->tab_moules_presses = $mp;

        }

        public function initMin($id,$id_article,$num,$delete = FALSE)
            {


                    $this->id=$id;
                    $this->num=$num;
                    /*$this->nom_prod=$nom_prod;
                    $this->delete=$delete;
                    $this->article = new Article();
                    $this->article->getArticleById($id_article);
                    $docs = new Documents();
                    $docs->getDocumentsByIdArticle($this->article->id);
                    $this->tab_docs= $docs;
                    $mp = new Moules_presses();
                    $mp->getAllByNumMoule($num);
                    $this->tab_moules_presses = $mp;*/

            }


        public function initWithoutMP($id,$id_article,$num,$delete = FALSE)
            {


                    $this->id=$id;
                    $this->num=$num;
                    $this->delete=$delete;
/*
                    $art =  new Article();
                    $art->getArticleById($id_article);
                    $this->article = $art;
                    $docs = new Documents();

                    $docs->getDocumentsByIdArticle($this->article->id);

                    $this->tab_docs= $docs;

*/
            }

    public function create($num)
        {
                $this->num=$num;
                $article = new Article();
                $this->article = $article;
                $mp = new Moules_presses();
                $this->tab_moules_presses = $mp;
                $docs = new Documents();
                //$docs->getDocumentsByIdArticle($this->article->id);
                $this->tab_docs= $docs;

        }



            public function save(){
              if ($this->delete) {
                $this->delete();
                $this->tab_docs->delete();
                $this->article->delete=TRUE;
                $this->article->save();
              }
              else {
                if($this->id == -1){
                  $this->insert();
                }
                else{
                  $this->update();
                }
                $this->tab_docs->save();
                $this->article->save();
                $this->tab_moules_presses->save();
              }

            }

            private function insert(){
              $this->article->save();

              $data = array(
                   'ID_ARTICLE' => $this->article->id,
                   'NUM_MOULE_INJECTION' => $this->num
                );
              $query = $this->db->insert("moule_injection", $data);
                $this->id = $this->db->insert_id();

                foreach (  $this->tab_docs->tab_documents as $key => $value) {
                  $value->id_article=$this->article->id;
                }
                foreach (  $this->tab_moules_presses->tab_moule_presse as $key => $value) {
                  $value->moule->id=$this->id;
                }
            }

            private function update(){
              $query = $this->db->query("UPDATE moule_injection SET NUM_MOULE_INJECTION = '{$this->num}' WHERE ID_MOULE_INJECTION = {$this->id}");
            }

            public function delete(){
              /*foreach ($this->tab_moules_presses->tab_moule_presse as $key => $value) {
                $value->deleted = TRUE;
                $value->save();
              }*/
              $this->tab_moules_presses->delete();
            //  $query = $this->db->query("DELETE FROM moule_presse WHERE ID_MOULE_INJECTION = {$this->id}");

              $query = $this->db->query("DELETE FROM moule_injection WHERE ID_MOULE_INJECTION = {$this->id}");
            }

            public function getMouleById($id){
              $query = $this->db->query("SELECT * FROM  moule_injection m, moule_presse mp, presse p WHERE p.id_presse=mp.id_presse AND mp.id_moule_injection=m.id_moule_injection AND m.ID_MOULE_INJECTION = {$id}");
              $this->init($query->row("ID_MOULE_INJECTION"),$query->row("ID_ARTICLE"),$query->row("NUM_MOULE_INJECTION"));
              return $this;
            }

            public function getMouleByIdWithoutMP($id){

              $query = $this->db->query("SELECT * FROM  moule_injection WHERE ID_MOULE_INJECTION = {$id}");

              $this->initWithoutMP($query->row("ID_MOULE_INJECTION"),$query->row("ID_ARTICLE"),$query->row("NUM_MOULE_INJECTION"));

              return $this;
            }

            public function getMouleByNum($num){
              $query = $this->db->query("SELECT * FROM  moule_injection WHERE NUM_MOULE_INJECTION like '{$num}'");
              if($query->row("ID_MOULE_INJECTION")==null){
                 throw new Exception('Moule inexistant');
              }
              $this->init($query->row("ID_MOULE_INJECTION"),$query->row("ID_ARTICLE"),$query->row("NUM_MOULE_INJECTION"));
              return $this;
            }

            public function getIdMoulePresseByIdMouleIdPresse($idM,$idP){
              $query = $this->db->query("SELECT mp.ID_MOULE_PRESSE FROM  moule_presse mp WHERE mp.ID_MOULE_INJECTION = {$idM} AND mp.ID_PRESSE = {$idP}");
              return $query->row('ID_MOULE_PRESSE');
            }

            public function existeByNum($num){
              $res= FALSE;
              $query = $this->db->query("SELECT * FROM  moule_injection WHERE NUM_MOULE_INJECTION LIKE '{$num}'");
              if($query->row("ID_MOULE_INJECTION")!=null){
                 $res=TRUE;
              }
              return $res;
            }


}
