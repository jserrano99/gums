<?php

/**
 * Description of EqAltas (Equivalencias de Códigos de Alta) 
 *
 * @author jluis
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Edificio
 *
 * @ORM\Table(name="gums_eq_altas" 
 *           )
 * @ORM\Entity
 */
class EqAltas {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var codigoLoc
     *
     * @ORM\Column(name="codigo_loc", type="string", length=3, nullable=false)
     */
    private $codigoLoc;

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
     * @var Altas|null
     *
     * @ORM\ManyToOne(targetEntity="Altas")
     * * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="altas_id", referencedColumnName="id",onDelete="CASCADE")
     * })
     */
    private $altas;

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
     * @return EqAltas
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
     * @return EqAltas
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
     * Set altas.
     *
     * @param \AppBundle\Entity\Altas|null $altas
     *
     * @return EqAltas
     */
    public function setAltas(\AppBundle\Entity\Altas $altas = null) {
        $this->altas = $altas;

        return $this;
    }

    /**
     * Get altas.
     *
     * @return \AppBundle\Entity\Altas|null
     */
    public function getAltas() {
        return $this->altas;
    }

}
