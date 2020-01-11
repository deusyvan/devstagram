<?php 
namespace Models;

use \Core\Model;
use \PDO;

class Photos extends Model {
    //Busca fotos randonicamente pela quantidade, excludes é opcional
    public function getRandomPhotos($per_page, $excludes = array()){
        $array = array();
        //Vamos garantir que sejam inteiros melhorando a segurança na query "SqlInjection"
        foreach ($excludes as $k => $item){
            $excludes[$k] = intval($item);
        }
        //Ver se existe excludes para criar a sql sem erros
        if(count($excludes) > 0){
            //Como não sabemos quantos são colocamos na query evitando os bindValue
            $sql = "SELECT * FROM photos WHERE id NOT IN(".implode(',', $excludes).") 
                    ORDER BY RAND() LIMIT ".$per_page;
        } else {
            $sql = "SELECT * FROM photos ORDER BY RAND() LIMIT ".$per_page;
        }
        //Roda a query depois de montada
        $sql = $this->db->query($sql);
        
        if($sql->rowCount() > 0){
            $array = $sql->fetchAll(PDO::FETCH_ASSOC);
            foreach ($array as $k => $item) {
                //Monta a url da foto corrigindo a url dentro do proprio array
                $array[$k]['url'] = BASE_URL.'media/photos/'.$item['url'];
                //Buscar a quantidade likes
                $array[$k]['like_count'] = $this->getLikeCount($item['id']);
                //Buscar os comentários
                $array[$k]['comments'] = $this->getComments($item['id']);
            }
        }
        return $array;
    }
    //Busca as fotos de um usuario específico
    public function getPhotosFromUser($id_user,$offset, $per_page){
        //$array = array($id_user);
        $array = array();
        $sql = "SELECT * FROM photos WHERE id_user = :id ORDER BY id DESC LIMIT "
                .$offset.", ".$per_page;
        $sql = $this->db->prepare($sql);
        $sql->bindValue(":id", $id_user);
        $sql->execute();
        
        if($sql->rowCount() > 0){
            //$array = array('entrou' => 'sim');
            $array = $sql->fetchAll(PDO::FETCH_ASSOC);
            foreach ($array as $k => $item) {
                //Monta a url da foto corrigindo a url dentro do proprio array
                $array[$k]['url'] = BASE_URL.'media/photos/'.$item['url'];
                //Buscar a quantidade likes
                $array[$k]['like_count'] = $this->getLikeCount($item['id']);
                //Buscar os comentários
                $array[$k]['comments'] = $this->getComments($item['id']);
            }
        }
        
        return $array;
    }
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
    
    //Busca informações de uma foto
    public function getPhoto($id_photo){
        $array = array();
        $users = new Users();
        //Busca as fotos dos seguidores. Ex array(0,1,4,18) = IN(0,1,4,18)
        $sql = "SELECT * FROM photos WHERE id = :id";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':id', $id_photo);
        $sql->execute();
            
        if($sql->rowCount() > 0){
            $array = $sql->fetch(PDO::FETCH_ASSOC);
            //Busca o info do referido id seguidor
            $user_info = $users->getInfo($array['id_user']);
            //Acrescenta name no array
            $array['name'] = $user_info['name'];
            //Acrescenta avatar no array
            $array['avatar'] = $user_info['avatar'];
            //Corrige a url dentro do proprio array
            $array['url'] = BASE_URL.'media/photos/'.$array['url'];
            //Buscar a quantidade likes
            $array['like_count'] = $this->getLikeCount($array['id']);
            //Buscar os comentários
            $array['comments'] = $this->getComments($array['id']);
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
    
    /* Deletar a Photo */
    public function deletePhoto($id_photo, $id_user){
        //Verifica se é o dono da foto
        $sql = "SELECT id FROM photos WHERE id = :id_photo AND id_user = :id_user";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(":id_photo", $id_photo);
        $sql->bindValue(":id_user", $id_user);
        $sql->execute();
        if($sql->rowCount() > 0){
            $sql = 'DELETE FROM photos WHERE id = :id';
            $sql = $this->db->prepare($sql);
            $sql->bindValue(":id", $id_photo);
            $sql->execute();
            
            //Comentários nas fotos
            $sql = 'DELETE FROM photos_comments WHERE id_photo = :id_photo';
            $sql = $this->db->prepare($sql);
            $sql->bindValue(":id_photo", $id_photo);
            $sql->execute();
            
            //Likes nas fotos
            $sql = 'DELETE FROM photos_likes WHERE id_photo = :id_photo';
            $sql = $this->db->prepare($sql);
            $sql->bindValue(":id_photo", $id_photo);
            $sql->execute();
            return '';
        } else {
            return 'Esta foto não é sua';
        }
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
    
    public function addComment($id_photo,$id_user,$txt){
        //Verifica se o txt não está vazio
        if(!empty($txt)){
            $sql = "INSERT INTO photos_comments (id_user, id_photo, date_comment, txt) 
                    VALUES (:id_user, :id_photo, NOW(), :txt)";
            $sql = $this->db->prepare($sql);
            $sql->bindValue(":id_user", $id_user);
            $sql->bindValue(":id_photo", $id_photo);
            $sql->bindValue(":txt", $txt);
            $sql->execute();
            return '';
        } else {
            return 'Comentário vazio';
        }
    }
    
    public function deleteComment($id_comment, $id_user){
        $sql = "SELECT photos_comments.id FROM photos_comments  
                LEFT JOIN photos ON photos.id = photos_comments.id_photo 
                WHERE (photos_comments.id_user = :id_user AND photos_comments.id = :id_comment)
                OR (photos_comments.id = :id_comment AND photos.id_user = :id_user)";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(":id_user", $id_user);
        $sql->bindValue(":id_comment", $id_comment);
        $sql->execute();
        if($sql->rowCount() > 0){
            $sql = "DELETE FROM photos_comments WHERE id = :id";
            $sql = $this->db->prepare($sql);
            $sql->bindValue(":id", $id_comment);
            $sql->execute();
            return '';
        } else {
            return "Este comentário não é seu";
        }
    }
    
    public function like($id_photo, $id_user){
        //Verifica se já não tem um like na foto
        $sql = "SELECT * FROM photos_likes WHERE id_user = :id_user AND id_photo = :id_photo";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':id_user', $id_user);
        $sql->bindValue(':id_photo', $id_photo);
        $sql->execute();
        if($sql->rowCount() == 0){
            $sql = "INSERT INTO photos_likes (id_user, id_photo) VALUES (:id_user, :id_photo)";
            $sql = $this->db->prepare($sql);
            $sql->bindValue(":id_user", $id_user);
            $sql->bindValue(":id_photo", $id_photo);
            $sql->execute();
            
            return '';
        } else {
            return 'Você já deu like nesta foto.';
        }
    }
    
    public function unlike($id_photo, $id_user){
        //Busca o like e deleta
        $sql = "DELETE FROM photos_likes WHERE id_user = :id_user AND id_photo = :id_photo";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(":id_user", $id_user);
        $sql->bindValue(":id_photo", $id_photo);
        $sql->execute();
        
        return '';
    }
}
