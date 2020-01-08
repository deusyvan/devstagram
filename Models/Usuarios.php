<?php 
namespace Models;

use \Core\Model;
use PDO;

class  Usuarios extends Model{
    public function getAll(){
        $array = array();
        
        $sql = "SELECT * FROM usuarios";
        $sql = $this->db->query($sql);
        
        if($sql->rowCount() > 0){
            $array = $sql->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $array;
    }
}