<?php

/**
 * Description of Edificio
 *
 * @author jluis
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Edificio
 *
 * @ORM\Table(name="gums_edificio")
 * 
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EdificioRepository")
 */
class Edificio {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    
    /**
     * @var codigo
     *
     * @ORM\Column(name="codigo", type="string", length=2, nullable=false)
     */
    private $codigo;
    
    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="string", length=255, nullable=true)
     */
    private $descripcion;

    /**
     * @var codigo
     *
     * @ORM\Column(name="area", type="string", length=1, nullable=false)
     */
    private $area;
    
    public function getId() {
        return $this->id;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getCodigo() {
        return $this->codigo;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
        return $this;
    }

    public function setCodigo($codigo) {
        $this->codigo = $codigo;
        return $this;
    }


    public function __toString() {
        return $this->descripcion;
    }


    /**
     * Set area.
     *
     * @param string $area
     *
     * @return Edificio
     */
    public function setArea($area)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get area.
     *
     * @return string
     */
    public function getArea()
    {
        return $this->area;
    }
}
