<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EqEpiAcc
 *
 * @ORM\Table(name="gums_eq_epiacc" 
 *           )
 * @ORM\Entity
 */
class EqEpiAcc {

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
     * @var EpiAcc|null
     *
     * @ORM\ManyToOne(targetEntity="EpiAcc")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="epiacc_id", referencedColumnName="id",onDelete="CASCADE")
     * })
     */
    private $epiAcc;

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
     * @return EqEpiAcc
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
     * @return EqEpiAcc
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
     * Set epiAcc.
     *
     * @param \AppBundle\Entity\EpiAcc|null $epiAcc
     *
     * @return EqEpiAcc
     */
    public function setEpiAcc(\AppBundle\Entity\EpiAcc $epiAcc = null) {
        $this->epiAcc = $epiAcc;

        return $this;
    }

    /**
     * Get epiAcc.
     *
     * @return \AppBundle\Entity\EpiAcc|null
     */
    public function getEpiAcc() {
        return $this->epiAcc;
    }

}
