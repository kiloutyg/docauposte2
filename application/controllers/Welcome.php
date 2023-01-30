<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

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

	public function __construct()
	{
		parent::__construct();
		$this->output->delete_cache();
		$this->load->library('session');
	}

	public function index()
	{
		//$this->load->view('welcome_message');
			$data['nom_propre'] = "";
			$data['title'] = "Documents au poste";
		  $this->load->view('templates/home', $data);

	}

	public function frontal($nom="injection")
	{
$this->session->set_userdata('module', $nom);
		$data['title'] = 'Site documents au poste ';
		$data['nom'] = $nom;

		if($nom=="injection"){
			$data['nom_propre'] =" - ASSEMBLAGE";
		}
		elseif($nom=="micro_chaine"){
			$data['nom_propre'] ="- DECHARGEMENT REPRISE";
		}
		elseif($nom=="assemblage"){
			$data['nom_propre'] ="ASSEMBLAGE";
		}
		elseif($nom=="ilot_packaging"){
			$data['nom_propre'] ="ILOT PACKAGING";
		}
		elseif($nom=="reprise_petite_pr"){
			$data['nom_propre'] ="REPRISE PETITE PR";
		}
		elseif($nom=="petites_pieces_a_peindre"){
			$data['nom_propre'] ="PETITES PIECES A PEINDRE";
		}
 /*utiliser doctine
 $this->load->model('moule');
$this->load->library('doctrine');
$em = $this->doctrine->em;
$moule= new MOULE;*/


    $this->load->view('templates/home', $data);

	}

	public function pastrouve()
{
header("HTTP/1.1 404 Not Found");
$data['javascript']="";
$data['style']=css_balise('style_user');
$data['nom']="";
$data['titre']="La page demandée n'existe pas.";
$data['contenu']="";
$this->load->view('templates/user',$data);
}

}
