<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EqModOcupa
 *
 * @ORM\Table(name="gums_eq_modocupa" 
 *           )
 * @ORM\Entity
 */
class EqModOcupa {

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
     * @var ModOcupa|null
     *
     * @ORM\ManyToOne(targetEntity="ModOcupa")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="modocupa_id", referencedColumnName="id",onDelete="CASCADE")
     * })
     */
    private $modOcupa;

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
     * @return EqModOcupa
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
     * @return EqModOcupa
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
     * Set modOcupa.
     *
     * @param \AppBundle\Entity\ModOcupa|null $modOcupa
     *
     * @return EqModOcupa
     */
    public function setModOcupa(\AppBundle\Entity\ModOcupa $modOcupa = null) {
        $this->modOcupa = $modOcupa;

        return $this;
    }

    /**
     * Get modOcupa.
     *
     * @return \AppBundle\Entity\ModOcupa|null
     */
    public function getModOcupa() {
        return $this->modOcupa;
    }

}
