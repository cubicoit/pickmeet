<?php

require 'constants.php';




if (isset($_SERVER)) {
	//echo var_dump($_FILES);
	//echo var_dump($_POST);

	//Distingui
	
	$tipo = isset($_SERVER["CONTENT_TYPE"]) ? $_SERVER["CONTENT_TYPE"] : "img";
	
	switch ($tipo) {
		case "img":
		case "img/jpg":
		case "img/png":
		case "img/jpeg":
		default:
			$upload_folder = IMG_UPLOAD_FOLDER;
			$eventi_folder = IMG_EVENTI_FOLDER;
			$slug = "img";
			break;

		case "audio":
		case "audio/wav":
		case "audio/mp3":
			$upload_folder = AUDIO_UPLOAD_FOLDER;
			$eventi_folder = AUDIO_PROGETTI_FOLDER;
			$slug = "audio";
			break;
	}
} else {
	die("error - no 4_SERVER defined");
}

//die(var_dump(($_SERVER["CONTENT_TYPE"] . " + tipo: " . $tipo)));

$fn = (isset($_SERVER['HTTP_X_FILENAME']) ? $_SERVER['HTTP_X_FILENAME'] : false);

if ($fn) {

	// AJAX call
	file_put_contents(
		$upload_folder . $fn,
		file_get_contents('php://input')
	);
	//echo "$fn uploaded";
	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	$extension = pathinfo($fn, PATHINFO_EXTENSION);

	$counter = 0;


	$final_file_name = $counter++.'-'.$slug.'.'.$extension;

	while (file_exists($eventi_folder.$final_file_name))
	{ // trova un nome univoco

		$final_file_name = $counter++.'-'.$slug.'.'.$extension;
	}



	if (file_exists($eventi_folder.$fn)) {
		rename($upload_folder.$fn, $upload_folder.$final_file_name);
	}
	if (!copy($upload_folder.$fn, $eventi_folder."$final_file_name")) {
		die("cannot copy file!");
		return false;

	};
	//die("file copiato in " . $eventi_folder."$final_file_name");
	if (!unlink($upload_folder.$fn)) {
		die("impossibile cancellare il file temporaneo");
	}

	echo $final_file_name;

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	exit();

}
else {


	echo "no HTTP_X_FILENAME";

	// form submit
	//$files = $_FILES['fileselect'];
	$files = $_FILES;

	/*foreach ($files['error'] as $id => $err) {
		if ($err == UPLOAD_ERR_OK) {
			$fn = $files['name'][$id];
			move_uploaded_file(
				$files['tmp_name'][$id],
				'uploads/' . $fn
			);
			echo "<p>File $fn uploaded.</p>";
		}
	}*/

}