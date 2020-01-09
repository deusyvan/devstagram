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
    
    public function deleteAll($id_user){
        /* Deletar em Photos */
        //Fotos
        $sql = 'DELETE FROM photos WHERE id_user = :id_user';
        $sql = $this->db->prepare($sql);
        $sql->bindValue(":id_user", $id_user);
        $sql->execute();
        
        //Comentários nas fotos
        $sql = 'DELETE FROM photos_comments WHERE id_user = :id_user';
        $sql = $this->db->prepare($sql);
        $sql->bindValue(":id_user", $id_user);
        $sql->execute();
        
        //Likes nas fotos
        $sql = 'DELETE FROM photos_likes WHERE id_user = :id_user';
        $sql = $this->db->prepare($sql);
        $sql->bindValue(":id_user", $id_user);
        $sql->execute();
        
        
    }
}
