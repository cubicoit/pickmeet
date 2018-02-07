<?php


// PHP Activerecord
require 'vendor/php-activerecord/ActiveRecord.php';
ActiveRecord\Config::initialize(function($cfg) {

    $app = \Slim\Slim::getInstance();

    $db_username = $app->config("username");
    $db_password = $app->config("db_password");
    $db_dbname = $app->config("db_dbname");;
    $db_host = $app->config("db_host");;

    $cfg->set_model_directory('models');
    $cfg->set_connections(array(
        //'development' => "mysql://username:password@localhost/database_name"
        'development' => "mysql://$db_username:$db_password@$db_host/$db_dbname",
        'production' => "mysql://$db_username:$db_password@$db_host/$db_dbname"
    ));
});


// Record factory
require 'libs/record_factory.php';

function getConnection() {
    $app = \Slim\Slim::getInstance();

    $db_username = $app->config("username");
    $db_password = $app->config("db_password");
    $db_dbname = $app->config("db_dbname");;
    $db_host = $app->config("db_host");;

    try {

        $conn = new PDO('mysql:host='.$db_host.';dbname=' . $db_dbname, $db_username, $db_password,
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    } catch(PDOException $e) {
        echo 'ERROR: ' . $e->getMessage();
    }
    return $conn;
}

#region: Response functions

$returnOk = function ($data = true) use ($app) {
    $res = $app->response();
    $res['Content-Type'] = 'application/json';

    /*$res->body(json_encode(array(
    'error'	=>	null,
    'result'	=>$data
    )));	*/
    $app->halt(200, json_encode(array(
        'error' => null,
        'result' => $data,
    )));
};

$returnError = function ($code, $description) use ($app) {
    $res = $app->response();
    $res['Content-Type'] = 'application/json';
    /*$res->body(json_encode(array(
    'code' => $code,
    'error'	=>	$description,
    )));	*/
    $app->halt(200, json_encode(array(
        'code' => $code,
        'error' => $description,
    )));
};

#endregion



#region: User Auth
$getUserID = function () use ($app) {
    $token = $app->request->params('token');
    if (!$token) {
        return null;
    }
    //$db = new DBConnection();
    $dbCon = getConnection();

    // Check existing email
    $query = '
	select * from tokens where token = "' . $token .'"';
    $params = array(
        ':token' => $token,
    );
    $res = $dbCon->query($query);
    $row = $res->fetch();
    if (!$row) {
        return null;
    }
    return $row["utente_id"];
};

function getUserID() {
    global $app;
    $token = $app->request->params('token');
    if (!$token) {
        return null;
    } else {
        return $token;
    }
}

$checkUser = function () use ($app, $getUserID, $returnError) {

    $userID = $getUserID();
    if (!$userID) {
        return $returnError(401, 'Not authorized');
    }
    return $userID;
};

// Login
$app->post('/login', function () use ($returnOk, $returnError) {


    $app = \Slim\Slim::getInstance();

    $res = $app->response();

    $data = json_decode($app->request->getBody()) ?: $app->request->params();

    //$emailValue = $app->request->params('email'); // questo non funziona...
    //$emailValue2 = $data->email;
    $loginValue = $data->login;
    //$fruttoValue = $data->frutto;
    $passwordValue = $data->password;

    //die("email: " .  $emailValue2 .  " - pwd: " . $passwordValue);
    // Login means with an existing user
    //$db = new DBConnection();
    $db = getConnection();

    // Check existing email
    $query = 'select * from utenti where email = "' . $loginValue . '"' ;
    //select * from utenti where utente_email = "' . $emailValue2 . '" AND  utente_conferma_registrazione = 1';

    $params = array(
        ':login' => $loginValue,
    );
    $res = $db->query($query);
    $row = $res->fetch();
    if (!$row) {
        //return $returnError(400, 'User not found or wrong login: ' );
        return $returnError(400, 'Errore di login - ' . $query );
    } else {

        //if (!password_verify($passwordValue, $row["utente_password"])) {
        if ($passwordValue != $row["password"]) {
            return $returnError(400, 'Password errata');
        } else {
            // return $returnOk($row);
        }
    }

    $token = generateToken($row);

    //$query = 'DELETE FROM tokens WHERE id IN (select id from (SELECT id FROM tokens WHERE utente_id = "' . $row['utente_id'] . '" ORDER BY created_at DESC LIMIT 5,100000))';
    //$query = 'DELETE FROM tokens WHERE id IN (SELECT id FROM tokens WHERE utente_id = "' . $row['utente_id'] . '" ORDER BY created_at DESC )';

    //$db->query($query);

    //$user = new UserModel();
    //Record_factory::Update(Utente::$table_name, array("utente_data_ultimoaccesso" => date('Y-m-d H:i:s')), $row['utente_id']);

    //$user = Utente::find($row['utente_id']);
    //die("SONKI5 " . $row['utente_id']);
    $ret = array(
        'token' => $token,
        'utente_id' => $row['id'],
        'utente' => $row
    );


    return $returnOk($ret);
    //return $returnOk($ret);

});

function generateToken($row_utente, $_token = false, $isChatter = false) {
    $row = $row_utente;

    $db = getConnection();

    // Create or update client if not existing
    // Generate token
    $token = null;

    $tablename = "tokens";
    $keyname = "utente_id";
    if ($isChatter) {
        $tablename = "tokenschat";
        $keyname = "chatter_id";
    }

    /*if ($row_utente["utente_origine"] != 1 && $_token) {
        // utente google o facebook
        $token = $_token;
    }*/

    while (!$token) {
        $t = sha1(microtime(true) . '_Fj430jgnb' . rand(1, 100000));
        $query = '
		select * from ' . $tablename . ' where token = "' . $t . '"';

        $res = $db->query($query);
        $row2 = $res->fetch();
        if (!$row2) {
            $token = $t;
        }
    }
    //

    $query = 'DELETE FROM ' . $tablename . ' WHERE '. $keyname . ' = "' . $row['id'] . '" ';
    $db->query($query);

    $query = '
	INSERT INTO '.$tablename.' (
		'.$keyname.',
		token,
		created_at
	)
	VALUES (
		"' .  $row['id'] . '",
		"' . $token . '",
		UTC_TIMESTAMP()
	)';

    $db->query($query);

    return $token;
}

#endregion

#region: utente
$app->get('/utenti', 'getUtenti'   ); // Using Get HTTP Method and process getUsers function


function getUtenti() {
    echo "getutenti";
    die();
    $app = \Slim\Slim::getInstance();


    header("Content-Type: application/json");
    try {

        $result = Utente::find('all');

        $newUtenti = array();
        foreach ($result as $utente) {

            $utenteArray = $utente->to_array();

            $newUtenti[] = $utenteArray;
        }

        $dbCon = null;
        echo '{"vini": ' . json_encode($newUtenti) . '}';
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}


$app->get('/utenti2/', function () use ($app, $returnOk, $returnError) {
    die();
    //$result = Banchetti::find('all');
    //$data['banchetti'] = $result->to_json();
    //$app->render('banchetti2/index.php', $data);
    //$app->render('banchetti2/index.php');
    //echo json_encode($data);
    //echo var_dump($data);


    $result = array();
    $results = Utente::find('all');

    foreach($results as $item) {
        array_push($result, $item->to_array()); //using to_array instead of to_json // # PER FAR FUNZIONARE TO_aRRAY SONO STATI INSERITI DEI PEZZI DI CODICE IN LIB/SERIALIZATION E LIB MODEL
    }

    echo json_encode($result);
    //return $returnOk($result);

})->name('utenti2');

$app->get('/get-profile', function () use ($app, $returnOk, $returnError, $checkUser) {
    //$db = new DBConnection();
    $db = getConnection();
    $data = json_decode($app->request->getBody()) ?: $app->request->params();
    //die(var_dump($data));
    $userID = $checkUser();
    //$id = $app->request->get('id', null);

    //$id = $data->utente_id;

    //$u = new UserModel();
    if (isset($data["utente_id"]) && !$userID) {
        //$user = $u->getUser($id);
        $user = Utente::find($data["utente_id"]);
        // unset($user->phone);

// cerca per token
        if (isset($data["token"])) {

        }

    } else {
        /*
                try {
                    $user = Utente::find($userID);
                } catch(PDOException $e) {
                    echo 'ERROR: ' . $e->getMessage();
                }*/

        // Check existing email
        $query = '
	    select * from utenti where id = "' . $userID .'"';

        $res = $db->query($query);
        $row = $res->fetch();
        if (!$row) {
            return null;
        }
        $user = $row;

    }
    return $returnOk(array('user' => $user));
});

// Insert utente
$app->post('/utente/',  function() use($app, $returnOk, $returnError) {

    //$utente = json_decode($app->request()->getBody());
    $utente = json_decode($app->request()->getBody());
    //die(var_dump($utente) .  " - " . $utente->nome);
    //die("utente banana: " . $utente->{'utente'});
    //$array_item["utente_password"] = $this->bcrypt->hash($this->input->post('utente_password'));

    //$bcrypt = new Bcrypt();

    $array_item = array(
        "nome" => $utente->nome,
        //"utente_cognome" => $utente->cognome,
        "email" => $utente->email,
        //"utente_password" => crypt($utente->password, "salter"),
        //"utente_citta" => $utente->citta,
        //"utente_nazione" => $utente->nazione,
        //"utente_origine" => $utente->origine,
        //"utente_data_creazione" => date('Y-m-d H:i:s')
    );

    if (isset($utente->cognome)) { $array_item["cognome"] = $utente->cognome; }
    if (isset($utente->password)) { $array_item["password"] = crypt($utente->password, "salter"); }
    //if (isset($utente->citta)) { $array_item["utente_citta"] = $utente->citta; }
    //if (isset($utente->nazione)) { $array_item["utente_nazione"] = $utente->nazione; }
    //if (isset($utente->note)) { $array_item["utente_note"] = $utente->note; }
    $token = false;
    if (isset($utente->token)) { $token = $utente->token; } // token ricevuto direttamente da google o facebook

    $utentedb = Utente::find("all", array('conditions' => array('email = ?', $utente->email)));

    if ($utentedb) { // esiste già un utente con questa email!
        return $returnError(400, 'Email già esistente');
    }

    if ($record = Record_factory::Create(Utente::$table_name, $array_item))
    {

        $token = generateToken($record->to_array(), $token);
        $utente->token = $token;

        echo json_encode($utente);
    } else {
        $returnError;
    }




});

// Invia email per attivazione account
//$app->get('/mailutente', 'inviaEmailUtente'   ); // Using Get HTTP Method and process getUsers function

$app->post('/mailutente/',  function() use($app, $returnOk, $returnError) {
    $utente = json_decode($app->request()->getBody());

    //bool mail ( string $to , string $subject , string $message [, string $additional_headers [, string $additional_parameters ]] )

    $nl = "\r\n";
    $email = $utente->email;
    $subject = "Hostaria App - conferma la tua registrazione";
    $URL_API = $app->config("URL_API");
    $url = $URL_API . "/attivautente?e=" . base64_encode($email);
    $link = sprintf(
        '<a href="%s">%s</a>',
        $url,
        $url
    );
    $message = "Abbiamo ricevuto una richiesta di registrazione all'App di Hostariaverona.com. $nl $nl Per validare l'indirizzo email utilizzato, clicca sul seguente link:$nl $url $nl $nl Grazie!";
    $mittente = "app@hostariaverona.com";
    $headers = 'From: ' . $mittente . "\r\n" .
        'Reply-To: ' . $mittente . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    $invioMail = mail($email, $subject, $message, $headers);

    if ($invioMail) {
        $risposta = array("msg" => "Email inviata correttamente");
     echo json_encode($risposta);
    } else {
        $returnError;
    }

});

// dopo il click del link via email l'utente va attivato
$app->get('/attivautente', 'attivaUtente'   ); // Using Get HTTP Method and process getUsers function

function attivaUtente()
{
    $app = \Slim\Slim::getInstance();
    $e = $app->request->get('e');

    $email = base64_decode($e);

    $utente = Utente::find("all", array('conditions' => array('utente_email = ?',  $email)));
    //header("Content-Type: application/json");
    if ($utente) {
        $utente_obj = Utente::find($utente[0]->utente_id);
        if ($utente_obj) {
            $utente_obj->utente_conferma_registrazione = 1;
            $utente_obj->save();

            echo sprintf(
                "<h2>%s</h2><p>%s</p>",
                "Hostaria Verona",
                "Grazie per aver confermato la tua email!"
            );
        }
        return;
    }

    echo sprintf(
        "<h2>%s</h2><p>%s</p>",
        "Hostaria Verona",
        "Benvenuto sul nostro sito"
    );
}



function invia_messaggio_email($tipo, $dati) {
    $array_output = array();

    $nl = "\r\n";
    $mittente = "info@hostariaverona.com";

    switch ($tipo) {
        case "invio_password":
            $email = $dati["email"];
            $id_cantina = $dati["id"];
            $password = $dati["password"];

            $subject = "Hostariaverona - i tuoi dati di accesso";

            $url = "http://www.hostariaverona.com/login-cantina/";
            $link = sprintf(
                '<a href="%s">%s</a>',
                $url,
                $url
            );
            $message = "Ecco i tuoi dati di accesso al sito Hostariaverona.com. $nl $nl Accedi dal seguente link:$nl $url $nl $nl Con questi dati: $nl $nl
             login: $email $nl
             password: $password $nl
             $nl Grazie!";

            break;
    }

    $headers = 'From: ' . $mittente . "\r\n" .
        'Reply-To: ' . $mittente . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    $invioMail = mail($email, $subject, $message, $headers);
    if ($invioMail) {
        $array_output["messaggio"] = "Email inviata correttamente";
    } else {
        $array_output["error"] = "Errore invio email";
    }

    return $array_output;
}

function randomPassword() {
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

#endregion

#region: Progetti

// Insert progetto
$app->post('/progetto',  function() use($app, $returnOk, $returnError) {

    $progetto = json_decode($app->request()->getBody());

    //$sql = "INSERT INTO User VALUES ( NULL , '$user->email' , '$user->name' , '$user->surname' , '$user->sex' , $user->years)";

    //$db->exec($sql);

    //$user->id = $db->lastInsertRowID();

    if (isset($progetto->id)) {
        unset($progetto->immagini);
        unset($progetto->autore);
        //update
        Record_factory::Update(Progetto::$table_name, $progetto, $progetto->id);
    } else {
        if ($record = Record_factory::Create(Progetto::$table_name, $progetto))
        {
            $testo = "Record creato correttamente con id " . $record->id;
            echo json_encode($record->to_array());
        } else {
            $returnError;
        }
    }





});

$app->get('/progetti/', 'getProgetti'   ); // Using Get HTTP Method and process getUsers function

function getProgetti() {
    $app = \Slim\Slim::getInstance();

    $data = json_decode($app->request->getBody()) ?: $app->request->params();

    $sql_query = "select * FROM progetti ";

    if ($data) {
        $sql_query .= " WHERE id_proprietario = '" . $data["id"] . "'";
    } else {

    }

    //order
    $sql_query .= " ORDER BY id";



    header("Content-Type: application/json");
    try {
        $dbCon = getConnection();
        $stmt   = $dbCon->query($sql_query);
        $users  = $stmt->fetchAll(PDO::FETCH_OBJ);
        $progetti = Progetto::find('all');

        if ($data) {
            $progetti = Progetto::find('all', array('conditions' => "id_proprietario = '" . $data["id"] ."'"));
        } else {
            $progetti = Progetto::find('all');
        }

        //$progetti = Progetto::find('all');

        //$result = Vino::find('all');

        $newprogetti = array();
        $progettiarray = array();
        foreach ($progetti as $progetto) {

            $progettiarray = $progetto->to_array();



            $immagini = $progetto->immagini;

            if ($immagini) {
                $newimmagini = array();
                foreach ($immagini as $immagine) {

                    //die(var_dump($immaginiArray));
                    $newimmagini[] = $immagine->to_array();
                }
                //$progettiarray["immagini"] = $newimmagini;
            }
            $newprogetti[] = $progettiarray;
        }




        $dbCon = null;
        echo '{"progetti": ' . json_encode($newprogetti) . '}';
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

$app->get('/progetto/', 'getProgetto'   ); // Using Get HTTP Method and process getUsers function

function getProgetto() {
    $app = \Slim\Slim::getInstance();
    $id = $app->request->get('id');
    //$sql_query = "select * FROM progetti WHERE id = $id";
    header("Content-Type: application/json");
    try {
        //$dbCon = getConnection();
        //$stmt   = $dbCon->query($sql_query);
        //$progetto  = $stmt->fetchAll(PDO::FETCH_OBJ);

        $progetto = Progetto::find($id);

        $progettiarray = $progetto->to_array();


        $immagini = $progetto->immagini;
        $autore = $progetto->autore;


        if ($immagini) {
            $newimmagini = array();
            foreach ($immagini as $immagine) {

                //die(var_dump($immaginiArray));
                $newimmagini[] = $immagine->to_array();
            }

            $newimmagini = array_sort($newimmagini, "ordine", SORT_ASC);

            $progettiarray["immagini"] = $newimmagini;
        }
        //$newprogetti[] = $progettiarray;

        $progettiarray["autore"] = $autore->to_array();
        unset($progettiarray["autore"]["password"]);
        unset($progettiarray["autore"]["login"]);

        //die(var_dump($progettiarray));

        $dbCon = null;
        //echo  json_encode($progetto);
        echo '{"progetto": ' . json_encode($progettiarray, JSON_UNESCAPED_UNICODE) . '}';
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

$app->post('/progetto/delete',  function() use($app, $returnOk, $returnError) {
    $progetto1 = json_decode($app->request()->getBody());

    $progetto = $progetto1->progetto;

    //die(var_dump($progetto));

    if (Record_factory::Delete(Progetto::$table_name, $progetto->id))
    {
        $testo = "Record eliminato correttamente con id " .  $progetto->id;
        echo json_encode($testo);
    } else {
        $returnError;
    }
});

#endregion

#region: immagini

$app->post('/immagine2', function () use ($app, $returnOk, $returnError) {
    //$userID = $checkUser();
    $db = new DBConnection();
    $filename = basename($_FILES['file']['name']);
    if(isset($_REQUEST['filename'])){
        $filename = basename($_REQUEST['filename']);
    }
    if (substr($filename, -4) == '.jpg' || substr($filename, -5) == '.jpeg' || substr($filename, -4) == '.png') {
        $fname = time() . rand(1, 1000) . $filename;
        //$destination = PATH_UPLOAD_MEDIA . 'img/avatars/' . $fname;
        $destination = IMG_UPLOAD_FOLDER . $fname;
        move_uploaded_file($_FILES['file']['tmp_name'], $destination);
        /*$query = '
		UPDATE users SET avatar = :avatar WHERE id = :id';
        $params = array(
            ':avatar' => $fname,
            ':id' => $userID,
        );
        $db->query($query, $params);
        $img = new abeautifulsite\SimpleImage($destination);
        $img->best_fit(100, 100)->save(PATH_UPLOAD_MEDIA . 'img/avatars/s_' . $fname);
        return $returnOk(array('avatar' => $fname));
        */
    }
    $returnError(500, 'Error');
});

// Insert immagine
$app->post('/immagine',  function() use($app, $returnOk, $returnError) {

    $immagine = json_decode($app->request()->getBody());

    //$sql = "INSERT INTO User VALUES ( NULL , '$user->email' , '$user->name' , '$user->surname' , '$user->sex' , $user->years)";

    //$db->exec($sql);

    //$user->id = $db->lastInsertRowID();
    if (isset($immagine->id)) {
        //update
        Record_factory::Update(Immagine::$table_name, $immagine, $immagine->id);
    } else {
        if ($record = Record_factory::Create(Immagine::$table_name, $immagine))
        {
            $testo = "Record creato correttamente con id " . $record->id;
            echo json_encode($immagine);
        } else {
            $returnError;
        }
    }





});

// Update immagine
$app->post('/immagine/post',  function() use($app, $returnOk, $returnError) {
    $immagine = json_decode($app->request()->getBody());
    if ($record = Record_factory::Update(Immagine::$table_name, $immagine, $immagine->id))
    {
        $testo = "Record aggiornato correttamente con id " . $record->id;
        echo json_encode($immagine);
    } else {
        $returnError;
    }
});

// Delete immagine
$app->post('/immagine/delete',  function() use($app, $returnOk, $returnError) {
    $immagine = json_decode($app->request()->getBody());
//die(var_dump($immagine));
    //elimina il file immagine
    if (file_exists(IMG_PROGETTI_FOLDER.$immagine->filename)) {
        if (!unlink(IMG_PROGETTI_FOLDER . $immagine->filename)) {
            //die("impossibile cancellare il file immagine");
        }
    }

    if (Record_factory::Delete(Immagine::$table_name, $immagine->id))
    {
        $testo = "Record eliminato correttamente con id " .  $immagine->id;

    } else {
        $returnError;
    }
});

// Delete audio
$app->post('/audio/delete',  function() use($app, $returnOk, $returnError) {
    $audio = json_decode($app->request()->getBody());
//die(var_dump($immagine));
    //elimina il file immagine
    if (!unlink(AUDIO_PROGETTI_FOLDER.$audio->audiofilename)) {
        die("impossibile cancellare il file audio");
    }

    /*if (Record_factory::Delete(Immagine::$table_name, $immagine->id))
    {
        $testo = "Record eliminato correttamente con id " .  $immagine->id;

    } else {
        $returnError;
    }*/
});

#endregion

#region: Evento
//partecipa
$app->post('/partecipa', function () use ($returnOk, $returnError) {


    $app = \Slim\Slim::getInstance();

    $res = $app->response();

    $data = json_decode($app->request->getBody()) ?: $app->request->params();

    //$emailValue = $app->request->params('email'); // questo non funziona...
    //$emailValue2 = $data->email;
    $ideventoValue = $data->idevento;
    //$fruttoValue = $data->frutto;


    //die("id evento: " .  $ideventoValue);
    // Login means with an existing user
    //$db = new DBConnection();
    $db = getConnection();

    // Check existing email
    $query = 'select * from eventi where id = "' . $ideventoValue . '"' ;
    //select * from utenti where utente_email = "' . $emailValue2 . '" AND  utente_conferma_registrazione = 1';


    $res = $db->query($query);
    $row = $res->fetch();
    if (!$row) {
        //return $returnError(400, 'User not found or wrong login: ' );
        return $returnError(400, 'evento ' . $ideventoValue .' non trovato' );
    } else {
        //Dati ricevuti correttamente

        //crea un chatter

        $array_item = array(
            "id_evento" => $ideventoValue,
            "codice" => getFreeChatterCode($ideventoValue)
        );

        if ($record = Record_factory::Create(Chatter::$table_name, $array_item))
        {

            $token = generateToken($record->to_array(), false, true);
            $record->tokenchat = $token;

            $record->save();


            $json_record = array(
                "id"=>$record->id,
                "id_evento"=>$record->id_evento,
                "tokenchat"=>$record->tokenchat
            );

            //echo json_encode($record->to_array());
            //echo json_encode($json_record);
            return $returnOk($record->to_array());
        } else {
            $returnError;
        }
    }

    //$token = generateToken($row);

    //$query = 'DELETE FROM tokens WHERE id IN (select id from (SELECT id FROM tokens WHERE utente_id = "' . $row['utente_id'] . '" ORDER BY created_at DESC LIMIT 5,100000))';
    //$query = 'DELETE FROM tokens WHERE id IN (SELECT id FROM tokens WHERE utente_id = "' . $row['utente_id'] . '" ORDER BY created_at DESC )';

    //$db->query($query);

    //$user = new UserModel();
    //Record_factory::Update(Utente::$table_name, array("utente_data_ultimoaccesso" => date('Y-m-d H:i:s')), $row['utente_id']);

    //$user = Utente::find($row['utente_id']);
    //die("SONKI5 " . $row['utente_id']);

    /*$ret = array(
        'token' => $token,
        'utente_id' => $row['id'],
        'utente' => $row
    );*/


    return $returnOk();
    //return $returnOk($ret);

});

#endregion;

#region: chatter

$getChatterID = function () use ($app) {
    $token = $app->request->params('tokenchat');
    if (!$token) {
        return null;
    }
    //$db = new DBConnection();
    $dbCon = getConnection();

    // Check existing email
    $query = '
	select * from tokenschat where token = "' . $token .'"';
    $params = array(
        ':token' => $token,
    );
    $res = $dbCon->query($query);
    $row = $res->fetch();
    if (!$row) {
        die("no row: $query");
        return null;
    }
    return $row["chatter_id"];
};

function getChatterID() {
    global $app;
    $token = $app->request->params('tokenchat');
    if (!$token) {
        return null;
    } else {
        return $token;
    }
}

$checkChatter = function () use ($app, $getChatterID, $returnError) {

    $chatterID = $getChatterID();
    if (!$chatterID) {
        return $returnError(401, 'Not authorized');
    }
    return $chatterID;
};

$app->get('/chatters2', 'getChatters'   ); // Using Get HTTP Method and process getUsers function


function getChatters() {
    echo "getChatters";
    die();
    $app = \Slim\Slim::getInstance();


    header("Content-Type: application/json");
    try {

        $result = Chatter::find('all');

        $newUtenti = array();
        foreach ($result as $utente) {

            $utenteArray = $utente->to_array();

            $newUtenti[] = $utenteArray;
        }

        $dbCon = null;
        echo '{"vini": ' . json_encode($newUtenti) . '}';
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}


$app->get('/chatters/', function () use ($app, $returnOk, $returnError) {
    //die();
    //$result = Banchetti::find('all');
    //$data['banchetti'] = $result->to_json();
    //$app->render('banchetti2/index.php', $data);
    //$app->render('banchetti2/index.php');
    //echo json_encode($data);
    //echo var_dump($data);
    $data = json_decode($app->request->getBody()) ? : $app->request->params();

    $result = array();
    $results = Chatter::find('all');

    if ($data) {


        if (isset($data["remove_Actives"])) {
            $results_actives = getUniqueChats($data["id_chatter"], $data["id_evento"]);

            if (count($results_actives["unique_chats"]) > 0) {

                $results = Chatter::find('all', array('conditions' => array(
                    "id_evento = '" . $data["id_evento"] . "' AND " . 'id not in (?)', $results_actives["unique_chats"]
                )
                ));
            } else {
                $results = Chatter::find('all');
            }
        } else {
            $results = Chatter::find('all', array('conditions' => "id_evento = '" . $data["id_evento"] ."'"));
        }

    } else {
        $results = Chatter::find('all');
    }

    foreach($results as $item) {
        array_push($result, $item->to_array()); //using to_array instead of to_json // # PER FAR FUNZIONARE TO_aRRAY SONO STATI INSERITI DEI PEZZI DI CODICE IN LIB/SERIALIZATION E LIB MODEL
    }

    echo json_encode($result);
    //return $returnOk($result);

})->name('chatters');

$app->get('/get-chatter-profile', function () use ($app, $returnOk, $returnError, $checkChatter) {
    //$db = new DBConnection();
    $db = getConnection();
    $data = json_decode($app->request->getBody()) ?: $app->request->params();
    //die(var_dump($data));
    $chatterID = $checkChatter();
    //$id = $app->request->get('id', null);

    //$id = $data->utente_id;

    //$u = new UserModel();
    if (
    (isset($data["chatter_id"]) || isset($data["id"]))
        && !$chatterID) {
        //$user = $u->getUser($id);
        $chatter_id = isset($data["chatter_id"])? $data["chatter_id"] : $data["id"];
        $chatter = Chatter::find($chatter_id);
        // unset($user->phone);


    } else {
        /*
                try {
                    $user = Utente::find($userID);
                } catch(PDOException $e) {
                    echo 'ERROR: ' . $e->getMessage();
                }*/

        // Check existing email
        $query = '
	    select * from chatters where id = "' . $chatterID .'"';

        $res = $db->query($query);
        $row = $res->fetch();
        if (!$row) {
            return $returnError(400, 'chatter non trovato');
        }
        $chatter = $row;


    }

    if ($chatter) {
        return $returnOk(array('chatter' => $chatter));
    } else {
        return $returnError(400, 'chatter non trovato');
    }

});

// Insert chatter
$app->post('/chatter/',  function() use($app, $returnOk, $returnError) {

    //$utente = json_decode($app->request()->getBody());
    $chatter = json_decode($app->request()->getBody());
    //die(var_dump($utente) .  " - " . $utente->nome);
    //die("utente banana: " . $utente->{'utente'});
    //$array_item["utente_password"] = $this->bcrypt->hash($this->input->post('utente_password'));

    //$bcrypt = new Bcrypt();

    $array_item = array(
        "nome" => $chatter->nome,
        //"utente_cognome" => $utente->cognome,
        "email" => $chatter->email,
        //"utente_password" => crypt($utente->password, "salter"),
        //"utente_citta" => $utente->citta,
        //"utente_nazione" => $utente->nazione,
        //"utente_origine" => $utente->origine,
        //"utente_data_creazione" => date('Y-m-d H:i:s')
    );

    if (isset($chatter->cognome)) { $array_item["cognome"] = $chatter->cognome; }
    if (isset($chatter->password)) { $array_item["password"] = crypt($chatter->password, "salter"); }
    //if (isset($utente->citta)) { $array_item["utente_citta"] = $utente->citta; }
    //if (isset($utente->nazione)) { $array_item["utente_nazione"] = $utente->nazione; }
    //if (isset($utente->note)) { $array_item["utente_note"] = $utente->note; }
    $token = false;
    if (isset($chatter->token)) { $token = $chatter->token; } // token ricevuto direttamente da google o facebook

    $utentedb = Chatter::find("all", array('conditions' => array('email = ?', $chatter->email)));

    if ($utentedb) { // esiste già un utente con questa email!
        return $returnError(400, 'Email già esistente');
    }

    if ($record = Record_factory::Create(Chatter::$table_name, $array_item))
    {

        $isChatter = true;
        $token = generateToken($record->to_array(), $token, $isChatter);
        $chatter->token = $token;

        echo json_encode($chatter);
    } else {
        $returnError;
    }




});

// Restituisce un codice libero e non utilizzato da nessun altro chatter per un determinato evento.
function getFreeChatterCode($id_evento) {
    $results = Chatter::find('all', array('conditions' => "id_evento = '" . $id_evento ."'"));

    $code = 1;
    $unique = false;


    do {
        $repeat = false;
        foreach ($results as $row) {
            if ($code==$row->codice) {
                $unique = false;
                $code++;
                $repeat = true;
                break;
            }
        }
        if (!$repeat) {
            $unique = true;
        }

    } while (!$unique);

    return $code;
}

#region: debug freecode
$app->get('/freecode/', function () use ($app, $returnOk, $returnError) {
    //$db = new DBConnection();
    $db = getConnection();
    $data = json_decode($app->request->getBody()) ?: $app->request->params();

    if ($data) {
        $id_evento = $data["id_evento"] ?: false;

        if ($id_evento) {
            $code = getFreeChatterCode($id_evento);
            return $returnOk(array('code' => $code));
        }
    }

    return $returnError(400, 'codice già esistente');

})->name('freecode');

$app->get("/debug", function ()  use ($app)  {
    //echo "<h1>Hello Slim World</h1>";
    echo $app->config("personaggio") . "<br>";

    $id_evento = 69;
   $code = getFreeChatterCode($id_evento);

   echo "code: $code";
});

#endregion


$app->get('/activechats/', function () use ($app, $returnOk, $returnError) {
    //$db = new DBConnection();
    $db = getConnection();
    $data = json_decode($app->request->getBody()) ?: $app->request->params();

    if ($data) {
        $id_chatter = $data["id_chatter"] ?: false;
        $id_evento = $data["id_evento"] ?: false;

        if ($id_chatter && $id_evento) {

            //$results = getUniqueChats($id_chatter, $id_evento);

            $results_actives = getUniqueChats($data["id_chatter"], $data["id_evento"]);

            $unique_chats = $results_actives["unique_chats"];

            if (count($unique_chats)>0) {

                $results = Chatter::find('all', array('conditions' => array(
                    "id_evento = '" . $data["id_evento"] . "' AND " . 'id in (?)', $results_actives["unique_chats"]
                )
                ));

                if ($results) {
                    $result = array();

                    foreach ($results as $item) {
                        $chat = $item->to_array();

                        //ottieni i messaggi non letti
                        $unread = getUnreadMessages($chat["id"], $id_chatter, $id_evento);

                        if ($unread) {
                            $chat["unread"] = $unread;
                        }

                        array_push($result, $chat); //using to_array instead of to_json // # PER FAR FUNZIONARE TO_aRRAY SONO STATI INSERITI DEI PEZZI DI CODICE IN LIB/SERIALIZATION E LIB MODEL
                    }

                    echo json_encode($result);
                } else {
                    return $returnError(400, 'no activechats');
                }
            } else {
                return $returnError(400, 'no activechats');
            }




        } else {
            return $returnError(400, 'chatter non esistente');
        }
    } else {
        return $returnError(400, 'dati non forniti');
    }



})->name('activechats');

function getUniqueChats($id_chatter, $id_evento) {
    $chatter = Chatter::find($id_chatter);

    $inviati = $chatter->messaggi_inviati;
    $ricevuti = $chatter->messaggi_ricevuti;

    $inviati_array = array();
    $ricevuti_array = array();

    $unique_chats = array();

    foreach ($inviati as $msg) {
        $inviati_array[] = $msg->to_array();

        if ($msg->id_evento != $id_evento) {
            continue;
        }

        $chat_seed = array($msg->mittente, $msg->destinatario);

        /*
        if (!in_array($chat_seed[0]."|".$chat_seed[1], $unique_chats)
        &&
            !in_array($chat_seed[1]."|".$chat_seed[0], $unique_chats)
        ) {
            $unique_chats[] = $chat_seed[0]."|".$chat_seed[1];
        }
        */
        if (!in_array($msg->destinatario, $unique_chats)) {
            $unique_chats[] = $msg->destinatario;
        }
    }
    foreach ($ricevuti as $msg) {
        $ricevuti_array[] = $msg->to_array();

        if ($msg->id_evento != $id_evento) {
            continue;
        }

        /*
        if (!in_array($chat_seed[0]."|".$chat_seed[1], $unique_chats)
            &&
            !in_array($chat_seed[1]."|".$chat_seed[0], $unique_chats)
        ) {
            $unique_chats[] = $chat_seed[0]."|".$chat_seed[1];
        }*/

        if (!in_array($msg->mittente, $unique_chats)) {
            $unique_chats[] = $msg->mittente;
        }
    }



    $results = array(

        "messaggi_inviati" => $inviati_array,
        "messaggi_ricevuti" => $ricevuti_array,
        "unique_chats" => $unique_chats
    );

    return $results;
}

$app->get('/messages/', function () use ($app, $returnOk, $returnError) {
    //$db = new DBConnection();
    $db = getConnection();
    $data = json_decode($app->request->getBody()) ?: $app->request->params();

    if ($data) {
        $id_mittente = $data["id_mittente"] ?: false;
        $id_destinatario = $data["id_destinatario"] ?: false;
        $id_evento = $data["id_evento"] ?: false;
        $nolimit = isset($data["nolimit"]) ?true: false;

        if ($id_mittente && $id_destinatario  && $id_evento) {

            //$results = getUniqueChats($id_chatter, $id_evento);

            //$results_actives = getUniqueChats($data["id_chatter"], $data["id_evento"]);

            $limit_condition = "";
            if (!$nolimit) {
                //$limit_condition = " ORDER BY 'id' DESC LIMIT 4";
                $limit_condition = 50;
            }

            $results = Messaggio::find('all', array(
                'order' => 'id ASC',
                'limit' => $limit_condition,

                'conditions' => array(
                "( ( mittente = '" . $id_mittente ."' AND " .
                "destinatario = '" . $id_destinatario ."' ) OR " .
                "( destinatario = '" . $id_mittente ."' AND " .
                "mittente = '" . $id_destinatario ."' ) ) AND " .
                "id_evento = '" . $id_evento . "'"
                )
            ));

            $result = array();

            foreach($results as $item) {

                //Aggiorna i messaggi come letti
                if ($item->destinatario==$id_mittente) {
                    $item->letto = 1;

                    if (
                    $item->save()
                    //$record = Record_factory::Update(Messaggio::$table_name, $item, $item->id)
                    ) {
                        //die("record aggiornato con successo: " . var_dump($item->to_array()));
                    } else {
                        //die("record non aggiornato");
                    }
                }

                $msg =  $item->to_array();

                $tempo = $msg["created_at"];
                $msg["time"] = formatMessageTime($tempo);

                array_push($result, $msg); //using to_array instead of to_json // # PER FAR FUNZIONARE TO_aRRAY SONO STATI INSERITI DEI PEZZI DI CODICE IN LIB/SERIALIZATION E LIB MODEL
            }

            echo json_encode($result);

        } else {
            return $returnError(400, 'chatter non esistente');
        }
    } else {
        return $returnError(400, 'dati non forniti');
    }



})->name('messages');

$app->post('/messaggio',  function() use($app, $returnOk, $returnError) {


    $db = getConnection();
    $data = json_decode($app->request->getBody()) ?: $app->request->params();

    if ($data) {
        $mittente = $data->mittente ?: false;
        $destinatario = $data->destinatario ?: false;
        $id_evento = $data->id_evento ?: false;
        $testo = $data->testo ?: false;

        $array_item = array(
            "mittente" => $mittente,
            "destinatario" => $destinatario,
            "id_evento" => $id_evento,
            "testo" => $testo
        );

        if ($record = Record_factory::Create(Messaggio::$table_name, $array_item))
        {
            $msg = $record->to_array();

            $tempo = $msg["created_at"];
            $msg["time"] = formatMessageTime($tempo);

            return $returnOk($msg);
        } else {
            $returnError;
        }

    } else {
        return $returnError(400, 'dati non forniti');
    }





});

// Update messaggio (applica lo stato "letto")
$app->post('/messaggio/update',  function() use($app, $returnOk, $returnError) {
    $messaggio = json_decode($app->request()->getBody());
    if ($record = Record_factory::Update(Messaggio::$table_name, $messaggio, $messaggio->id))
    {
        $testo = "Record aggiornato correttamente con id " . $record->id;
        echo json_encode($messaggio);
    } else {
        $returnError;
    }
});

// formatta il valore di "created_at" del messaggio in versione più breve
function formatMessageTime($tempo) {
    $date = new DateTime($tempo);
   return  $date->format('H:i');
}

// ottieni il numero dei messaggi non letti di una data chat
function getUnreadMessages($id_mittente, $id_destinatario, $id_evento) {
     $results = Messaggio::find('all', array(

        'conditions' => array(
            "mittente = '" . $id_mittente ."' AND " .
            "destinatario = '" . $id_destinatario ."' AND " .
            "id_evento = '" . $id_evento . "' AND " .
            "letto = '0'"
        )
    ));

    if ($results) {
        return count($results);
    }

    return false;
}

#endregion