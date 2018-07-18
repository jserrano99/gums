<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;


class AltasController extends Controller {

    private $sesion;

    public function __construct() {
        $this->sesion = new Session();
    }

    public function queryAction(Request $request) {
        $isAjax = $request->isXmlHttpRequest();

        $datatable = $this->get('sg_datatables.factory')->create(\AppBundle\Datatables\AltasDatatable::class);
        $datatable->buildDatatable();

        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $datatableQueryBuilder = $responseService->getDatatableQueryBuilder();
            $datatableQueryBuilder->buildQuery();

            return $responseService->getResponse();
        }

        return $this->render('altas/query.html.twig', array(
                    'datatable' => $datatable,
        ));
    }

    public function verEquiAction(Request $request, $id) {
        $isAjax = $request->isXmlHttpRequest();
        $em = $this->getDoctrine()->getManager();
        $Altas_repo = $em->getRepository("AppBundle:Altas");
        $Altas = $Altas_repo->find($id);

        $datatable = $this->get('sg_datatables.factory')->create(\AppBundle\Datatables\EqAltasDatatable::class);
        $datatable->buildDatatable();

        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $datatableQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $datatableQueryBuilder->getQb();
            $qb->andWhere('altas = :altas');
            $qb->setParameter('altas', $Altas);

            return $responseService->getResponse();
        }

        return $this->render('eqaltas/query.html.twig', array(
                    'datatable' => $datatable,
        ));
    }

    public function editAction(Request $request, $id) {
        $entityManager = $this->getDoctrine()->getManager();
        $Altas_repo = $entityManager->getRepository("AppBundle:Altas");
        $Altas = $Altas_repo->find($id);

        $form = $this->createForm(\AppBundle\Form\AltasType::class, $Altas);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $entityManager->persist($Altas);
            $entityManager->flush();
            $resultado = $this->sincroniza($Altas->getId(), "UPDATE");
            $params = array("error" => $resultado["error"],
                "log" => $resultado["log"]);
            return $this->render('finSincro.html.twig', $params);
        }

        $params = array("Altas" => $Altas,
            "form" => $form->createView(),
            "accion" => 'MODIFICACIÓN');
        return $this->render("altas/edit.html.twig", $params);
    }

    public function deleteAction($id) {
        $entityManager = $this->getDoctrine()->getManager();
        $Altas_repo = $entityManager->getRepository("AppBundle:Altas");
        $Altas = $Altas_repo->find($id);
        $resultado = $this->sincroniza($Altas->getId(), "DELETE");
        $entityManager->remove($Altas);
        $entityManager->flush();
        $params = array("error" => $resultado["error"],
            "log" => $resultado["log"]);
        return $this->render('finSincro.html.twig', $params);
    }

    public function addAction(Request $request) {
        $entityManager = $this->getDoctrine()->getManager();
        $Altas_repo = $entityManager->getRepository("AppBundle:Altas");
        $Altas = new \AppBundle\Entity\Altas();

        $form = $this->createForm(\AppBundle\Form\AltasType::class, $Altas);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            try {
                $entityManager->persist($Altas);
                $entityManager->flush();
                $this->creaEquivalencia($Altas);
                $resultado = $this->sincroniza($Altas->getId(), "INSERT");
                $params = array("error" => $resultado["error"],
                    "log" => $resultado["log"]);
                return $this->render('finSincro.html.twig', $params);
            } catch (UniqueConstraintViolationException $ex) {
                $status = "Error ya existe un motivo de alta este codigo: " . $Altas->getCodigo();
                $this->sesion->getFlashBag()->add("status", $status);
            } catch (Doctrine\DBAL\DBALException $ex) {
                $status = "ERROR GENERAL=" . $ex->getMessage();
                $this->sesion->getFlashBag()->add("status", $status);
            }
        }

        $params = array("Altas" => $Altas,
            "form" => $form->createView(),
            "accion" => 'CREACIÓN');
        return $this->render("altas/edit.html.twig", $params);
    }

    public function creaEquivalencia($Altas) {
        $entityManager = $this->getDoctrine()->getManager();
        $Edificio_repo = $entityManager->getRepository("AppBundle:Edificio");
        $EdificioAll = $Edificio_repo->createQueryBuilder('u')
                ->where("u.area = 'S' ")
                ->getQuery()->getResult();
        foreach ($EdificioAll as $Edificio) {
            $EqAltas = new \AppBundle\Entity\EqAltas();
            $EqAltas->setEdificio($Edificio);
            $EqAltas->setAltas($Altas);
            $EqAltas->setCodigoLoc($Altas->getCodigo());
            $entityManager->persist($EqAltas);
            $entityManager->flush();
        }

        return true;
    }

    public function sincroniza($id, $accion) {
        $root = $this->get('kernel')->getRootDir();
        $modo = $this->getParameter('modo');
        $php_script = "php " . $root . "/scripts/sincroAltas.php  " . $modo . " " . $id . " " . $accion;

        $mensaje = exec($php_script, $salida, $valor);
        $resultado["error"] = $valor;
        $resultado["log"] = $salida;

        return $resultado;
    }

}
