<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EqMoviPat
 *
 * @ORM\Table(name="gums_eq_movipat" 
 *           )
 * @ORM\Entity
 */
class EqMoviPat {

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
     *   @ORM\JoinColumn(name="edificio_id", referencedColumnName="id",)
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
     * @var MoviPat|null
     *
     * @ORM\ManyToOne(targetEntity="MoviPat")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="movipat_id", referencedColumnName="id",onDelete="CASCADE")
     * })
     */
    private $moviPat;

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
     * @return EqMoviPat
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
     * @return EqMoviPat
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
     * Set moviPat.
     *
     * @param MoviPat|null $moviPat
     *
     * @return EqMoviPat
     */
    public function setMoviPat(\AppBundle\Entity\MoviPat $moviPat = null) {
        $this->moviPat = $moviPat;

        return $this;
    }

    /**
     * Get moviPat.
     *
     * @return \AppBundle\Entity\MoviPat|null
     */
    public function getMoviPat() {
        return $this->moviPat;
    }

}
