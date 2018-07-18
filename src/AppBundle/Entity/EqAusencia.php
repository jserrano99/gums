<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EqAusencia
 *
 * @ORM\Table(name="gums_eq_ausencias" 
 *           )
 * @ORM\Entity
 */
class EqAusencia {

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
     * @ORM\Column(name="codigo_loc", type="string", length=3, nullable=false)
     */
    private $codigoLoc;

    /**
     * @var Ausencia|null
     *
     * @ORM\ManyToOne(targetEntity="Ausencia")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ausencia_id", referencedColumnName="id",onDelete="CASCADE")
     * })
     */
    private $ausencia;

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
     * @return EqAusencia
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
     * @return EqAusencia
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
     * Set ausencia.
     *
     * @param \AppBundle\Entity\Ausencia|null $ausencia
     *
     * @return EqAusencia
     */
    public function setAusencia(\AppBundle\Entity\Ausencia $ausencia = null) {
        $this->ausencia = $ausencia;

        return $this;
    }

    /**
     * Get ausencia.
     *
     * @return \AppBundle\Entity\Ausencia|null
     */
    public function getAusencia() {
        return $this->ausencia;
    }

}
