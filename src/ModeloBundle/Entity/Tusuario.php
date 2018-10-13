<?php

namespace ModeloBundle\Entity;

/**
 * Tusuario
 */
class Tusuario
{
    /**
     * @var integer
     */
    private $pkidusuario;

    /**
     * @var string
     */
    private $codigousuario;

    /**
     * @var integer
     */
    private $identificacion;

    /**
     * @var string
     */
    private $nombreusuario;

    /**
     * @var string
     */
    private $contrasenia;

    /**
     * @var string
     */
    private $apellido;

    /**
     * @var boolean
     */
    private $usuarioactivo = true;

    /**
     * @var \DateTime
     */
    private $creacionusuario = 'now()';

    /**
     * @var \DateTime
     */
    private $modificacionusuario = 'now()';

    /**
     * @var string
     */
    private $permisos;

    /**
     * @var \ModeloBundle\Entity\Trol
     */
    private $fkidrol;


    /**
     * Get pkidusuario
     *
     * @return integer
     */
    public function getPkidusuario()
    {
        return $this->pkidusuario;
    }

    /**
     * Set codigousuario
     *
     * @param string $codigousuario
     *
     * @return Tusuario
     */
    public function setCodigousuario($codigousuario)
    {
        $this->codigousuario = $codigousuario;

        return $this;
    }

    /**
     * Get codigousuario
     *
     * @return string
     */
    public function getCodigousuario()
    {
        return $this->codigousuario;
    }

    /**
     * Set identificacion
     *
     * @param integer $identificacion
     *
     * @return Tusuario
     */
    public function setIdentificacion($identificacion)
    {
        $this->identificacion = $identificacion;

        return $this;
    }

    /**
     * Get identificacion
     *
     * @return integer
     */
    public function getIdentificacion()
    {
        return $this->identificacion;
    }

    /**
     * Set nombreusuario
     *
     * @param string $nombreusuario
     *
     * @return Tusuario
     */
    public function setNombreusuario($nombreusuario)
    {
        $this->nombreusuario = $nombreusuario;

        return $this;
    }

    /**
     * Get nombreusuario
     *
     * @return string
     */
    public function getNombreusuario()
    {
        return $this->nombreusuario;
    }

    /**
     * Set contrasenia
     *
     * @param string $contrasenia
     *
     * @return Tusuario
     */
    public function setContrasenia($contrasenia)
    {
        $this->contrasenia = $contrasenia;

        return $this;
    }

    /**
     * Get contrasenia
     *
     * @return string
     */
    public function getContrasenia()
    {
        return $this->contrasenia;
    }

    /**
     * Set apellido
     *
     * @param string $apellido
     *
     * @return Tusuario
     */
    public function setApellido($apellido)
    {
        $this->apellido = $apellido;

        return $this;
    }

    /**
     * Get apellido
     *
     * @return string
     */
    public function getApellido()
    {
        return $this->apellido;
    }

    /**
     * Set usuarioactivo
     *
     * @param boolean $usuarioactivo
     *
     * @return Tusuario
     */
    public function setUsuarioactivo($usuarioactivo)
    {
        $this->usuarioactivo = $usuarioactivo;

        return $this;
    }

    /**
     * Get usuarioactivo
     *
     * @return boolean
     */
    public function getUsuarioactivo()
    {
        return $this->usuarioactivo;
    }

    /**
     * Set creacionusuario
     *
     * @param \DateTime $creacionusuario
     *
     * @return Tusuario
     */
    public function setCreacionusuario($creacionusuario)
    {
        $this->creacionusuario = $creacionusuario;

        return $this;
    }

    /**
     * Get creacionusuario
     *
     * @return \DateTime
     */
    public function getCreacionusuario()
    {
        return $this->creacionusuario;
    }

    /**
     * Set modificacionusuario
     *
     * @param \DateTime $modificacionusuario
     *
     * @return Tusuario
     */
    public function setModificacionusuario($modificacionusuario)
    {
        $this->modificacionusuario = $modificacionusuario;

        return $this;
    }

    /**
     * Get modificacionusuario
     *
     * @return \DateTime
     */
    public function getModificacionusuario()
    {
        return $this->modificacionusuario;
    }

    /**
     * Set permisos
     *
     * @param string $permisos
     *
     * @return Tusuario
     */
    public function setPermisos($permisos)
    {
        $this->permisos = $permisos;

        return $this;
    }

    /**
     * Get permisos
     *
     * @return string
     */
    public function getPermisos()
    {
        return $this->permisos;
    }

    /**
     * Set fkidrol
     *
     * @param \ModeloBundle\Entity\Trol $fkidrol
     *
     * @return Tusuario
     */
    public function setFkidrol(\ModeloBundle\Entity\Trol $fkidrol = null)
    {
        $this->fkidrol = $fkidrol;

        return $this;
    }

    /**
     * Get fkidrol
     *
     * @return \ModeloBundle\Entity\Trol
     */
    public function getFkidrol()
    {
        return $this->fkidrol;
    }
    /**
     * @var string
     */
    private $rutaimagen;


    /**
     * Set rutaimagen
     *
     * @param string $rutaimagen
     *
     * @return Tusuario
     */
    public function setRutaimagen($rutaimagen)
    {
        $this->rutaimagen = $rutaimagen;

        return $this;
    }

    /**
     * Get rutaimagen
     *
     * @return string
     */
    public function getRutaimagen()
    {
        return $this->rutaimagen;
    }
    /**
     * @var integer
     */
    private $numerorecibo;


    /**
     * Set numerorecibo
     *
     * @param integer $numerorecibo
     *
     * @return Tusuario
     */
    public function setNumerorecibo($numerorecibo)
    {
        $this->numerorecibo = $numerorecibo;

        return $this;
    }

    /**
     * Get numerorecibo
     *
     * @return integer
     */
    public function getNumerorecibo()
    {
        return $this->numerorecibo;
    }
}
