<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CargaInicialController extends Controller {

    public function indexAction() {

        return $this->render('cargaInicial/lanza.html.twig');
    }

    public function lanzaAction($tabla) {
        $resultado = $this->lanzaCarga($tabla);
        $params = array("error" => $resultado["error"],
            "log" => $resultado["log"]);
        return $this->render('finProceso.html.twig', $params);
    }

    public function lanzaCarga($tabla) {
        $root = $this->get('kernel')->getRootDir();
        $modo = $this->getParameter('modo');
        $php_script = "php ". $root . "/scripts/carga.php"." " .$tabla ." ". $modo;
        $res = exec($php_script, $salida, $valor);
        $resultado["error"] = $valor;
        $resultado["log"] = $salida;
        return $resultado;
    }

    
}
