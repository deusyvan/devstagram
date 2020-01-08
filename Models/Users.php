<?php 
namespace Models;

use Core\Model;

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
    
    //Criar o token jwt
    public function createJwt(){
        $jwt = new Jwt();
        return $jwt->create(array('id_user'=>$this->id_user));
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
}