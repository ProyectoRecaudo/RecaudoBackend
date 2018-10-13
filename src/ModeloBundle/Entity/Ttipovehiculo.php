<?php

namespace ModeloBundle\Entity;

/**
 * Ttipovehiculo
 */
class Ttipovehiculo
{
    /**
     * @var string
     */
    private $codigotipovehiculo;

    /**
     * @var string
     */
    private $nombretipovehiculo;

    /**
     * @var string
     */
    private $descripciontipovehiculo;

    /**
     * @var \DateTime
     */
    private $creaciontipovehiculo = 'now()';

    /**
     * @var \DateTime
     */
    private $modificaciontipovehiculo = 'now()';

    /**
     * @var boolean
     */
    private $tipovehiculoactivo = '1';

    /**
     * @var integer
     */
    private $pkidtipovehiculo;


    /**
     * Set codigotipovehiculo
     *
     * @param string $codigotipovehiculo
     *
     * @return Ttipovehiculo
     */
    public function setCodigotipovehiculo($codigotipovehiculo)
    {
        $this->codigotipovehiculo = $codigotipovehiculo;

        return $this;
    }

    /**
     * Get codigotipovehiculo
     *
     * @return string
     */
    public function getCodigotipovehiculo()
    {
        return $this->codigotipovehiculo;
    }

    /**
     * Set nombretipovehiculo
     *
     * @param string $nombretipovehiculo
     *
     * @return Ttipovehiculo
     */
    public function setNombretipovehiculo($nombretipovehiculo)
    {
        $this->nombretipovehiculo = $nombretipovehiculo;

        return $this;
    }

    /**
     * Get nombretipovehiculo
     *
     * @return string
     */
    public function getNombretipovehiculo()
    {
        return $this->nombretipovehiculo;
    }

    /**
     * Set descripciontipovehiculo
     *
     * @param string $descripciontipovehiculo
     *
     * @return Ttipovehiculo
     */
    public function setDescripciontipovehiculo($descripciontipovehiculo)
    {
        $this->descripciontipovehiculo = $descripciontipovehiculo;

        return $this;
    }

    /**
     * Get descripciontipovehiculo
     *
     * @return string
     */
    public function getDescripciontipovehiculo()
    {
        return $this->descripciontipovehiculo;
    }

    /**
     * Set creaciontipovehiculo
     *
     * @param \DateTime $creaciontipovehiculo
     *
     * @return Ttipovehiculo
     */
    public function setCreaciontipovehiculo($creaciontipovehiculo)
    {
        $this->creaciontipovehiculo = $creaciontipovehiculo;

        return $this;
    }

    /**
     * Get creaciontipovehiculo
     *
     * @return \DateTime
     */
    public function getCreaciontipovehiculo()
    {
        return $this->creaciontipovehiculo;
    }

    /**
     * Set modificaciontipovehiculo
     *
     * @param \DateTime $modificaciontipovehiculo
     *
     * @return Ttipovehiculo
     */
    public function setModificaciontipovehiculo($modificaciontipovehiculo)
    {
        $this->modificaciontipovehiculo = $modificaciontipovehiculo;

        return $this;
    }

    /**
     * Get modificaciontipovehiculo
     *
     * @return \DateTime
     */
    public function getModificaciontipovehiculo()
    {
        return $this->modificaciontipovehiculo;
    }

    /**
     * Set tipovehiculoactivo
     *
     * @param boolean $tipovehiculoactivo
     *
     * @return Ttipovehiculo
     */
    public function setTipovehiculoactivo($tipovehiculoactivo)
    {
        $this->tipovehiculoactivo = $tipovehiculoactivo;

        return $this;
    }

    /**
     * Get tipovehiculoactivo
     *
     * @return boolean
     */
    public function getTipovehiculoactivo()
    {
        return $this->tipovehiculoactivo;
    }

    /**
     * Get pkidtipovehiculo
     *
     * @return integer
     */
    public function getPkidtipovehiculo()
    {
        return $this->pkidtipovehiculo;
    }
}
