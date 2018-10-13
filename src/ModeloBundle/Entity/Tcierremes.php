<?php

namespace ModeloBundle\Entity;

/**
 * Tcierremes
 */
class Tcierremes
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
     * @var integer
     */
    private $mes;

    /**
     * @var string
     */
    private $mesletras;

    /**
     * @var integer
     */
    private $year;

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
    private $creacioncierremes = 'now()';

    /**
     * @var \DateTime
     */
    private $modificacioncierremes = 'now()';

    /**
     * @var integer
     */
    private $pkidcierremes;

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
     * @return Tcierremes
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
     * @return Tcierremes
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
     * @return Tcierremes
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
     * @return Tcierremes
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
     * @return Tcierremes
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
     * @return Tcierremes
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
     * @return Tcierremes
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
     * @return Tcierremes
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
     * @return Tcierremes
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
     * @return Tcierremes
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
     * @return Tcierremes
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
     * Set mes
     *
     * @param integer $mes
     *
     * @return Tcierremes
     */
    public function setMes($mes)
    {
        $this->mes = $mes;

        return $this;
    }

    /**
     * Get mes
     *
     * @return integer
     */
    public function getMes()
    {
        return $this->mes;
    }

    /**
     * Set mesletras
     *
     * @param string $mesletras
     *
     * @return Tcierremes
     */
    public function setMesletras($mesletras)
    {
        $this->mesletras = $mesletras;

        return $this;
    }

    /**
     * Get mesletras
     *
     * @return string
     */
    public function getMesletras()
    {
        return $this->mesletras;
    }

    /**
     * Set year
     *
     * @param integer $year
     *
     * @return Tcierremes
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return integer
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set identificacionrecaudador
     *
     * @param string $identificacionrecaudador
     *
     * @return Tcierremes
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
     * @return Tcierremes
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
     * @return Tcierremes
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
     * Set creacioncierremes
     *
     * @param \DateTime $creacioncierremes
     *
     * @return Tcierremes
     */
    public function setCreacioncierremes($creacioncierremes)
    {
        $this->creacioncierremes = $creacioncierremes;

        return $this;
    }

    /**
     * Get creacioncierremes
     *
     * @return \DateTime
     */
    public function getCreacioncierremes()
    {
        return $this->creacioncierremes;
    }

    /**
     * Set modificacioncierremes
     *
     * @param \DateTime $modificacioncierremes
     *
     * @return Tcierremes
     */
    public function setModificacioncierremes($modificacioncierremes)
    {
        $this->modificacioncierremes = $modificacioncierremes;

        return $this;
    }

    /**
     * Get modificacioncierremes
     *
     * @return \DateTime
     */
    public function getModificacioncierremes()
    {
        return $this->modificacioncierremes;
    }

    /**
     * Get pkidcierremes
     *
     * @return integer
     */
    public function getPkidcierremes()
    {
        return $this->pkidcierremes;
    }

    /**
     * Set fkidplaza
     *
     * @param \ModeloBundle\Entity\Tplaza $fkidplaza
     *
     * @return Tcierremes
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
     * @return Tcierremes
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
    /**
     * @var \ModeloBundle\Entity\Tsector
     */
    private $fkisector;


    /**
     * Set fkisector
     *
     * @param \ModeloBundle\Entity\Tsector $fkisector
     *
     * @return Tcierremes
     */
    public function setFkisector(\ModeloBundle\Entity\Tsector $fkisector = null)
    {
        $this->fkisector = $fkisector;

        return $this;
    }

    /**
     * Get fkisector
     *
     * @return \ModeloBundle\Entity\Tsector
     */
    public function getFkisector()
    {
        return $this->fkisector;
    }
}
