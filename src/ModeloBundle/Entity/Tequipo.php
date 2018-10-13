<?php

namespace ModeloBundle\Entity;

/**
 * Tequipo
 */
class Tequipo
{
    /**
     * @var string
     */
    private $codigoequipo;

    /**
     * @var string
     */
    private $nombrequipo;

    /**
     * @var string
     */
    private $descripcionequipo;

    /**
     * @var string
     */
    private $identificacionequipo;

    /**
     * @var boolean
     */
    private $equipoactivo = '1';

    /**
     * @var \DateTime
     */
    private $creacionequipo = 'now()';

    /**
     * @var \DateTime
     */
    private $modificacionequipo = 'now()';

    /**
     * @var integer
     */
    private $pkidequipo;

    /**
     * @var \ModeloBundle\Entity\Tusuario
     */
    private $fkidusuario;


    /**
     * Set codigoequipo
     *
     * @param string $codigoequipo
     *
     * @return Tequipo
     */
    public function setCodigoequipo($codigoequipo)
    {
        $this->codigoequipo = $codigoequipo;

        return $this;
    }

    /**
     * Get codigoequipo
     *
     * @return string
     */
    public function getCodigoequipo()
    {
        return $this->codigoequipo;
    }

    /**
     * Set nombrequipo
     *
     * @param string $nombrequipo
     *
     * @return Tequipo
     */
    public function setNombrequipo($nombrequipo)
    {
        $this->nombrequipo = $nombrequipo;

        return $this;
    }

    /**
     * Get nombrequipo
     *
     * @return string
     */
    public function getNombrequipo()
    {
        return $this->nombrequipo;
    }

    /**
     * Set descripcionequipo
     *
     * @param string $descripcionequipo
     *
     * @return Tequipo
     */
    public function setDescripcionequipo($descripcionequipo)
    {
        $this->descripcionequipo = $descripcionequipo;

        return $this;
    }

    /**
     * Get descripcionequipo
     *
     * @return string
     */
    public function getDescripcionequipo()
    {
        return $this->descripcionequipo;
    }

    /**
     * Set identificacionequipo
     *
     * @param string $identificacionequipo
     *
     * @return Tequipo
     */
    public function setIdentificacionequipo($identificacionequipo)
    {
        $this->identificacionequipo = $identificacionequipo;

        return $this;
    }

    /**
     * Get identificacionequipo
     *
     * @return string
     */
    public function getIdentificacionequipo()
    {
        return $this->identificacionequipo;
    }

    /**
     * Set equipoactivo
     *
     * @param boolean $equipoactivo
     *
     * @return Tequipo
     */
    public function setEquipoactivo($equipoactivo)
    {
        $this->equipoactivo = $equipoactivo;

        return $this;
    }

    /**
     * Get equipoactivo
     *
     * @return boolean
     */
    public function getEquipoactivo()
    {
        return $this->equipoactivo;
    }

    /**
     * Set creacionequipo
     *
     * @param \DateTime $creacionequipo
     *
     * @return Tequipo
     */
    public function setCreacionequipo($creacionequipo)
    {
        $this->creacionequipo = $creacionequipo;

        return $this;
    }

    /**
     * Get creacionequipo
     *
     * @return \DateTime
     */
    public function getCreacionequipo()
    {
        return $this->creacionequipo;
    }

    /**
     * Set modificacionequipo
     *
     * @param \DateTime $modificacionequipo
     *
     * @return Tequipo
     */
    public function setModificacionequipo($modificacionequipo)
    {
        $this->modificacionequipo = $modificacionequipo;

        return $this;
    }

    /**
     * Get modificacionequipo
     *
     * @return \DateTime
     */
    public function getModificacionequipo()
    {
        return $this->modificacionequipo;
    }

    /**
     * Get pkidequipo
     *
     * @return integer
     */
    public function getPkidequipo()
    {
        return $this->pkidequipo;
    }

    /**
     * Set fkidusuario
     *
     * @param \ModeloBundle\Entity\Tusuario $fkidusuario
     *
     * @return Tequipo
     */
    public function setFkidusuario(\ModeloBundle\Entity\Tusuario $fkidusuario = null)
    {
        $this->fkidusuario = $fkidusuario;

        return $this;
    }

    /**
     * Get fkidusuario
     *
     * @return \ModeloBundle\Entity\Tusuario
     */
    public function getFkidusuario()
    {
        return $this->fkidusuario;
    }
}
