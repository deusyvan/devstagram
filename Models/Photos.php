<?php 
namespace Models;

use \Core\Model;
use PDO;

class Photos extends Model {
    //Buscar ultimas fotos dos usuarios
    public function getFeedCollection($ids, $offset, $per_page){
        $array = array();
        $users = new Users();
        //Verifica se possui pelo menos um seguidor
        if(count($ids) > 0){
            //Busca as fotos dos seguidores. Ex array(0,1,4,18) = IN(0,1,4,18)
            $sql = "SELECT * FROM photos WHERE id_user IN(".implode(',', $ids).") 
                    ORDER BY id DESC LIMIT "
                    .$offset.", ".$per_page;
            $sql = $this->db->query($sql);
            if($sql->rowCount() > 0){
                $array = $sql->fetchAll(PDO::FETCH_ASSOC);
                //busca de outros dados para o array
                foreach ($array as $k => $item){
                    //Busca o info do referido id seguidor
                    $user_info = $users->getInfo($item['id_user']);
                    //Acrescenta name no array
                    $array[$k]['name'] = $user_info['name'];
                    //Acrescenta avatar no array
                    $array[$k]['avatar'] = $user_info['avatar'];
                    //Corrige a url dentro do proprio array
                    $array[$k]['url'] = BASE_URL.'media/photos/'.$item['url'];
                    //Buscar a quantidade likes
                    $array[$k]['like_count'] = $this->getLikeCount($item['id']);
                    //Buscar os comentários
                    $array[$k]['comments'] = $this->getComments($item['id']);
                }
            }
        }
        
        return $array;
    }
    
    //Busca os comentarios
    public function getComments($id_photo){
        $array = array();
        $sql = "SELECT photos_comments.*, users.name FROM photos_comments 
                LEFT JOIN users ON users.id = photos_comments.id_user 
                WHERE photos_comments.id_photo = :id";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':id', $id_photo);
        $sql->execute();
        if($sql->rowCount() > 0){
            $array = $sql->fetchAll(PDO::FETCH_ASSOC);
        }
        return $array;
    }
    //Busca a quantidade de likes
    public function getLikeCount($id_photo){
        $sql = "SELECT COUNT(*) AS c FROM photos_likes WHERE id_photo = :id";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':id', $id_photo);
        $sql->execute();
        $info = $sql->fetch();
        return $info['c'];
    }
    
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
