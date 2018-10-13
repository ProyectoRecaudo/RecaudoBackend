<?php

namespace ModeloBundle\Entity;

/**
 * Ttipoparqueadero
 */
class Ttipoparqueadero
{
    /**
     * @var string
     */
    private $codigotipoparqueadero;

    /**
     * @var string
     */
    private $nombretipoparqueadero;

    /**
     * @var string
     */
    private $descripciontipoparqueadero;

    /**
     * @var boolean
     */
    private $tipoparqueaderoactivo = '1';

    /**
     * @var \DateTime
     */
    private $creaciontipoparqueadero = 'now()';

    /**
     * @var \DateTime
     */
    private $modificaciontipoparqueadero = 'now()';

    /**
     * @var integer
     */
    private $pkidtipoparqueadero;


    /**
     * Set codigotipoparqueadero
     *
     * @param string $codigotipoparqueadero
     *
     * @return Ttipoparqueadero
     */
    public function setCodigotipoparqueadero($codigotipoparqueadero)
    {
        $this->codigotipoparqueadero = $codigotipoparqueadero;

        return $this;
    }

    /**
     * Get codigotipoparqueadero
     *
     * @return string
     */
    public function getCodigotipoparqueadero()
    {
        return $this->codigotipoparqueadero;
    }

    /**
     * Set nombretipoparqueadero
     *
     * @param string $nombretipoparqueadero
     *
     * @return Ttipoparqueadero
     */
    public function setNombretipoparqueadero($nombretipoparqueadero)
    {
        $this->nombretipoparqueadero = $nombretipoparqueadero;

        return $this;
    }

    /**
     * Get nombretipoparqueadero
     *
     * @return string
     */
    public function getNombretipoparqueadero()
    {
        return $this->nombretipoparqueadero;
    }

    /**
     * Set descripciontipoparqueadero
     *
     * @param string $descripciontipoparqueadero
     *
     * @return Ttipoparqueadero
     */
    public function setDescripciontipoparqueadero($descripciontipoparqueadero)
    {
        $this->descripciontipoparqueadero = $descripciontipoparqueadero;

        return $this;
    }

    /**
     * Get descripciontipoparqueadero
     *
     * @return string
     */
    public function getDescripciontipoparqueadero()
    {
        return $this->descripciontipoparqueadero;
    }

    /**
     * Set tipoparqueaderoactivo
     *
     * @param boolean $tipoparqueaderoactivo
     *
     * @return Ttipoparqueadero
     */
    public function setTipoparqueaderoactivo($tipoparqueaderoactivo)
    {
        $this->tipoparqueaderoactivo = $tipoparqueaderoactivo;

        return $this;
    }

    /**
     * Get tipoparqueaderoactivo
     *
     * @return boolean
     */
    public function getTipoparqueaderoactivo()
    {
        return $this->tipoparqueaderoactivo;
    }

    /**
     * Set creaciontipoparqueadero
     *
     * @param \DateTime $creaciontipoparqueadero
     *
     * @return Ttipoparqueadero
     */
    public function setCreaciontipoparqueadero($creaciontipoparqueadero)
    {
        $this->creaciontipoparqueadero = $creaciontipoparqueadero;

        return $this;
    }

    /**
     * Get creaciontipoparqueadero
     *
     * @return \DateTime
     */
    public function getCreaciontipoparqueadero()
    {
        return $this->creaciontipoparqueadero;
    }

    /**
     * Set modificaciontipoparqueadero
     *
     * @param \DateTime $modificaciontipoparqueadero
     *
     * @return Ttipoparqueadero
     */
    public function setModificaciontipoparqueadero($modificaciontipoparqueadero)
    {
        $this->modificaciontipoparqueadero = $modificaciontipoparqueadero;

        return $this;
    }

    /**
     * Get modificaciontipoparqueadero
     *
     * @return \DateTime
     */
    public function getModificaciontipoparqueadero()
    {
        return $this->modificaciontipoparqueadero;
    }

    /**
     * Get pkidtipoparqueadero
     *
     * @return integer
     */
    public function getPkidtipoparqueadero()
    {
        return $this->pkidtipoparqueadero;
    }
}
