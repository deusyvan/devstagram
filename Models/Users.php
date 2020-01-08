<?php 
namespace Models;

use Core\Model;

class Users extends Model{
    
    private $id_user;
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
    
    public function createJwt(){
        $jwt = new Jwt();
        return $jwt->create(array('id_user'=>$this->id_user));
    }
}