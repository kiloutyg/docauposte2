#!/usr/local/bin/php -q
<?php
set_time_limit(1500);
  ini_set('memory_limit','2048M');

  include("/PHPExcel 1.8.0\Classes\PHPExcel.php");

  function getCell( PHPExcel_Worksheet $sheet, /* string */ $x = 'A', /* int */ $y = 1 ) {

      return $sheet->getCell( $x . $y );

  }

  //$inputFileName = base_url('assets/nomenclatures/Nomenclatures.xlsx');
  $inputFileName = 'D:\xamp\htdocs\docAuPoste\assets\nomenclatures\Nomenclatures.xlsx';
  //$inputFileType = 'Excel2007';
  /**  Identify the type of $inputFileName  **/
  $inputFileType = PHPExcel_IOFactory::identify($inputFileName);


  /**  Create a new Reader of the type that has been identified  **/
  $objReader = PHPExcel_IOFactory::createReader($inputFileType);
  /**  Advise the Reader that we only want to load cell data  **/
  $objReader->setReadDataOnly(true);

  /**  Load $inputFileName to a PHPExcel Object  **/
  $objPHPExcel = $objReader->load($inputFileName);

  //Avec getRowIterator, on parcour toutes les lignes du tableau
  $rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();

  //connection
  $mysqli = new mysqli("localhost", "user_mattec", "user_mattec", "docAuPoste");
  if ($mysqli->connect_errno) {
      printf("Échec de la connexion : %s\n", $mysqli->connect_error);
      exit();
  }

  $d="TRUNCATE TABLE nomenclature";
  if ($result = $mysqli->query($d)) {
  //good
  }

  foreach($rowIterator as $row) {
    $id_nomenclature = $row->getRowIndex ();
    /*$objPHPExcel->getActiveSheet()->getCellByColumnAndRow('0',$id_nomenclature)->getValue()*/
    $sheet = $objPHPExcel->getActiveSheet();

    $div_root = getCell($sheet,'A',$id_nomenclature)->getValue();
    $ref_root = getCell($sheet,'B',$id_nomenclature)->getValue();
    $des_root = getCell($sheet,'C',$id_nomenclature)->getValue();
    $alt_root = getCell($sheet,'D',$id_nomenclature)->getValue();
    $quantite_root = getCell($sheet,'R',$id_nomenclature)->getValue();
    $ref_semi = getCell($sheet,'E',$id_nomenclature)->getValue();
    $des_semi = getCell($sheet,'F',$id_nomenclature)->getValue();
    $multi_niveau_composant = getCell($sheet,'G',$id_nomenclature)->getValue();
    $pos_composant = getCell($sheet,'H',$id_nomenclature)->getValue();
    $mag_composant = getCell($sheet,'J',$id_nomenclature)->getValue();
    $mag2_composant = getCell($sheet,'K',$id_nomenclature)->getValue();
    $div_composant = getCell($sheet,'I',$id_nomenclature)->getValue();
    $ref_composant = getCell($sheet,'L',$id_nomenclature)->getValue();
    $des_composant = getCell($sheet,'M',$id_nomenclature)->getValue();
    $ty_composant = getCell($sheet,'N',$id_nomenclature)->getValue();
    $verF_composant = getCell($sheet,'O',$id_nomenclature)->getValue();
    $quantite_composant = getCell($sheet,'P',$id_nomenclature)->getValue();
    $unite_composant = getCell($sheet,'S',$id_nomenclature)->getValue();
    $niveau_composant = getCell($sheet,'Q',$id_nomenclature)->getValue();

    $s="INSERT INTO nomenclature (ID_NOMENCLATURE, DIV_ROOT, REF_ROOT, DES_ROOT, ALT_ROOT, QUANTITE_ROOT, REF_SEMI, DES_SEMI, MULTI_NIVEAU_COMPOSANT,POS_COMPOSANT,MAG_COMPOSANT, MAG2_COMPOSANT, DIV_COMPOSANT, REF_COMPOSANT, DES_COMPOSANT, TY_COMPOSANT, QUANTITE_COMPOSANT, UNITE_COMPOSANT, NIVEAU_COMPOSANT,VERF_COMPOSANT) VALUES({$id_nomenclature}, '{$div_root}', {$ref_root},'{$des_root}',{$alt_root},{$quantite_root}, {$ref_semi}, '{$des_semi}','{$multi_niveau_composant}',{$pos_composant},'{$mag_composant}', '{$mag2_composant}', '{$div_composant}',{$ref_composant},'{$des_composant}','{$ty_composant}', {$quantite_composant}, '{$unite_composant}',{$niveau_composant},'{$verF_composant}')";
    if ($result = $mysqli->query($s)) {
    //good
    }
    /*echo "<pre>";
    var_dump($result);
    var_dump($mysqli);
    echo "</pre>";
  break;*/
  }
  $mysqli->close();
