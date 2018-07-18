<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;

class EqFcoController extends Controller {

    private $sesion;

    public function __construct() {
        $this->sesion = new Session();
    }

    public function queryAction(Request $request) {
        $isAjax = $request->isXmlHttpRequest();

        $datatable = $this->get('sg_datatables.factory')->create(\AppBundle\Datatables\EqFcoDatatable::class);
        $datatable->buildDatatable();

        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $datatableQueryBuilder = $responseService->getDatatableQueryBuilder();
            $datatableQueryBuilder->buildQuery();

            return $responseService->getResponse();
        }

        return $this->render('eqfco/query.html.twig', array(
                    'datatable' => $datatable,
        ));
    }

    public function deleteAction($id) {
        $entityManager = $this->getDoctrine()->getManager();
        $EqFco_repo = $entityManager->getRepository("AppBundle:EqFco");
        $EqFco = $EqFco_repo->find($id);
        $resultado = $this->sincroniza($id, "DELETE");
        $entityManager->remove($EqFco);
        $entityManager->flush();

        $params = array("error" => $resultado["error"],
            "log" => $resultado["log"]);
        return $this->render('finSincro.html.twig', $params);
    }

    public function ajaxComprobarAction($codigoLoc, $edificio_id) {
        $entityManager = $this->getDoctrine()->getManager();
        $Edificio_repo = $entityManager->getRepository("AppBundle:Edificio");
        $Edificio = $Edificio_repo->find($edificio_id);

        $EqFco_repo = $entityManager->getRepository("AppBundle:EqFco");
        $EqFco = $EqFco_repo->createQueryBuilder('u')
                        ->where('u.edificio = :edificio and u.codigoLoc = :codigoLoc')
                        ->setParameter('edificio', $Edificio)
                        ->setParameter('codigoLoc', $codigoLoc)
                        ->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        if ($EqFco) {
            $EqFco = $EqFco[0];
        }

        $response = new Response();
        $response->setContent(json_encode($EqFco));
        $response->headers->set("Content-type", "application/json");
        return $response;
    }

    public function ComprobarAction($codigoLoc, $Edificio) {
        $entityManager = $this->getDoctrine()->getManager();
        $EqFco_repo = $entityManager->getRepository("AppBundle:EqFco");
        $EqFco = $EqFco_repo->createQueryBuilder('u')
                        ->where('u.edificio = :edificio and u.codigoLoc = :codigoLoc')
                        ->setParameter('edificio', $Edificio)
                        ->setParameter('codigoLoc', $codigoLoc)
                        ->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        if ($EqFco) {
            $EqFco = $EqFco[0];
            return true;
        }
        return null;
    }

    public function addAction(Request $request) {
        $entityManager = $this->getDoctrine()->getManager();
        $EqFco_repo = $entityManager->getRepository("AppBundle:EqFco");

        $EqFco = new \AppBundle\Entity\EqFco();
        $form = $this->createForm(\AppBundle\Form\EqFcoType::class, $EqFco);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($this->ComprobarAction($EqFco->getCodigoLoc(), $EqFco->getEdificio())) {
                $status = ' Ya Existe Equivalencia para Edificio ' . $EqFco->getEdificio() . ' Codigo : ' . $EqFco->getCodigoLoc();
                $this->sesion->getFlashBag()->add("status", $status);
            } else {
                $entityManager->persist($EqFco);
                $entityManager->flush();
                $resultado = $this->sincroniza($EqFco->getId(), "INSERT");
                $params = array("error" => $resultado["error"],
                    "log" => $resultado["log"]);
                return $this->render('finSincro.html.twig', $params);
            }
        }

        $params = array("EqFco" => $EqFco,
            "form" => $form->createView(),
            "accion" => 'MODIFICACIÓN');

        return $this->render("eqfco/edit.html.twig", $params);
    }

    public function sincroniza($id, $accion) {
        $root = $this->get('kernel')->getRootDir();
        $modo = $this->getParameter('modo');
        $php_script = "php " . $root . "/scripts/sincroEqFco.php  " . $modo . " " . $id . " " . $accion;

        $mensaje = exec($php_script, $salida, $valor);
        $resultado["error"] = $valor;
        $resultado["log"] = $salida;

        return $resultado;
    }

}
