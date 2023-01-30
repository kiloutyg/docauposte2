<?php

class Moules_presses extends CI_Model {

    public $tab_moule_presse = array();

    public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
                $this->load->database();
                $this->load->model('moule_presse');
        }

      public function save(){
        foreach ($this->tab_moule_presse as $key => $value) {
          $value->save();
        }
      }

        public function getAllByNumMoule($num){
          $query = $this->db->query("SELECT mp.ID_MOULE_PRESSE, mp.ID_MOULE_INJECTION,mp.ID_PRESSE FROM  moule_injection m,moule_presse mp WHERE m.NUM_MOULE_INJECTION like '{$num}' AND m.ID_MOULE_INJECTION = mp.ID_MOULE_INJECTION");
          if ($query->num_rows() > 0){
             foreach ($query->result() as $row){
               $mp = new Moule_presse();
               $mp->init($row->ID_MOULE_PRESSE,$row->ID_MOULE_INJECTION,$row->ID_PRESSE);
              $this->tab_moule_presse[$row->ID_MOULE_PRESSE]=$mp;
             }
          }
          return $this->tab_moule_presse;
        }

        public function add($moule_presse){
          array_push($this->tab_moule_presse,$moule_presse);
        }

        public function delete(){
          foreach ($this->tab_moule_presse as $key => $value) {
            $value->delete();
          }
        }
}
