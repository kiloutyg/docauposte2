<?php

class Reference_ilot_packaging extends CI_Model {

    public $id=-1;

    public $num;

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
                    $this->tab_docs= $docs;*/
            }

    public function create($num)
        {
                $this->num=$num;
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
                   'NUM_REFERENCE_ILOT_PACKAGING' => $this->num
                );
              $query = $this->db->insert("reference_ilot_packaging", $data);
                $this->id = $this->db->insert_id();
                foreach (  $this->tab_docs->tab_documents as $key => $value) {
                  $value->id_article=$this->article->id;
                }
            }

            private function update(){
              $data = array(
                   'NUM_REFERENCE_ILOT_PACKAGING' => $this->num
                );

$this->db->where('ID_REFERENCE_ILOT_PACKAGING', $this->id);
$this->db->update('reference_ilot_packaging', $data);

            }

            public function delete(){
              $query = $this->db->query("DELETE FROM reference_ilot_packaging WHERE ID_REFERENCE_ILOT_PACKAGING = {$this->id}");
            }

            public function getReferenceById($id){
              $query = $this->db->query("SELECT * FROM  reference_ilot_packaging WHERE ID_REFERENCE_ILOT_PACKAGING = {$id}");
              $this->init($query->row("ID_REFERENCE_ILOT_PACKAGING"),$query->row("ID_ARTICLE"),$query->row("NUM_REFERENCE_ILOT_PACKAGING"));
              return $this;
            }

            public function getReferenceByNum($num){
              $query = $this->db->query("SELECT * FROM  reference_ilot_packaging WHERE NUM_REFERENCE_ILOT_PACKAGING like '{$num}'");
              if($query->row("ID_REFERENCE_ILOT_PACKAGING")==null){
                 throw new Exception('Réference inexistant');
              }
              $this->init($query->row("ID_REFERENCE_ILOT_PACKAGING"),$query->row("ID_ARTICLE"),$query->row("NUM_REFERENCE_ILOT_PACKAGING"));
              return $this;
            }

            public function existeByNum($num){
              $res= FALSE;
              $query = $this->db->query("SELECT * FROM  reference_ilot_packaging WHERE NUM_REFERENCE_ILOT_PACKAGING LIKE '{$num}'");
              if($query->row("ID_REFERENCE_ILOT_PACKAGING")!=null){
                 $res=TRUE;
              }
              return $res;
            }

}
