<?php 
namespace Models;

use Core\Model;
use PDO;

class Users extends Model{
    
    private $id_user;
    
    //Criar novo usuario
    public function create($name,$email,$pass) {
        if(!$this->emailExists($email)){
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $sql = 'INSERT INTO users (name, email, pass) VALUES (:name, :email, :pass)';
            $sql = $this->db->prepare($sql);
            $sql->bindValue(':name', $name);
            $sql->bindValue(':email', $email);
            $sql->bindValue(':pass', $hash);
            $sql->execute();
            //Pega o ultimo id inserido e carrega o id_user
            $this->id_user = $this->db->lastInsertId();
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    //Verifica se existe o email e se a senha está correta
    public function checkCredentials($email, $pass){
        $sql = "SELECT id,pass FROM users WHERE email = :email";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':email', $email);
        $sql->execute();
        if ($sql->rowCount() > 0) {
            $info = $sql->fetch();
            //Verifica o hash da senha se está correto
            if (password_verify($pass, $info['pass'])) {
                $this->id_user = $info['id'];
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }
    
    public function getId(){
        return $this->id_user;
    }
    
    //Buscando informações do usuario pelo id e definindo avatar senão existir
    public function getInfo($id){
        $array = array();
        $sql = "SELECT id, name, email, avatar FROM users WHERE id = :id";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':id', $id);
        $sql->execute();
        //Achando o usuário
        if($sql->rowCount() > 0){
            $array = $sql->fetch(PDO::FETCH_ASSOC);
            
            $photos = new Photos();
            
            if(!empty($array['avatar'])){
                $array['avatar'] = BASE_URL.'media/avatar/'.$array['avatar'];
            }else {
                $array['avatar'] = BASE_URL.'media/avatar/default.jpg';
            }
            //Colocando os seguidores, as que segue e as fotos postadas
            $array['following'] = $this->getFollowingCount($id);
            $array['followers'] = $this->getFollowersCount($id);
            $array['photos_count'] = $photos->getPhotosCount($id);
        }
        
        return $array;
    }
    //Buscar quantos estão seguidondo
    public function getFollowingCount($id_user){
        $sql = "SELECT COUNT(*) AS c FROM users_following WHERE id_user_active = :id";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':id', $id_user);
        $sql->execute();
        $info = $sql->fetch();
        return $info['c'];
    }
    
    //Buscar quantos seguem
    public function getFollowersCount($id_user){
        $sql = "SELECT COUNT(*) AS c FROM users_following WHERE id_user_passive = :id";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':id', $id_user);
        $sql->execute();
        $info = $sql->fetch();
        return $info['c'];
    }
    
    //Criar o token jwt
    public function createJwt(){
        $jwt = new Jwt();
        return $jwt->create(array('id_user'=>$this->id_user));
    }
    
    public function validateJwt($token){
        $jwt = new Jwt();
        $info = $jwt->validate($token);
        //Atraves do token recupera o id do usuario, enviado através do token
        if(isset($info->id_user)){
            $this->id_user = $info->id_user;
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    //Verifica se existe
    public function emailExists($email){
        $sql = "SELECT id FROM users WHERE email = :email";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':email', $email);
        $sql->execute();
        if($sql->rowCount() > 0){
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    public function editInfo($id, $data){
        //Quem pode editar somente o proprio usuario logado
        if($id === $this->getId()){
            
            $toChange = array();
            //Verifica os campos que não estão vazio e válidos após atribui para toChange
            if(!empty($data['name'])){
                $toChange['name'] = $data['name'];
            }
            if(!empty($data['email'])){
                if(filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
                    if(!$this->emailExists($data['email'])){
                        $toChange['email'] = $data['email'];
                    } else {
                        return 'E-mail já existente!';
                    }
                } else {
                    return 'E-mail inválido';
                }
                
            }
            if(!empty($data['pass'])){
                $toChange['pass'] = password_hash($data['pass'], PASSWORD_DEFAULT);
            }
            
            if(count($toChange) > 0){
                //Define os campos que não vieram vazios e não inválidos
                $fields = array();
                foreach($toChange as $k => $v){
                    $fields[] = $k.' = :'.$k;
                }
                
                //Monta a query para atualizar no banco
                $sql = "UPDATE users SET ".implode(',', $fields)." WHERE id = :id";
                $sql = $this->db->prepare($sql);
                $sql->bindValue(':id', $id);
                
                //Fazer os binds
                foreach ($toChange as $key => $value) {
                    $sql->bindValue(":".$key, $value);
                }
                
                $sql->execute();
                return '';
            }else {
                return 'Preencha os dados corretamente!';
            }
            
        }else {
            return 'Não é permitido editar outro usuário';
        }
    }
    //Só quem pode excluir é o usuario logado
    public function delete($id){
        //Verificar o que corresponde ao usuário: Deletar ou inativar
        if($id === $this->getId()){
            $p = new Photos();
            // Deletar em Photos
            $p->deleteAll($id);
            //Deleta em users_following: Seguidores e seguidos
            $sql = "DELETE FROM users_following WHERE id_user_active = :id OR
                    id_user_passive = :id";
            $sql = $this->db->prepare($sql);
            $sql->bindValue(":id", $id);
            $sql->execute();
            
            //Delete o usuário
            $sql = "DELETE FROM users WHERE id = :id";
            $sql = $this->db->prepare($sql);
            $sql->bindValue(":id", $id);
            $sql->execute();
            
            return '';
        } else {
            return 'Não é permitido excluir outro usuário!';
        }
    }
}