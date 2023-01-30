<?php
/*
Fonction rac. url dossier asset
*/
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    if ( ! function_exists('asset_url'))
    {
      function asset_url()
      {
         return base_url().'assets/';
      }
    }

/*
Fonction rac. balise
*/

    if ( ! function_exists('js_balise'))
    {
      function js_balise($nom)
      {
        return '<script src="' . base_url() . 'assets/js/' . $nom . '.js"></script>';
      }
    }

    if ( ! function_exists('css_balise'))
    {
      function css_balise($nom)
      {
        return '<link rel="stylesheet" type="text/css" href="'. base_url() . 'assets/css/' . $nom . '.css" media="all"/>';
      }
    }
/*
Fonction rac. url
*/
    if ( ! function_exists('js_url'))
    {
      function js_url($nom)
      {
        return   base_url() . 'assets/js/' . $nom . '.js';
      }
    }

    if ( ! function_exists('css_url'))
    {
      function css_url($nom)
      {
        return   base_url() . 'assets/css/' . $nom . '.css';
      }
    }

    if ( ! function_exists('img_url'))
    {
      function img_url($nom)
      {
        return  base_url() . 'assets/img/'.$nom;
      }
    }
