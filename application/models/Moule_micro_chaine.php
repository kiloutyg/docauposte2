<?php

class Moule_micro_chaine extends CI_Model {

    public $id=-1;

    public $num;

    public $prog_flammage;


    public $article = NULL;

    public $tab_docs = NULL;

    public $delete = FALSE;

    public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
                $this->load->database();
                $this->load->model('article');
                $this->load->model('documents');

        }

    public function init($id,$id_article,$num,$prog_flammage,$delete = FALSE)
        {


                $this->id=$id;
                $this->num=$num;
                $this->prog_flammage=$prog_flammage;
                $this->delete=$delete;
                $this->article = new Article();
                $this->article->getArticleById($id_article);
                $docs = new Documents();
                $docs->getDocumentsByIdArticle($this->article->id);
                $this->tab_docs= $docs;
        }

        public function initMin($id,$id_article,$num,$prog_flammage,$delete = FALSE)
            {


                    $this->id=$id;
                    $this->num=$num;
                  /*  $this->nom_prod=$nom_prod;
                    $this->prog_flammage=$prog_flammage;
                    $this->delete=$delete;
                    $this->article = new Article();
                    $this->article->getArticleById($id_article);
                    $docs = new Documents();
                    $docs->getDocumentsByIdArticle($this->article->id);
                    $this->tab_docs= $docs;*/
            }

    public function create($num,$prog_flammage)
        {
                $this->num=$num;
                $this->prog_flammage=$prog_flammage;
                $this->article = new Article();
                $this->tab_docs = new Documents();
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
              }

            }

            private function insert(){
              $this->article->save();

              $data = array(
                   'ID_ARTICLE' => $this->article->id,
                   'PROG_FLAMMAGE' => $this->prog_flammage,
                   'NUM_MOULE_MICRO_CHAINE' => $this->num,
                );
              $query = $this->db->insert("moule_micro_chaine", $data);
                $this->id = $this->db->insert_id();
                foreach (  $this->tab_docs->tab_documents as $key => $value) {
                  $value->id_article=$this->article->id;
                }
            }

            private function update(){

              $data = array(
                   'PROG_FLAMMAGE' => $this->prog_flammage,
                   'NUM_MOULE_MICRO_CHAINE' => $this->num,
                );

$this->db->where('ID_MOULE_MICRO_CHAINE', $this->id);
$this->db->update('moule_micro_chaine', $data);
            }

            public function delete(){
              $query = $this->db->query("DELETE FROM moule_micro_chaine WHERE ID_MOULE_MICRO_CHAINE = {$this->id}");
            }

            public function getMouleById($id){
              $query = $this->db->query("SELECT * FROM  moule_micro_chaine WHERE ID_MOULE_MICRO_CHAINE = {$id}");
              $this->init($query->row("ID_MOULE_MICRO_CHAINE"),$query->row("ID_ARTICLE"),$query->row("NUM_MOULE_MICRO_CHAINE"),$query->row("PROG_FLAMMAGE"));
              return $this;
            }

            public function getMouleByNum($num){
              $query = $this->db->query("SELECT * FROM  moule_micro_chaine WHERE NUM_MOULE_MICRO_CHAINE LIKE '{$num}'");
              if($query->row("ID_MOULE_MICRO_CHAINE")==null){
                 throw new Exception('Moule inexistant');
              }
              $this->init($query->row("ID_MOULE_MICRO_CHAINE"),$query->row("ID_ARTICLE"),$query->row("NUM_MOULE_MICRO_CHAINE"),$query->row("PROG_FLAMMAGE"));
              return $this;
            }

            public function existeByNum($num){
              $res= FALSE;
              $query = $this->db->query("SELECT * FROM  moule_micro_chaine WHERE NUM_MOULE_MICRO_CHAINE LIKE '{$num}'");
              if($query->row("ID_MOULE_MICRO_CHAINE")!=null){
                 $res=TRUE;
              }
              return $res;
            }


}
