<?php 
namespace Controllers;

use \Core\Controller;
use \Models\Photos;
use \Models\Users;

class PhotosController extends Controller{
    public function index(){}
    
    public function random(){
        $array = array('error'=>'', 'logged'=>FALSE);
        $method = $this->getMethod();
        $data = $this->getRequestData();
        
        $users = new Users();
        $p = new Photos();
        //Verifica se o token existe e se está válido = true true ou seja logado
        if(!empty($data['jwt']) && $users->validateJwt($data['jwt'])){
            $array['logged'] = TRUE;
            //Só possui metodo GET
            if($method == 'GET'){
                //Realizar a paginação das fotos aleatórias por meio de exclusão
                //O usuário define com quantas fotos quer ver por página, padrão 10
                $per_page = 10;
                if(!empty($data['per_page'])){
                    $per_page = intval($data['per_page']);
                }
                
                //Enviar os itens excludentes do random caso se vier em data['excludes']
                $excludes = array();
                if(!empty($data['excludes'])){
                    $excludes = explode(',', $data['excludes']);
                }
                
                //Pede para o model as fotos aleatorias de acordo com par_page
                $array['data'] = $p->getRandomPhotos($per_page, $excludes);
                
            } else {
                $array['error'] = 'Método '.$method.' não disponível!';
            }
            
        }else{
            $array['error'] = 'Acesso negado!';
        }
        
        $this->returnJson($array);
    }
    
    //Endpoint /photo/id
    public function view($id_photo){
        $array = array('error'=>'', 'logged'=>FALSE);
        
        $method = $this->getMethod();
        $data = $this->getRequestData();
        
        $users = new Users();
        $p = new Photos();
        
        //Verifica se o token existe e se está válido = true true ou seja logado
        if(!empty($data['jwt']) && $users->validateJwt($data['jwt'])){
            $array['logged'] = TRUE;
            //Verifica o metodo 
            switch ($method) {
                case 'GET':
                    $array['data'] = $p->getPhoto($id_photo);
                    break;
                case 'DELETE':
                    //Só vai retornar algo se houver erro 
                    $array['error'] = $p->deletePhoto($id_photo, $users->getId());
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
    
    public function comment($id_photo) {
        $array = array('error'=>'', 'logged'=>FALSE);
        
        $method = $this->getMethod();
        $data = $this->getRequestData();
        
        $users = new Users();
        $p = new Photos();
        
        //Verifica se o token existe e se está válido = true true ou seja logado
        if(!empty($data['jwt']) && $users->validateJwt($data['jwt'])){
            $array['logged'] = TRUE;
            //Verifica o metodo
            switch ($method) {
                case 'POST':
                    if(!empty($data['txt'])){
                        $array['error'] = $p->addComment($id_photo,$users->getId(),$data['txt']);
                    } else {
                        $array['error'] = 'Comentário vazio!';
                    }
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
    
    public function delete_comment($id_comment){
        $array = array('error'=>'', 'logged'=>FALSE);
        
        $method = $this->getMethod();
        $data = $this->getRequestData();
        
        $users = new Users();
        $p = new Photos();
        
        //Verifica se o token existe e se está válido = true true ou seja logado
        if(!empty($data['jwt']) && $users->validateJwt($data['jwt'])){
            $array['logged'] = TRUE;
            //Verifica o metodo
            switch ($method) {
                case 'DELETE':
                    $array['error'] = $p->deleteComment($id_comment,$users->getId());
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
    
    //Endpoint 
    public function like($id_photo){
        $array = array('error'=>'', 'logged'=>FALSE);
        
        $method = $this->getMethod();
        $data = $this->getRequestData();
        
        $users = new Users();
        $p = new Photos();
        
        //Verifica se o token existe e se está válido = true true ou seja logado
        if(!empty($data['jwt']) && $users->validateJwt($data['jwt'])){
            $array['logged'] = TRUE;
            //Verifica o metodo
            switch ($method) {
                case 'POST':
                    $array['error'] = $p->like($id_photo, $users->getId());
                    break;
                case 'DELETE':
                    $array['error'] = $p->unlike($id_photo, $users->getId());
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