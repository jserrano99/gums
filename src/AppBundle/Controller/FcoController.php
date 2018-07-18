<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class FcoController extends Controller {

    private $sesion;

    public function __construct() {
        $this->sesion = new Session();
    }

    public function queryAction(Request $request) {
        $isAjax = $request->isXmlHttpRequest();

        $datatable = $this->get('sg_datatables.factory')->create(\AppBundle\Datatables\FcoDatatable::class);
        $datatable->buildDatatable();

        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $datatableQueryBuilder = $responseService->getDatatableQueryBuilder();
            $datatableQueryBuilder->buildQuery();

            return $responseService->getResponse();
        }

        return $this->render('fco/query.html.twig', array(
                    'datatable' => $datatable,
        ));
    }

    public function verEquiAction(Request $request, $id) {
        $isAjax = $request->isXmlHttpRequest();
        $em = $this->getDoctrine()->getManager();
        $Fco_repo = $em->getRepository("AppBundle:Fco");
        $Fco = $Fco_repo->find($id);

        $datatable = $this->get('sg_datatables.factory')->create(\AppBundle\Datatables\EqFcoDatatable::class);
        $datatable->buildDatatable();

        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $datatableQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $datatableQueryBuilder->getQb();
            $qb->andWhere('fco = :fco');
            $qb->setParameter('fco', $Fco);

            return $responseService->getResponse();
        }

        return $this->render('eqfco/query.html.twig', array(
                    'datatable' => $datatable,
        ));
    }

    public function editAction(Request $request, $id) {
        $entityManager = $this->getDoctrine()->getManager();
        $Fco_repo = $entityManager->getRepository("AppBundle:Fco");
        $Fco = $Fco_repo->find($id);

        $form = $this->createForm(\AppBundle\Form\FcoType::class, $Fco);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($Fco);
            $entityManager->flush();
            $resultado = $this->sincroniza($Fco->getId(), "UPDATE");
            $params = array("error" => $resultado["error"],
                "log" => $resultado["log"]);
            return $this->render('finSincro.html.twig', $params);
        }

        $params = array("Fco" => $Fco,
            "form" => $form->createView(),
            "accion" => 'MODIFICACIÓN');
        return $this->render("fco/edit.html.twig", $params);
    }

    public function deleteAction($id) {
        $entityManager = $this->getDoctrine()->getManager();
        $Fco_repo = $entityManager->getRepository("AppBundle:Fco");
        $Fco = $Fco_repo->find($id);
        $resultado = $this->sincroniza($Fco->getId(), "DELETE");
        $entityManager->remove($Fco);
        $entityManager->flush();
        $params = array("error" => $resultado["error"],
            "log" => $resultado["log"]);
        return $this->render('finSincro.html.twig', $params);
    }

    public function addAction(Request $request) {
        $entityManager = $this->getDoctrine()->getManager();
        $Fco_repo = $entityManager->getRepository("AppBundle:Fco");
        $Fco = new \AppBundle\Entity\Fco();

        $form = $this->createForm(\AppBundle\Form\FcoType::class, $Fco);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($Fco);
                $entityManager->flush();
                $this->creaEquivalencia($Fco);
                $resultado = $this->sincroniza($Fco->getId(), "INSERT");
                $params = array("error" => $resultado["error"],
                    "log" => $resultado["log"]);
                return $this->render('finSincro.html.twig', $params);
            } catch (UniqueConstraintViolationException $ex) {
                $status = "Error ya existe una forma de contratación con este codigo: " . $Fco->getCodigo();
                $this->sesion->getFlashBag()->add("status", $status);
            } catch (Doctrine\DBAL\DBALException $ex) {
                $status = "ERROR GENERAL=" . $ex->getMessage();
                $this->sesion->getFlashBag()->add("status", $status);
            }
        }

        $params = array("Fco" => $Fco,
            "form" => $form->createView(),
            "accion" => 'CREACIÓN');
        return $this->render("fco/edit.html.twig", $params);
    }

    public function creaEquivalencia($Fco) {
        $entityManager = $this->getDoctrine()->getManager();
        $Edificio_repo = $entityManager->getRepository("AppBundle:Edificio");
        $EdificioAll = $Edificio_repo->createQueryBuilder('u')
                        ->where("u.area = 'S' ")
                        ->getQuery()->getResult();
        foreach ($EdificioAll as $Edificio) {
            $EqFco = new \AppBundle\Entity\EqFco();
            $EqFco->setEdificio($Edificio);
            $EqFco->setFco($Fco);
            $EqFco->setCodigoLoc($Fco->getCodigo());
            $entityManager->persist($EqFco);
            $entityManager->flush();
        }

        return true;
    }

    public function sincroniza($id, $accion) {
        $root = $this->get('kernel')->getRootDir();
        $modo = $this->getParameter('modo');
        $php_script = "php " . $root . "/scripts/sincroFco.php  " . $modo . " " . $id . " " . $accion;

        $mensaje = exec($php_script, $salida, $valor);
        $resultado["error"] = $valor;
        $resultado["log"] = $salida;

        return $resultado;
    }

}
