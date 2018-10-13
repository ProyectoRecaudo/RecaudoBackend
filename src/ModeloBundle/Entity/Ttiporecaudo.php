<?php

namespace ModeloBundle\Entity;

/**
 * Ttiporecaudo
 */
class Ttiporecaudo
{
    /**
     * @var integer
     */
    private $pkidtiporecaudo;

    /**
     * @var string
     */
    private $codigotiporecaudo;

    /**
     * @var string
     */
    private $nombretiporecaudo;

    /**
     * @var boolean
     */
    private $tiporecaudoactivo;

    /**
     * @var \DateTime
     */
    private $creaciontiporecaudo = 'now()';

    /**
     * @var \DateTime
     */
    private $modificaciontiporecaudo = 'now()';


    /**
     * Get pkidtiporecaudo
     *
     * @return integer
     */
    public function getPkidtiporecaudo()
    {
        return $this->pkidtiporecaudo;
    }

    /**
     * Set codigotiporecaudo
     *
     * @param string $codigotiporecaudo
     *
     * @return Ttiporecaudo
     */
    public function setCodigotiporecaudo($codigotiporecaudo)
    {
        $this->codigotiporecaudo = $codigotiporecaudo;

        return $this;
    }

    /**
     * Get codigotiporecaudo
     *
     * @return string
     */
    public function getCodigotiporecaudo()
    {
        return $this->codigotiporecaudo;
    }

    /**
     * Set nombretiporecaudo
     *
     * @param string $nombretiporecaudo
     *
     * @return Ttiporecaudo
     */
    public function setNombretiporecaudo($nombretiporecaudo)
    {
        $this->nombretiporecaudo = $nombretiporecaudo;

        return $this;
    }

    /**
     * Get nombretiporecaudo
     *
     * @return string
     */
    public function getNombretiporecaudo()
    {
        return $this->nombretiporecaudo;
    }

    /**
     * Set tiporecaudoactivo
     *
     * @param boolean $tiporecaudoactivo
     *
     * @return Ttiporecaudo
     */
    public function setTiporecaudoactivo($tiporecaudoactivo)
    {
        $this->tiporecaudoactivo = $tiporecaudoactivo;

        return $this;
    }

    /**
     * Get tiporecaudoactivo
     *
     * @return boolean
     */
    public function getTiporecaudoactivo()
    {
        return $this->tiporecaudoactivo;
    }

    /**
     * Set creaciontiporecaudo
     *
     * @param \DateTime $creaciontiporecaudo
     *
     * @return Ttiporecaudo
     */
    public function setCreaciontiporecaudo($creaciontiporecaudo)
    {
        $this->creaciontiporecaudo = $creaciontiporecaudo;

        return $this;
    }

    /**
     * Get creaciontiporecaudo
     *
     * @return \DateTime
     */
    public function getCreaciontiporecaudo()
    {
        return $this->creaciontiporecaudo;
    }

    /**
     * Set modificaciontiporecaudo
     *
     * @param \DateTime $modificaciontiporecaudo
     *
     * @return Ttiporecaudo
     */
    public function setModificaciontiporecaudo($modificaciontiporecaudo)
    {
        $this->modificaciontiporecaudo = $modificaciontiporecaudo;

        return $this;
    }

    /**
     * Get modificaciontiporecaudo
     *
     * @return \DateTime
     */
    public function getModificaciontiporecaudo()
    {
        return $this->modificaciontiporecaudo;
    }
}
