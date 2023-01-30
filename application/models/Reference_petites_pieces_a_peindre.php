<?php

class Reference_petites_pieces_a_peindre extends CI_Model {

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
                   'NUM_REFERENCE_PETITES_PIECES_A_PEINDRE' => $this->num
                );
              $query = $this->db->insert("reference_petites_pieces_a_peindre", $data);
                $this->id = $this->db->insert_id();
                foreach (  $this->tab_docs->tab_documents as $key => $value) {
                  $value->id_article=$this->article->id;
                }
            }

            private function update(){
              $data = array(
                   'NUM_REFERENCE_PETITES_PIECES_A_PEINDRE' => $this->num
                );

$this->db->where('ID_REFERENCE_PETITES_PIECES_A_PEINDRE', $this->id);
$this->db->update('reference_petites_pieces_a_peindre', $data);

            }

            public function delete(){
              $query = $this->db->query("DELETE FROM reference_petites_pieces_a_peindre WHERE ID_REFERENCE_PETITES_PIECES_A_PEINDRE = {$this->id}");
            }

            public function getReferenceById($id){
              $query = $this->db->query("SELECT * FROM  reference_petites_pieces_a_peindre WHERE ID_REFERENCE_PETITES_PIECES_A_PEINDRE = {$id}");
              $this->init($query->row("ID_REFERENCE_PETITES_PIECES_A_PEINDRE"),$query->row("ID_ARTICLE"),$query->row("NUM_REFERENCE_PETITES_PIECES_A_PEINDRE"));
              return $this;
            }

            public function getReferenceByNum($num){
              $query = $this->db->query("SELECT * FROM  reference_petites_pieces_a_peindre WHERE NUM_REFERENCE_PETITES_PIECES_A_PEINDRE like '{$num}'");
              if($query->row("ID_REFERENCE_PETITES_PIECES_A_PEINDRE")==null){
                 throw new Exception('Réference inexistant');
              }
              $this->init($query->row("ID_REFERENCE_PETITES_PIECES_A_PEINDRE"),$query->row("ID_ARTICLE"),$query->row("NUM_REFERENCE_PETITES_PIECES_A_PEINDRE"));
              return $this;
            }

            public function existeByNum($num){
              $res= FALSE;
              $query = $this->db->query("SELECT * FROM  reference_petites_pieces_a_peindre WHERE NUM_REFERENCE_PETITES_PIECES_A_PEINDRE LIKE '{$num}'");
              if($query->row("ID_REFERENCE_PETITES_PIECES_A_PEINDRE")!=null){
                 $res=TRUE;
              }
              return $res;
            }

}
