<?php 
namespace Models;

use \Core\Model;

class Photos extends Model {
    //Buscar quantos seguem
    public function getPhotosCount($id_user){
        $sql = "SELECT COUNT(*) AS c FROM photos WHERE id_user = :id";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':id', $id_user);
        $sql->execute();
        $info = $sql->fetch();
        return $info['c'];
    }
    //Criar o token jw
}
