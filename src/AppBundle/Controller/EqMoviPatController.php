<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;

class EqMoviPatController extends Controller {

    private $sesion;

    public function __construct() {
        $this->sesion = new Session();
    }

    public function queryAction(Request $request) {
        $isAjax = $request->isXmlHttpRequest();

        $datatable = $this->get('sg_datatables.factory')->create(\AppBundle\Datatables\EqMoviPatDatatable::class);
        $datatable->buildDatatable();

        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $datatableQueryBuilder = $responseService->getDatatableQueryBuilder();
            $datatableQueryBuilder->buildQuery();

            return $responseService->getResponse();
        }

        return $this->render('eqmovipat/query.html.twig', array(
                    'datatable' => $datatable,
        ));
    }

    public function deleteAction($id) {
        $entityManager = $this->getDoctrine()->getManager();
        $EqMoviPat_repo = $entityManager->getRepository("AppBundle:EqMoviPat");
        $EqMoviPat = $EqMoviPat_repo->find($id);
        $resultado = $this->sincroniza($id, "DELETE");
        $entityManager->remove($EqMoviPat);
        $entityManager->flush();

        $params = array("error" => $resultado["error"],
            "log" => $resultado["log"]);
        return $this->render('finSincro.html.twig', $params);
    }

    public function ajaxComprobarAction($codigoLoc, $edificio_id) {
        $entityManager = $this->getDoctrine()->getManager();
        $Edificio_repo = $entityManager->getRepository("AppBundle:Edificio");
        $Edificio = $Edificio_repo->find($edificio_id);

        $EqMoviPat_repo = $entityManager->getRepository("AppBundle:EqMoviPat");
        $EqMoviPat = $EqMoviPat_repo->createQueryBuilder('u')
                        ->where('u.edificio = :edificio and u.codigoLoc = :codigoLoc')
                        ->setParameter('edificio', $Edificio)
                        ->setParameter('codigoLoc', $codigoLoc)
                        ->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        if ($EqMoviPat) {
            $EqMoviPat = $EqMoviPat[0];
        }

        $response = new Response();
        $response->setContent(json_encode($EqMoviPat));
        $response->headers->set("Content-type", "application/json");
        return $response;
    }

    public function ComprobarAction($codigoLoc, $Edificio) {
        $entityManager = $this->getDoctrine()->getManager();
        $EqMoviPat_repo = $entityManager->getRepository("AppBundle:EqMoviPat");
        $EqMoviPat = $EqMoviPat_repo->createQueryBuilder('u')
                        ->where('u.edificio = :edificio and u.codigoLoc = :codigoLoc')
                        ->setParameter('edificio', $Edificio)
                        ->setParameter('codigoLoc', $codigoLoc)
                        ->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        if ($EqMoviPat) {
            $EqMoviPat = $EqMoviPat[0];
            return true;
        }
        return null;
    }

    public function addAction(Request $request) {
        $entityManager = $this->getDoctrine()->getManager();
        $EqMoviPat_repo = $entityManager->getRepository("AppBundle:EqMoviPat");

        $EqMoviPat = new \AppBundle\Entity\EqMoviPat();
        $form = $this->createForm(\AppBundle\Form\EqMoviPatType::class, $EqMoviPat);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($this->ComprobarAction($EqMoviPat->getCodigoLoc(), $EqMoviPat->getEdificio())) {
                $status = ' Ya Existe Equivalencia para Edificio ' . $EqMoviPat->getEdificio() . ' Codigo : ' . $EqMoviPat->getCodigoLoc();
                $this->sesion->getFlashBag()->add("status", $status);
            } else {
                $entityManager->persist($EqMoviPat);
                $entityManager->flush();
                $resultado = $this->sincroniza($EqMoviPat->getId(), "INSERT");
                $params = array("error" => $resultado["error"],
                    "log" => $resultado["log"]);
                return $this->render('finSincro.html.twig', $params);
            }
        }

        $params = array("EqMoviPat" => $EqMoviPat,
            "form" => $form->createView(),
            "accion" => 'MODIFICACIÓN');

        return $this->render("eqmodocupa/edit.html.twig", $params);
    }

    public function sincroniza($id, $accion) {
        $root = $this->get('kernel')->getRootDir();
        $modo = $this->getParameter('modo');
        $php_script = "php " . $root . "/scripts/sincroEqMoviPat.php  " . $modo . " " . $id . " " . $accion;

        $mensaje = exec($php_script, $salida, $valor);
        $resultado["error"] = $valor;
        $resultado["log"] = $salida;

        return $resultado;
    }

}
