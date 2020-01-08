<?php 
namespace Core;

class Controller {
    //Para pegar os metodos
    public function getMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }
    //Pegar dados da requisição
    public function getRequestData() {
        switch ($this->getMethod()){
            case 'GET':
                return $_GET;
                break;
            case 'PUT':
            case 'DELETE':
                parse_str(file_get_contents('php://input'),$data);
                return (array) $data;
                break;
            case 'POST':
                $data = json_decode(file_get_contents('php://input'));
                //Proteção para todos os métodos funcionarem
                if(is_null($data)){
                    $data = $_POST;
                }
                return (array) $data;
                break;
        }
    }
    //Fazer o retorno em json
    public function returnJson($array) {
        header("Content-Type: application/json");
        echo json_encode($array);
        exit;//Parar a execução para não ter risco de quebrar json
    }
}
