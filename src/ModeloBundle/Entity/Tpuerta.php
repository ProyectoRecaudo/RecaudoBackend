<?php

namespace ModeloBundle\Entity;

/**
 * Tpuerta
 */
class Tpuerta
{
    /**
     * @var string
     */
    private $codigopuerta;

    /**
     * @var string
     */
    private $nombrepuerta;

    /**
     * @var \DateTime
     */
    private $creacionpuerta = 'now()';

    /**
     * @var \DateTime
     */
    private $modificacionpuerta = 'now()';

    /**
     * @var boolean
     */
    private $puertaactivo = '1';

    /**
     * @var integer
     */
    private $pkidpuerta;

    /**
     * @var \ModeloBundle\Entity\Tplaza
     */
    private $fkidplaza;


    /**
     * Set codigopuerta
     *
     * @param string $codigopuerta
     *
     * @return Tpuerta
     */
    public function setCodigopuerta($codigopuerta)
    {
        $this->codigopuerta = $codigopuerta;

        return $this;
    }

    /**
     * Get codigopuerta
     *
     * @return string
     */
    public function getCodigopuerta()
    {
        return $this->codigopuerta;
    }

    /**
     * Set nombrepuerta
     *
     * @param string $nombrepuerta
     *
     * @return Tpuerta
     */
    public function setNombrepuerta($nombrepuerta)
    {
        $this->nombrepuerta = $nombrepuerta;

        return $this;
    }

    /**
     * Get nombrepuerta
     *
     * @return string
     */
    public function getNombrepuerta()
    {
        return $this->nombrepuerta;
    }

    /**
     * Set creacionpuerta
     *
     * @param \DateTime $creacionpuerta
     *
     * @return Tpuerta
     */
    public function setCreacionpuerta($creacionpuerta)
    {
        $this->creacionpuerta = $creacionpuerta;

        return $this;
    }

    /**
     * Get creacionpuerta
     *
     * @return \DateTime
     */
    public function getCreacionpuerta()
    {
        return $this->creacionpuerta;
    }

    /**
     * Set modificacionpuerta
     *
     * @param \DateTime $modificacionpuerta
     *
     * @return Tpuerta
     */
    public function setModificacionpuerta($modificacionpuerta)
    {
        $this->modificacionpuerta = $modificacionpuerta;

        return $this;
    }

    /**
     * Get modificacionpuerta
     *
     * @return \DateTime
     */
    public function getModificacionpuerta()
    {
        return $this->modificacionpuerta;
    }

    /**
     * Set puertaactivo
     *
     * @param boolean $puertaactivo
     *
     * @return Tpuerta
     */
    public function setPuertaactivo($puertaactivo)
    {
        $this->puertaactivo = $puertaactivo;

        return $this;
    }

    /**
     * Get puertaactivo
     *
     * @return boolean
     */
    public function getPuertaactivo()
    {
        return $this->puertaactivo;
    }


    /**
     * Set fkidplaza
     *
     * @param \ModeloBundle\Entity\Tplaza $fkidplaza
     *
     * @return Tpuerta
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
     * Get pkidpuerta
     *
     * @return integer
     */
    public function getPkidpuerta()
    {
        return $this->pkidpuerta;
    }
}
