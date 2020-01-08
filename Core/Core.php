<?php 
namespace Core;

class Core {
    
    public function run(){
        
        $url = '/';
        if (isset($_GET['url'])){
            $url .= $_GET['url'];
        }
        
        //Implementação dos routers
        $url = $this->checkRoutes($url);
        
        $params = array();
        
        if (!empty($url) && $url != '/'){
            $url = explode('/', $url);
            array_shift($url);
            
            $currentController = $url[0].'Controller';
            array_shift($url);
            
            if (isset($url[0]) && !empty($url[0])){
                $currentAction = $url[0];
                array_shift($url);
            } else {
                $currentAction = 'index';
            }
            
            if (count($url) > 0){
                $params = $url;
            }
            
        } else {
            $currentController = 'HomeController';
            $currentAction = 'index';
        }
        
        $currentController = ucfirst($currentController);//Colocando somente a primeira letra em maiuscula
        
        $prefix = '\Controllers\\';
        
        if(!file_exists('Controllers/'.$currentController.'.php') || 
            !method_exists($prefix.$currentController, $currentAction)){
            $currentController = 'NotFoundController';
            $currentaction = 'index';
        }
        
        $newController = $prefix.$currentController;
        $c = new $newController();
        
        call_user_func_array(array($c,$currentAction), $params);
    }
    
    public function checkRoutes($url) {
        global $routes;
        
        foreach($routes as $pt => $newurl) {
            
            // Identifica os argumentos e substitui por regex
            $pattern = preg_replace('(\{[a-z0-9]{1,}\})', '([a-z0-9-]{1,})', $pt);
            //            echo "padrao: ".$pattern."<br>";
            //            echo "URL: ".$url."<br>";exit;
            
            // Faz o match da URL com o padrão se for segue
            if(preg_match('#^('.$pattern.')*$#i', $url, $matches) === 1) {
                //Remove os dois primeiros resultados pois são irrelevantes
                //var_dump($matches);exit;
                array_shift($matches);
                array_shift($matches);
                
                // Pega todos os argumentos para associar
                $itens = array();
                //all pois pode ter mais de um argumento(parametro) o que está em $pt e salvando em $m
                if(preg_match_all('(\{[a-z0-9]{1,}\})', $pt, $m)) {
                    //Localiza o que está entre colchetes
                    //print_r($m);exit;
                    //Retira os colchetes
                    $itens = preg_replace('(\{|\})', '', $m[0]);
                    //print_r($itens);exit;
                }
                
                // Faz a associação com os argumentos recolhidos
                $arg = array();
                foreach($matches as $key => $match) {
                    //Juntando o resultado de $matches com o resultado de $itens
                    $arg[$itens[$key]] = $match;
                }
                //print_r($arg);exit;
                //print_r($matches);exit;
                //print_r($itens);exit;
                
                // Monta a nova url
                foreach($arg as $argkey => $argvalue) {
                    //Substituindo todos os argumentos pelos seus valores
                    $newurl = str_replace(':'.$argkey, $argvalue, $newurl);
                }
                
                $url = $newurl;
                //echo "URL: ".$url;exit;
                //Encerra o forech quando encontra a url certa e retorna a nova
                break;
                
            }
        }
        return $url;
    }
}
