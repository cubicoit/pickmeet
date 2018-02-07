<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed (utente)');

/**
 * Evento Class
 *
 * @author	 	Simone Sorio
 * @link		http://www.cubico.it/
 */


class Evento extends ActiveRecord\Model {

    static $table_name = 'eventi';
    static $primary_key = 'id';
    public  static $arrayEsclusione = array("cgi", "id", "prog_id", "galleria", "prog_autore_id", "prog_contatore_visite", "prog_slug", "created_at", "updated_at", "prog_stato", "prog_immagine_copertina_id",
        "descrizione_1", "descrizione_2", "descrizione_3", "descrizione_4", "descrizione_5", "descrizione_6", "descrizione_7", "descrizione_8", "descrizione_9", "descrizione_10");
    public static $arrayEsclusione_form = array(); // campi extra da non utilizzare nella creazione del form ma necessari e inserimento e modifica
    //public $table_prefix = "prog_";

    static $belongs_to = array(
        //array('edificio', 'class_name' => 'Edifici', 'foreign_key' => 'prog_edificio_id')

    );

    static $has_many = array(
        //array('immagini_soluzione', 'class_name' => 'Immagini_soluzioni', 'foreign_key' => 'rkm0_soluzione_id')
        array('chatter', 'class_name' => 'Chatter',  'primary_key' => 'id', 'foreign_key' => 'id_evento')


    );

    static $has_one = array(
        //array('articolo2image', 'class_name' => 'Articolo2image', 'primary_key' => 'id', 'foreign_key' => 'id_articolo')
        //array('immagine_copertina', 'class_name' => 'Immagini_soluzioni', 'primary_key' => 'prog_immagine_copertina_id', 'foreign_key' => 'id'),
        //array('stato', 'class_name' => 'Stati_soluzioni', 'primary_key' => 'prog_stato', 'foreign_key' => 'solst_id')
    );

    /* IMPORTANTE PER AUTOFORM!*/
    function _arrayEsclusione() {
        return Evento::$arrayEsclusione;
    }

   /* static $has_one = array(
        array('utente', 'class_name' => 'Utente', 'foreign_key' => 'email', 'primary_key'=>'rkm0_eventi_email')
    );*/
}

