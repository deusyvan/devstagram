<?php 
namespace Controllers;

use \Core\Controller;
use Models\Users;
use Models\Photos;

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
                    $array['error'] = $users->delete($id);
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

    public function feed(){
        $array = array('error'=>'', 'logged'=>FALSE);
        $method = $this->getMethod();
        $data = $this->getRequestData();
        
        $users = new Users();
        //Verifica se o token existe e se está válido = true true ou seja logado
        if(!empty($data['jwt']) && $users->validateJwt($data['jwt'])){
            $array['logged'] = TRUE;
            //Verifica o metodo
            if($method == 'GET'){
                //Realizar a paginação dos feeds com offset iniciando do primeiro
                $offset = 0;
                //Pega o offset que o usuario enviou e passa de string para um int
                if(!empty($data['offset'])){
                    $offset = intval($data['offset']);
                }
                //O usuário define com quantos feeds quer ver por página, padrão 10
                $per_page = 10;
                if(!empty($data['per_page'])){
                    $per_page = intval($data['per_page']);
                }
                //Passa para o model as informações para ele criar conforme o usuario pediu
                $array['data'] = $users->getFeed($offset, $per_page);
                
            } else {
                $array['error'] = 'Método '.$method.' não disponível!';
            }
            
        }else{
            $array['error'] = 'Acesso negado!';
        }
        
        $this->returnJson($array);
    }
    
    //Endpoint user/{id}/photo
    public function photos($id_user){
        $array = array('error'=>'', 'logged'=>FALSE);
        $method = $this->getMethod();
        $data = $this->getRequestData();
        
        $users = new Users();
        $p = new Photos();
        //Verifica se o token existe e se está válido = true true ou seja logado
        if(!empty($data['jwt']) && $users->validateJwt($data['jwt'])){
            $array['logged'] = TRUE;
            $array['is_me'] = FALSE;
            //Busca o is_me pra saber se as fotos é do usuario ou de outro
            if($id_user == $users->getId()){
                $array['is_me'] = TRUE;
            }
            //Só possui metodo GET
            if($method == 'GET'){
                //Realizar a paginação das fotos com offset iniciando do primeiro
                $offset = 0;
                //Pega o offset que o usuario enviou e passa de string para um int
                if(!empty($data['offset'])){
                    $offset = intval($data['offset']);
                }
                //O usuário define com quantas fotos quer ver por página, padrão 10
                $per_page = 10;
                if(!empty($data['per_page'])){
                    $per_page = intval($data['per_page']);
                }
                //Passa para o model as informações para ele visualizar as fotos conforme o usuario pedir
                $array['data'] = $p->getPhotosFromUser($id_user,$offset, $per_page);
                
            } else {
                $array['error'] = 'Método '.$method.' não disponível!';
            }
            
        }else{
            $array['error'] = 'Acesso negado!';
        }
        
        $this->returnJson($array);
    }
    
    //Endpoint follow/id
    public function follow($id_user) {
        $array = array('error'=>'', 'logged'=>FALSE);
        $method = $this->getMethod();
        $data = $this->getRequestData();
        
        $users = new Users();
        
        //Verifica se o token existe e se está válido = true true ou seja logado
        if(!empty($data['jwt']) && $users->validateJwt($data['jwt'])){
            $array['logged'] = TRUE;
            //Verifica o méthod enviado para seguir ou deseguir o usuario
            switch ($method) {
                case 'POST':
                    //$array['error']='Entrou seguir: ';
                    $users->follow($id_user);
                    break;
                case 'DELETE':
                    //$array['error']='Entrou desequir: ';
                    $users->unfollow($id_user);
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