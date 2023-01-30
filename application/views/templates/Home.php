<html>
	<head>
		<link rel="icon" href="<?php echo img_url('favicon.ico')?>" />
		<title><?php echo $title; ?></title>
    <?php echo css_balise('style_frontal'); ?>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	</head>
	<body>
	
	
	<a  href="http://slanwp0167/docAuPoste/injection"><img class="retour" src="home.png" width="90"/></a>


	<?php if(isset($nom_propre) && $nom_propre!=""): ?>
			<div id='administration-index'>
				<a href='<?php echo base_url('administration/connection/'.$nom); ?>'>Menu administration</a>
			</div>
		<?php endif; ?>
		<div id="contenu">
			<div id="lieu">
Usine De Langres
			</div>
			<div id="titre">
DOCUMENTS AU POSTE <?php echo $nom_propre; ?>
			</div>
		<?php if(isset($nom_propre) && $nom_propre!=""): ?>
			<div id='utilisateur-index'>
				<a style="color:white;" href='<?php echo base_url('utilisateur/'.$nom); ?>'>CHOIX PRODUITS</a>
				<!--<img src='<?php echo img_url('suivant.png')?> <div><a style="color:white;" href='nomenclatures'>NOMENCLATURES</a></div>-->
			</div>

				<div id="info">
				
					
					<p>En cas de problème, alerter le manager</p>
				
				</div>
				
			</div>
		<?php else: ?>
			<p >
				<div><a style="color:white;" href='injection'>ASSEMBLAGE</a></div>
				<!--<div><a style="color:white;" href='micro_chaine'>MICRO-CHAINE</a></div>
				<div><a style="color:white;" href='ilot_packaging'>ILOT-PACKAGING</a></div>
				<div><a style="color:white;" href='reprise_petite_pr'>REPRISE PETITE PR</a></div>
				<div><a style="color:white;" href='petites_pieces_a_peindre'>PETITES PIECES A PEINDRE</a></div>
				<div><a style="color:white;" href='nomenclatures'>NOMENCLATURES</a></div>-->
			</p>
		<?php endif; ?>
	</body>
</html>
