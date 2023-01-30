<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Utilisateur extends CI_Controller {

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
private $data = array('style' =>"" ,'javascript' =>"",'search'=>FALSE );
	public function __construct()
	{

		parent::__construct();
		$this->output->delete_cache();
		$this->load->database();
		$this->load->library('session');
		$this->data['style'] .= css_balise('bootstrap.min');
		$this->data['style'] .= css_balise('style_user');
		$this->data['javascript'].=js_balise('jquery-2.2.3.min');

	}

	public function index()
	{
		$this->load->view('welcome_message');
	}

  public function rechercheNomenclatures(){
		$module = $this->session->userdata('module');
  //  $this->data['nom']="nomenclatures";
	$this->data['nom']=$module;
		$this->data['javascript'].=js_balise('angular.min');
		$this->data['javascript'].=js_balise('scriptAngular');
    $this->data['contenu']=<<<HTML
<form class="searchBloc searchCenter"  ng-controller="search_nomenclature_Ctrl" action="http://sruiwp0136/docAuPoste/utilisateur/listeNomenclatures" method="post">
	<input type="hidden" name='ref' id="ref" ng-value='idSAP'/>
	Recherche
	<div id='model_search'>{{idSAP}}</div>
<div id='keyboard'>
	<div class="key" ng-click="tap(7)">7</div>
	<div class="key" ng-click="tap(8)">8</div>
	<div class="key" ng-click="tap(9)">9</div>

	<div class="key" ng-click="tap(4)">4</div>
	<div class="key" ng-click="tap(5)">5</div>
	<div class="key" ng-click="tap(6)">6</div>

	<div class="key" ng-click="tap(1)">1</div>
	<div class="key" ng-click="tap(2)">2</div>
	<div class="key" ng-click="tap(3)">3</div>

	<div class="key" ng-click="tap(0)">0</div>
	<div class="key" ng-click="back()"><span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span></div>
	<div class="key" ng-click="raz()">RAZ</div>
	<div id="raz" class="key" onclick="this.parentNode.parentNode.submit()">Rechercher</div>
	</div>
	<sript></script>
	</form> 
<style>
#contenu{
width: 95%;
    margin: auto;
    margin-bottom: 10px;
}
</style>
HTML;
    $this->data['titre']="Nomenclatures";
      $this->load->view('templates/user',	$this->data);
  }

	public function nomenclature($ref=-1,$alt=-1,$div='',$des='',$qt=-1){
		$module = $this->session->userdata('module');
	//  $this->data['nom']="nomenclatures";
	$this->data['nom']=$module;
		if($ref==-1){
			$ref=$this->input->post('ref');
			$alt=$this->input->post('alt');
			$div=$this->input->post('div');
			$des=$this->input->post('des');
			$qt=$this->input->post('qt');
		}

		$html="";
//Avoir le même affichage des positions que sur le CS12
function formatPos($int){
	$res="";
	if($int<100){
		$res="00".$int;
	}elseif ($int<1000) {
		$res="0".$int;
	}
	return $res;
}

function loop($mysqli,$ref_root,$alt_root,$ref_semi){
  $ret="";
  $s2="SELECT n.ID_NOMENCLATURE, n.DIV_ROOT, n.REF_ROOT, n.DES_ROOT, n.ALT_ROOT, n.QUANTITE_ROOT, n.REF_SEMI, n.DES_SEMI, n.MULTI_NIVEAU_COMPOSANT,n.POS_COMPOSANT,n.MAG_COMPOSANT, n.MAG2_COMPOSANT, n.DIV_COMPOSANT, n.REF_COMPOSANT, n.DES_COMPOSANT, n.TY_COMPOSANT, n.QUANTITE_COMPOSANT, n.UNITE_COMPOSANT, n.NIVEAU_COMPOSANT  FROM `nomenclature` n   WHERE n.`REF_ROOT` = {$ref_root} AND n.ALT_ROOT={$alt_root} AND n.REF_SEMI = {$ref_semi} ORDER BY n.POS_COMPOSANT";

  if ($result2 = $mysqli->query($s2)){

      while($obj2 = $result2->fetch_object()){
				/*composant*/
				$v=$obj2;

				$s3="SELECT DISTINCT VERF_COMPOSANT FROM nomenclature WHERE `REF_ROOT` = {$ref_root} AND ALT_ROOT={$alt_root} AND REF_SEMI = {$v->REF_COMPOSANT}";
				$verF = "";
				if ($result3 = $mysqli->query($s3)){
					while($tmp = $result3->fetch_object()){
						$verF = $tmp->VERF_COMPOSANT;
					}

				}

				/*style de base*/
        $color = 'rgba(0,0,0,0)';
        $weight = 'normal';
        $decoration = 'none';
        $style ='normal';
				/*style suivant le type de composant*/
        /*if(substr($v->REF_COMPOSANT,0,1)==1){
          $color="rgba(255,0,0,0.5)";
        }elseif(substr($v->REF_COMPOSANT,0,1)==2){
          //$color="rgba(0,255,0,0.5)";
          $weight = 'bold';
        }elseif(substr($v->REF_COMPOSANT,0,1)==8){
          //$color="rgba(0,255,0,0.5)";
          $decoration = 'underline';
        }elseif(substr($v->REF_COMPOSANT,0,1)==6){
          //$color="rgba(0,255,0,0.5)";
          $style = 'italic';
        }*/
        $ret.= "<tr style='background:{$color};font-weight:{$weight};text-decoration : {$decoration};font-style : {$style};'>";
        $ret.= "<td>".str_repeat('. ',$v->NIVEAU_COMPOSANT).$v->NIVEAU_COMPOSANT."</td>";
        $ret.= "<td>".$v->MULTI_NIVEAU_COMPOSANT."</td>";
        $ret.= "<td>".formatPos($v->POS_COMPOSANT)."</td>";
        $ret.= "<td>".$v->DIV_COMPOSANT."</td>";
        $ret.= "<td>".$v->MAG_COMPOSANT."</td>";
        $ret.= "<td>".$v->REF_COMPOSANT."</td>";
        $ret.= "<td>".$v->DES_COMPOSANT."</td>";
				/*Operateur ternaire = si()alors{}sinon{}*/
				$qt=($v->UNITE_COMPOSANT=="PCE")?number_format($v->QUANTITE_COMPOSANT, 0, '', ' '):number_format($v->QUANTITE_COMPOSANT, 3, ',', ' ');
        $ret.= "<td class='right'>".$qt."</td>";
        $ret.= "<td>".$v->UNITE_COMPOSANT."</td>";
        $ret.= "<td class='center'>".$v->TY_COMPOSANT."</td>";
        $ret.= "<td>".$v->MAG2_COMPOSANT."</td>";
				$ret.= "<td>".$verF."</td>";
        $ret.= "</tr>";

        $ret.= loop($mysqli,$ref_root,$alt_root,$v->REF_COMPOSANT);
      }
      return $ret;
  }
}

//connection
$mysqli = new mysqli("slanwp0167", "user_mattec", "user_mattec", "test2");
if ($mysqli->connect_errno) {
    printf("Échec de la connexion : %s\n", $mysqli->connect_error);
    exit();
}
/* Impression du document CS12
*/
$html.="<span id='print' onClick='window.print()' class='glyphicon glyphicon-print' aria-hidden='true' style='font-size: -webkit-xxx-large;float: right;
    margin-right: 5%;'></span>";
$html.="<div id='nomenclature'>";
$html.= "<div id='etiquette'>";
$html.= "<div>Article : ".$ref."</div>";
$html.= "<div>Alternative : ".$alt."</div>";
$html.= "<div>Désignation : ".$des."</div>";
$html.= "<div>Qté (PCE) : ".number_format($qt, 0, '', ' ')."</div>";
$html.= "</div>";

$html.=<<<HTML
		<div id="etiquetteErreur" style="float: right;">
			<span class="glyphicon glyphicon-warning-sign" aria-hidden="true" style="font-size: 56px; color:red;"></span>
			<span style="margin-left:10px">Si des erreurs sont repérées, veuillez contacter votre manager  </span>
		</div>
HTML;

$html.= "<div id=table>";
$html.= "<TABLE>";
$html.= <<<HTML
<tr>
<th>Niveau</th>
<th>Mt.Niv</th>
<th>Pos.</th>
<th>Div</th>
<th>Mag.</th>
<th>ID objet</th>
<th>Désignation objet</th>
<th>Qté (UMC)</th>
<th>UQ</th>
<th>Ty.</th>
<th>Mag.</th>
<th>VersF</th>
</tr>
HTML;
$html.= loop($mysqli,$ref,$alt,$ref);
$html.= "</TABLE> ";
$html.= "</div>";
$html.="</div>";
$mysqli->close();



$html.=<<<HTML
<style>
body{
  /*font-size: small;*/
}
#etiquette, #etiquetteErreur{
border: black 1px solid;
    border-radius: 5px;
    display: inline-block;
    padding: 10px;
    margin-bottom: 10px;
}
table{
  font-size: inherit;
  border: none;
  border-collapse: collapse;
}
td, th {
    border-left: 1px solid #000;
    border-right: 1px solid #000;
		padding: 0 5px;
}
th {
    border-bottom: 1px solid #000;
    background:grey;
}
td:first-child,th:first-child {
border-left: none;
}

td:last-child,th:last-child {
border-right: none;
}
#table{
border:black 1px solid;
display: table;
}
.right{
  text-align: right;
    padding-right: 10px;
}
.center{
  text-align: center;
}
#contenu{
width: 95%;
    margin: auto;
    margin-bottom: 10px;
}
#nomenclature{
font-size:smaller;
}
@media print{

  body{
    font-size: small;
  }
	#contenu{
	width:100%;
	margin:0px;
}
	#header, #etiquetteErreur{
		display:none;
	}
	#print{
		display:none;
	}
}
</style>
HTML;
$this->data['contenu']=$html;
    $this->data['titre']="Nomenclature - ".$ref;
      $this->load->view('templates/user',	$this->data);
  }

	public function listeNomenclatures($ref=-1){
		if($ref==-1){
			$ref=$this->input->post('ref');
		}

		$module = $this->session->userdata('module');
	//  $this->data['nom']="nomenclatures";
	$this->data['nom']=$module;
		$this->data['titre']="Liste Nomenclatures - ".$ref;

$html="";
//connection
$mysqli = new mysqli("slanwp0167", "user_mattec", "", "");
if ($mysqli->connect_errno) {
    printf("Échec de la connexion : %s\n", $mysqli->connect_error);
    exit();
}
$num_rows=-1;
$s="SELECT DISTINCT REF_ROOT,ALT_ROOT, DIV_ROOT, DES_ROOT ,DIV_COMPOSANT,QUANTITE_ROOT FROM `nomenclature` WHERE `REF_ROOT` = {$ref} AND  NIVEAU_COMPOSANT=1 AND QUANTITE_COMPOSANT=QUANTITE_ROOT  ORDER BY POS_COMPOSANT";
if ($result = $mysqli->query($s)) {
if($result->num_rows==0){
	$s="SELECT DISTINCT REF_ROOT,ALT_ROOT, DIV_ROOT, DES_ROOT ,DIV_COMPOSANT,QUANTITE_ROOT FROM `nomenclature` WHERE `REF_ROOT` = {$ref} AND  NIVEAU_COMPOSANT=1  ORDER BY POS_COMPOSANT"; // si le premier éléméent de la nomenclature n'a pas une quantité équal au produit "racine"
	if($result = $mysqli->query($s)) {
		// erreur
	}

}
  while($obj = $result->fetch_object()){

		$num_rows=$result->num_rows;
		if($num_rows==1){
		  $this->nomenclature($ref,$obj->ALT_ROOT,$obj->DIV_ROOT,$obj->DES_ROOT,$obj->QUANTITE_ROOT);
		}else{
$base_url = base_url();
$html.=<<<HTML
		<form id="test{$obj->ALT_ROOT}" action="{$base_url}utilisateur/nomenclature" method="POST">
		<input type="hidden" name="ref" value="$ref"/>
		<input type="hidden" name="alt" value="{$obj->ALT_ROOT}"/>
		<input type="hidden" name="div" value="{$obj->DIV_ROOT}"/>
		<input type="hidden" name="des" value="{$obj->DES_ROOT}"/>
		<input type="hidden" name="qt" value="{$obj->QUANTITE_ROOT}"/>
		</form>
		<a href='#' onclick='document.getElementById("test{$obj->ALT_ROOT}").submit()'>
HTML;
			$html.= "<div id='etiquette'>";
			$html.= "<div>Article : ".$ref."</div>";
			$html.= "<div>Alternative : ".$obj->ALT_ROOT."</div>";
			$html.= "<div>Désignation : ".$obj->DES_ROOT."</div>";
			$html.= "<div>Qté (PCE) : ".number_format($obj->QUANTITE_ROOT, 0, '', ' ')."</div>";
			$html.= "</div>";
			$html.="</a>";

		}


}



$result->close();
}
$mysqli->close();

if($num_rows>1 ){
$html.=<<<HTML
	<style>
	#etiquette{
	border: black 1px solid;
	    border-radius: 5px;
	    display: inline-block;
	    padding: 10px;
	    margin-bottom: 10px;

	}
	a{
	  color : black;
	}
	#contenu{
	width: 95%;
	    margin: auto;
	    margin-bottom: 10px;
	}
	</style>
HTML;
	$this->data['contenu']=$html;
	$this->load->view('templates/user',	$this->data);
}
elseif($num_rows==-1){
	$this->rechercheNomenclatures();
}

  }

	private function liste($tab,$idContain,$classElement,$attLien,$attAff,$control="",$search=""){
		$res = "";
		$attAff="{{element.{$attAff}}}";
		$res.="<div id='{$idContain}'  ng-init='tab={$tab}' ng-controller='liste_Ctrl'>";
$open="onclick='return open_pdf_vivaldi(this);'";
if(strstr($classElement,"nomenclature")){
	$url = base_url()."utilisateur/".$control."/{{element.".$attLien."}}";
$open=<<<HTML
target="_blank" href="javascript:(function(){return open_a_window('{$url}');})()"
HTML;
	$attLien="";
	$attAff="Nomenclature ".$attAff;
}
	else
	{
		if($control!="" ){
			$attLien=base_url("utilisateur/{$control}/{{element.{$attLien}}}/".	$this->data['tmp']);
		}
		else{
			$attLien="{{element.{$attLien}}}";
		}
		}


		$res.="<a  ".$open." href='".$attLien."'  ng-repeat='element in tab";
		 //$res.=($search==TRUE)?"| filter:filtre":"";
		 $res.=($search==TRUE)?"| startsWith:filtre.num":"";
		 $res.="' ><div class='{$classElement}'>{$attAff}</div></a>";
		$res.="</div>";

/*Mettre le tout avac la recherche*/
		if($search!=""){
			$res=<<<HTML
			
			<div  ng-controller="search_Ctrl" ng-init="filtre={'{$search}':''}">

<!--
<div class="searchBloc">
Recherche
  <div id='model_search'  ng-model="filtre.{$search}">{{filtre.$search}}</div>
			  <div id='keyboard'>
			    <div class="key" ng-click="tap(7)">7</div>
			    <div class="key" ng-click="tap(8)">8</div>
			    <div class="key" ng-click="tap(9)">9</div>

			    <div class="key" ng-click="tap(4)">4</div>
			    <div class="key" ng-click="tap(5)">5</div>
			    <div class="key" ng-click="tap(6)">6</div>

			    <div class="key" ng-click="tap(1)">1</div>
			    <div class="key" ng-click="tap(2)">2</div>
			    <div class="key" ng-click="tap(3)">3</div>

			    <div class="key" ng-click="tap(0)">0</div>
			    <div class="key" ng-click="tap('-')">-</div>
			    <div class="key" ng-click="back()"><span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span></div>

					<div id="raz" class="key" ng-click="raz()">RAZ</div>

			    </div>
	</div>
	-->				$res
			</div>
			
HTML;
		}

		return $res;
	}

	public function injection()
	{
		$this->data['javascript'].=js_balise('angular.min');
		$this->data['javascript'].=js_balise('scriptAngular');
		//print_r($_SERVER["REMOTE_ADDR"]);
    	$this->data['nom']="injection";
			$this->data['tmp']=	$this->data['nom'];
			$this->data['titre']="Assemblage - Produits";

$this->load->model('presses');
$tab_presses = $this->presses->getAllPresses();
			$tab_presse = json_encode($tab_presses);
			$this->data['contenu']=$this->liste($tab_presse,'presses','presse','id','num','injection_presse');


    $this->load->view('templates/user',	$this->data);
	}

	public function injection_presse($idPresse)
	{
$this->data['javascript'].=js_balise('angular.min');
$this->data['javascript'].=js_balise('scriptAngular');

		$this->load->model('moules_injection');
		$res = $this->moules_injection->getMoulesByPresse($idPresse);
		$this->load->model('presse');
		$presse = $this->presse->getPresseById($idPresse);

$this->data['titre']="Assemblage - Produit ".$presse->num;

$this->data['nom']="injection";
$this->data['tmp']=$this->data['nom']."/".$idPresse;
$tab_moules = json_encode($res);
$this->data['contenu']=$this->liste($tab_moules,'moules','moule','id','num','moule',"num");

    $this->load->view('templates/user',	$this->data);
	}

	public function moule($idMoule,$type,$idPresse=-1)
	{
		$this->data['javascript'].=js_balise('angular.min');
		$this->data['javascript'].=js_balise('scriptAngular');
		$this->data['javascript'].=js_balise('openPDF2');
		$moule=NULL;
		$idMP = -1;
switch ($type) {
	case 'injection':
	$this->load->model('moule_injection');
	$this->load->model('moule_presse');
	$moule = new Moule_injection();
	$idMP = $this->moule_presse->getIdMoulePresseByIdMouleIdPresse($idMoule,$idPresse);
		break;

		case 'micro_chaine':
		$this->load->model('moule_micro_chaine');
		$moule = new Moule_micro_chaine();
			break;

	default:
		# code...
		break;
}
		$moule->getMouleById($idMoule);

	
		$this->data['titre']="".$moule->num;
		$this->data['nom']=$type;
		$this->data['tmp']=	$this->data['nom'];

$this->data['contenu']="";


		$this->data['contenu'].="<p id='nom_prod' style='display:flex; justify-content: space-around; flex-wrap: wrap;'>";
		foreach ($moule->article->tab_ref_SAP->tab_references as $key => $value) {
			$this->data['contenu'].="<span style='width:50%'>{$value->id_SAP} - {$value->nom_prod}</span>";
		}
		$this->data['contenu'].="</p>";

$this->data['contenu'].="<script>var incident_qualite=false; var information=false;</script>";
$this->data['contenu'].="<div id='info_qualite'>";
		if($moule->article->incident_qualite){
			$this->data['contenu'].="<a onclick='unlock_incident(); return open_pdf_presse(this);' href='{$moule->article->incident_qualite->lien}'><div id='incident'>Nouvel incident qualité. Cliquez pour afficher.</div></a>";
$this->data['contenu'].="<script>incident_qualite=true;</script>";
		}
		if($moule->article->information){
			$this->data['contenu'].="<a onclick='unlock_informationt(); return open_pdf_presse(this);' href='{$moule->article->information->lien}'><div id='information'>Nouvelle information. Cliquez pour afficher.</div></a>";
			$this->data['contenu'].="<script>information=true;</script>";
		}
$this->data['contenu'].="</div>";

		$tab_document = json_encode($moule->tab_docs->tab_documents);
		$this->data['contenu'].=$this->liste($tab_document,'documents','document','lien','doc_type.label');

if($type=="injection"){
	$tab_doc_moule_presse= json_encode($moule->tab_moules_presses->tab_moule_presse[$idMP]->tab_docs->tab_documents);

	$this->data['contenu'].=$this->liste($tab_doc_moule_presse,'documentsMP','document','lien','doc_type.label');
}
	$tab_ref_SAP = json_encode($moule->article->tab_ref_SAP->tab_references);
	$this->data['contenu'].=$this->liste($tab_ref_SAP,'documents','document nomenclature','id_SAP','id_SAP','listeNomenclatures');
$this->data['contenu'].=<<<HTML
<script>

unlock_general();


function lock_incident(){
	/*Operateur ternaire = si()alors{}sinon{}*/
	incident_qualite=($("#incident > a").length)?true:false;
	unlock_general();
}

function lock_information(){
	/*Operateur ternaire = si()alors{}sinon{}*/
	information=($("#information > a").length)?true:false;
	unlock_general();
}

function unlock_incident(){
	incident_qualite=false;
	$("#incident").css('background-color','grey');
		unlock_general();
}

function unlock_informationt(){
	information=false;
	$("#information").css('background-color','grey');
	unlock_general();
}

function unlock_general(){
	if(!incident_qualite && !information){
		$('#documents, #documentsMP').show();
		$("#incident").css('background-color','red');
		$(" #information").css('background-color','orange');
	}
	else{
		$('#documents, #documentsMP').hide();
	}
}

</script>
HTML;



    $this->load->view('templates/user',	$this->data);
	}

	public function reference_ilot_packaging($idMoule,$type)
	{
		$this->data['javascript'].=js_balise('angular.min');
		$this->data['javascript'].=js_balise('scriptAngular');
		$this->data['javascript'].=js_balise('openPDF2');

		$reference=NULL;

			$this->load->model('references_ilot_packaging');
			$reference = new Reference_ilot_packaging();

		$reference->getReferenceById($idMoule);

		$this->data['titre']=ucfirst(str_replace('_','-',$type))." - Reference ".$reference->num;
		$this->data['nom']=$type;
		$this->data['tmp']=$this->data['nom'];
		$tab_document = json_encode($reference->tab_docs->tab_documents);
		$tab_ref_SAP = json_encode($reference->article->tab_ref_SAP->tab_references);



		$this->data['contenu']="";
		$this->data['contenu'].="<p id='nom_prod' style='display:flex; justify-content: space-around; flex-wrap: wrap;'>";
		foreach ($reference->article->tab_ref_SAP->tab_references as $key => $value) {
			$this->data['contenu'].="<span style='width:50%'>{$value->id_SAP} - {$value->nom_prod}</span>";
		}


		$this->data['contenu'].="<script>var incident_qualite=false; var information=false;</script>";
		$this->data['contenu'].="<div id='info_qualite'>";
				if($reference->article->incident_qualite){
					$this->data['contenu'].="<a onclick='unlock_incident(); return open_pdf_presse(this);' href='{$reference->article->incident_qualite->lien}'><div id='incident'>Nouvel incident qualité. Cliquez pour afficher.</div></a>";
		$this->data['contenu'].="<script>incident_qualite=true;</script>";
				}
				if($reference->article->information){
					$this->data['contenu'].="<a onclick='unlock_informationt(); return open_pdf_presse(this);' href='{$reference->article->information->lien}'><div id='information'>Nouvelle information. Cliquez pour afficher.</div></a>";
					$this->data['contenu'].="<script>information=true;</script>";
				}
		$this->data['contenu'].="</div>";
		$this->data['contenu'].=$this->liste($tab_document,'documents','document','lien','doc_type.label');
		$this->data['contenu'].=$this->liste($tab_ref_SAP,'documents','document nomenclature','id_SAP','id_SAP','listeNomenclatures');
$base_url= base_url();

$this->data['contenu'].=<<<HTML
<script>

unlock_general();


function lock_incident(){
	incident_qualite=($("#incident > a").length)?true:false;
	unlock_general();
}

function lock_information(){
	information=($("#information > a").length)?true:false;
	unlock_general();
}

function unlock_incident(){
	incident_qualite=false;
	$("#incident").css('background-color','grey');
		unlock_general();
}

function unlock_informationt(){
	information=false;
	$("#information").css('background-color','grey');
	unlock_general();
}

function unlock_general(){
	if(!incident_qualite && !information){
		$('#documents, #documentsMP').show();
		$("#incident").css('background-color','red');
		$(" #information").css('background-color','orange');
	}
	else{
		$('#documents, #documentsMP').hide();
	}
}

</script>
HTML;


    $this->load->view('templates/user',	$this->data);
	}

	public function reference_reprise_petite_pr($idMoule,$type)
	{
		$this->data['javascript'].=js_balise('angular.min');
		$this->data['javascript'].=js_balise('scriptAngular');
		$this->data['javascript'].=js_balise('openPDF2');

		$reference=NULL;

			$this->load->model('references_reprise_petite_pr');
			$reference = new Reference_reprise_petite_pr();

		$reference->getReferenceById($idMoule);

		$this->data['titre']=ucfirst(str_replace('_','-',$type))." - Reference ".$reference->num;
		$this->data['nom']=$type;
		$this->data['tmp']=$this->data['nom'];
		$tab_document = json_encode($reference->tab_docs->tab_documents);
		$tab_ref_SAP = json_encode($reference->article->tab_ref_SAP->tab_references);



		$this->data['contenu']="";
		$this->data['contenu'].="<p id='nom_prod' style='display:flex; justify-content: space-around; flex-wrap: wrap;'>";
		foreach ($reference->article->tab_ref_SAP->tab_references as $key => $value) {
			$this->data['contenu'].="<span style='width:50%'>{$value->id_SAP} - {$value->nom_prod}</span>";
		}


		$this->data['contenu'].="<script>var incident_qualite=false; var information=false;</script>";
		$this->data['contenu'].="<div id='info_qualite'>";
				if($reference->article->incident_qualite){
					$this->data['contenu'].="<a onclick='unlock_incident(); return open_pdf_presse(this);' href='{$reference->article->incident_qualite->lien}'><div id='incident'>Nouvel incident qualité. Cliquez pour afficher.</div></a>";
		$this->data['contenu'].="<script>incident_qualite=true;</script>";
				}
				if($reference->article->information){
					$this->data['contenu'].="<a onclick='unlock_informationt(); return open_pdf_presse(this);' href='{$reference->article->information->lien}'><div id='information'>Nouvelle information. Cliquez pour afficher.</div></a>";
					$this->data['contenu'].="<script>information=true;</script>";
				}
		$this->data['contenu'].="</div>";
		$this->data['contenu'].=$this->liste($tab_document,'documents','document','lien','doc_type.label');
		$this->data['contenu'].=$this->liste($tab_ref_SAP,'documents','document nomenclature','id_SAP','id_SAP','listeNomenclatures');
$base_url= base_url();

$this->data['contenu'].=<<<HTML
<script>

unlock_general();


function lock_incident(){
	incident_qualite=($("#incident > a").length)?true:false;
	unlock_general();
}

function lock_information(){
	information=($("#information > a").length)?true:false;
	unlock_general();
}

function unlock_incident(){
	incident_qualite=false;
	$("#incident").css('background-color','grey');
		unlock_general();
}

function unlock_informationt(){
	information=false;
	$("#information").css('background-color','grey');
	unlock_general();
}

function unlock_general(){
	if(!incident_qualite && !information){
		$('#documents, #documentsMP').show();
		$("#incident").css('background-color','red');
		$(" #information").css('background-color','orange');
	}
	else{
		$('#documents, #documentsMP').hide();
	}
}

</script>
HTML;


    $this->load->view('templates/user',	$this->data);
	}

	public function reference_petites_pieces_a_peindre($idMoule,$type)
	{
		$this->data['javascript'].=js_balise('angular.min');
		$this->data['javascript'].=js_balise('scriptAngular');
		$this->data['javascript'].=js_balise('openPDF2');

		$reference=NULL;

			$this->load->model('references_petites_pieces_a_peindre');
			$reference = new Reference_petites_pieces_a_peindre();

		$reference->getReferenceById($idMoule);

		$this->data['titre']=ucfirst(str_replace('_','-',$type))." - Reference ".$reference->num;
		$this->data['nom']=$type;
		$this->data['tmp']=$this->data['nom'];
		$tab_document = json_encode($reference->tab_docs->tab_documents);
		$tab_ref_SAP = json_encode($reference->article->tab_ref_SAP->tab_references);



		$this->data['contenu']="";
		$this->data['contenu'].="<p id='nom_prod' style='display:flex; justify-content: space-around; flex-wrap: wrap;'>";
		foreach ($reference->article->tab_ref_SAP->tab_references as $key => $value) {
			$this->data['contenu'].="<span style='width:50%'>{$value->id_SAP} - {$value->nom_prod}</span>";
		}


		$this->data['contenu'].="<script>var incident_qualite=false; var information=false;</script>";
		$this->data['contenu'].="<div id='info_qualite'>";
				if($reference->article->incident_qualite){
					$this->data['contenu'].="<a onclick='unlock_incident(); return open_pdf_presse(this);' href='{$reference->article->incident_qualite->lien}'><div id='incident'>Nouvel incident qualité. Cliquez pour afficher.</div></a>";
		$this->data['contenu'].="<script>incident_qualite=true;</script>";
				}
				if($reference->article->information){
					$this->data['contenu'].="<a onclick='unlock_informationt(); return open_pdf_presse(this);' href='{$reference->article->information->lien}'><div id='information'>Nouvelle information. Cliquez pour afficher.</div></a>";
					$this->data['contenu'].="<script>information=true;</script>";
				}
		$this->data['contenu'].="</div>";
		$this->data['contenu'].=$this->liste($tab_document,'documents','document','lien','doc_type.label');
		$this->data['contenu'].=$this->liste($tab_ref_SAP,'documents','document nomenclature','id_SAP','id_SAP','listeNomenclatures');
$base_url= base_url();

$this->data['contenu'].=<<<HTML
<script>

unlock_general();


function lock_incident(){
	incident_qualite=($("#incident > a").length)?true:false;
	unlock_general();
}

function lock_information(){
	information=($("#information > a").length)?true:false;
	unlock_general();
}

function unlock_incident(){
	incident_qualite=false;
	$("#incident").css('background-color','grey');
		unlock_general();
}

function unlock_informationt(){
	information=false;
	$("#information").css('background-color','grey');
	unlock_general();
}

function unlock_general(){
	if(!incident_qualite && !information){
		$('#documents, #documentsMP').show();
		$("#incident").css('background-color','red');
		$(" #information").css('background-color','orange');
	}
	else{
		$('#documents, #documentsMP').hide();
	}
}

</script>
HTML;


    $this->load->view('templates/user',	$this->data);
	}
	
  public function micro_chaine()
	{
		$this->data['javascript'].=js_balise('angular.min');
		$this->data['javascript'].=js_balise('scriptAngular');


	 	$this->data['titre']="Déchargement reprise";
		$this->data['nom']="micro_chaine";
		$this->data['tmp']=$this->data['nom'];

		$this->load->model('moules_micro_chaine');
		$res = $this->moules_micro_chaine->getAllmoules();

$tab_moules = json_encode($res);
$this->data['contenu']=$this->liste($tab_moules,'moules','moule','id','num','moule',"num");
		$this->load->view('templates/user',	$this->data);
	}

  public function ilot_packaging()
	{
		$this->data['javascript'].=js_balise('angular.min');
		$this->data['javascript'].=js_balise('scriptAngular');

		$this->load->model('references_ilot_packaging');
		$res = $this->references_ilot_packaging->getAllReference();

/*
		echo "<pre>";
		var_dump($res);
		echo "</pre>";
*/

		$this->data['titre']="Ilot packaging - References";
		$this->data['nom']="ilot_packaging";
		$this->data['tmp']=	$this->data['nom'];
		$tab_references = json_encode($res);
		$this->data['contenu']=$this->liste($tab_references,'moules','moule','id','num','reference_ilot_packaging',"num");

	 $this->load->view('templates/user',	$this->data);
	}

  public function reprise_petite_pr()
	{
		$this->data['javascript'].=js_balise('angular.min');
		$this->data['javascript'].=js_balise('scriptAngular');

		$this->load->model('references_reprise_petite_pr');
		$res = $this->references_reprise_petite_pr->getAllReference();

		$this->data['titre']="Reprise petite PR - References";
		$this->data['nom']="reprise_petite_pr";
		$this->data['tmp']=	$this->data['nom'];
		$tab_references = json_encode($res);
		$this->data['contenu']=$this->liste($tab_references,'moules','moule','id','num','reference_reprise_petite_pr',"num");

	 $this->load->view('templates/user',	$this->data);
	}

  public function petites_pieces_a_peindre()
	{
		$this->data['javascript'].=js_balise('angular.min');
		$this->data['javascript'].=js_balise('scriptAngular');

		$this->load->model('references_petites_pieces_a_peindre');
		$res = $this->references_petites_pieces_a_peindre->getAllReference();

/*
		echo "<pre>";
		var_dump($res);
		echo "</pre>";
*/

		$this->data['titre']="Petites pièces à peindre - References";
		$this->data['nom']="petites_pieces_a_peindre";
		$this->data['tmp']=	$this->data['nom'];
		$tab_references = json_encode($res);
		$this->data['contenu']=$this->liste($tab_references,'moules','moule','id','num','reference_petites_pieces_a_peindre',"num");

	 $this->load->view('templates/user',	$this->data);
	}
	
	public function openPDF2(){
		/*Version paramatre variable qui évolura si mise a jour du moteur php car avec la version actuel sur le serveur on ne peut utiliser '...'*/
		$codes =func_get_args();
		$code ="";
		foreach ($codes as $key =>$value) {
			if($key==0){
				$code = $value;
			}
			else{
				$code = $code.'/'.$value;
			}
		}

		/*Emplacement de "cache"*/
		$path = "assets/pdf/tmp.pdf";
		/*Pour recuperer sur le serveur vivaldi*/
		//$url="http://sruiwp0081/vivaldiqms/retr_doc.aspx?code={$code}&dossier=1";
	$url="http://vivaldi_ruitz/retr_doc.aspx?code={$code}&dossier=2";
		//http://vivaldi_ruitz/retr_doc.aspx?code=ST-RSA-XFA-peau-PI-FTS11h&dossier=2

/*enregistrement du fichier téléchargé*/
		$fp = fopen($path, 'w');
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		$data = curl_exec($ch);
		$curl_errno = curl_errno($ch);
		$curl_error = curl_error($ch);
		curl_close($ch);
		fclose($fp);

		if ($curl_errno > 0) {
		echo "cURL Error ($curl_errno): $curl_error\n";
		} else {
			$this->data['code']=$code;
			$this->data['path']=base_url().$path;
			$this->load->view('templates/pdf',	$this->data);
		}
	}



}
