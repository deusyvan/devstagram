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
                if($users->checkCredentials($data['email'], $data['pass'])){
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
    
    public function new_record(){
        $array = array('error'=>'');
        $method = $this->getMethod();
        $data = $this->getRequestData();
        //Corpo de um endpoint
        if($method == 'POST'){
            if(!empty($data['name']) && !empty($data['email']) && !empty($data['pass'])){
                //Verifica no proprio php o email com uma constante
                if(filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
                    $users = new Users();
                    if($users->create($data['name'],$data['email'],$data['pass'])){
                        $array['jwt'] = $users->createJwt();
                    } else {
                        $array['error'] = 'E-mail já existe!';
                    }
                } else {
                    $array['error'] = 'E-mail inválido!';
                }
            } else {
                $array['error'] = 'Dados não preenchidos';
            }
        } else {
            $array['error'] = 'Método de requisição incompatível';
        }
        //end corpo
        
        $this->returnJson($array);
    }
    
    public function view($id){
        $array = array('error'=>'', 'logged'=>FALSE);
        $method = $this->getMethod();
        $data = $this->getRequestData();
        
        $users = new Users();
        //Verifica se o token existe e se está válido = true true ou seja logado
        if(!empty($data['jwt']) && $users->validateJwt($data['jwt'])){
            $array['logged'] = TRUE;
            //Verifica se o usuario eh realmente o que está logado - is_me
            $array['is_me'] = FALSE;
            if($id == $users->getId()){
                $array['is_me'] = TRUE;
            }
            //Verifica o méthod enviado
            switch ($method) {
                case 'GET':
                    $array['data'] = $users->getInfo($id);
                    //Verificar se o usuario existe
                    if(count($array['data']) === 0){
                        $array['error'] = 'Usuário não existe';
                    }
                    break;
                case 'PUT':
                    $array['error'] = $users->editInfo($id, $data);
                    break;
                case 'DELETE':
                    
                    break;
                
                default:
                    $array['error'] = 'Método '.$method.' não disponível!';
                break;
            }
            
        }else{
            $array['error'] = 'Acesso negado!';
        }
        
        $this->returnJson($array);
    }
}