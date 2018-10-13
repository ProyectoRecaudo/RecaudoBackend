<?php

namespace ModeloBundle\Entity;

/**
 * Tincremento
 */
class Tincremento
{
    /**
     * @var float
     */
    private $valorincremento;

    /**
     * @var string
     */
    private $resolucionincremento;

    /**
     * @var string
     */
    private $documentoresolucionincremento;

    /**
     * @var \DateTime
     */
    private $creacionincremento = 'now()';

    /**
     * @var \DateTime
     */
    private $modificacionincremento = 'now()';

    /**
     * @var boolean
     */
    private $incrementoactivo = '1';

    /**
     * @var integer
     */
    private $pkidincremento;

    /**
     * @var \ModeloBundle\Entity\Tplaza
     */
    private $fkidplaza;


    /**
     * Set valorincremento
     *
     * @param float $valorincremento
     *
     * @return Tincremento
     */
    public function setValorincremento($valorincremento)
    {
        $this->valorincremento = $valorincremento;

        return $this;
    }

    /**
     * Get valorincremento
     *
     * @return float
     */
    public function getValorincremento()
    {
        return $this->valorincremento;
    }

    /**
     * Set resolucionincremento
     *
     * @param string $resolucionincremento
     *
     * @return Tincremento
     */
    public function setResolucionincremento($resolucionincremento)
    {
        $this->resolucionincremento = $resolucionincremento;

        return $this;
    }

    /**
     * Get resolucionincremento
     *
     * @return string
     */
    public function getResolucionincremento()
    {
        return $this->resolucionincremento;
    }

    /**
     * Set documentoresolucionincremento
     *
     * @param string $documentoresolucionincremento
     *
     * @return Tincremento
     */
    public function setDocumentoresolucionincremento($documentoresolucionincremento)
    {
        $this->documentoresolucionincremento = $documentoresolucionincremento;

        return $this;
    }

    /**
     * Get documentoresolucionincremento
     *
     * @return string
     */
    public function getDocumentoresolucionincremento()
    {
        return $this->documentoresolucionincremento;
    }

    /**
     * Set creacionincremento
     *
     * @param \DateTime $creacionincremento
     *
     * @return Tincremento
     */
    public function setCreacionincremento($creacionincremento)
    {
        $this->creacionincremento = $creacionincremento;

        return $this;
    }

    /**
     * Get creacionincremento
     *
     * @return \DateTime
     */
    public function getCreacionincremento()
    {
        return $this->creacionincremento;
    }

    /**
     * Set modificacionincremento
     *
     * @param \DateTime $modificacionincremento
     *
     * @return Tincremento
     */
    public function setModificacionincremento($modificacionincremento)
    {
        $this->modificacionincremento = $modificacionincremento;

        return $this;
    }

    /**
     * Get modificacionincremento
     *
     * @return \DateTime
     */
    public function getModificacionincremento()
    {
        return $this->modificacionincremento;
    }

    /**
     * Set incrementoactivo
     *
     * @param boolean $incrementoactivo
     *
     * @return Tincremento
     */
    public function setIncrementoactivo($incrementoactivo)
    {
        $this->incrementoactivo = $incrementoactivo;

        return $this;
    }

    /**
     * Get incrementoactivo
     *
     * @return boolean
     */
    public function getIncrementoactivo()
    {
        return $this->incrementoactivo;
    }

    /**
     * Get pkidincremento
     *
     * @return integer
     */
    public function getPkidincremento()
    {
        return $this->pkidincremento;
    }

    /**
     * Set fkidplaza
     *
     * @param \ModeloBundle\Entity\Tplaza $fkidplaza
     *
     * @return Tincremento
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
}
