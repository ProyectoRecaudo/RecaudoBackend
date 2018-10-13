<?php

namespace ModeloBundle\Entity;

/**
 * Tsector
 */
class Tsector
{
    /**
     * @var integer
     */
    private $pkidsector;

    /**
     * @var string
     */
    private $codigosector;

    /**
     * @var string
     */
    private $nombresector;

    /**
     * @var boolean
     */
    private $sectoractivo = true;

    /**
     * @var \DateTime
     */
    private $creacionsector = 'now()';

    /**
     * @var \DateTime
     */
    private $modificacionsector = 'now()';

    /**
     * @var \ModeloBundle\Entity\Tplaza
     */
    private $fkidplaza;

    /**
     * @var \ModeloBundle\Entity\Ttiposector
     */
    private $fkidtiposector;

    /**
     * @var \ModeloBundle\Entity\Tzona
     */
    private $fkidzona;


    /**
     * Get pkidsector
     *
     * @return integer
     */
    public function getPkidsector()
    {
        return $this->pkidsector;
    }

    /**
     * Set codigosector
     *
     * @param string $codigosector
     *
     * @return Tsector
     */
    public function setCodigosector($codigosector)
    {
        $this->codigosector = $codigosector;

        return $this;
    }

    /**
     * Get codigosector
     *
     * @return string
     */
    public function getCodigosector()
    {
        return $this->codigosector;
    }

    /**
     * Set nombresector
     *
     * @param string $nombresector
     *
     * @return Tsector
     */
    public function setNombresector($nombresector)
    {
        $this->nombresector = $nombresector;

        return $this;
    }

    /**
     * Get nombresector
     *
     * @return string
     */
    public function getNombresector()
    {
        return $this->nombresector;
    }

    /**
     * Set sectoractivo
     *
     * @param boolean $sectoractivo
     *
     * @return Tsector
     */
    public function setSectoractivo($sectoractivo)
    {
        $this->sectoractivo = $sectoractivo;

        return $this;
    }

    /**
     * Get sectoractivo
     *
     * @return boolean
     */
    public function getSectoractivo()
    {
        return $this->sectoractivo;
    }

    /**
     * Set creacionsector
     *
     * @param \DateTime $creacionsector
     *
     * @return Tsector
     */
    public function setCreacionsector($creacionsector)
    {
        $this->creacionsector = $creacionsector;

        return $this;
    }

    /**
     * Get creacionsector
     *
     * @return \DateTime
     */
    public function getCreacionsector()
    {
        return $this->creacionsector;
    }

    /**
     * Set modificacionsector
     *
     * @param \DateTime $modificacionsector
     *
     * @return Tsector
     */
    public function setModificacionsector($modificacionsector)
    {
        $this->modificacionsector = $modificacionsector;

        return $this;
    }

    /**
     * Get modificacionsector
     *
     * @return \DateTime
     */
    public function getModificacionsector()
    {
        return $this->modificacionsector;
    }

    /**
     * Set fkidplaza
     *
     * @param \ModeloBundle\Entity\Tplaza $fkidplaza
     *
     * @return Tsector
     */
    public function setFkidplaza(\ModeloBundle\Entity\Tplaza $fkidplaza = null)
    {
        $this->fkidplaza = $fkidplaza;

        return $this;
    }

    /**
     * Get fkidplaza
     *
     * @return \ModeloBundle\Entity\Tplaza
     */
    public function getFkidplaza()
    {
        return $this->fkidplaza;
    }

    /**
     * Set fkidtiposector
     *
     * @param \ModeloBundle\Entity\Ttiposector $fkidtiposector
     *
     * @return Tsector
     */
    public function setFkidtiposector(\ModeloBundle\Entity\Ttiposector $fkidtiposector = null)
    {
        $this->fkidtiposector = $fkidtiposector;

        return $this;
    }

    /**
     * Get fkidtiposector
     *
     * @return \ModeloBundle\Entity\Ttiposector
     */
    public function getFkidtiposector()
    {
        return $this->fkidtiposector;
    }

    /**
     * Set fkidzona
     *
     * @param \ModeloBundle\Entity\Tzona $fkidzona
     *
     * @return Tsector
     */
    public function setFkidzona(\ModeloBundle\Entity\Tzona $fkidzona = null)
    {
        $this->fkidzona = $fkidzona;

        return $this;
    }

    /**
     * Get fkidzona
     *
     * @return \ModeloBundle\Entity\Tzona
     */
    public function getFkidzona()
    {
        return $this->fkidzona;
    }
}
