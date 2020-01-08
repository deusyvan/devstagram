<?php 
require 'environment.php';

global $config;
$config = array();

if(ENVIRONMENT == 'development'){
    
    define("BASE_URL", "http://localhost/devstagram/");
    $config['dbname'] = 'devstagram_api';
    $config['host'] = 'localhost';
    $config['dbuser'] = 'dfsweb';
    $config['dbpass'] = '28033011';
    $config['jwt_secret_key'] = "abC123!";
} else {
    
    define("BASE_URL", "http://meusite.com.br/devstagram");
    $config['dbname'] = 'devstagram_api';
    $config['host'] = 'localhost';
    $config['dbuser'] = 'dfsweb';
    $config['dbpass'] = '28033011';
    $config['jwt_secret_key'] = "abC123!";
}

global $db;
try {
    
    $db = new PDO("mysql:dbname=".$config['dbname'].";host=".$config['host'], $config['dbuser'], $config['dbpass']);
    
} catch ( PDOException $e) {
    echo "ERRO: ".$e->getMessage();
    exit;
}

?>