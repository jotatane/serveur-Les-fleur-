<?php
function ScanDirectory($Directory, $dirCheckUselessFiles = array(), $tableau=false, $ignoreEntry = ['.','..',
	'index.php',".htaccess"], $ignoreDirectory = array()){
$slash = '';
	$MyDirectory = opendir($Directory) or die('Erreur');
	while($Entry = @readdir($MyDirectory)){
		if(!equal($Entry,$ignoreEntry)){
			if(is_dir($Directory.'/'.$Entry)){
				$slash = '/';			
			}
                        else
                        {
                         $slash = '';
                        }
            $elem = substr($Directory.'/'.$Entry, strlen(strstr($Directory.'/'.$Entry, '/', true))+1).$slash;
			$tableau[] = array(
				"path"=>$elem,
				"checksumSHA1"=>is_dir($elem) ? false : sha1_file($elem),
				"url"=>(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].str_replace("index.php","",$_SERVER['REQUEST_URI']).$elem
			);
			if(!contain($Directory,$ignoreDirectory) && is_dir($Directory.'/'.$Entry)){
				$tableau = ScanDirectory($Directory.'/'.$Entry,array(),$tableau,$ignoreEntry,$ignoreDirectory);
			}
		}
	}
	foreach($dirCheckUselessFiles as $v){
        $tableau[] = array(
            "dirCheckUselessFiles"=>$v
        );
    }
	closedir($MyDirectory);
	return $tableau;
}

function contain($file,$array){
	foreach ($array as $value) {
		if(strpos($file,$value) !== false){
			return true;
		}
	}
	return false;
}

function equal($file,$array){
	foreach ($array as $value) {
		if($file == $value){
			return true;
		}
	}
	return false;
}

function contains($files,$array){
	foreach ($files as $value) {
		if(contain($value,$array)){
			return true;
		}
	}
	return false;
}

// Si vous souhaitez faire supprimer des vieux fichiers dans des répertoires prédéfinis c'est par ici
// Ajoutez dans le tableau ci-après le répertoire à faire analyser
$tableau = ScanDirectory('.',["mods","config"]);
$tableau = $tableau == false ? [] : $tableau;
echo json_encode($tableau);
?>