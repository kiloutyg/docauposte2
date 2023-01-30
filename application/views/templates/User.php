<!DOCTYPE html>
<html lang="fr" ng-app="app">
<head>
  <link rel="icon" href="<?php echo img_url('favicon.ico')?>" />
  <title>Utilisateur - <?php echo $nom; ?></title>
  <?php echo $style; ?>
  <?php echo $javascript; ?>
  <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
</head>
	<body>
  <header id="header">
    <img src="<?php echo img_url("logo_po_exe.jpg"); ?>"     height="100%">
    <div id="titre">
      <?php echo $titre; ?>
    </div>
    <div>
    <a href='<?php echo base_url($nom); ?>'><div id='home'><img src="<?php echo img_url('home.png'); ?>" alt="" width="80" /></div></a>
	
    <a href='#' onclick='history.back()'><div id='back'><img src="<?php echo img_url('retour.png'); ?>" alt="" /></div></a>
  </div>
  </header>
  
  <div id="contenu" >
    <?php echo $contenu; ?>
  </div>


  <!--
  document.getElementById('toto').textContent
  -->

	</body>
  <script>
/*Cacher le bouton retour s'il ne sert à rien*/
    if(history.length<2){
      $('#back').css('display','none')
    }


  </script>
</html>
