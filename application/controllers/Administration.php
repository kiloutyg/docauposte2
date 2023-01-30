<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class administration extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
private $data = array('style' =>"" ,'javascript' =>"" );
	public function __construct()
	{
		parent::__construct();
		$this->output->delete_cache();
		$this->load->library('session');
		$this->data['style'] .= css_balise('style_admin');
		$this->data['javascript'].=js_balise('jquery-2.2.3.min');
		//var_dump($this->session->all_userdata());
	}

/*permet de verifier authorisation et de renvoyer sur la page de connection du modul corespondant*/
	public function verif_auth($nom,$loop=FALSE){
		$auth = ($this->session->userdata('authorization')!=NULL)?$this->session->userdata('authorization'):'none';
		$this->session->set_userdata('authorization', $auth);
		if($auth=='none' && !$loop){
			redirect('administration/connection/'.$nom);
		}
		return $auth;

	}

	public function index()
	{
		$this->load->view('welcome_message');
	}
/*
	public function inj(){
		$ref = NULL;

			$sql1 = "SELECT m.id_moule, m.num_moule, m.nom_prod, p.num_presse, m.lien_erreur FROM moule m, presse p WHERE m.id_presse = p.id_presse";

			$dbhost = 'localhost';
			 $dbuser = 'user_mattec';
			 $dbpass = 'user_mattec';
			 $db='mattec';

			 $dbuser2 = 'user_mattec';
			 $dbpass2 = 'user_mattec';
			 $db2='docauposte';

			 $mysqli = new mysqli($dbhost, $dbuser, $dbpass, $db);
			 $mysqli2 = new mysqli($dbhost, $dbuser2, $dbpass2, $db2);

	 //Vérification de la connexion
	 if ($mysqli->connect_errno) {
			 printf("Échec de la connexion : %s\n", $mysqli->connect_error);
			 exit();
	 }

	if ($result1 = $mysqli->query($sql1)) {
		while ($row1 = $result1->fetch_array(MYSQLI_ASSOC)){
			$ref['num'] = str_replace('.','-',$row1['num_moule']);

			$t="SELECT NUM_MOULE_INJECTION FROM moule_injection WHERE NUM_MOULE_INJECTION LIKE '".$ref['num']."'";
			$tt=-1;
				if ($re = $mysqli2->query($t)) {
					$r = $re->fetch_array(MYSQLI_ASSOC);
					$tt=($r['NUM_MOULE_INJECTION'])?$r['NUM_MOULE_INJECTION']:-1;
				}
				if($tt==-1){




		$ref['nom_prod'] = str_replace('*',' ',$row1['nom_prod']);
		$ref['num_presse'] = $row1['num_presse'];
		$ref['doc']['new'] = array();
		$old = array('http://sruiwp0136/site/page/user/pdf_presse/','.pdf');
		$ref['incident_qualite']=str_replace($old,'',$row1['lien_erreur']);

		$ref['information']="";

$sq="SELECT ID_PRESSE FROM presse WHERE NUM_PRESSE LIKE '".$ref['num_presse']."'";
$p=-1;
	if ($re = $mysqli2->query($sq)) {
		$r = $re->fetch_array(MYSQLI_ASSOC);
		$p=($r['ID_PRESSE'])?$r['ID_PRESSE']:-1;
	}
		$ref['presse'][$p] = 'on';


			$sql = "SELECT t.nom_lien,d.lien FROM docs d, doc_type t WHERE d.id_typedoc = t.id_typedoc and d.id_moule =".$row1['id_moule'];
	if ($result = $mysqli->query($sql)) {

		$search   = array("http://vivaldi_ruitz/retr_doc.aspx?code=", "&dossier=2");
	$i= 0;
	while ($row = $result->fetch_array(MYSQLI_ASSOC)) {

		$row['lien'] = str_replace($search,'',$row['lien']);

		//print_r($row);
		$doc = array();
		$doc['lien'] = $row['lien'];

	$id=-1;
		$sql2 = 'SELECT ID_DOC_TYPE FROM doc_type WHERE NOM_DOC_TYPE like "'.$row['nom_lien'].'"';
		if ($result2 = $mysqli2->query($sql2)) {
			$row2 = $result2->fetch_array(MYSQLI_ASSOC);
			$id=($row2['ID_DOC_TYPE'])?$row2['ID_DOC_TYPE']:-1;

			$result2->close();
		}
if($id==-1){

	$add = "INSERT INTO `docauposte`.`doc_type` (`ID_DOC_TYPE`, `NOM_DOC_TYPE`) VALUES (NULL, '".$row['nom_lien']."');";
	$mysqli2->query($add);


	$sql2 = 'SELECT ID_DOC_TYPE FROM doc_type WHERE NOM_DOC_TYPE like "'.$row['nom_lien'].'"';
	if ($result2 = $mysqli2->query($sql2)) {
		$row2 = $result2->fetch_array(MYSQLI_ASSOC);
		$id=($row2['ID_DOC_TYPE'])?$row2['ID_DOC_TYPE']:-1;

		$result2->close();
	}
}
		$doc['id_doc_type'] = $id;
		if($row['nom_lien']=="Working Instruction 1"){
			$doc['w']="la";
		}
			//print_r($doc);
	$ref['doc']['new'][$i]=$doc;
	$i++;
	}

	$m="SELECT ID_MOULE_INJECTION FROM moule_injection WHERE NUM_MOULE_INJECTION LIKE '".$ref['num']."'";
	$f=-1;
		if ($de = $mysqli2->query($m)) {
			$d = $de->fetch_array(MYSQLI_ASSOC);
			$f=($d['ID_MOULE_INJECTION'])?$d['ID_MOULE_INJECTION']:-1;
		}
			$f;
	if($f==-1){
		echo "<pre style='background-color:green'>";
	}
	else{
		echo "<pre style='background-color:orange'>";
	}

	print_r($ref);
	echo "</pre>";
	$this->new_moule_injection($ref);
			$result->close();
			$ref=NULL;
	}
	}

}
	}
	$mysqli->close();
	$mysqli2->close();
	}

	public function ilot(){
		$ref = NULL;

		  $sql1 = "SELECT id_moule, num_moule, nom_prod FROM moule";

	    $dbhost = 'localhost';
	     $dbuser = 'user_ilot';
	     $dbpass = 'user_ilot';
	     $db='ilot_packaging';

	     $dbuser2 = 'user_mattec';
	     $dbpass2 = 'user_mattec';
	     $db2='docauposte';

	     $mysqli = new mysqli($dbhost, $dbuser, $dbpass, $db);
	     $mysqli2 = new mysqli($dbhost, $dbuser2, $dbpass2, $db2);

	 // Vérification de la connexion
	 if ($mysqli->connect_errno) {
	     printf("Échec de la connexion : %s\n", $mysqli->connect_error);
	     exit();
	 }

	if ($result1 = $mysqli->query($sql1)) {
		while ($row1 = $result1->fetch_array(MYSQLI_ASSOC)){
		$ref['num'] = str_replace('.','-',$row1['num_moule']);
		$ref['nom_prod'] = str_replace('*',' ',$row1['nom_prod']);
		$ref['doc']['new'] = array();
		$ref['incident_qualite']="";

		$ref['information']="";

		  $sql = "SELECT t.nom_lien,d.lien FROM docs d, doc_type t WHERE d.id_typedoc = t.id_typedoc and d.id_moule =".$row1['id_moule'];
	if ($result = $mysqli->query($sql)) {

		$search   = array("http://vivaldi_ruitz/retr_doc.aspx?code=", "&dossier=2");
	$i= 0;
	while ($row = $result->fetch_array(MYSQLI_ASSOC)) {

		$row['lien'] = str_replace($search,'',$row['lien']);

	  //print_r($row);
		$doc = array();
		$doc['lien'] = $row['lien'];

	$id=-1;
		$sql2 = 'SELECT ID_DOC_TYPE FROM doc_type WHERE NOM_DOC_TYPE like "'.$row['nom_lien'].'"';
		if ($result2 = $mysqli2->query($sql2)) {
		  $row2 = $result2->fetch_array(MYSQLI_ASSOC);
		  $id=($row2['ID_DOC_TYPE'])?$row2['ID_DOC_TYPE']:-1;

		  $result2->close();
		}
		if($id==-1){

			$add = "INSERT INTO `docauposte`.`doc_type` (`ID_DOC_TYPE`, `NOM_DOC_TYPE`) VALUES (NULL, '".$row['nom_lien']."');";
			$mysqli2->query($add);


			$sql2 = 'SELECT ID_DOC_TYPE FROM doc_type WHERE NOM_DOC_TYPE like "'.$row['nom_lien'].'"';
			if ($result2 = $mysqli2->query($sql2)) {
				$row2 = $result2->fetch_array(MYSQLI_ASSOC);
				$id=($row2['ID_DOC_TYPE'])?$row2['ID_DOC_TYPE']:-1;

				$result2->close();
			}
		}
		$doc['id_doc_type'] = $id;
			//print_r($doc);
	$ref['doc']['new'][$i]=$doc;
	$i++;
	}
	echo "<pre>";
	print_r($ref);
	echo "</pre>";
	$this->new_reference_ilot_packaging($ref);
	    $result->close();
	}
	}


	}
	$mysqli->close();
	$mysqli2->close();
	}

	public function mc(){
		$ref = NULL;

		  $sql1 = "SELECT id_moule, num_moule, nom_prod, prog_flammage FROM moule";

	    $dbhost = 'localhost';
	     $dbuser = 'user_micro';
	     $dbpass = 'user_mattec';
	     $db='micro_chaine';

	     $dbuser2 = 'user_mattec';
	     $dbpass2 = 'user_mattec';
	     $db2='docauposte';

	     $mysqli = new mysqli($dbhost, $dbuser, $dbpass, $db);
	     $mysqli2 = new mysqli($dbhost, $dbuser2, $dbpass2, $db2);

	 // Vérification de la connexion
	 if ($mysqli->connect_errno) {
	     printf("Échec de la connexion : %s\n", $mysqli->connect_error);
	     exit();
	 }

	if ($result1 = $mysqli->query($sql1)) {
		while ($row1 = $result1->fetch_array(MYSQLI_ASSOC)){
		$ref['num'] = str_replace('.','-',$row1['num_moule']);
		$ref['nom_prod'] = str_replace('*',' ',$row1['nom_prod']);
		$ref['prog_flammage'] =$row1['prog_flammage'];
		$ref['doc']['new'] = array();
		$ref['incident_qualite']="";

		$ref['information']="";

		  $sql = "SELECT t.nom_lien,d.lien FROM docs d, doc_type t WHERE d.id_typedoc = t.id_typedoc and d.id_moule =".$row1['id_moule'];
	if ($result = $mysqli->query($sql)) {

		$search   = array("http://vivaldi_ruitz/retr_doc.aspx?code=", "&dossier=2");
	$i= 0;
	while ($row = $result->fetch_array(MYSQLI_ASSOC)) {

		$row['lien'] = str_replace($search,'',$row['lien']);

	  //print_r($row);
		$doc = array();
		$doc['lien'] = $row['lien'];

	$id=-1;
		$sql2 = 'SELECT ID_DOC_TYPE FROM doc_type WHERE NOM_DOC_TYPE like "'.$row['nom_lien'].'"';
		if ($result2 = $mysqli2->query($sql2)) {
		  $row2 = $result2->fetch_array(MYSQLI_ASSOC);
			$id=($row2['ID_DOC_TYPE'])?$row2['ID_DOC_TYPE']:-1;

		  $result2->close();
		}
		if($id==-1){

			$add = "INSERT INTO `docauposte`.`doc_type` (`ID_DOC_TYPE`, `NOM_DOC_TYPE`) VALUES (NULL, '".$row['nom_lien']."');";
			$mysqli2->query($add);


			$sql2 = 'SELECT ID_DOC_TYPE FROM doc_type WHERE NOM_DOC_TYPE like "'.$row['nom_lien'].'"';
			if ($result2 = $mysqli2->query($sql2)) {
				$row2 = $result2->fetch_array(MYSQLI_ASSOC);
				$id=($row2['ID_DOC_TYPE'])?$row2['ID_DOC_TYPE']:-1;

				$result2->close();
			}
		}
		$doc['id_doc_type'] = $id;
			//print_r($doc);
	$ref['doc']['new'][$i]=$doc;
	$i++;
	}
	echo "<pre>";
	print_r($ref);
	echo "</pre>";

	$this->new_moule_micro_chaine($ref);
	    $result->close();
	}
	}


	}
	$mysqli->close();
	$mysqli2->close();
	}
*/
/*
public function nomenclatureIlot(){
	$ref = NULL;

		$sql1 = "SELECT ID_ARTICLE,NUM_REFERENCE_ILOT_PACKAGING FROM `reference_ilot_packaging`";

		$dbhost = 'localhost';
		 $dbuser = 'user_mattec';
		 $dbpass = 'user_mattec';
		 $db='docauposte';

		 $dbuser2 = 'user_mattec';
		 $dbpass2 = 'user_mattec';
		 $db2='docauposte';

		 $mysqli = new mysqli($dbhost, $dbuser, $dbpass, $db);
		 $mysqli2 = new mysqli($dbhost, $dbuser2, $dbpass2, $db2);

 // Vérification de la connexion
 if ($mysqli->connect_errno) {
		 printf("Échec de la connexion : %s\n", $mysqli->connect_error);
		 exit();
 }

if ($result1 = $mysqli->query($sql1)) {
	while ($row1 = $result1->fetch_array(MYSQLI_ASSOC)){
		$tab = explode("-", $row1['NUM_REFERENCE_ILOT_PACKAGING']);
		foreach ($tab as $key => $value) {
			$sql2="INSERT INTO `reference_sap` (ID_ARTICLE,ID_SAP) VALUES (".$row1['ID_ARTICLE'].",{$value})";
			$mysqli->query($sql2);
		}
	}

	}
}
*/
/*effacer les session de tout les utilisateurs*/
/*sans protection de connection*/
public function delete_session(){
	$this->session->sess_destroy();
}
/*parametre loop, permet de savoir si on est en phase de formulaire ou de reception*/
  public function connection($nom,$loop=FALSE)
	{
		$auth = $this->verif_auth($nom,TRUE);
		if($auth =='none'){

			if(  ( $loop || empty($this->input->post())) ){
			    $this->load->helper('form');

					/* /!\test si le nom existe */

					$this->data['nom']=$nom;

					if($nom=="injection"){
						$this->data['titre']="Injection";
					}
					elseif($nom=="micro_chaine"){
						$this->data['titre']="Micro chaine";
					}
					elseif($nom=="ilot_packaging"){
						$this->data['titre']="Ilot packaging";
					}
					elseif($nom=="reprise_petite_pr"){
						$this->data['titre']="Reprise petite PR";
						}
					elseif($nom=="assemblage"){
						$this->data['titre']="Assemblage";
					}
					elseif($nom=="petites_pieces_a_peindre"){
						$this->data['titre']="Petites pièces à peindre";
					}

			    $attributes = array('id' => 'login', 'name' => 'myform');
					/*controleur de verification de connection*/
					$this->data['contenu']="";
			    $this->data['contenu'].=form_open('administration/connection/'.$nom,$attributes);
			    $this->data['contenu'].=form_label('Identifiant : ', 'identifiant');
			    $input = array(
			              'name'        => 'identifiant',
			              'id'          => 'identifiant',
										'class'				=> 'inputClass',
										'required'			=> 'TRUE',
			              'value'       => ''
			            );
			    $this->data['contenu'].=form_input($input);
			    $this->data['contenu'].=form_label('Mots de passe : ', 'password');
			    $input = array(
			              'name'        => 'password',
			              'id'          => 'password',
										'class'				=> 'inputClass',
										'required'			=> 'TRUE',
			              'value'       => ''
			            );
			    $this->data['contenu'].=form_password($input);
					$this->data['contenu'].=form_submit('connection', 'Connection');
			    $this->data['contenu'].=form_close();
			    $this->load->view('templates/Admin', $this->data);
					}
					else{
						/*10.112.4.48 la mienne */
						/*10.112.4.80 DAMI0284*/
						$ip = $this->input->ip_address();
						$user = gethostbyaddr($ip);
						/*connection automatique*/
						$tab_autorise_less = array("Patrick"=>"dami0290.ad.ponet");
						$tab_autorise_full = array("remi"=>"lrui0134.ad.ponet","kevin"=>"LLAN0808.ad.ponet");
						$autorise_less=in_array($user,$tab_autorise_less);
						$autorise_full=in_array($user,$tab_autorise_full);
						$id = $this->input->post('identifiant');
						$passw = $this->input->post('password');
						if(($id=="administrateur" && $passw=="Admin02") || $autorise_full){
							$auth ='full';
							$this->session->set_userdata('authorization', $auth);
								redirect('administration/connection/'.$nom);

						}
						elseif (($id=="administrateur" && $passw=="Admin01") || $autorise_less) {
							$auth ='less';
							$this->session->set_userdata('authorization', $auth);
							redirect('administration/connection/'.$nom);
						}
						else { // echec de connection
						redirect('administration/connection/'.$nom);
						}
					}



		}
		else{
			if($nom=="injection"){
					redirect('administration/injection');
				//$this->injection();
			}
			elseif($nom=="micro_chaine"){
				redirect('administration/micro_chaine');
			}
			elseif($nom=="ilot_packaging"){
				redirect('administration/ilot_packaging');
			}
			elseif($nom=="reprise_petite_pr"){
				redirect('administration/reprise_petite_pr');
			}
			elseif($nom=="petites_pieces_a_peindre"){
				redirect('administration/petites_pieces_a_peindre');
			}
			else{
				echo "ici";
			}
		}
	}

	public function injection()
	{
		/*$this->load->model('moules_micro_chaine');
		$td = new Moules_micro_chaine();
		$td->getAllmoules();
		//$td->getMouleById(0);
	/*$tab_article =	$td->getAllArticles();
	$tab_article[0]->incident_qualite->lien = "fb.fr";
		$td->save();



echo "<pre>";
		var_dump($td);
echo "</pre>";
*/

		$this->data['nom']="injection";
		$this->data['titre']="Injection";
		$base_url=base_url();
		//$this->data['contenu']=json_encode($this->input->post());
		$this->data['contenu']="";
		$this->data['contenu'].="<div id='liste'>";
$auth = $this->verif_auth('injection');
	$this->data['contenu'].=($auth=='full')?"<a class='itemListe' href='{$base_url}administration/gestion_presse/injection' >Gestion presses</a>":"";
	$this->data['contenu'].="<a class='itemListe' href='{$base_url}administration/gestion_moules/injection'>Gestion moules</a>";
	$this->data['contenu'].=($auth=='full')?"<a class='itemListe' href='{$base_url}administration/gestion_typeDoc/injection'>Gestion des types de document</a>":"";
	$this->data['contenu'].="</div>";
		$this->load->view('templates/Admin', $this->data);
	}
	public function assemblage()
	{
		/*$this->load->model('moules_micro_chaine');
		$td = new Moules_micro_chaine();
		$td->getAllmoules();
		//$td->getMouleById(0);
	/*$tab_article =	$td->getAllArticles();
	$tab_article[0]->incident_qualite->lien = "fb.fr";
		$td->save();



echo "<pre>";
		var_dump($td);
echo "</pre>";
*/

		$this->data['nom']="assemblage";
		$this->data['titre']="Assemblage";
		$base_url=base_url();
		//$this->data['contenu']=json_encode($this->input->post());
		$this->data['contenu']="";
		$this->data['contenu'].="<div id='liste'>";
$auth = $this->verif_auth('assemblage');
	$this->data['contenu'].=($auth=='full')?"<a class='itemListe' href='{$base_url}administration/gestion_presse/assemblage' >Gestion presses</a>":"";
	$this->data['contenu'].="<a class='itemListe' href='{$base_url}administration/gestion_moules/assemblage'>Gestion moules</a>";
	$this->data['contenu'].=($auth=='full')?"<a class='itemListe' href='{$base_url}administration/gestion_typeDoc/assemblage'>Gestion des types de document</a>":"";
	$this->data['contenu'].="</div>";
		$this->load->view('templates/Admin', $this->data);
	}
  public function micro_chaine()
	{
		$this->data['nom']="micro_chaine";
		$this->data['titre']="Micro chaine";
		$base_url=base_url();
		//$this->data['contenu']=json_encode($this->input->post());
		$this->data['contenu']="";
		$this->data['contenu'].="<div id='liste'>";
$auth = $this->verif_auth('injection');
	$this->data['contenu'].="<a class='itemListe' href='{$base_url}administration/gestion_moules/micro_chaine'>Gestion moules</a>";
	$this->data['contenu'].=($auth=='full')?"<a class='itemListe' href='{$base_url}administration/gestion_typeDoc/micro_chaine'>Gestion des types de document</a>":"";
	$this->data['contenu'].="</div>";
		$this->load->view('templates/Admin', $this->data);
	}

  public function ilot_packaging()
	{
		$this->data['nom']="ilot_packaging";
		$this->data['titre']="Ilot packaging";
		$base_url=base_url();
		//$this->data['contenu']=json_encode($this->input->post());
		$this->data['contenu']="";
		$this->data['contenu'].="<div id='liste'>";
		$auth = $this->verif_auth('injection');
		$this->data['contenu'].="<a class='itemListe' href='{$base_url}administration/gestion_references_ilot_packaging/ilot_packaging'>Gestion référence</a>";
		$this->data['contenu'].=($auth=='full')?"<a class='itemListe' href='{$base_url}administration/gestion_typeDoc/ilot_packaging'>Gestion des types de document</a>":"";
		$this->data['contenu'].="</div>";
		$this->load->view('templates/Admin', $this->data);
	}

  public function reprise_petite_pr()
	{
		$this->data['nom']="reprise_petite_pr";
		$this->data['titre']="Reprise petite PR";
		$base_url=base_url();
		//$this->data['contenu']=json_encode($this->input->post());
		$this->data['contenu']="";
		$this->data['contenu'].="<div id='liste'>";
		$auth = $this->verif_auth('injection');
		$this->data['contenu'].="<a class='itemListe' href='{$base_url}administration/gestion_references_reprise_petite_pr/reprise_petite_pr'>Gestion référence</a>";
		$this->data['contenu'].=($auth=='full')?"<a class='itemListe' href='{$base_url}administration/gestion_typeDoc/reprise_petite_pr'>Gestion des types de document</a>":"";
		$this->data['contenu'].="</div>";
		$this->load->view('templates/Admin', $this->data);
	}

  public function petites_pieces_a_peindre()
	{
		$this->data['nom']="petites_pieces_a_peindre";
		$this->data['titre']="Petites pièces à peindre";
		$base_url=base_url();
		//$this->data['contenu']=json_encode($this->input->post());
		$this->data['contenu']="";
		$this->data['contenu'].="<div id='liste'>";
		$auth = $this->verif_auth('injection');
		$this->data['contenu'].="<a class='itemListe' href='{$base_url}administration/gestion_references_petites_pieces_a_peindre/petites_pieces_a_peindre'>Gestion référence</a>";
		$this->data['contenu'].=($auth=='full')?"<a class='itemListe' href='{$base_url}administration/gestion_typeDoc/petites_pieces_a_peindre'>Gestion des types de document</a>":"";
		$this->data['contenu'].="</div>";
		$this->load->view('templates/Admin', $this->data);
	}

	public function gestion_typeDoc($nom){
		$this->load->helper('form');
		$this->load->database();
		$this->data['style'] .= css_balise('bootstrap.min');
		$this->data['style'] .= css_balise('bootstrap-toggle.min');
		/*mon style prioritaire*/$this->data['style'] .= css_balise('style_admin');
		$this->data['javascript'].=js_balise('bootstrap-toggle.min');
		$this->data['nom']=$nom;
		//marqué le type
		$this->data['titre']="Gestion type documents";
		//$this->data['contenu']=json_encode($this->input->post());
		$this->data['contenu']="";
		$this->load->model('typesDocuments');
		$tab_typeDocs = $this->typesDocuments->getAllTypesDocuments();
		$this->data['contenu'].=$this->listFormGenerator('liste_presses',$tab_typeDocs,'id','label',"gestion_typeDoc");
		$this->load->view('templates/Admin', $this->data);
	}

	public function gestion_presse($nom)
	{

			$this->load->helper('form');
			$this->load->database();

			$this->data['style'] .= css_balise('bootstrap.min');
			$this->data['style'] .= css_balise('bootstrap-toggle.min');
			/*mon style prioritaire*/$this->data['style'] .= css_balise('style_admin');
			$this->data['javascript'].=js_balise('bootstrap-toggle.min');


		$this->data['nom']=$nom;
		//marqué le type
		$this->data['titre']="Gestion presse";
		//$this->data['contenu']=json_encode($this->input->post());
		$this->data['contenu']="";

$this->load->model('presses');
$tab_presses = $this->presses->getAllPresses();

$this->data['contenu'].=$this->listFormGenerator('liste_presses',$tab_presses,'id','num',"gestion_presse");

		$this->load->view('templates/Admin', $this->data);
	}

	public function gestion_moules($nom)
	{
		$this->load->helper('form');

		$base_url=base_url();

		$this->data['style'] .= css_balise('bootstrap.min');
		$this->data['style'] .= css_balise('bootstrap-toggle.min');
		/*mon style prioritaire*/$this->data['style'] .= css_balise('style_admin');
		$this->data['javascript'].=js_balise('bootstrap-toggle.min');

		$this->data['nom']=$nom;
		//marqué le type
		$this->data['titre']="Gestion moules";
		//$this->data['contenu']=json_encode($this->input->post());
		$allNum = NULL;
		switch ($nom) {
			case 'micro_chaine':
				$this->load->model('moules_micro_chaine');
				$allNum = $this->moules_micro_chaine->getAllNum();
				break;

			case 'injection':
				$this->load->model('moules_injection');
				$allNum = $this->moules_injection->getAllNum();
				break;

			default:
				# code...
				break;
		}
$auth = $this->verif_auth('injection');
$this->data['contenu']="<div id='liste'>";
$this->data['contenu'].=($auth=="full")?"<div class='itemListe'  onClick='nouveau_moule();' href='' >Nouveau moule</div>":"";

$listInteractive = '<label for="input_num_moules" style="border-top: rgb(0, 85, 161) 1px solid;padding-top: 10px;">Choisir un moule:</label><input list="num_moules" type="text" class="itemListe" id="input_num_moules"> <datalist id="num_moules">';
foreach ($allNum as $key => $value) {
	$listInteractive .="<option value='$value'>";
}
$listInteractive .="</datalist>";


$this->data['contenu'].=$listInteractive;
		$this->data['contenu'].=($auth!="full")?"<div class='itemListe' onClick='gestion();' href=''>Modification du moule</div>":<<<HTML
			<div class="doubleIteme">
				<div class='itemListe' onClick='gestion();' href=''>Modification du moule</div>
				<div class='itemListe' onClick='clonner();' href='' title='Pré-remplir une création'>Dupliquer un moule</div>
			</div>
		</div>
HTML;
$this->data['contenu'].=($auth!="full")?"":<<<HTML
		<script>

		var redirect =(function(num){
			document.location.href = "{$base_url}administration/gestion_moule/{$nom}/"+num;
		});

		var nouveau_moule =(function(){
			redirect("");
		});

		var clonner =(function(){
			var num = $("#input_num_moules").val();
			redirect(num+"/TRUE");
		});
</script>
HTML;

$this->data['contenu'].=<<<HTML
<script>


var redirect =(function(num){
	document.location.href = "{$base_url}administration/gestion_moule/{$nom}/"+num;
});
		var gestion =(function(){
			var num = $("#input_num_moules").val();
			if(num!=''){
				redirect(num);
			}
		});

		</script>
HTML;




		$this->load->view('templates/Admin', $this->data);
	}

	public function gestion_references_ilot_packaging($nom)
	{
		$this->load->helper('form');
		$this->load->model('references_ilot_packaging');
		$base_url=base_url();
		$this->data['style'] .= css_balise('bootstrap.min');
		$this->data['style'] .= css_balise('bootstrap-toggle.min');
		/*mon style prioritaire*/$this->data['style'] .= css_balise('style_admin');
		$this->data['javascript'].=js_balise('bootstrap-toggle.min');
		$this->data['nom']=$nom;
		//marqué le type
		$this->data['titre']="Gestion references";
		//$this->data['contenu']=json_encode($this->input->post());
		$allNum = $this->references_ilot_packaging->getAllNum();
		$auth = ($this->session->userdata('authorization')!=NULL)?$this->session->userdata('authorization'):'none';
		$this->data['contenu']="";
		$this->data['contenu'].="<div id='liste'>";
		$this->data['contenu'].=($auth=="full")?"<div class='itemListe'  onClick='nouvelle_reference();' href='' >Nouvelle reference</div>":"";
		$listInteractive = '<label for="input_num_moules" style="border-top: rgb(0, 85, 161) 1px solid;padding-top: 10px;">Choisir une reference:</label><input class="itemListe" list="num_references" type="text" id="input_num_references"> <datalist id="num_references">';
		foreach ($allNum as $key => $value) {
			$listInteractive .="<option value='$value'>";
		}
		$listInteractive .="</datalist>";
		$this->data['contenu'].=$listInteractive;
		$this->data['contenu'].="<div class='doubleIteme'>";
		$this->data['contenu'].="<div class='itemListe' onClick='gestion();' href=''>Modification reference</div>";
		$this->data['contenu'].=($auth=="full")?"<div class='itemListe' onClick='clonner();' href='' title='Pré-remplir une création'>Dupliquer une reference</div>":"";
		$this->data['contenu'].="</div>";
		$this->data['contenu'].="</div>";
		$this->data['contenu'].=<<<HTML
		<script>
		var redirect =(function(num){
		document.location.href = "{$base_url}administration/gestion_reference_ilot_packaging/{$nom}/"+num;
		});
		var gestion =(function(){
		var num = $("#input_num_references").val();
		redirect(num);
		});
		</script>
HTML;
		if($auth=="full"){
		$this->data['contenu'].=<<<HTML
		<script>
		var nouvelle_reference =(function(){
			redirect("");
		});
		var clonner =(function(){
			var num = $("#input_num_references").val();
			redirect(num+"/TRUE");
		});
		</script>
HTML;
		}
		$this->load->view('templates/Admin', $this->data);
	}

	public function gestion_references_reprise_petite_pr($nom)
	{
		$this->load->helper('form');
		$this->load->model('references_reprise_petite_pr');
		$base_url=base_url();
		$this->data['style'] .= css_balise('bootstrap.min');
		$this->data['style'] .= css_balise('bootstrap-toggle.min');
		/*mon style prioritaire*/$this->data['style'] .= css_balise('style_admin');
		$this->data['javascript'].=js_balise('bootstrap-toggle.min');
		$this->data['nom']=$nom;
		//marqué le type
		$this->data['titre']="Gestion references";
		//$this->data['contenu']=json_encode($this->input->post());
		$allNum = $this->references_reprise_petite_pr->getAllNum();
		$auth = ($this->session->userdata('authorization')!=NULL)?$this->session->userdata('authorization'):'none';
		$this->data['contenu']="";
		$this->data['contenu'].="<div id='liste'>";
		$this->data['contenu'].=($auth=="full")?"<div class='itemListe'  onClick='nouvelle_reference();' href='' >Nouvelle reference</div>":"";
		$listInteractive = '<label for="input_num_moules" style="border-top: rgb(0, 85, 161) 1px solid;padding-top: 10px;">Choisir une reference:</label><input class="itemListe" list="num_references" type="text" id="input_num_references"> <datalist id="num_references">';
		foreach ($allNum as $key => $value) {
			$listInteractive .="<option value='$value'>";
		}
		$listInteractive .="</datalist>";
		$this->data['contenu'].=$listInteractive;
		$this->data['contenu'].="<div class='doubleIteme'>";
		$this->data['contenu'].="<div class='itemListe' onClick='gestion();' href=''>Modification reference</div>";
		$this->data['contenu'].=($auth=="full")?"<div class='itemListe' onClick='clonner();' href='' title='Pré-remplir une création'>Dupliquer une reference</div>":"";
		$this->data['contenu'].="</div>";
		$this->data['contenu'].="</div>";
		$this->data['contenu'].=<<<HTML
		<script>
		var redirect =(function(num){
		document.location.href = "{$base_url}administration/gestion_reference_reprise_petite_pr/{$nom}/"+num;
		});
		var gestion =(function(){
		var num = $("#input_num_references").val();
		redirect(num);
		});
		</script>
HTML;
		if($auth=="full"){
		$this->data['contenu'].=<<<HTML
		<script>
		var nouvelle_reference =(function(){
			redirect("");
		});
		var clonner =(function(){
			var num = $("#input_num_references").val();
			redirect(num+"/TRUE");
		});
		</script>
HTML;
		}
		$this->load->view('templates/Admin', $this->data);
	}

	public function gestion_references_petites_pieces_a_peindre($nom)
	{
		$this->load->helper('form');
		$this->load->model('references_petites_pieces_a_peindre');
		$base_url=base_url();
		$this->data['style'] .= css_balise('bootstrap.min');
		$this->data['style'] .= css_balise('bootstrap-toggle.min');
		/*mon style prioritaire*/$this->data['style'] .= css_balise('style_admin');
		$this->data['javascript'].=js_balise('bootstrap-toggle.min');
		$this->data['nom']=$nom;
		//marqué le type
		$this->data['titre']="Gestion references";
		//$this->data['contenu']=json_encode($this->input->post());
		$allNum = $this->references_petites_pieces_a_peindre->getAllNum();
		$auth = ($this->session->userdata('authorization')!=NULL)?$this->session->userdata('authorization'):'none';
		$this->data['contenu']="";
		$this->data['contenu'].="<div id='liste'>";
		$this->data['contenu'].=($auth=="full")?"<div class='itemListe'  onClick='nouvelle_reference();' href='' >Nouvelle reference</div>":"";
		$listInteractive = '<label for="input_num_moules" style="border-top: rgb(0, 85, 161) 1px solid;padding-top: 10px;">Choisir une reference:</label><input class="itemListe" list="num_references" type="text" id="input_num_references"> <datalist id="num_references">';
		foreach ($allNum as $key => $value) {
			$listInteractive .="<option value='$value'>";
		}
		$listInteractive .="</datalist>";
		$this->data['contenu'].=$listInteractive;
		$this->data['contenu'].="<div class='doubleIteme'>";
		$this->data['contenu'].="<div class='itemListe' onClick='gestion();' href=''>Modification reference</div>";
		$this->data['contenu'].=($auth=="full")?"<div class='itemListe' onClick='clonner();' href='' title='Pré-remplir une création'>Dupliquer une reference</div>":"";
		$this->data['contenu'].="</div>";
		$this->data['contenu'].="</div>";
		$this->data['contenu'].=<<<HTML
		<script>
		var redirect =(function(num){
		document.location.href = "{$base_url}administration/gestion_reference_petites_pieces_a_peindre/{$nom}/"+num;
		});
		var gestion =(function(){
		var num = $("#input_num_references").val();
		redirect(num);
		});
		</script>
HTML;
		if($auth=="full"){
		$this->data['contenu'].=<<<HTML
		<script>
		var nouvelle_reference =(function(){
			redirect("");
		});
		var clonner =(function(){
			var num = $("#input_num_references").val();
			redirect(num+"/TRUE");
		});
		</script>
HTML;
		}
		$this->load->view('templates/Admin', $this->data);
	}

	public function gestion_moule_injection($nom,$num_moule=-1,$clone=FALSE){
		$this->data['style'] .= css_balise('bootstrap.min');
		$this->data['style'] .= css_balise('bootstrap-toggle.min');
		/*mon style prioritaire*/$this->data['style'] .= css_balise('style_admin');
		$this->data['javascript'].=js_balise('bootstrap-toggle.min');
		$this->data['javascript'].=js_balise('script_admin');

		$this->data['nom']=$nom;
		//marqué le type
		$this->data['titre']="Gestion moule";
		//$this->data['contenu']=json_encode($this->input->post());
		$this->data['contenu']="";
$this->load->model('moules_presses');


$this->moules_presses->getAllByNumMoule($num_moule);

/*echo "<pre>";
print_r($this->moules_presses);
echo "</pre>";*/
$tab_id_presse_valid = array();
foreach ($this->moules_presses->tab_moule_presse as $key => $value) {
	array_push($tab_id_presse_valid,$value->presse->id);
}


$this->load->model('presses');
$this->presses->getAllPresses();



$this->load->model('moule_injection');
$this->load->model('typesDocuments');
$typesDocuments = $this->typesDocuments->getAllTypesDocuments();
$moule = NULL;
$new_moule = FALSE;
$action="";
if ($num_moule==-1) {
	$moule = new Moule_injection();
	$moule->create("",0);
	$new_moule = TRUE;
}
else {
	if($clone){
		$new_moule = TRUE;
		$action="../";
	}
try {
    $moule = $this->moule_injection->getMouleByNum($num_moule);
} catch (Exception $e) {
header("Refresh: 0 ; url= ".base_url()."administration/gestion_moules/injection");
			echo "<script>alert('".$e->getMessage().": {$num_moule} ');</script>";


		exit();
}

}
$action .= "../../maj_moule/".$nom;

/*
echo "<pre>";
print_r($typesDocuments);
echo "</pre>";
*/
/*
SELECT doc_type.id_typedoc, doc_type.nom_lien FROM doc_type
LEFT JOIN docs ON doc_type.id_typedoc = docs.id_typedoc WHERE docs.id_doc IS NULL
*/


$this->data['contenu'].="<form id='XXX' action='{$action}' method='post'>";
$this->data['contenu'].="<input type='hidden' name='type' value={$nom}>";
$this->data['contenu'].="<input type='hidden' name='new_moule' value={$new_moule}>";
$this->data['contenu'].="<input type='hidden' name='id_article' value={$moule->article->id}>";
$this->data['contenu'].="<input type='hidden' name='id' value={$moule->id}>";
$this->data['contenu'].="<input id='delete' type='hidden' name='delete' value=''>";

$auth = ($this->session->userdata('authorization')!=NULL)?$this->session->userdata('authorization'):'none';
$fantome = "";
if($auth!="full"){
$fantome = "style='display:none;'";
}
foreach ($this->presses->tab_presses as $key => $value) {
	$checked = "";
	if(in_array($value->id,$tab_id_presse_valid)){
$checked = "checked";
	}
	$this->data['contenu'].= "<span {$fantome}>".$value->num."<input  {$checked}  type='checkbox' name='presse[{$value->id}]' data-toggle='toggle' data-on=' ' data-off=' ' data-onstyle='success' data-offstyle='default' data-size='mini'></span>";
}

$information =(isset($moule->article->information))?$moule->article->information->lien:"";;
$qualite =(isset($moule->article->incident_qualite))?$moule->article->incident_qualite->lien:"";

$disable = ($auth=="full")?"":"readonly='readonly'";
$this->data['contenu'].=<<<HTML
<div>Numéro de moule : <input type='text' name='num' required="required" value='{$moule->num}' pattern='[A-Z0-9]*[-]?[A-Z0-9]*' {$disable}></div>
HTML;
$this->data['contenu'].=<<<HTML
<div>Incident qualité : <input type='text' name='incident_qualite' value='{$qualite}'></div>
<div>Information : <input type='text' name='information' value='{$information}'></div>
HTML;

$fantome= "";
if($auth!="full"){
	$fantome= "style='display:none'";
}
$this->data['contenu'].="<div style='display:flex;'>Référence(s) SAP :<div>";
/*echo "<pre>";
var_dump($reference->article);
echo "</pre>";*/
$nbMaxRef=4;
	foreach ($moule->article->tab_ref_SAP->tab_references as $key => $value) {
		if($new_moule){
			$id="[new][{$nbMaxRef}]";
		}
		else{
			$id="[{$value->id}]";
		}
		$this->data['contenu'].="<div style='margin-bottom:2px'><input type='text' name='reference_sap{$id}[uid]' value='{$value->id_SAP}' {$disable} ><input type='text' name='reference_sap{$id}[nom_prod]' value='{$value->nom_prod}' {$disable} ></div>";
		$nbMaxRef--;
	}
	for ($nbMaxRef; $nbMaxRef > 0 ; $nbMaxRef--) {
		$this->data['contenu'].="<div style='margin-bottom:2px'><input type='text' name='reference_sap[new][{$nbMaxRef}][uid]' value='' {$disable} ><input type='text' name='reference_sap[new][{$nbMaxRef}][nom_prod]' value='' {$disable} ></div>";
	}
	$this->data['contenu'].="</div></div>";

	$this->data['contenu'].="<table {$fantome} class='table' id='documentElement'>";
	/*******/
	foreach ($moule->tab_docs->tab_documents as $key => $value) {
		$select ="<select name='doc[{$value->id}][id_doc_type]' class='doc{$value->id}'>";
		foreach ($typesDocuments as $clef => $typeDocument) {
			$selected ="";
			if($typeDocument->id == $value->doc_type->id){
				$selected = "selected";
			}
			$select .= "<option value='{$typeDocument->id}' {$selected}>{$typeDocument->label}";
		}
	$select .="</select>";
		$this->data['contenu'].="<tr><td>".$select."  <td> <input type='text' class='doc{$value->id}' name='doc[{$value->id}][lien]' value='{$value->lien}' required='required'><td><input class='toggle-event' data-link=doc{$value->id} name='doc[{$value->id}][actif]' type='checkbox' checked data-toggle='toggle' data-on=' ' data-off=' ' data-onstyle='success' data-offstyle='danger' data-size='mini'></tr></div>";
	}
	$select ="<select name='doc[new][1][id_doc_type]'>";
	$select .= "<option value='' >Type";
	foreach ($typesDocuments as $clef => $typeDocument) {
		$select .= "<option value='{$typeDocument->id}' >{$typeDocument->label}";
	}
	$select .="</select>";
	$this->data['contenu'].="<tr class='formNewDoc'><td>".$select." <td> <input id=1 type='text' name='doc[new][1][lien]' value=''></tr>";
	/****/




$this->data['contenu'].="</table>";
$this->data['contenu'].="<span {$fantome}>";
foreach ($moule->tab_moules_presses->tab_moule_presse as $key => $moule_presse)  {
	$this->data['contenu'].=$moule_presse->presse->num;
	$this->data['contenu'].="<table  class='table documentMPElement' id='docMP{$moule_presse->id}'>";
	if($new_moule){
			$this->data['contenu'] .= "<input type='hidden' name='docMP[{$moule_presse->id}][id_presse]' value='{$moule_presse->presse->id}' >";
	}

	/*******/
	foreach ($moule_presse->tab_docs->tab_documents as $key => $value) {
		$select ="<select name='docMP[{$moule_presse->id}][{$value->id}][id_doc_type]' class='docMP{$value->id}'  >";
		foreach ($typesDocuments as $clef => $typeDocument) {
			$selected ="";
			if($typeDocument->id == $value->doc_type->id){
				$selected = "selected";
			}
			$select .= "<option value='{$typeDocument->id}' {$selected}>{$typeDocument->label}";
		}
	$select .="</select>";
		$this->data['contenu'].="<tr><td>".$select."  <td> <input type='text' name='docMP[{$moule_presse->id}][{$value->id}][lien]' value='{$value->lien}' class='docMP{$value->id}' required='required'><td><input class='toggle-event' data-link=docMP{$value->id} name='docMP[{$moule_presse->id}][{$value->id}][actif]' type='checkbox' checked data-toggle='toggle' data-on=' ' data-off=' ' data-onstyle='success' data-offstyle='danger' data-size='mini'></tr>";
	}
	$select ="<select name='docMP[new][{$moule_presse->id}][1][id_doc_type]'>";
	$select .= "<option value='' >Type";
	foreach ($typesDocuments as $clef => $typeDocument) {
		$select .= "<option value='{$typeDocument->id}' >{$typeDocument->label}";
	}
	$select .="</select>";
	$this->data['contenu'].="<tr  class='formNewDocMP'><td>".$select." <td> <input id=1 type='text' name='docMP[new][{$moule_presse->id}][1][lien]' value=''></tr>";
	/****/
	$this->data['contenu'].="</table>";
}
$this->data['contenu'].="</span>";
$labelSub = ($new_moule==1)?"Créer":"Mise à jour";
$this->data['contenu'].="<input type='submit' value='{$labelSub}'>";
if($new_moule!=1){
$this->data['contenu'].=($auth!="full")?"":<<<HTML
<input id='deleteButton' type='button' value='Supprimer' onclick='myFunction()'>

<script>
myFunction= function(){
	if(confirm("Vous les vous supprimer ?")){
	$('#delete').val('on');
		$('#XXX').submit();
	}
}
</script>
HTML;
}
$this->data['contenu'].="</form>";


		$this->load->view('templates/Admin', $this->data);
	}

	public function gestion_moule_micro_chaine($nom,$num_moule=-1,$clone=FALSE){
		$this->data['style'] .= css_balise('bootstrap.min');
		$this->data['style'] .= css_balise('bootstrap-toggle.min');
		/*mon style prioritaire*/$this->data['style'] .= css_balise('style_admin');
		$this->data['javascript'].=js_balise('bootstrap-toggle.min');
		$this->data['javascript'].=js_balise('script_admin');

		$this->data['nom']=$nom;
		//marqué le type
		$this->data['titre']="Gestion moule";
		//$this->data['contenu']=json_encode($this->input->post());
		$this->data['contenu']="";

$this->load->model('moule_micro_chaine');
$this->load->model('typesDocuments');
$typesDocuments = $this->typesDocuments->getAllTypesDocuments();
$moule = NULL;
$new_moule = FALSE;
$action="";
if ($num_moule==-1) {
	$moule = new Moule_micro_chaine();
	$moule->create("","",0);
	$new_moule = TRUE;
}
else {
	if($clone){
		$new_moule = TRUE;
		$action="../";
	}
try {
    $moule = $this->moule_micro_chaine->getMouleByNum($num_moule);
} catch (Exception $e) {
header("Refresh: 0 ; url= ".base_url()."administration/gestion_moules/micro_chaine");
			echo "<script>alert('".$e->getMessage().": {$num_moule} ');</script>";


		exit();
}


}
$action .= "../../maj_moule/".$nom;

/*
echo "<pre>";
print_r($typesDocuments);
echo "</pre>";
*/
/*
SELECT doc_type.id_typedoc, doc_type.nom_lien FROM doc_type
LEFT JOIN docs ON doc_type.id_typedoc = docs.id_typedoc WHERE docs.id_doc IS NULL
*/

$this->data['contenu'].="<form id='XXX' action='{$action}' method='post'>";
$this->data['contenu'].="<input type='hidden' name='type' value={$nom}>";
$this->data['contenu'].="<input type='hidden' name='new_moule' value={$new_moule} >";
$this->data['contenu'].="<input type='hidden' name='id_article' value={$moule->article->id}>";
$this->data['contenu'].="<input type='hidden' name='id' value={$moule->id}>";
$this->data['contenu'].="<input id='delete' type='hidden' name='delete' value=''>";

$information =(isset($moule->article->information))?$moule->article->information->lien:"";;
$qualite =(isset($moule->article->incident_qualite))?$moule->article->incident_qualite->lien:"";

$auth = ($this->session->userdata('authorization')!=NULL)?$this->session->userdata('authorization'):'none';
$disable = ($auth=="full")?"":"readonly='readonly'";

$this->data['contenu'].=<<<HTML
<div>Numéro de moule : <input type='text' name='num' required="required" value='{$moule->num}' pattern='[A-Z0-9]*[-]?[A-Z0-9]*' {$disable}></div>
<div>Pogramme de flammage : <input type="text" name="prog_flammage" value='{$moule->prog_flammage}' {$disable}></div>
<div>Incident qualité : <input type='text' name='incident_qualite' value='{$qualite}'></div>
<div>Information : <input type='text' name='information' value='{$information}'></div>

HTML;
$fantome= "";
if($auth!="full"){
	$fantome= "style='display:none'";
}
$this->data['contenu'].="<div style='display:flex;'>Référence(s) SAP :<div>";
/*echo "<pre>";
var_dump($reference->article);
echo "</pre>";*/
$nbMaxRef=4;
$nbMaxRef=4;
	foreach ($moule->article->tab_ref_SAP->tab_references as $key => $value) {
		if($new_moule){
			$id="[new][{$nbMaxRef}]";
		}
		else{
			$id="[{$value->id}]";
		}
		$this->data['contenu'].="<div style='margin-bottom:2px'><input type='text' name='reference_sap{$id}[uid]' value='{$value->id_SAP}' {$disable} ><input type='text' name='reference_sap{$id}[nom_prod]' value='{$value->nom_prod}' {$disable} ></div>";
		$nbMaxRef--;
	}
	for ($nbMaxRef; $nbMaxRef > 0 ; $nbMaxRef--) {
		$this->data['contenu'].="<div style='margin-bottom:2px'><input type='text' name='reference_sap[new][{$nbMaxRef}][uid]' value='' {$disable} ><input type='text' name='reference_sap[new][{$nbMaxRef}][nom_prod]' value='' {$disable} ></div>";
	}
	$this->data['contenu'].="</div></div>";

/*******/
$this->data['contenu'].="<table {$fantome} class='table' id='documentElement'>";
foreach ($moule->tab_docs->tab_documents as $key => $value) {
	$select ="<select name='doc[{$value->id}][id_doc_type]' class='doc{$value->id}'>";
	foreach ($typesDocuments as $clef => $typeDocument) {
		$selected ="";
		if($typeDocument->id == $value->doc_type->id){
			$selected = "selected";
		}
		$select .= "<option value='{$typeDocument->id}' {$selected}>{$typeDocument->label}";
	}
$select .="</select>";
	$this->data['contenu'].="<tr><td>".$select."  <td> <input type='text' class='doc{$value->id}' name='doc[{$value->id}][lien]' value='{$value->lien}' required='required'><td><input class='toggle-event' data-link=doc{$value->id} name='doc[{$value->id}][actif]' type='checkbox' checked data-toggle='toggle' data-on=' ' data-off=' ' data-onstyle='success' data-offstyle='danger' data-size='mini'></tr></div>";
}
$select ="<select name='doc[new][1][id_doc_type]'>";
$select .= "<option value='' >Type";
foreach ($typesDocuments as $clef => $typeDocument) {
	$select .= "<option value='{$typeDocument->id}' >{$typeDocument->label}";
}
$select .="</select>";
$this->data['contenu'].="<tr class='formNewDoc'><td>".$select." <td> <input id=1 type='text' name='doc[new][1][lien]' value=''></tr>";
/****/


$this->data['contenu'].="</table>";

$labelSub = ($new_moule==1)?"Créer":"Mise à jour";
$this->data['contenu'].="<input type='submit' value='{$labelSub}'>";
if($new_moule!=1){
	if($auth=="full"){
$this->data['contenu'].="<input id='deleteButton' type='button' value='Supprimer' onclick='myFunction()'>";

$this->data['contenu'].=<<<HTML
<script>
myFunction= function(){
	if(confirm("Vous les vous supprimer ?")){
	$('#delete').val('on');
		$('#XXX').submit();
	}
}
</script>
HTML;
}
}
$this->data['contenu'].="</form>";


		$this->load->view('templates/Admin', $this->data);
	}

	public function gestion_moule($nom,$num_moule=-1,$clone=FALSE)
	{
switch ($nom) {
	case 'micro_chaine':
		$this->gestion_moule_micro_chaine($nom,$num_moule,$clone);
		break;
	case 'injection':
		$this->gestion_moule_injection($nom,$num_moule,$clone);
		break;

	default:
		# code...
		break;
}

	}

	public function gestion_reference_ilot_packaging($nom,$num_reference=-1,$clone=FALSE)
	{
		$this->data['style'] .= css_balise('bootstrap.min');
		$this->data['style'] .= css_balise('bootstrap-toggle.min');
		/*mon style prioritaire*/$this->data['style'] .= css_balise('style_admin');
		$this->data['javascript'].=js_balise('bootstrap-toggle.min');
		$this->data['javascript'].=js_balise('script_admin');

		$this->data['nom']=$nom;
		//marqué le type
		$this->data['titre']="Gestion reference";
		//$this->data['contenu']=json_encode($this->input->post());
		$this->data['contenu']="";

$this->load->model('reference_ilot_packaging');
$this->load->model('typesDocuments');
$typesDocuments = $this->typesDocuments->getAllTypesDocuments();
$reference = NULL;
$new_reference = FALSE;
$action="";
if ($num_reference==-1) {
	$reference = new Reference_ilot_packaging();
	$reference->create("","",0);
	$new_reference = TRUE;
}
else {
	if($clone){
		$new_reference = TRUE;
		$action="../";
	}
	try {
	    $reference = $this->reference_ilot_packaging->getReferenceByNum($num_reference);
	} catch (Exception $e) {
	header("Refresh: 0 ; url= ".base_url()."administration/gestion_references_ilot_packaging/ilot_packaging");
				echo "<script>alert('".$e->getMessage().": {$num_reference} ');</script>";


			exit();
	}

}
$action .= "../../maj_reference_ilot_packaging";

/*
echo "<pre>";
print_r($typesDocuments);
echo "</pre>";
*/
/*
SELECT doc_type.id_typedoc, doc_type.nom_lien FROM doc_type
LEFT JOIN docs ON doc_type.id_typedoc = docs.id_typedoc WHERE docs.id_doc IS NULL
*/

$this->data['contenu'].="<form id='XXX' action='{$action}' method='post'>";
$this->data['contenu'].="<input type='hidden' name='type' value={$nom}>";
$this->data['contenu'].="<input type='hidden' name='new_reference' value={$new_reference}>";
$this->data['contenu'].="<input type='hidden' name='id_article' value={$reference->article->id}>";
$this->data['contenu'].="<input type='hidden' name='id' value={$reference->id}>";
$this->data['contenu'].="<input id='delete' type='hidden' name='delete' value=''>";

$information =(isset($reference->article->information))?$reference->article->information->lien:"";
$qualite =(isset($reference->article->incident_qualite))?$reference->article->incident_qualite->lien:"";

$auth = ($this->session->userdata('authorization')!=NULL)?$this->session->userdata('authorization'):'none';
/*read only plutot que disabled pour que la donné soit envoyé quand même*/
$disable = ($auth=="full")?"":"readonly='readonly'";

$this->data['contenu'].=<<<HTML
<div style="display:flex;">
<div>
	<div>Numéro de reference : <input type='text' name='num' required="required" value='{$reference->num}' pattern='[A-Z0-9]*[-]?[A-Z0-9]*' {$disable}></div> <!-- patterne pour n avoir que des lettre chiffre et 1 ou 0 - dans le mot -->
	<div>Incident qualité : <input type='text' name='incident_qualite' value='{$qualite}'></div>
	<div>Information : <input type='text' name='information' value='{$information}'></div>
</div>
</div>
HTML;
$fantome= "";
if($auth!="full"){
	$fantome= "style='display:none'";
}
$this->data['contenu'].="<div style='display:flex;'>Référence(s) SAP :<div>";
/*echo "<pre>";
var_dump($reference->article);
echo "</pre>";*/
$nbMaxRef=4;
	foreach ($reference->article->tab_ref_SAP->tab_references as $key => $value) {
		if($new_reference){
			$id="[new][{$nbMaxRef}]";
		}
		else{
			$id="[{$value->id}]";
		}
		$this->data['contenu'].="<div style='margin-bottom:2px'><input type='text' name='reference_sap{$id}[uid]' value='{$value->id_SAP}' {$disable} ><input type='text' name='reference_sap{$id}[nom_prod]' value='{$value->nom_prod}' {$disable} ></div>";
		$nbMaxRef--;
	}
	for ($nbMaxRef; $nbMaxRef > 0 ; $nbMaxRef--) {
		$this->data['contenu'].="<div style='margin-bottom:2px'><input type='text' name='reference_sap[new][{$nbMaxRef}][uid]' value='' {$disable} ><input type='text' name='reference_sap[new][{$nbMaxRef}][nom_prod]' value='' {$disable} ></div>";
	}
	$this->data['contenu'].="</div></div>";

$this->data['contenu'].="<table {$fantome} class='table' id='documentElement'>";
foreach ($reference->tab_docs->tab_documents as $key => $value) {
	$select ="<select name='doc[{$value->id}][id_doc_type]' class='doc{$value->id}'>";
	foreach ($typesDocuments as $clef => $typeDocument) {
		$selected ="";
		if($typeDocument->id == $value->doc_type->id){
			$selected = "selected";
		}
		$select .= "<option value='{$typeDocument->id}' {$selected}>{$typeDocument->label}";
	}
$select .="</select>";
	$this->data['contenu'].="<tr><td>".$select."   <td> <input type='text' class='doc{$value->id}' name='doc[{$value->id}][lien]' value='{$value->lien}' required='required'><td><input class='toggle-event' data-link=doc{$value->id} name='doc[{$value->id}][actif]' type='checkbox' checked data-toggle='toggle' data-on=' ' data-off=' ' data-onstyle='success' data-offstyle='danger' data-size='mini'></tr></div>";
}
$select ="<select name='doc[new][1][id_doc_type]'>";
$select .= "<option value='' >Type";
foreach ($typesDocuments as $clef => $typeDocument) {
	$select .= "<option value='{$typeDocument->id}' >{$typeDocument->label}";
}
$select .="</select>";
$this->data['contenu'].="<tr class='formNewDoc'><td>".$select." <td> <input id=1 type='text' name='doc[new][1][lien]' value=''></tr>";
/****/


$this->data['contenu'].="</table>";

$labelSub = ($new_reference==1)?"Créer":"Mise à jour";
$this->data['contenu'].="<input type='submit' value='{$labelSub}'>";
if($new_reference!=1){
	if($auth=="full"){
$this->data['contenu'].="<input id='deleteButton' type='button' value='Supprimer' onclick='myFunction()'>";

$this->data['contenu'].=<<<HTML
<script>
myFunction= function(){
	if(confirm("Vous les vous supprimer ?")){
	$('#delete').val('on');
		$('#XXX').submit();
	}
}
</script>
HTML;
}
}
$this->data['contenu'].="</form>";


		$this->load->view('templates/Admin', $this->data);
	}

	public function gestion_reference_reprise_petite_pr($nom,$num_reference=-1,$clone=FALSE)
	{
		$this->data['style'] .= css_balise('bootstrap.min');
		$this->data['style'] .= css_balise('bootstrap-toggle.min');
		/*mon style prioritaire*/$this->data['style'] .= css_balise('style_admin');
		$this->data['javascript'].=js_balise('bootstrap-toggle.min');
		$this->data['javascript'].=js_balise('script_admin');

		$this->data['nom']=$nom;
		//marqué le type
		$this->data['titre']="Gestion reference";
		//$this->data['contenu']=json_encode($this->input->post());
		$this->data['contenu']="";

$this->load->model('reference_reprise_petite_pr');
$this->load->model('typesDocuments');
$typesDocuments = $this->typesDocuments->getAllTypesDocuments();
$reference = NULL;
$new_reference = FALSE;
$action="";
if ($num_reference==-1) {
	$reference = new Reference_reprise_petite_pr();
	$reference->create("","",0);
	$new_reference = TRUE;
}
else {
	if($clone){
		$new_reference = TRUE;
		$action="../";
	}
	try {
	    $reference = $this->reference_reprise_petite_pr->getReferenceByNum($num_reference);
	} catch (Exception $e) {
	header("Refresh: 0 ; url= ".base_url()."administration/gestion_references_reprise_petite_pr/reprise_petite_pr");
				echo "<script>alert('".$e->getMessage().": {$num_reference} ');</script>";


			exit();
	}

}
$action .= "../../maj_reference_reprise_petite_pr";

/*
echo "<pre>";
print_r($typesDocuments);
echo "</pre>";
*/
/*
SELECT doc_type.id_typedoc, doc_type.nom_lien FROM doc_type
LEFT JOIN docs ON doc_type.id_typedoc = docs.id_typedoc WHERE docs.id_doc IS NULL
*/

$this->data['contenu'].="<form id='XXX' action='{$action}' method='post'>";
$this->data['contenu'].="<input type='hidden' name='type' value={$nom}>";
$this->data['contenu'].="<input type='hidden' name='new_reference' value={$new_reference}>";
$this->data['contenu'].="<input type='hidden' name='id_article' value={$reference->article->id}>";
$this->data['contenu'].="<input type='hidden' name='id' value={$reference->id}>";
$this->data['contenu'].="<input id='delete' type='hidden' name='delete' value=''>";

$information =(isset($reference->article->information))?$reference->article->information->lien:"";
$qualite =(isset($reference->article->incident_qualite))?$reference->article->incident_qualite->lien:"";

$auth = ($this->session->userdata('authorization')!=NULL)?$this->session->userdata('authorization'):'none';
/*read only plutot que disabled pour que la donné soit envoyé quand même*/
$disable = ($auth=="full")?"":"readonly='readonly'";

$this->data['contenu'].=<<<HTML
<div style="display:flex;">
<div>
	<div>Numéro de reference : <input type='text' name='num' required="required" value='{$reference->num}' pattern='[A-Z0-9]*[-]?[A-Z0-9]*' {$disable}></div> <!-- patterne pour n avoir que des lettre chiffre et 1 ou 0 - dans le mot -->
	<div>Incident qualité : <input type='text' name='incident_qualite' value='{$qualite}'></div>
	<div>Information : <input type='text' name='information' value='{$information}'></div>
</div>
</div>
HTML;
$fantome= "";
if($auth!="full"){
	$fantome= "style='display:none'";
}
$this->data['contenu'].="<div style='display:flex;'>Référence(s) SAP :<div>";
/*echo "<pre>";
var_dump($reference->article);
echo "</pre>";*/
$nbMaxRef=4;
	foreach ($reference->article->tab_ref_SAP->tab_references as $key => $value) {
		if($new_reference){
			$id="[new][{$nbMaxRef}]";
		}
		else{
			$id="[{$value->id}]";
		}
		$this->data['contenu'].="<div style='margin-bottom:2px'><input type='text' name='reference_sap{$id}[uid]' value='{$value->id_SAP}' {$disable} ><input type='text' name='reference_sap{$id}[nom_prod]' value='{$value->nom_prod}' {$disable} ></div>";
		$nbMaxRef--;
	}
	for ($nbMaxRef; $nbMaxRef > 0 ; $nbMaxRef--) {
		$this->data['contenu'].="<div style='margin-bottom:2px'><input type='text' name='reference_sap[new][{$nbMaxRef}][uid]' value='' {$disable} ><input type='text' name='reference_sap[new][{$nbMaxRef}][nom_prod]' value='' {$disable} ></div>";
	}
	$this->data['contenu'].="</div></div>";

$this->data['contenu'].="<table {$fantome} class='table' id='documentElement'>";
foreach ($reference->tab_docs->tab_documents as $key => $value) {
	$select ="<select name='doc[{$value->id}][id_doc_type]' class='doc{$value->id}'>";
	foreach ($typesDocuments as $clef => $typeDocument) {
		$selected ="";
		if($typeDocument->id == $value->doc_type->id){
			$selected = "selected";
		}
		$select .= "<option value='{$typeDocument->id}' {$selected}>{$typeDocument->label}";
	}
$select .="</select>";
	$this->data['contenu'].="<tr><td>".$select."   <td> <input type='text' class='doc{$value->id}' name='doc[{$value->id}][lien]' value='{$value->lien}' required='required'><td><input class='toggle-event' data-link=doc{$value->id} name='doc[{$value->id}][actif]' type='checkbox' checked data-toggle='toggle' data-on=' ' data-off=' ' data-onstyle='success' data-offstyle='danger' data-size='mini'></tr></div>";
}
$select ="<select name='doc[new][1][id_doc_type]'>";
$select .= "<option value='' >Type";
foreach ($typesDocuments as $clef => $typeDocument) {
	$select .= "<option value='{$typeDocument->id}' >{$typeDocument->label}";
}
$select .="</select>";
$this->data['contenu'].="<tr class='formNewDoc'><td>".$select." <td> <input id=1 type='text' name='doc[new][1][lien]' value=''></tr>";
/****/


$this->data['contenu'].="</table>";

$labelSub = ($new_reference==1)?"Créer":"Mise à jour";
$this->data['contenu'].="<input type='submit' value='{$labelSub}'>";
if($new_reference!=1){
	if($auth=="full"){
$this->data['contenu'].="<input id='deleteButton' type='button' value='Supprimer' onclick='myFunction()'>";

$this->data['contenu'].=<<<HTML
<script>
myFunction= function(){
	if(confirm("Vous les vous supprimer ?")){
	$('#delete').val('on');
		$('#XXX').submit();
	}
}
</script>
HTML;
}
}
$this->data['contenu'].="</form>";


		$this->load->view('templates/Admin', $this->data);
	}

	public function gestion_reference_petites_pieces_a_peindre($nom,$num_reference=-1,$clone=FALSE)
	{
		$this->data['style'] .= css_balise('bootstrap.min');
		$this->data['style'] .= css_balise('bootstrap-toggle.min');
		/*mon style prioritaire*/$this->data['style'] .= css_balise('style_admin');
		$this->data['javascript'].=js_balise('bootstrap-toggle.min');
		$this->data['javascript'].=js_balise('script_admin');

		$this->data['nom']=$nom;
		//marqué le type
		$this->data['titre']="Gestion reference";
		//$this->data['contenu']=json_encode($this->input->post());
		$this->data['contenu']="";

$this->load->model('reference_petites_pieces_a_peindre');
$this->load->model('typesDocuments');
$typesDocuments = $this->typesDocuments->getAllTypesDocuments();
$reference = NULL;
$new_reference = FALSE;
$action="";
if ($num_reference==-1) {
	$reference = new Reference_petites_pieces_a_peindre();
	$reference->create("","",0);
	$new_reference = TRUE;
}
else {
	if($clone){
		$new_reference = TRUE;
		$action="../";
	}
	try {
	    $reference = $this->reference_petites_pieces_a_peindre->getReferenceByNum($num_reference);
	} catch (Exception $e) {
	header("Refresh: 0 ; url= ".base_url()."administration/gestion_references_petites_pieces_a_peindre/petites_pieces_a_peindre");
				echo "<script>alert('".$e->getMessage().": {$num_reference} ');</script>";


			exit();
	}

}
$action .= "../../maj_reference_petites_pieces_a_peindre";

/*
echo "<pre>";
print_r($typesDocuments);
echo "</pre>";
*/
/*
SELECT doc_type.id_typedoc, doc_type.nom_lien FROM doc_type
LEFT JOIN docs ON doc_type.id_typedoc = docs.id_typedoc WHERE docs.id_doc IS NULL
*/

$this->data['contenu'].="<form id='XXX' action='{$action}' method='post'>";
$this->data['contenu'].="<input type='hidden' name='type' value={$nom}>";
$this->data['contenu'].="<input type='hidden' name='new_reference' value={$new_reference}>";
$this->data['contenu'].="<input type='hidden' name='id_article' value={$reference->article->id}>";
$this->data['contenu'].="<input type='hidden' name='id' value={$reference->id}>";
$this->data['contenu'].="<input id='delete' type='hidden' name='delete' value=''>";

$information =(isset($reference->article->information))?$reference->article->information->lien:"";
$qualite =(isset($reference->article->incident_qualite))?$reference->article->incident_qualite->lien:"";

$auth = ($this->session->userdata('authorization')!=NULL)?$this->session->userdata('authorization'):'none';
/*read only plutot que disabled pour que la donné soit envoyé quand même*/
$disable = ($auth=="full")?"":"readonly='readonly'";

$this->data['contenu'].=<<<HTML
<div style="display:flex;">
<div>
	<div>Numéro de reference : <input type='text' name='num' required="required" value='{$reference->num}' pattern='[A-Z0-9]*[-]?[A-Z0-9]*' {$disable}></div> <!-- patterne pour n avoir que des lettre chiffre et 1 ou 0 - dans le mot -->
	<div>Incident qualité : <input type='text' name='incident_qualite' value='{$qualite}'></div>
	<div>Information : <input type='text' name='information' value='{$information}'></div>
</div>
</div>
HTML;
$fantome= "";
if($auth!="full"){
	$fantome= "style='display:none'";
}
$this->data['contenu'].="<div style='display:flex;'>Référence(s) SAP :<div>";
/*echo "<pre>";
var_dump($reference->article);
echo "</pre>";*/
$nbMaxRef=4;
	foreach ($reference->article->tab_ref_SAP->tab_references as $key => $value) {
		if($new_reference){
			$id="[new][{$nbMaxRef}]";
		}
		else{
			$id="[{$value->id}]";
		}
		$this->data['contenu'].="<div style='margin-bottom:2px'><input type='text' name='reference_sap{$id}[uid]' value='{$value->id_SAP}' {$disable} ><input type='text' name='reference_sap{$id}[nom_prod]' value='{$value->nom_prod}' {$disable} ></div>";
		$nbMaxRef--;
	}
	for ($nbMaxRef; $nbMaxRef > 0 ; $nbMaxRef--) {
		$this->data['contenu'].="<div style='margin-bottom:2px'><input type='text' name='reference_sap[new][{$nbMaxRef}][uid]' value='' {$disable} ><input type='text' name='reference_sap[new][{$nbMaxRef}][nom_prod]' value='' {$disable} ></div>";
	}
	$this->data['contenu'].="</div></div>";

$this->data['contenu'].="<table {$fantome} class='table' id='documentElement'>";
foreach ($reference->tab_docs->tab_documents as $key => $value) {
	$select ="<select name='doc[{$value->id}][id_doc_type]' class='doc{$value->id}'>";
	foreach ($typesDocuments as $clef => $typeDocument) {
		$selected ="";
		if($typeDocument->id == $value->doc_type->id){
			$selected = "selected";
		}
		$select .= "<option value='{$typeDocument->id}' {$selected}>{$typeDocument->label}";
	}
$select .="</select>";
	$this->data['contenu'].="<tr><td>".$select."   <td> <input type='text' class='doc{$value->id}' name='doc[{$value->id}][lien]' value='{$value->lien}' required='required'><td><input class='toggle-event' data-link=doc{$value->id} name='doc[{$value->id}][actif]' type='checkbox' checked data-toggle='toggle' data-on=' ' data-off=' ' data-onstyle='success' data-offstyle='danger' data-size='mini'></tr></div>";
}
$select ="<select name='doc[new][1][id_doc_type]'>";
$select .= "<option value='' >Type";
foreach ($typesDocuments as $clef => $typeDocument) {
	$select .= "<option value='{$typeDocument->id}' >{$typeDocument->label}";
}
$select .="</select>";
$this->data['contenu'].="<tr class='formNewDoc'><td>".$select." <td> <input id=1 type='text' name='doc[new][1][lien]' value=''></tr>";
/****/


$this->data['contenu'].="</table>";

$labelSub = ($new_reference==1)?"Créer":"Mise à jour";
$this->data['contenu'].="<input type='submit' value='{$labelSub}'>";
if($new_reference!=1){
	if($auth=="full"){
$this->data['contenu'].="<input id='deleteButton' type='button' value='Supprimer' onclick='myFunction()'>";

$this->data['contenu'].=<<<HTML
<script>
myFunction= function(){
	if(confirm("Vous les vous supprimer ?")){
	$('#delete').val('on');
		$('#XXX').submit();
	}
}
</script>
HTML;
}
}
$this->data['contenu'].="</form>";


		$this->load->view('templates/Admin', $this->data);
	}
	
public function new_moule_injection($post){

$res=NULL;
if (isset($post['presse'])) {

	$this->load->model('moule_injection');
	$moule = new moule_injection();
	if($moule->existeByNum($post['num'])){
		$this->data['nom']="Erreur";
		$this->data['titre']="Erreur";
		$this->data['contenu']="Le moule existe déjà.";
		$this->load->view('templates/Admin', $this->data);
		$res = array('idMoule'=>$moule->id);
	}
	else{
		/*echo "<pre style='background:green;'>";
		print_r($post);
		echo "</pre>";
		exit();*/
	$moule->create($post['num']);

	foreach ($post['reference_sap']['new'] as $idRefN => $refN) {
		if(isset($refN['uid']) && $refN['uid']!=''){
			$ref_SAP = new Reference_sap();
			$ref_SAP->init(-1,$moule->article->id,$refN['uid'],$refN['nom_prod']);
			$moule->article->tab_ref_SAP->add($ref_SAP);
		}
	}

/*Ajouter de document*/
foreach ($post['doc']['new'] as $key => $doc) {
	if($doc['lien']!=''){
		$docment = new Document();
		$docment->init(-1,$doc['lien'],$moule->article->id,$doc['id_doc_type']);
		$moule->tab_docs->add($docment);
	}
}

//ajout incident qualitee
	$moule->article->addIncident($post['incident_qualite']);

//maj information
	$moule->article->addInformation($post['information']);

foreach ($post['presse'] as $idPresse => $value) {
	$mp = new Moule_presse();
	$mp->create($moule,$idPresse);
$moule->tab_moules_presses->add($mp);
}
$moule->save();
	/*echo "<pre style=''>";
	print_r($moule);
	echo "</pre>";*/
	$res = array('idMoule'=>$moule->id,'idArticle'=>$moule->article->id,'moules_presse'=>array());

	foreach ($moule->tab_moules_presses->tab_moule_presse as $key => $value) {
		$res['moules_presse'][$value->presse->id]=$value->id;
	}
	return $res;
	}
	}
	else {
		echo "Il est obligatoire d'enregistrer au moin une presse";
		$res = array('idMoule'=>NULL,'idArticle'=>NULL,'moules_presse'=>array());
	}


return $res;
}

public function new_moule_micro_chaine($post){


	$this->load->model('moule_micro_chaine');
	$moule = new moule_micro_chaine();
$res = NULL;
if($moule->existeByNum($post['num'])){
	$this->data['nom']="Erreur";
	$this->data['titre']="Erreur";
	$this->data['contenu']="Le moule existe déjà.";
	$this->load->view('templates/Admin', $this->data);
	$res = array('idMoule'=>$moule->id);
}
else{

	/*echo "<pre style='background:green;'>";
	print_r($post);
	echo "</pre>";*/
		$moule->create($post['num'],$post['prog_flammage']);

		foreach ($post['reference_sap']['new'] as $idRefN => $refN) {
			if(isset($refN['uid']) && $refN['uid']!=''){
				$ref_SAP = new Reference_sap();
				$ref_SAP->init(-1,$moule->article->id,$refN['uid'],$refN['nom_prod']);
				$moule->article->tab_ref_SAP->add($ref_SAP);
			}
		}

		//ajout incident qualitee
			$moule->article->addIncident($post['incident_qualite']);

		//maj information
			$moule->article->addInformation($post['information']);

	/*Ajouter de document*/
	foreach ($post['doc']['new'] as $key => $doc) {
		if($doc['lien']!=''){
			$docment = new Document();
			$docment->init(-1,$doc['lien'],$moule->article->id,$doc['id_doc_type']);
			$moule->tab_docs->add($docment);
		}
	}


	$moule->save();
		echo "<pre style=''>";
		print_r($moule);
		echo "</pre>";
$res = array('idMoule'=>$moule->id,'idArticle'=>$moule->article->id);
}

return $res;
}

public function new_reference_ilot_packaging($post){

	$res = NULL;
	$this->load->model('reference_ilot_packaging');
	$reference = new Reference_ilot_packaging();

	if($reference->existeByNum($post['num'])){
		$this->data['nom']="Erreur";
		$this->data['titre']="Erreur";
		$this->data['contenu']="La référence existe déjà.";
		$this->load->view('templates/Admin', $this->data);
		$res = array('idMoule'=>$reference->id);
	}
	else{
		echo "<pre style='background:green;'>";
		print_r($post);
		echo "</pre>";

	$reference->create($post['num']);


			foreach ($post['reference_sap']['new'] as $idRefN => $refN) {
				if(isset($refN['uid']) && $refN['uid']!=''){
					$ref_SAP = new Reference_sap();
					$ref_SAP->init(-1,$reference->article->id,$refN['uid'],$refN['nom_prod']);
					$reference->article->tab_ref_SAP->add($ref_SAP);
				}
			}
	//ajout incident qualitee
		$reference->article->addIncident($post['incident_qualite']);

	//maj information
		$reference->article->addInformation($post['information']);

/*Ajouter de document*/
foreach ($post['doc']['new'] as $key => $doc) {
	if($doc['lien']!=''){
		$docment = new Document();
		$docment->init(-1,$doc['lien'],$reference->article->id,$doc['id_doc_type']);
		$reference->tab_docs->add($docment);
	}
}


$reference->save();
	echo "<pre style=''>";
	print_r($reference);
	echo "</pre>";
$res = array('idMoule'=>$reference->id,'idArticle'=>$reference->article->id);
}
return $res;
}

public function new_reference_reprise_petite_pr($post){

	$res = NULL;
	$this->load->model('reference_reprise_petite_pr');
	$reference = new Reference_reprise_petite_pr();

	if($reference->existeByNum($post['num'])){
		$this->data['nom']="Erreur";
		$this->data['titre']="Erreur";
		$this->data['contenu']="La référence existe déjà.";
		$this->load->view('templates/Admin', $this->data);
		$res = array('idMoule'=>$reference->id);
	}
	else{
		echo "<pre style='background:green;'>";
		print_r($post);
		echo "</pre>";

	$reference->create($post['num']);


			foreach ($post['reference_sap']['new'] as $idRefN => $refN) {
				if(isset($refN['uid']) && $refN['uid']!=''){
					$ref_SAP = new Reference_sap();
					$ref_SAP->init(-1,$reference->article->id,$refN['uid'],$refN['nom_prod']);
					$reference->article->tab_ref_SAP->add($ref_SAP);
				}
			}
	//ajout incident qualitee
		$reference->article->addIncident($post['incident_qualite']);

	//maj information
		$reference->article->addInformation($post['information']);

/*Ajouter de document*/
foreach ($post['doc']['new'] as $key => $doc) {
	if($doc['lien']!=''){
		$docment = new Document();
		$docment->init(-1,$doc['lien'],$reference->article->id,$doc['id_doc_type']);
		$reference->tab_docs->add($docment);
	}
}


$reference->save();
	echo "<pre style=''>";
	print_r($reference);
	echo "</pre>";
$res = array('idMoule'=>$reference->id,'idArticle'=>$reference->article->id);
}
return $res;
}

public function new_reference_petites_pieces_a_peindre($post){

	$res = NULL;
	$this->load->model('reference_petites_pieces_a_peindre');
	$reference = new Reference_petites_pieces_a_peindre();

	if($reference->existeByNum($post['num'])){
		$this->data['nom']="Erreur";
		$this->data['titre']="Erreur";
		$this->data['contenu']="La référence existe déjà.";
		$this->load->view('templates/Admin', $this->data);
		$res = array('idMoule'=>$reference->id);
	}
	else{
		echo "<pre style='background:green;'>";
		print_r($post);
		echo "</pre>";

	$reference->create($post['num']);


			foreach ($post['reference_sap']['new'] as $idRefN => $refN) {
				if(isset($refN['uid']) && $refN['uid']!=''){
					$ref_SAP = new Reference_sap();
					$ref_SAP->init(-1,$reference->article->id,$refN['uid'],$refN['nom_prod']);
					$reference->article->tab_ref_SAP->add($ref_SAP);
				}
			}
	//ajout incident qualitee
		$reference->article->addIncident($post['incident_qualite']);

	//maj information
		$reference->article->addInformation($post['information']);

/*Ajouter de document*/
foreach ($post['doc']['new'] as $key => $doc) {
	if($doc['lien']!=''){
		$docment = new Document();
		$docment->init(-1,$doc['lien'],$reference->article->id,$doc['id_doc_type']);
		$reference->tab_docs->add($docment);
	}
}


$reference->save();
	echo "<pre style=''>";
	print_r($reference);
	echo "</pre>";
$res = array('idMoule'=>$reference->id,'idArticle'=>$reference->article->id);
}
return $res;
}

public function del_moule_injection($post){
	/*echo "<pre style='background:grey;'>";
	print_r($post);
	echo "</pre>";*/

	$this->load->model('moule_injection');
	$moule = new moule_injection();
	//TRUE pour la supression
	$moule->init($post['id'],$post['id_article'],$post['num'],TRUE);

	echo "<pre style=''>";
	print_r($moule);
	echo "</pre>";

	$moule->save();
}

public function del_moule_micro_chaine($post){
	echo "<pre style='background:grey;'>";
	print_r($post);
	echo "</pre>";

	$this->load->model('moule_micro_chaine');
	$moule = new moule_micro_chaine();
	//TRUE pour la supression
	$moule->init($post['id'],$post['id_article'],$post['num'],$post['prog_flammage'],TRUE);

	echo "<pre style=''>";
	print_r($moule);
	echo "</pre>";

	$moule->save();
}

public function del_reference_ilot_packaging($post){
	echo "<pre style='background:grey;'>";
	print_r($post);
	echo "</pre>";

	$this->load->model('reference_ilot_packaging');
	$reference = new Reference_ilot_packaging();
	//TRUE pour la supression
	$reference->init($post['id'],$post['id_article'],$post['num'],TRUE);

	echo "<pre style=''>";
	print_r($reference);
	echo "</pre>";
	$reference->save();
}

public function del_reference_reprise_petite_pr($post){
	echo "<pre style='background:grey;'>";
	print_r($post);
	echo "</pre>";

	$this->load->model('reference_reprise_petite_pr');
	$reference = new Reference_reprise_petite_pr();
	//TRUE pour la supression
	$reference->init($post['id'],$post['id_article'],$post['num'],TRUE);

	echo "<pre style=''>";
	print_r($reference);
	echo "</pre>";
	$reference->save();
}

public function del_reference_petites_pieces_a_peindre($post){
	echo "<pre style='background:grey;'>";
	print_r($post);
	echo "</pre>";

	$this->load->model('reference_petites_pieces_a_peindre');
	$reference = new Reference_petites_pieces_a_peindre();
	//TRUE pour la supression
	$reference->init($post['id'],$post['id_article'],$post['num'],TRUE);

	echo "<pre style=''>";
	print_r($reference);
	echo "</pre>";
	$reference->save();
}

public function copy_moule_injection($post){
$res = $this->new_moule_injection($post);

if($res['idMoule']!=-1){
$post['id']=$res['idMoule'];
$post['id_article']=$res['idArticle'];
	unset($post['doc']['new']);
foreach ($post['doc'] as $key => $value) {
	if($key!='new'){
	$post['doc']['new'][$key]['id_doc_type']=$value['id_doc_type'];
	$post['doc']['new'][$key]['lien']=$value['lien'];
	$post['doc']['new'][$key]['actif']=$value['actif'];
	unset($post['doc'][$key]);
	}

}

foreach ($post['docMP'] as $key => $value) {
	if($key!='new'){
		$id_presse=$value['id_presse'];
		unset($value['id_presse']);
		print_r($value);
		foreach ($value as $clef => $val) {
			if(!isset($value[$clef]['actif']) || $value[$clef]['actif']!='on'){
				unset($value[$clef]);
			}
		}
		$post['docMP']['new'][$res['moules_presse'][$id_presse]]=$value;

		unset($post['docMP']['new'][$key]);
		unset($post['docMP'][$key]) ;
	}
}
/*echo "<pre>";
var_dump($post);
echo "</pre>";
exit();*/
$this->mod_moule_injection($post,TRUE);
}
}


public function copy_moule_micro_chaine($post){
$res = $this->new_moule_micro_chaine($post);
if($res['idMoule']!=-1){
	$post['id']=$res['idMoule'];
	$post['id_article']=$res['idArticle'];
		unset($post['doc']['new']);
	foreach ($post['doc'] as $key => $value) {
		if($key!='new'){
		$post['doc']['new'][$key]['id_doc_type']=$value['id_doc_type'];
		$post['doc']['new'][$key]['lien']=$value['lien'];
		$post['doc']['new'][$key]['actif']=$value['actif'];
		unset($post['doc'][$key]);
		}
	}


	$this->mod_moule_micro_chaine($post,TRUE);
}

}

public function copy_reference_ilot_packaging($post){
$res = $this->new_reference_ilot_packaging($post);
if($res['idMoule']!=-1){
$post['id']=$res['idMoule'];
$post['id_article']=$res['idArticle'];
	unset($post['doc']['new']);
foreach ($post['doc'] as $key => $value) {
	if($key!='new'){
	$post['doc']['new'][$key]['id_doc_type']=$value['id_doc_type'];
	$post['doc']['new'][$key]['lien']=$value['lien'];
	$post['doc']['new'][$key]['actif']=$value['actif'];
	unset($post['doc'][$key]);
	}
}


$this->mod_reference_ilot_packaging($post,TRUE);
}
}

public function copy_reference_reprise_petite_pr($post){
$res = $this->new_reference_reprise_petite_pr($post);
if($res['idMoule']!=-1){
$post['id']=$res['idMoule'];
$post['id_article']=$res['idArticle'];
	unset($post['doc']['new']);
foreach ($post['doc'] as $key => $value) {
	if($key!='new'){
	$post['doc']['new'][$key]['id_doc_type']=$value['id_doc_type'];
	$post['doc']['new'][$key]['lien']=$value['lien'];
	$post['doc']['new'][$key]['actif']=$value['actif'];
	unset($post['doc'][$key]);
	}
}


$this->mod_reference_reprise_petite_pr($post,TRUE);
}
}

public function copy_reference_petites_pieces_a_peindre($post){
$res = $this->new_reference_petites_pieces_a_peindre($post);
if($res['idMoule']!=-1){
$post['id']=$res['idMoule'];
$post['id_article']=$res['idArticle'];
	unset($post['doc']['new']);
foreach ($post['doc'] as $key => $value) {
	if($key!='new'){
	$post['doc']['new'][$key]['id_doc_type']=$value['id_doc_type'];
	$post['doc']['new'][$key]['lien']=$value['lien'];
	$post['doc']['new'][$key]['actif']=$value['actif'];
	unset($post['doc'][$key]);
	}
}


$this->mod_reference_petites_pieces_a_peindre($post,TRUE);
}
}

public function mod_moule_injection($post,$clone=FALSE){
	/*echo "<pre style='background:orange;'>";
	print_r($post);
	echo "</pre>";*/

	$this->load->model('moule_injection');
	$moule = new moule_injection();

	$moule->init($post['id'],$post['id_article'],$post['num']);

// ne sert à rien de modifier ref sur un clone nouvelement créé
if(!$clone){
	foreach ($post['reference_sap'] as $idRef => $ref) {
		if($idRef!="new"){
				$ref_SAP = new Reference_sap();
				$ref_SAP->init($idRef,$moule->article->id,$ref['uid'],$ref['nom_prod']);
				if($ref['uid']==''){
					$ref_SAP->delete= TRUE;
				}
				$moule->article->tab_ref_SAP->add($ref_SAP);

		}
		else{
			foreach ($ref as $idRefN => $refN) {
				if(isset($refN['uid']) && $refN['uid']!=''){
					$ref_SAP = new Reference_sap();
					$ref_SAP->init(-1,$moule->article->id,$refN['uid'],$refN['nom_prod']);
					$moule->article->tab_ref_SAP->add($ref_SAP);
				}
			}
		}
	}
}


	//maj incident qualitee
	if(!isset($moule->article->incident_qualite)){
		$moule->article->addIncident($post['incident_qualite']);
	}
	else{
		$moule->article->incident_qualite->lien= $post['incident_qualite'];
	}

	//maj information
	if(!isset($moule->article->information)){
		$moule->article->addInformation($post['information']);
	}
	else{
		$moule->article->information->lien= $post['information'];
	}


$tabPress =array();
if(isset($post['presse'])){
	$tabPress =$post['presse'];
}
	foreach ($moule->tab_moules_presses->tab_moule_presse as $id => $mp) {
		if (array_key_exists($mp->presse->id,$tabPress)) {
			unset($tabPress[$mp->presse->id]);
		}
		else {
			echo "<div style='background:red'>la</div>";
			echo $mp->presse->id;
			$mp->delete=TRUE;
		}

	}



	foreach ($tabPress as $idPresse => $value) {
		$mp = new Moule_presse();
		$mp->create($moule,$idPresse);
		$moule->tab_moules_presses->add($mp);
	}


	foreach ($post['doc'] as $idDoc => $doc) {
		if($idDoc!="new"){
			$document = new Document();
			$document->init($idDoc,$doc['lien'],$moule->article->id,$doc['id_doc_type']);

			if(!isset($doc['actif'])){
				$document->delete= TRUE;
			}
			$moule->tab_docs->add($document);
		}
		else {
			foreach ($doc as $key => $docN) {
				if(isset($docN['lien']) && $docN['lien']!=''){
					$document = new Document();
					$document->init(-1,$docN['lien'],$moule->article->id,$docN['id_doc_type']);
					$moule->tab_docs->add($document);
				}
			}

		}
	}

if(isset($post['docMP'])){
	foreach ($post['docMP'] as $idMP => $DocMP) {
		echo "..".$idMP."==".$clone."<br>" ;
		if ($idMP=='new') {

			foreach ($DocMP as $clef => $gpDoc) {
if(isset($gpDoc))
				foreach ($gpDoc as $key => $doc) {
					echo "ici";
					if(isset($doc['lien']) && $doc['lien']!=''){
						echo "**".$idMP."//".$doc['lien'];
						$document = new Doc_moule_presse();
						$document->init(-1,$doc['lien'],$clef,$doc['id_doc_type']);
						$mp = $moule->tab_moules_presses->tab_moule_presse[$clef];
						$mp->tab_docs->add($document);
					}
				}

			}
		}
		else{
			if($clone){
				foreach ($DocMP as $key => $value) {
					if($value['lien']!='' && !isset($value['active'])){
						$document = new Doc_moule_presse();
						$document->init(-1,$value['lien'],$key,$value['id_doc_type']);
						$mp = $moule->tab_moules_presses->tab_moule_presse[$key];
						$mp->tab_docs->add($document);
					}
				}
			}
			else {
				foreach ($DocMP as $key => $value) {
					echo "<pre>";
					print_r($value);
					echo "</pre>";

						$document = new Doc_moule_presse();
						$document->init($key,$value['lien'],$idMP,$value['id_doc_type']);
						if(!isset($value['actif'])){
							$document->delete=TRUE;
						}
						$mp = $moule->tab_moules_presses->tab_moule_presse[$idMP];
						$mp->tab_docs->tab_documents[$document->id] = $document;


				}
			}

		}



	}
}


	$moule->save();
/*	echo "<pre style=''>";
	print_r($moule);
	echo "</pre>";*/

}

public function mod_moule_micro_chaine($post,$clone=FALSE){
	/*echo "<pre style='background:orange;'>";
	print_r($post);
	echo "</pre>";*/

	$this->load->model('moule_micro_chaine');
	$moule = new moule_micro_chaine();

	$moule->init($post['id'],$post['id_article'],$post['num'],$post['prog_flammage']);

//la modif des reference ne sert à rien sur une clone nouvelement créé
if(!$clone){
foreach ($post['reference_sap'] as $idRef => $ref) {
	if($idRef!="new"){
		$ref_SAP = new Reference_sap();
		$ref_SAP->init($idRef,$moule->article->id,$ref['uid'],$ref['nom_prod']);
		if($ref['uid']==''){
			$ref_SAP->delete= TRUE;
		}
		$moule->article->tab_ref_SAP->add($ref_SAP);
	}
	else{
		foreach ($ref as $idRefN => $refN) {
			if(isset($refN['uid']) && $refN['uid']!=''){
				$ref_SAP = new Reference_sap();
				$ref_SAP->init(-1,$moule->article->id,$refN['uid'],$refN['nom_prod']);
				$moule->article->tab_ref_SAP->add($ref_SAP);
			}
		}
	}
}
}

	//maj incident qualitee
	if(!isset($moule->article->incident_qualite)){
		$moule->article->addIncident($post['incident_qualite']);
	}
	else{
		$moule->article->incident_qualite->lien= $post['incident_qualite'];
	}

	//maj information
	if(!isset($moule->article->information)){
		$moule->article->addInformation($post['information']);
	}
	else{
		$moule->article->information->lien= $post['information'];
	}

	foreach ($post['doc'] as $idDoc => $doc) {
		if($idDoc!="new"){
			$document = new Document();
			$document->init($idDoc,$doc['lien'],$moule->article->id,$doc['id_doc_type']);

			if(!isset($doc['actif'])){
				$document->delete= TRUE;
			}
			$moule->tab_docs->add($document);
		}
		else {
			foreach ($doc as $key => $docN) {
				if(isset($docN['lien']) && $docN['lien']!=''){
					$document = new Document();
					$document->init(-1,$docN['lien'],$moule->article->id,$docN['id_doc_type']);
					$moule->tab_docs->add($document);
				}
			}

		}
	}


	$moule->save();
	/*echo "<pre style=''>";
	print_r($moule);
	echo "</pre>";*/
	header("Refresh: 0 ; url= ".base_url()."administration/gestion_moule/micro_chaine/".$moule->num);
}

public function mod_reference_ilot_packaging($post,$clone=FALSE){
	echo "<pre style='background:orange;'>";
	print_r($post);
	echo "</pre>";

	$this->load->model('reference_ilot_packaging');
	$reference = new Reference_ilot_packaging();

	$reference->init($post['id'],$post['id_article'],$post['num']);


	//maj incident qualitee
	if(!isset($reference->article->incident_qualite)){
		$reference->article->addIncident($post['incident_qualite']);
	}
	else{
		$reference->article->incident_qualite->lien= $post['incident_qualite'];
	}

	//maj information
	if(!isset($reference->article->information)){
		$reference->article->addInformation($post['information']);
	}
	else{
		$reference->article->information->lien= $post['information'];
	}

//la modif des reference ne sert à rien sur une clone nouvelement créé
if(!$clone){
foreach ($post['reference_sap'] as $idRef => $ref) {
	if($idRef!="new"){
		$ref_SAP = new Reference_sap();
		$ref_SAP->init($idRef,$reference->article->id,$ref['uid'],$ref['nom_prod']);
		if($ref['uid']==''){
			$ref_SAP->delete= TRUE;
		}
		$reference->article->tab_ref_SAP->add($ref_SAP);
	}
	else{
		foreach ($ref as $idRefN => $refN) {
			if(isset($refN['uid']) && $refN['uid']!=''){
				$ref_SAP = new Reference_sap();
				$ref_SAP->init(-1,$reference->article->id,$refN['uid'],$refN['nom_prod']);
				$reference->article->tab_ref_SAP->add($ref_SAP);
			}
		}
	}
}
}

	foreach ($post['doc'] as $idDoc => $doc) {
		if($idDoc!="new"){
			$document = new Document();
			$document->init($idDoc,$doc['lien'],$reference->article->id,$doc['id_doc_type']);

			if(!isset($doc['actif'])){
				$document->delete= TRUE;
			}
			$reference->tab_docs->add($document);
		}
		else {
			foreach ($doc as $key => $docN) {
				if(isset($docN['lien']) && $docN['lien']!=''){
					$document = new Document();
					$document->init(-1,$docN['lien'],$reference->article->id,$docN['id_doc_type']);
					$reference->tab_docs->add($document);
				}
			}

		}
	}


	$reference->save();
	/*echo "<pre style=''>";
	print_r($reference);
	echo "</pre>";*/
}

public function mod_reference_reprise_petite_pr($post,$clone=FALSE){
	echo "<pre style='background:orange;'>";
	print_r($post);
	echo "</pre>";

	$this->load->model('reference_reprise_petite_pr');
	$reference = new Reference_reprise_petite_pr();

	$reference->init($post['id'],$post['id_article'],$post['num']);


	//maj incident qualitee
	if(!isset($reference->article->incident_qualite)){
		$reference->article->addIncident($post['incident_qualite']);
	}
	else{
		$reference->article->incident_qualite->lien= $post['incident_qualite'];
	}

	//maj information
	if(!isset($reference->article->information)){
		$reference->article->addInformation($post['information']);
	}
	else{
		$reference->article->information->lien= $post['information'];
	}

//la modif des reference ne sert à rien sur une clone nouvelement créé
if(!$clone){
foreach ($post['reference_sap'] as $idRef => $ref) {
	if($idRef!="new"){
		$ref_SAP = new Reference_sap();
		$ref_SAP->init($idRef,$reference->article->id,$ref['uid'],$ref['nom_prod']);
		if($ref['uid']==''){
			$ref_SAP->delete= TRUE;
		}
		$reference->article->tab_ref_SAP->add($ref_SAP);
	}
	else{
		foreach ($ref as $idRefN => $refN) {
			if(isset($refN['uid']) && $refN['uid']!=''){
				$ref_SAP = new Reference_sap();
				$ref_SAP->init(-1,$reference->article->id,$refN['uid'],$refN['nom_prod']);
				$reference->article->tab_ref_SAP->add($ref_SAP);
			}
		}
	}
}
}

	foreach ($post['doc'] as $idDoc => $doc) {
		if($idDoc!="new"){
			$document = new Document();
			$document->init($idDoc,$doc['lien'],$reference->article->id,$doc['id_doc_type']);

			if(!isset($doc['actif'])){
				$document->delete= TRUE;
			}
			$reference->tab_docs->add($document);
		}
		else {
			foreach ($doc as $key => $docN) {
				if(isset($docN['lien']) && $docN['lien']!=''){
					$document = new Document();
					$document->init(-1,$docN['lien'],$reference->article->id,$docN['id_doc_type']);
					$reference->tab_docs->add($document);
				}
			}

		}
	}


	$reference->save();
	/*echo "<pre style=''>";
	print_r($reference);
	echo "</pre>";*/
}

public function mod_reference_petites_pieces_a_peindre($post,$clone=FALSE){
	echo "<pre style='background:orange;'>";
	print_r($post);
	echo "</pre>";

	$this->load->model('reference_petites_pieces_a_peindre');
	$reference = new Reference_petites_pieces_a_peindre();

	$reference->init($post['id'],$post['id_article'],$post['num']);


	//maj incident qualitee
	if(!isset($reference->article->incident_qualite)){
		$reference->article->addIncident($post['incident_qualite']);
	}
	else{
		$reference->article->incident_qualite->lien= $post['incident_qualite'];
	}

	//maj information
	if(!isset($reference->article->information)){
		$reference->article->addInformation($post['information']);
	}
	else{
		$reference->article->information->lien= $post['information'];
	}

//la modif des reference ne sert à rien sur une clone nouvelement créé
if(!$clone){
foreach ($post['reference_sap'] as $idRef => $ref) {
	if($idRef!="new"){
		$ref_SAP = new Reference_sap();
		$ref_SAP->init($idRef,$reference->article->id,$ref['uid'],$ref['nom_prod']);
		if($ref['uid']==''){
			$ref_SAP->delete= TRUE;
		}
		$reference->article->tab_ref_SAP->add($ref_SAP);
	}
	else{
		foreach ($ref as $idRefN => $refN) {
			if(isset($refN['uid']) && $refN['uid']!=''){
				$ref_SAP = new Reference_sap();
				$ref_SAP->init(-1,$reference->article->id,$refN['uid'],$refN['nom_prod']);
				$reference->article->tab_ref_SAP->add($ref_SAP);
			}
		}
	}
}
}

	foreach ($post['doc'] as $idDoc => $doc) {
		if($idDoc!="new"){
			$document = new Document();
			$document->init($idDoc,$doc['lien'],$reference->article->id,$doc['id_doc_type']);

			if(!isset($doc['actif'])){
				$document->delete= TRUE;
			}
			$reference->tab_docs->add($document);
		}
		else {
			foreach ($doc as $key => $docN) {
				if(isset($docN['lien']) && $docN['lien']!=''){
					$document = new Document();
					$document->init(-1,$docN['lien'],$reference->article->id,$docN['id_doc_type']);
					$reference->tab_docs->add($document);
				}
			}

		}
	}


	$reference->save();
	/*echo "<pre style=''>";
	print_r($reference);
	echo "</pre>";*/
}

public function maj_moule_injection($post){

	if(isset($post['new_moule']) && $post['new_moule']==1){
		if($post['id']==-1){
			//echo "Nouveau";
			$this->new_moule_injection($post);
		}
		else{
			//echo "Clone";
			$this->copy_moule_injection($post);
		}

	}
	else {
		if(isset($post['delete']) && $post['delete']=='on'){
			//echo "Delete";
			$this->del_moule_injection($post);
		}
		else{
			//echo "Modification";



			$this->mod_moule_injection($post);
		}

	}

}


public function maj_moule_micro_chaine($post){

	if(isset($post['new_moule']) && $post['new_moule']==1){
		if($post['id']==-1){
			echo "Nouveau";
			$this->new_moule_micro_chaine($post);
		}
		else{
			echo "Clone";
			$this->copy_moule_micro_chaine($post);
		}

	}
	else {
		if(isset($post['delete']) && $post['delete']=='on'){
			echo "Delete";
			$this->del_moule_micro_chaine($post);
		}
		else{
			echo "Modification";



			$this->mod_moule_micro_chaine($post);
		}

	}
}

public function maj_moule($type){
	$post = $this->input->post();
	if($post['num']==""){
		header("Refresh: 0 ; url= ".base_url()."administration/gestion_moule/".$type);
		echo "<script>alert('Le moule doit avoir un numéro');</script>";
		exit();
	}

	switch ($type) {
		case 'injection':
			$this->maj_moule_injection($post);
			break;
			case 'micro_chaine':
					$this->maj_moule_micro_chaine($post);
				break;
		default:
			# code...
			break;
	}
header("Refresh: 0 ; url= ".base_url()."administration/gestion_moules/".$type);
}


	public function maj_reference_ilot_packaging(){
		$post = $this->input->post();
		if($post['num']==""){
			header("Refresh: 0 ; url= ".base_url()."administration/gestion_reference_ilot_packaging/ilot_packaging/");
			echo "<script>alert('Le moule doit avoir un numéro');</script>";
			exit();
		}
		if(isset($post['new_reference']) && $post['new_reference']==1){
			if($post['id']==-1){
				echo "Nouveau";

				$this->new_reference_ilot_packaging($post);
			}
			else{
				echo "Clone";

				$this->copy_reference_ilot_packaging($post);
			}

		}
		else {
			if(isset($post['delete']) && $post['delete']=='on'){
				echo "Delete";

				$this->del_reference_ilot_packaging($post);
			}
			else{
				echo "Modification";

				$this->mod_reference_ilot_packaging($post);
			}

		}
		header("Refresh: 0 ; url= ".base_url()."administration/gestion_references_ilot_packaging/ilot_packaging");
		//redirect('administration/gestion_references_ilot_packaging/ilot_packaging');
	}

	public function maj_reference_reprise_petite_pr(){
		$post = $this->input->post();
		if($post['num']==""){
			header("Refresh: 0 ; url= ".base_url()."administration/gestion_reference_reprise_petite_pr/reprise_petite_pr/");
			echo "<script>alert('Le moule doit avoir un numéro');</script>";
			exit();
		}
		if(isset($post['new_reference']) && $post['new_reference']==1){
			if($post['id']==-1){
				echo "Nouveau";

				$this->new_reference_reprise_petite_pr($post);
			}
			else{
				echo "Clone";

				$this->copy_reference_reprise_petite_pr($post);
			}

		}
		else {
			if(isset($post['delete']) && $post['delete']=='on'){
				echo "Delete";

				$this->del_reference_reprise_petite_pr($post);
			}
			else{
				echo "Modification";

				$this->mod_reference_reprise_petite_pr($post);
			}

		}
		header("Refresh: 0 ; url= ".base_url()."administration/gestion_references_reprise_petite_pr/reprise_petite_pr");
		//redirect('administration/gestion_references_reprise_petite_pr/reprise_petite_pr');
	}

	public function maj_reference_petites_pieces_a_peindre(){
		$post = $this->input->post();
		if($post['num']==""){
			header("Refresh: 0 ; url= ".base_url()."administration/gestion_reference_petites_pieces_a_peindre/petites_pieces_a_peindre/");
			echo "<script>alert('Le moule doit avoir un numéro');</script>";
			exit();
		}
		if(isset($post['new_reference']) && $post['new_reference']==1){
			if($post['id']==-1){
				echo "Nouveau";

				$this->new_reference_petites_pieces_a_peindre($post);
			}
			else{
				echo "Clone";

				$this->copy_reference_petites_pieces_a_peindre($post);
			}

		}
		else {
			if(isset($post['delete']) && $post['delete']=='on'){
				echo "Delete";

				$this->del_reference_petites_pieces_a_peindre($post);
			}
			else{
				echo "Modification";

				$this->mod_reference_petites_pieces_a_peindre($post);
			}

		}
		header("Refresh: 0 ; url= ".base_url()."administration/gestion_references_petites_pieces_a_peindre/petites_pieces_a_peindre");
		//redirect('administration/gestion_references_petites_pieces_a_peindre/petites_pieces_a_peindre');
	}
	
	public function maj(){
$nom = $this->input->post('name');
$new = $this->input->post('new');
$mod = $this->input->post('mod');
$del = $this->input->post('del');


if($nom=="gestion_typeDoc"){
	$this->load->model('typesDocuments');
	$tds = new TypesDocuments();
	if($new!=null){
		foreach ($new as $key => $value) {
			$td = new TypeDocument();
			$td->init(-1,$value['value'],FALSE);
			$tds->add($td);
		}
	}
	if($mod!=null){
		foreach ($mod as $key => $value) {
			$td = new TypeDocument();
			$td->init($value['id'],$value['value'],FALSE);
			$tds->add($td);
		}
	}

	if($del!=null){
		foreach ($del as $key => $value) {
			$td = new TypeDocument();
			$td->init($value['id'],"",TRUE);
			$tds->add($td);
		}
	}

	$tds->save();
}
else if($nom=="gestion_presse"){
	$this->load->model('presses');
	$ps = new Presses();
	if($new!=null){
		foreach ($new as $key => $value) {
			$p = new Presse();
			$p->init(-1,$value['value'],FALSE);
			$ps->add($p);
		}
	}
	if($mod!=null){
		foreach ($mod as $key => $value) {
			$p = new Presse();
			$p->init($value['id'],$value['value'],FALSE);
			$ps->add($p);
		}
	}

	if($del!=null){
		foreach ($del as $key => $value) {
			$p = new Presse();
			$p->init($value['id'],"",TRUE);
			$ps->add($p);
		}
	}

	$ps->save();
}

	}



	public function listFormGenerator($id,$tab,$champId,$champValue,$nom){
		$this->data['javascript'].=js_balise('script_admin');
		$res="";
		$attributes = array('id' => $id,'class' =>'liste', 'name' => 'myform');
		/*controleur de verification de connection*/
		$res.=form_open('administration/connection/'.$nom,$attributes);


		$res.="<div id='inputgroup'>";
		$idcourant= 0;
		foreach ($tab as $key => $value) {
			$idcourant = $value->$champId;
			$input = array(
								'name'        => $idcourant,
								'id'          => $idcourant,
								'class'				=> 'inputClass '.$idcourant,
								'value'       => $value->$champValue,
								'required'			=> 'required',
							);
			$res.="<div class='itemFormListe'>";
			$res.=form_input($input);
			$res.="<input class='toggle-event' data-link={$idcourant} type='checkbox' checked data-toggle='toggle' data-on=' ' data-off=' ' data-onstyle='success' data-offstyle='danger' data-size='mini'>";
			$res.="</div>";
		}

		$input = array(
							'name'        => $idcourant+1,
							'id'          => $idcourant+1,
							'class'				=> 'newElement',
							'value'       => '',
						);
		$res.=form_input($input);
		$res.="</div>";
		$input2 = array(
							'name'        => 'enregistrer',
							'content'       => 'Enregistrer',
							'onclick'			=>'sub()'
						);
		$res.=form_button($input2);
		$res.=form_close();
		$res.=<<< HTML
		<script>
		var nom = '{$nom}';
		var sub = function(){

			var array_mod = $("[changed='changed']");
			var array_new = $(".newElement");
			var array_del = $("[readonly='readonly']");
			var _new=[] ;
 		 	var _mod=[] ;
		  var _del=[] ;
			var rendu="NEW: ";
			for (var i=0; i < array_new.length; i++) {
				if(array_new[i].value!=''){
					var element={};
						element.id=array_new[i].id;
						element.value=array_new[i].value;
						_new.push(element);
					rendu+=array_new[i].value;
				}

			}
		rendu+="CHA: ";
		for (var i=0; i < array_mod.length; i++) {
			var element={};
				element.id=array_mod[i].id;
				element.value=array_mod[i].value;
				_mod.push(element);
			rendu+=array_mod[i].value;

		}
		rendu+="DEL: ";
		for (var i=0; i < array_del.length; i++) {
			var element={};
				element.id=array_del[i].id;
				_del.push(element);
			rendu+=array_del[i].value;
		}

		if (confirm(rendu)){
		  ajax();
		}else{
		  alert('ici');
		}
	 function ajax(){

		 $.ajax({
			 method:'POST',
			 data: {
				 			name: nom,
				 			new: _new,
							mod: _mod,
							del: _del,
						},
				url: "../maj",
				context: document.body,
			success:function(data){
				location.reload();
			}
		});
};

		};

		</script>

HTML;

		return $res;
	}

}
