<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed (utente)');

/**
 * Articolo Class
 *
 * @author	 	Simone Sorio
 * @link		http://www.cubico.it/
 */


class Immagine extends ActiveRecord\Model {

    static $table_name = 'immagini';
    static $primary_key = 'id';
    public  static $arrayEsclusione = array("cgi", "id", "prog_id", "galleria", "prog_autore_id", "prog_contatore_visite", "prog_slug", "created_at", "updated_at", "prog_stato", "prog_immagine_copertina_id",
        "descrizione_1", "descrizione_2", "descrizione_3", "descrizione_4", "descrizione_5", "descrizione_6", "descrizione_7", "descrizione_8", "descrizione_9", "descrizione_10");
    public static $arrayEsclusione_form = array(
        "utente_stato", "utente_tipo", "utente_admin", "utente_ultimo_ip", "utente_accessi", "utente_conferma_registrazione",
        "utente_societa", "utente_partitaiva", "utente_codicefiscale", "utente_nazione", "utente_eta", "utente_data_creazione", "utente_data_lastupdate", "utente_fax", "utente_cellulare",
        "utente_id", "utente_cliente_id", "prog_edificio_id", "prog_titolo_en", "prog_testo_en", "utente_password", "utente_newsletter", "permessi_id"); // campi extra da non utilizzare nella creazione del form ma necessari e inserimento e modifica
    //public $table_prefix = "prog_";

    static $belongs_to = array(
        //array('edificio', 'class_name' => 'Edifici', 'foreign_key' => 'prog_edificio_id')

    );

    static $has_many = array(
        //array('immagini_soluzione', 'class_name' => 'Immagini_soluzioni', 'foreign_key' => 'rkm0_soluzione_id')
        //array('immagini', 'class_name' => 'image', 'through' => 'articolo2image', 'foreign_key' => 'id_articolo')


    );

    static $has_one = array(
        array('messaggio', 'class_name' => 'Messaggio', 'primary_key' => 'id_messaggio', 'foreign_key' => 'id')
        //array('immagine_copertina', 'class_name' => 'Immagini_soluzioni', 'primary_key' => 'prog_immagine_copertina_id', 'foreign_key' => 'id'),
        //array('stato', 'class_name' => 'Stati_soluzioni', 'primary_key' => 'prog_stato', 'foreign_key' => 'solst_id')
    );

    /* IMPORTANTE PER AUTOFORM!*/
    function _arrayEsclusione() {
        return Immagine::$arrayEsclusione;
    }

   /* static $has_one = array(
        array('utente', 'class_name' => 'Utente', 'foreign_key' => 'email', 'primary_key'=>'rkm0_utenti_email')
    );*/
}


/* End of file articoli.php */
/* Location: ./models/articoli.php */