<?php

namespace ModeloBundle\Entity;

/**
 * Testadoinfraestructura
 */
class Testadoinfraestructura
{
    /**
     * @var string
     */
    private $codigoestado;

    /**
     * @var string
     */
    private $nombreestado;

    /**
     * @var string
     */
    private $descripcionestado;

    /**
     * @var boolean
     */
    private $estadoactivo = '1';

    /**
     * @var \DateTime
     */
    private $creacionestado = 'now()';

    /**
     * @var \DateTime
     */
    private $modificacionestado = 'now()';

    /**
     * @var integer
     */
    private $pkidestado;


    /**
     * Set codigoestado
     *
     * @param string $codigoestado
     *
     * @return Testadoinfraestructura
     */
    public function setCodigoestado($codigoestado)
    {
        $this->codigoestado = $codigoestado;

        return $this;
    }

    /**
     * Get codigoestado
     *
     * @return string
     */
    public function getCodigoestado()
    {
        return $this->codigoestado;
    }

    /**
     * Set nombreestado
     *
     * @param string $nombreestado
     *
     * @return Testadoinfraestructura
     */
    public function setNombreestado($nombreestado)
    {
        $this->nombreestado = $nombreestado;

        return $this;
    }

    /**
     * Get nombreestado
     *
     * @return string
     */
    public function getNombreestado()
    {
        return $this->nombreestado;
    }

    /**
     * Set descripcionestado
     *
     * @param string $descripcionestado
     *
     * @return Testadoinfraestructura
     */
    public function setDescripcionestado($descripcionestado)
    {
        $this->descripcionestado = $descripcionestado;

        return $this;
    }

    /**
     * Get descripcionestado
     *
     * @return string
     */
    public function getDescripcionestado()
    {
        return $this->descripcionestado;
    }

    /**
     * Set estadoactivo
     *
     * @param boolean $estadoactivo
     *
     * @return Testadoinfraestructura
     */
    public function setEstadoactivo($estadoactivo)
    {
        $this->estadoactivo = $estadoactivo;

        return $this;
    }

    /**
     * Get estadoactivo
     *
     * @return boolean
     */
    public function getEstadoactivo()
    {
        return $this->estadoactivo;
    }

    /**
     * Set creacionestado
     *
     * @param \DateTime $creacionestado
     *
     * @return Testadoinfraestructura
     */
    public function setCreacionestado($creacionestado)
    {
        $this->creacionestado = $creacionestado;

        return $this;
    }

    /**
     * Get creacionestado
     *
     * @return \DateTime
     */
    public function getCreacionestado()
    {
        return $this->creacionestado;
    }

    /**
     * Set modificacionestado
     *
     * @param \DateTime $modificacionestado
     *
     * @return Testadoinfraestructura
     */
    public function setModificacionestado($modificacionestado)
    {
        $this->modificacionestado = $modificacionestado;

        return $this;
    }

    /**
     * Get modificacionestado
     *
     * @return \DateTime
     */
    public function getModificacionestado()
    {
        return $this->modificacionestado;
    }

    /**
     * Get pkidestado
     *
     * @return integer
     */
    public function getPkidestado()
    {
        return $this->pkidestado;
    }
    /**
     * @var boolean
     */
    private $estadoinfraestructuraactivo = '1';


    /**
     * Set estadoinfraestructuraactivo
     *
     * @param boolean $estadoinfraestructuraactivo
     *
     * @return Testadoinfraestructura
     */
    public function setEstadoinfraestructuraactivo($estadoinfraestructuraactivo)
    {
        $this->estadoinfraestructuraactivo = $estadoinfraestructuraactivo;

        return $this;
    }

    /**
     * Get estadoinfraestructuraactivo
     *
     * @return boolean
     */
    public function getEstadoinfraestructuraactivo()
    {
        return $this->estadoinfraestructuraactivo;
    }
    /**
     * @var string
     */
    private $codigoestadoinfraestructura;

    /**
     * @var string
     */
    private $nombreestadoinfraestructura;

    /**
     * @var string
     */
    private $descripcionestadoinfraestructura;

    /**
     * @var \DateTime
     */
    private $creacionestadoinfraestructura = 'now()';

    /**
     * @var \DateTime
     */
    private $modificacionestadoinfraestructura = 'now()';

    /**
     * @var integer
     */
    private $pkidestadoinfraestructura;


    /**
     * Set codigoestadoinfraestructura
     *
     * @param string $codigoestadoinfraestructura
     *
     * @return Testadoinfraestructura
     */
    public function setCodigoestadoinfraestructura($codigoestadoinfraestructura)
    {
        $this->codigoestadoinfraestructura = $codigoestadoinfraestructura;

        return $this;
    }

    /**
     * Get codigoestadoinfraestructura
     *
     * @return string
     */
    public function getCodigoestadoinfraestructura()
    {
        return $this->codigoestadoinfraestructura;
    }

    /**
     * Set nombreestadoinfraestructura
     *
     * @param string $nombreestadoinfraestructura
     *
     * @return Testadoinfraestructura
     */
    public function setNombreestadoinfraestructura($nombreestadoinfraestructura)
    {
        $this->nombreestadoinfraestructura = $nombreestadoinfraestructura;

        return $this;
    }

    /**
     * Get nombreestadoinfraestructura
     *
     * @return string
     */
    public function getNombreestadoinfraestructura()
    {
        return $this->nombreestadoinfraestructura;
    }

    /**
     * Set descripcionestadoinfraestructura
     *
     * @param string $descripcionestadoinfraestructura
     *
     * @return Testadoinfraestructura
     */
    public function setDescripcionestadoinfraestructura($descripcionestadoinfraestructura)
    {
        $this->descripcionestadoinfraestructura = $descripcionestadoinfraestructura;

        return $this;
    }

    /**
     * Get descripcionestadoinfraestructura
     *
     * @return string
     */
    public function getDescripcionestadoinfraestructura()
    {
        return $this->descripcionestadoinfraestructura;
    }

    /**
     * Set creacionestadoinfraestructura
     *
     * @param \DateTime $creacionestadoinfraestructura
     *
     * @return Testadoinfraestructura
     */
    public function setCreacionestadoinfraestructura($creacionestadoinfraestructura)
    {
        $this->creacionestadoinfraestructura = $creacionestadoinfraestructura;

        return $this;
    }

    /**
     * Get creacionestadoinfraestructura
     *
     * @return \DateTime
     */
    public function getCreacionestadoinfraestructura()
    {
        return $this->creacionestadoinfraestructura;
    }

    /**
     * Set modificacionestadoinfraestructura
     *
     * @param \DateTime $modificacionestadoinfraestructura
     *
     * @return Testadoinfraestructura
     */
    public function setModificacionestadoinfraestructura($modificacionestadoinfraestructura)
    {
        $this->modificacionestadoinfraestructura = $modificacionestadoinfraestructura;

        return $this;
    }

    /**
     * Get modificacionestadoinfraestructura
     *
     * @return \DateTime
     */
    public function getModificacionestadoinfraestructura()
    {
        return $this->modificacionestadoinfraestructura;
    }

    /**
     * Get pkidestadoinfraestructura
     *
     * @return integer
     */
    public function getPkidestadoinfraestructura()
    {
        return $this->pkidestadoinfraestructura;
    }
}
