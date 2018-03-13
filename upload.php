<?php
$iUploadMaxFilesize = ini_get('upload_max_filesize');
$iMaxFileUploads = ini_get('max_file_uploads');

$aErrorTypes = [
	1 => 'Die hochgeladene Datei überschreitet die in der Anweisung upload_max_filesize in php.ini festgelegte Größe.',
	2 => 'Die hochgeladene Datei überschreitet die in dem HTML Formular mittels der Anweisung MAX_FILE_SIZE angegebene maximale Dateigröße. ',
	3 => 'Die Datei wurde nur teilweise hochgeladen.',
	4 => 'Es wurde keine Datei hochgeladen.',
	6 => 'Fehlender temporärer Ordner.',
	7 => 'Speichern der Datei auf die Festplatte ist fehlgeschlagen.',
	8 => 'Eine PHP Erweiterung hat den Upload der Datei gestoppt. PHP bietet keine Möglichkeit an, um festzustellen welche Erweiterung das Hochladen der Datei gestoppt hat.'
];

if (isset($_FILES['mmnfiles'])) {
	$aSuccess = [];
	$iCount = count($_FILES['mmnfiles']['name']);
	
	for ($i = 0; $i < $iCount; $i++) {
		if ($_FILES['mmnfiles']['error'][$i] > 0) {
			outputJSON('Es ist ein Fehler beim Upload der Datei "' . $_FILES['mmnfiles']['name'][$i] . '" aufgetreten: ' . $aErrorTypes[$_FILES['vs-file']['error'][$i]] );
		}
		
		if (!getimagesize($_FILES['mmnfiles']['tmp_name'][$i])) {
			outputJSON('Die angegebene Datei "' . $_FILES['mmnfiles']['name'][$i] . '" ist kein Bildformat.');
		}
		
		$aAllowedMimeTypes = [ 'image/jpeg', 'image/png' ];
		if (!in_array($_FILES['mmnfiles']['type'][$i], $aAllowedMimeTypes)) {
			outputJSON('Der Dateityp der Datei "' . $_FILES['mmnfiles']['name'][$i] . '" wird nicht unterstützt: ' . $_FILES['mmnfile']['type'][$i]);
		}
		
		if ($_FILES['mmnfiles']['size'][$i] > 1000000) {
			outputJSON('Die Datei "' . $_FILES['mmnfiles']['name'][$i] . '" ist zu groß. Bitte wählen Sie eine kleinere Datei.');
		}
		
		$sFilename = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $_FILES['mmnfiles']['name'][$i]);
		$sFilename = mb_ereg_replace("([\.]{2,})", '', $sFilename);
		$sFilename = md5(microtime()) . '-' . strtolower($sFilename);
		
		if (file_exists(__DIR__ . '/uploads/' . $sFilename)) {
			outputJSON('Eine Datei mit dem Namen "' . $_FILES['mmnfiles']['name'][$i] . '" existiert bereits auf dem Server.');
		}
		
		if (!move_uploaded_file($_FILES['mmnfiles']['tmp_name'][$i], __DIR__ . '/uploads/' . $sFilename)) {
			outputJSON('Whoooot? Das Zielverzeichnis ist nicht beschreibbar.');
		}
		
		$aSuccess[] = $i;
	}
	
	outputJSON(
		'Unfassbare ' . count($aSuccess) . ' von ' . $iCount . ' Bildern wurden auf den Server geladen.',
		'success',
		$aSuccess
	);
}

/**
 * Liefert einen JSON String als Fehler- oder Erfolgsnachricht
 *
 * @param string $sMessage
 * @param string $sStatus
 */
function outputJSON($sMessage, $sStatus = 'error', $aIndex = null) {
	header('Content-Type: application/json');
	$sResponse = json_encode([
		'message' => $sMessage,
		'type' => $sStatus,
		'index' => $aIndex
	]);
	die($sResponse);
}
