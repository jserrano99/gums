<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class ModOcupaController extends Controller {

    private $sesion;

    public function __construct() {
        $this->sesion = new Session();
    }

    public function queryAction(Request $request) {
        $isAjax = $request->isXmlHttpRequest();

        $datatable = $this->get('sg_datatables.factory')->create(\AppBundle\Datatables\ModOcupaDatatable::class);
        $datatable->buildDatatable();

        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $datatableQueryBuilder = $responseService->getDatatableQueryBuilder();
            $datatableQueryBuilder->buildQuery();

            return $responseService->getResponse();
        }

        return $this->render('modocupa/query.html.twig', array(
                    'datatable' => $datatable,
        ));
    }

    public function verEquiAction(Request $request, $id) {
        $isAjax = $request->isXmlHttpRequest();
        $em = $this->getDoctrine()->getManager();
        $ModOcupa_repo = $em->getRepository("AppBundle:ModOcupa");
        $ModOcupa = $ModOcupa_repo->find($id);

        $datatable = $this->get('sg_datatables.factory')->create(\AppBundle\Datatables\EqModOcupaDatatable::class);
        $datatable->buildDatatable();

        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $datatableQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $datatableQueryBuilder->getQb();
            $qb->andWhere('modOcupa = :modOcupa');
            $qb->setParameter('modOcupa', $ModOcupa);

            return $responseService->getResponse();
        }

        return $this->render('eqmodocupa/query.html.twig', array(
                    'datatable' => $datatable,
        ));
    }

    public function editAction(Request $request, $id) {
        $entityManager = $this->getDoctrine()->getManager();
        $ModOcupa_repo = $entityManager->getRepository("AppBundle:ModOcupa");
        $ModOcupa = $ModOcupa_repo->find($id);

        $form = $this->createForm(\AppBundle\Form\ModOcupaType::class, $ModOcupa);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $entityManager->persist($ModOcupa);
            $entityManager->flush();
            $resultado = $this->sincroniza($ModOcupa->getId(), "UPDATE");
            $params = array("error" => $resultado["error"],
                "log" => $resultado["log"]);
            return $this->render('finSincro.html.twig', $params);
        }

        $params = array("ModOcupa" => $ModOcupa,
            "form" => $form->createView(),
            "accion" => 'MODIFICACIÓN');
        return $this->render("modocupa/edit.html.twig", $params);
    }

    public function deleteAction($id) {
        $entityManager = $this->getDoctrine()->getManager();
        $ModOcupa_repo = $entityManager->getRepository("AppBundle:ModOcupa");
        $ModOcupa = $ModOcupa_repo->find($id);
        $resultado = $this->sincroniza($ModOcupa->getId(), "DELETE");
        $entityManager->remove($ModOcupa);
        $entityManager->flush();
        $params = array("error" => $resultado["error"],
            "log" => $resultado["log"]);
        return $this->render('finSincro.html.twig', $params);
    }

    public function addAction(Request $request) {
        $entityManager = $this->getDoctrine()->getManager();
        $ModOcupa_repo = $entityManager->getRepository("AppBundle:ModOcupa");
        $ModOcupa = new \AppBundle\Entity\ModOcupa();

        $form = $this->createForm(\AppBundle\Form\ModOcupaType::class, $ModOcupa);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            try {
                $entityManager->persist($ModOcupa);
                $entityManager->flush();
                $this->creaEquivalencia($ModOcupa);
                $resultado = $this->sincroniza($ModOcupa->getId(), "INSERT");
                $params = array("error" => $resultado["error"],
                    "log" => $resultado["log"]);
                return $this->render('finSincro.html.twig', $params);
            } catch (UniqueConstraintViolationException $ex) {
                $status = "Error ya existe un modo ocupación con este codigo: " . $ModOcupa->getCodigo();
                $this->sesion->getFlashBag()->add("status", $status);
            } catch (Doctrine\DBAL\DBALException $ex) {
                $status = "ERROR GENERAL=" . $ex->getMessage();
                $this->sesion->getFlashBag()->add("status", $status);
            }
        }

        $params = array("ModOcupa" => $ModOcupa,
            "form" => $form->createView(),
            "accion" => 'CREACIÓN');
        return $this->render("modocupa/edit.html.twig", $params);
    }

    public function creaEquivalencia($ModOcupa) {
        $entityManager = $this->getDoctrine()->getManager();
        $Edificio_repo = $entityManager->getRepository("AppBundle:Edificio");
        $EdificioAll = $Edificio_repo->createQueryBuilder('u')
                        ->where("u.area = 'S' ")
                        ->getQuery()->getResult();
        foreach ($EdificioAll as $Edificio) {
            $EqModOcupa = new \AppBundle\Entity\EqModOcupa();
            $EqModOcupa->setEdificio($Edificio);
            $EqModOcupa->setModOcupa($ModOcupa);
            $EqModOcupa->setCodigoLoc($ModOcupa->getCodigo());
            $entityManager->persist($EqModOcupa);
            $entityManager->flush();
        }

        return true;
    }

    public function sincroniza($id, $accion) {
        $root = $this->get('kernel')->getRootDir();
        $modo = $this->getParameter('modo');
        $php_script = "php " . $root . "/scripts/sincroModOcupa.php  " . $modo . " " . $id . " " . $accion;

        $mensaje = exec($php_script, $salida, $valor);
        $resultado["error"] = $valor;
        $resultado["log"] = $salida;

        return $resultado;
    }

}
