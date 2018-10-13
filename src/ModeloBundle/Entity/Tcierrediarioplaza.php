<?php

namespace ModeloBundle\Entity;

/**
 * Tcierrediarioplaza
 */
class Tcierrediarioplaza
{
    /**
     * @var float
     */
    private $recaudototalacuerdo;

    /**
     * @var float
     */
    private $recaudocuotaacuerdo;

    /**
     * @var float
     */
    private $recaudodeudaacuerdo;

    /**
     * @var float
     */
    private $recaudodeuda;

    /**
     * @var float
     */
    private $recaudomultas;

    /**
     * @var float
     */
    private $recaudocuotames;

    /**
     * @var float
     */
    private $recaudoanimales;

    /**
     * @var float
     */
    private $recaudopesaje;

    /**
     * @var float
     */
    private $recaudovehiculos;

    /**
     * @var float
     */
    private $recaudoparqueaderos;

    /**
     * @var float
     */
    private $recaudoeventuales;

    /**
     * @var string
     */
    private $identificacionrecaudador;

    /**
     * @var string
     */
    private $nombrerecaudador;

    /**
     * @var string
     */
    private $apellidorecaudador;

    /**
     * @var \DateTime
     */
    private $creacioncierrediarioplaza = 'now()';

    /**
     * @var \DateTime
     */
    private $modificacioncierrediarioplaza = 'now()';

    /**
     * @var integer
     */
    private $pkidcierrediarioplaza;

    /**
     * @var \ModeloBundle\Entity\Tplaza
     */
    private $fkidplaza;

    /**
     * @var \ModeloBundle\Entity\Tusuario
     */
    private $fkidusuariorecaudador;


    /**
     * Set recaudototalacuerdo
     *
     * @param float $recaudototalacuerdo
     *
     * @return Tcierrediarioplaza
     */
    public function setRecaudototalacuerdo($recaudototalacuerdo)
    {
        $this->recaudototalacuerdo = $recaudototalacuerdo;

        return $this;
    }

    /**
     * Get recaudototalacuerdo
     *
     * @return float
     */
    public function getRecaudototalacuerdo()
    {
        return $this->recaudototalacuerdo;
    }

    /**
     * Set recaudocuotaacuerdo
     *
     * @param float $recaudocuotaacuerdo
     *
     * @return Tcierrediarioplaza
     */
    public function setRecaudocuotaacuerdo($recaudocuotaacuerdo)
    {
        $this->recaudocuotaacuerdo = $recaudocuotaacuerdo;

        return $this;
    }

    /**
     * Get recaudocuotaacuerdo
     *
     * @return float
     */
    public function getRecaudocuotaacuerdo()
    {
        return $this->recaudocuotaacuerdo;
    }

    /**
     * Set recaudodeudaacuerdo
     *
     * @param float $recaudodeudaacuerdo
     *
     * @return Tcierrediarioplaza
     */
    public function setRecaudodeudaacuerdo($recaudodeudaacuerdo)
    {
        $this->recaudodeudaacuerdo = $recaudodeudaacuerdo;

        return $this;
    }

    /**
     * Get recaudodeudaacuerdo
     *
     * @return float
     */
    public function getRecaudodeudaacuerdo()
    {
        return $this->recaudodeudaacuerdo;
    }

    /**
     * Set recaudodeuda
     *
     * @param float $recaudodeuda
     *
     * @return Tcierrediarioplaza
     */
    public function setRecaudodeuda($recaudodeuda)
    {
        $this->recaudodeuda = $recaudodeuda;

        return $this;
    }

    /**
     * Get recaudodeuda
     *
     * @return float
     */
    public function getRecaudodeuda()
    {
        return $this->recaudodeuda;
    }

    /**
     * Set recaudomultas
     *
     * @param float $recaudomultas
     *
     * @return Tcierrediarioplaza
     */
    public function setRecaudomultas($recaudomultas)
    {
        $this->recaudomultas = $recaudomultas;

        return $this;
    }

    /**
     * Get recaudomultas
     *
     * @return float
     */
    public function getRecaudomultas()
    {
        return $this->recaudomultas;
    }

    /**
     * Set recaudocuotames
     *
     * @param float $recaudocuotames
     *
     * @return Tcierrediarioplaza
     */
    public function setRecaudocuotames($recaudocuotames)
    {
        $this->recaudocuotames = $recaudocuotames;

        return $this;
    }

    /**
     * Get recaudocuotames
     *
     * @return float
     */
    public function getRecaudocuotames()
    {
        return $this->recaudocuotames;
    }

    /**
     * Set recaudoanimales
     *
     * @param float $recaudoanimales
     *
     * @return Tcierrediarioplaza
     */
    public function setRecaudoanimales($recaudoanimales)
    {
        $this->recaudoanimales = $recaudoanimales;

        return $this;
    }

    /**
     * Get recaudoanimales
     *
     * @return float
     */
    public function getRecaudoanimales()
    {
        return $this->recaudoanimales;
    }

    /**
     * Set recaudopesaje
     *
     * @param float $recaudopesaje
     *
     * @return Tcierrediarioplaza
     */
    public function setRecaudopesaje($recaudopesaje)
    {
        $this->recaudopesaje = $recaudopesaje;

        return $this;
    }

    /**
     * Get recaudopesaje
     *
     * @return float
     */
    public function getRecaudopesaje()
    {
        return $this->recaudopesaje;
    }

    /**
     * Set recaudovehiculos
     *
     * @param float $recaudovehiculos
     *
     * @return Tcierrediarioplaza
     */
    public function setRecaudovehiculos($recaudovehiculos)
    {
        $this->recaudovehiculos = $recaudovehiculos;

        return $this;
    }

    /**
     * Get recaudovehiculos
     *
     * @return float
     */
    public function getRecaudovehiculos()
    {
        return $this->recaudovehiculos;
    }

    /**
     * Set recaudoparqueaderos
     *
     * @param float $recaudoparqueaderos
     *
     * @return Tcierrediarioplaza
     */
    public function setRecaudoparqueaderos($recaudoparqueaderos)
    {
        $this->recaudoparqueaderos = $recaudoparqueaderos;

        return $this;
    }

    /**
     * Get recaudoparqueaderos
     *
     * @return float
     */
    public function getRecaudoparqueaderos()
    {
        return $this->recaudoparqueaderos;
    }

    /**
     * Set recaudoeventuales
     *
     * @param float $recaudoeventuales
     *
     * @return Tcierrediarioplaza
     */
    public function setRecaudoeventuales($recaudoeventuales)
    {
        $this->recaudoeventuales = $recaudoeventuales;

        return $this;
    }

    /**
     * Get recaudoeventuales
     *
     * @return float
     */
    public function getRecaudoeventuales()
    {
        return $this->recaudoeventuales;
    }

    /**
     * Set identificacionrecaudador
     *
     * @param string $identificacionrecaudador
     *
     * @return Tcierrediarioplaza
     */
    public function setIdentificacionrecaudador($identificacionrecaudador)
    {
        $this->identificacionrecaudador = $identificacionrecaudador;

        return $this;
    }

    /**
     * Get identificacionrecaudador
     *
     * @return string
     */
    public function getIdentificacionrecaudador()
    {
        return $this->identificacionrecaudador;
    }

    /**
     * Set nombrerecaudador
     *
     * @param string $nombrerecaudador
     *
     * @return Tcierrediarioplaza
     */
    public function setNombrerecaudador($nombrerecaudador)
    {
        $this->nombrerecaudador = $nombrerecaudador;

        return $this;
    }

    /**
     * Get nombrerecaudador
     *
     * @return string
     */
    public function getNombrerecaudador()
    {
        return $this->nombrerecaudador;
    }

    /**
     * Set apellidorecaudador
     *
     * @param string $apellidorecaudador
     *
     * @return Tcierrediarioplaza
     */
    public function setApellidorecaudador($apellidorecaudador)
    {
        $this->apellidorecaudador = $apellidorecaudador;

        return $this;
    }

    /**
     * Get apellidorecaudador
     *
     * @return string
     */
    public function getApellidorecaudador()
    {
        return $this->apellidorecaudador;
    }

    /**
     * Set creacioncierrediarioplaza
     *
     * @param \DateTime $creacioncierrediarioplaza
     *
     * @return Tcierrediarioplaza
     */
    public function setCreacioncierrediarioplaza($creacioncierrediarioplaza)
    {
        $this->creacioncierrediarioplaza = $creacioncierrediarioplaza;

        return $this;
    }

    /**
     * Get creacioncierrediarioplaza
     *
     * @return \DateTime
     */
    public function getCreacioncierrediarioplaza()
    {
        return $this->creacioncierrediarioplaza;
    }

    /**
     * Set modificacioncierrediarioplaza
     *
     * @param \DateTime $modificacioncierrediarioplaza
     *
     * @return Tcierrediarioplaza
     */
    public function setModificacioncierrediarioplaza($modificacioncierrediarioplaza)
    {
        $this->modificacioncierrediarioplaza = $modificacioncierrediarioplaza;

        return $this;
    }

    /**
     * Get modificacioncierrediarioplaza
     *
     * @return \DateTime
     */
    public function getModificacioncierrediarioplaza()
    {
        return $this->modificacioncierrediarioplaza;
    }

    /**
     * Get pkidcierrediarioplaza
     *
     * @return integer
     */
    public function getPkidcierrediarioplaza()
    {
        return $this->pkidcierrediarioplaza;
    }

    /**
     * Set fkidplaza
     *
     * @param \ModeloBundle\Entity\Tplaza $fkidplaza
     *
     * @return Tcierrediarioplaza
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
     * Set fkidusuariorecaudador
     *
     * @param \ModeloBundle\Entity\Tusuario $fkidusuariorecaudador
     *
     * @return Tcierrediarioplaza
     */
    public function setFkidusuariorecaudador(\ModeloBundle\Entity\Tusuario $fkidusuariorecaudador = null)
    {
        $this->fkidusuariorecaudador = $fkidusuariorecaudador;

        return $this;
    }

    /**
     * Get fkidusuariorecaudador
     *
     * @return \ModeloBundle\Entity\Tusuario
     */
    public function getFkidusuariorecaudador()
    {
        return $this->fkidusuariorecaudador;
    }
}
