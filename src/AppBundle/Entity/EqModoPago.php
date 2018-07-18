<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EqModoPago
 *
 * @ORM\Table(name="gums_eq_modopago" 
 *           )
 * @ORM\Entity
 */
class EqModoPago {

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
     * @var ModoPago|null
     *
     * @ORM\ManyToOne(targetEntity="ModoPago")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="modopago_id", referencedColumnName="id",onDelete="CASCADE")
     * })
     */
    private $modoPago;

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
     * @return EqModoPago
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
     * @return EqModoPago
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
     * Set modoPago.
     *
     * @param \AppBundle\Entity\ModoPago|null $modoPago
     *
     * @return EqModoPago
     */
    public function setModoPago(\AppBundle\Entity\ModoPago $modoPago = null) {
        $this->modoPago = $modoPago;

        return $this;
    }

    /**
     * Get modoPago.
     *
     * @return \AppBundle\Entity\ModoPago|null
     */
    public function getModoPago() {
        return $this->modoPago;
    }

}
