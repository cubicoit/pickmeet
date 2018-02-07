<?php
/**
 *
 * @package   corteciva
 * @author    Simone Sorio
 * @link    http://www.cubico.it
 * @since   Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Factory Class per Soluzioni
 *
 * Classe che permette la creazione e gestione dei record
 *
 * @subpackage  Libraries
 * @category  Libraries
 */
class Record_factory {


    function __construct() {

    }

    public static function Create($table_name = "", $input = FALSE ) {

        // Determina quale istanza creare per la tabella
        $instance = Record_factory::_getInstance($table_name);

        if ( $input !== FALSE ) {

            $data_item = array();

            foreach ($input as $name => $value)
            {
                if (!in_array($name, $instance->_arrayEsclusione()))
                {
                    $data_item[$name] = $value;
                }
            }
            // $data_item = $input->post();
            $instance = Record_factory::_getInstance($table_name, $data_item);
            $instance->save();

            return $instance;
        } else {
            return $instance;
        }
    }

    public static function Update($table_name = "", $input = false, $id = false) {

        // Determina quale istanza creare per la tabella
        $instance = Record_factory::_getInstance($table_name);

        if ( $input !== FALSE && $id != false ) {
            $data_item = array();

            foreach ($input as $name => $value)
            {
                if (!in_array($name, $instance->_arrayEsclusione()))
                {
                    $data_item[$name] = $value;
                }
            }

            $record = $instance::find($id);
            if ($record->update_attributes($data_item)) {
                return $record;
            }
        }
        return false;
    }

    public static function Delete($table_name = "", $id = false) {

        $instance = Record_factory::_getInstance($table_name);


        if ( $id != false ) {
            $record = $instance::find($id);


            if (            $record->delete()) {
                return true;
            }

        }
        return false;
    }

    public static function _getInstance($table_name, $data_item = array())
    {
        $instance = false;
        switch ($table_name)
        {
            case "int18":
                $instance = new Int18($data_item);
                break;

            case "pages":
                $instance = new Page($data_item);
                break;

            case "news":
                $instance = new News($data_item);
                break;

            case "images":
                $instance = new Image($data_item);
                break;

            case Progetto::$table_name:
                $instance = new Progetto($data_item);
                break;

            case Immagine::$table_name:
                $instance = new Immagine($data_item);
                break;

            case Chatter::$table_name:
                $instance = new Chatter($data_item);
                break;

            case Evento::$table_name:
                $instance = new Evento($data_item);
                break;

            case Utente::$table_name:
                $instance = new Utente($data_item);
                break;
            case Messaggio::$table_name:
                $instance = new Messaggio($data_item);
                break;


        }
        return $instance;
    }
}