<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EqTipoIlt
 *
 * @ORM\Table(name="gums_eq_tipo_ilt" 
 *           )
 * @ORM\Entity
 */
class EqTipoIlt {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Edificio|null
     *
     * @ORM\ManyToOne(targetEntity="Edificio")
     * * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="edificio_id", referencedColumnName="id")
     * })
     */
    private $edificio;

    /**
     * @var codigoLoc
     *
     * @ORM\Column(name="codigo_loc", type="string", length=1, nullable=false)
     */
    private $codigoLoc;

    /**
     * @var TipoIlt|null
     *
     * @ORM\ManyToOne(targetEntity="TipoIlt")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipoIlt_id", referencedColumnName="id",onDelete="CASCADE")
     * })
     */
    private $tipoIlt;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set codigoLoc.
     *
     * @param string $codigoLoc
     *
     * @return EqTipoIlt
     */
    public function setCodigoLoc($codigoLoc) {
        $this->codigoLoc = $codigoLoc;

        return $this;
    }

    /**
     * Get codigoLoc.
     *
     * @return string
     */
    public function getCodigoLoc() {
        return $this->codigoLoc;
    }

    /**
     * Set edificio.
     *
     * @param \AppBundle\Entity\Edificio|null $edificio
     *
     * @return EqTipoIlt
     */
    public function setEdificio(\AppBundle\Entity\Edificio $edificio = null) {
        $this->edificio = $edificio;

        return $this;
    }

    /**
     * Get edificio.
     *
     * @return \AppBundle\Entity\Edificio|null
     */
    public function getEdificio() {
        return $this->edificio;
    }

    /**
     * Set tipoIlt.
     *
     * @param \AppBundle\Entity\TipoIlt|null $tipoIlt
     *
     * @return EqTipoIlt
     */
    public function setTipoIlt(\AppBundle\Entity\TipoIlt $tipoIlt = null) {
        $this->tipoIlt = $tipoIlt;

        return $this;
    }

    /**
     * Get tipoIlt.
     *
     * @return \AppBundle\Entity\TipoIlt|null
     */
    public function getTipoIlt() {
        return $this->tipoIlt;
    }

}
