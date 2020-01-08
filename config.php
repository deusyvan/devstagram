<?php 
require 'environment.php';

$config = array();

if(ENVIRONMENT == 'development'){
    
    define("BASE_URL", "http://localhost/estrutura_mvc/");
    $config['dbname'] = 'estrutura_mvc';
    $config['host'] = 'localhost';
    $config['dbuser'] = 'dfsweb';
    $config['dbpass'] = '28033011';
    
} else {
    
    define("BASE_URL", "http://meusite.com.br/");
    $config['dbname'] = 'estrutura_mvc';
    $config['host'] = 'localhost';
    $config['dbuser'] = 'dfsweb';
    $config['dbpass'] = '28033011';
}

global $db;
try {
    
    $db = new PDO("mysql:dbname=".$config['dbname'].";host=".$config['host'], $config['dbuser'], $config['dbpass']);
    
} catch ( PDOException $e) {
    echo "ERRO: ".$e->getMessage();
    exit;
}

?>