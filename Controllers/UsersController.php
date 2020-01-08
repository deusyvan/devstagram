<?php 
namespace Controllers;

use \Core\Controller;
use Models\Users;

class UsersController extends Controller{
    //
    public function index(){}
    
    public function login(){
        //Array já inicia com error vazio
        $array = array('error'=>'');
        //Busca os dados em data
        $data = $this->getRequestData();
        //Busca o metodo
        $method = $this->getMethod();
        //Avalia o metodo e os dados
        if($method == 'POST'){
            if(!empty($data['email']) && !empty($data['pass'])){
                $users = new Users();
                if ($users->checkCredentials($data['email'], $data['pass'])){
                    // Gerar o JWT;
                    $array['jwt'] = $users->createJwt();
                } else {
                    $array['error'] = 'Acesso negado';
                }
            } else {
                $array['error'] = 'E-mail e/ou senha não preenchido.';
            }
        }else {
            $array['error'] = 'Método de requisição incompatível';
        }
        
        $this->returnJson($array);
    }
}