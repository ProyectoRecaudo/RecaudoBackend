<?php

namespace ModeloBundle\Entity;

/**
 * Ttipopuesto
 */
class Ttipopuesto
{
    /**
     * @var string
     */
    private $codigotipopuesto;

    /**
     * @var string
     */
    private $nombretipopuesto;

    /**
     * @var string
     */
    private $descripciontipopuesto;

    /**
     * @var boolean
     */
    private $tipopuestoactivo = '1';

    /**
     * @var \DateTime
     */
    private $creaciontipopuesto = 'now()';

    /**
     * @var \DateTime
     */
    private $modificaciontipopuesto = 'now()';

    /**
     * @var integer
     */
    private $pkidtipopuesto;


    /**
     * Set codigotipopuesto
     *
     * @param string $codigotipopuesto
     *
     * @return Ttipopuesto
     */
    public function setCodigotipopuesto($codigotipopuesto)
    {
        $this->codigotipopuesto = $codigotipopuesto;

        return $this;
    }

    /**
     * Get codigotipopuesto
     *
     * @return string
     */
    public function getCodigotipopuesto()
    {
        return $this->codigotipopuesto;
    }

    /**
     * Set nombretipopuesto
     *
     * @param string $nombretipopuesto
     *
     * @return Ttipopuesto
     */
    public function setNombretipopuesto($nombretipopuesto)
    {
        $this->nombretipopuesto = $nombretipopuesto;

        return $this;
    }

    /**
     * Get nombretipopuesto
     *
     * @return string
     */
    public function getNombretipopuesto()
    {
        return $this->nombretipopuesto;
    }

    /**
     * Set descripciontipopuesto
     *
     * @param string $descripciontipopuesto
     *
     * @return Ttipopuesto
     */
    public function setDescripciontipopuesto($descripciontipopuesto)
    {
        $this->descripciontipopuesto = $descripciontipopuesto;

        return $this;
    }

    /**
     * Get descripciontipopuesto
     *
     * @return string
     */
    public function getDescripciontipopuesto()
    {
        return $this->descripciontipopuesto;
    }

    /**
     * Set tipopuestoactivo
     *
     * @param boolean $tipopuestoactivo
     *
     * @return Ttipopuesto
     */
    public function setTipopuestoactivo($tipopuestoactivo)
    {
        $this->tipopuestoactivo = $tipopuestoactivo;

        return $this;
    }

    /**
     * Get tipopuestoactivo
     *
     * @return boolean
     */
    public function getTipopuestoactivo()
    {
        return $this->tipopuestoactivo;
    }

    /**
     * Set creaciontipopuesto
     *
     * @param \DateTime $creaciontipopuesto
     *
     * @return Ttipopuesto
     */
    public function setCreaciontipopuesto($creaciontipopuesto)
    {
        $this->creaciontipopuesto = $creaciontipopuesto;

        return $this;
    }

    /**
     * Get creaciontipopuesto
     *
     * @return \DateTime
     */
    public function getCreaciontipopuesto()
    {
        return $this->creaciontipopuesto;
    }

    /**
     * Set modificaciontipopuesto
     *
     * @param \DateTime $modificaciontipopuesto
     *
     * @return Ttipopuesto
     */
    public function setModificaciontipopuesto($modificaciontipopuesto)
    {
        $this->modificaciontipopuesto = $modificaciontipopuesto;

        return $this;
    }

    /**
     * Get modificaciontipopuesto
     *
     * @return \DateTime
     */
    public function getModificaciontipopuesto()
    {
        return $this->modificaciontipopuesto;
    }

    /**
     * Get pkidtipopuesto
     *
     * @return integer
     */
    public function getPkidtipopuesto()
    {
        return $this->pkidtipopuesto;
    }
}
