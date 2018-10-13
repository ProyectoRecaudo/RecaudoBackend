<?php

namespace ModeloBundle\Entity;

/**
 * Tfactura
 */
class Tfactura
{
    /**
     * @var integer
     */
    private $numerofactura;

    /**
     * @var string
     */
    private $nombrebeneficiario;

    /**
     * @var string
     */
    private $identificacionbeneficiario;

    /**
     * @var string
     */
    private $mesfactura;

    /**
     * @var \DateTime
     */
    private $fechalimite;

    /**
     * @var float
     */
    private $saldo;

    /**
     * @var string
     */
    private $nombreusuario;

    /**
     * @var boolean
     */
    private $facturacancelada = '';

    /**
     * @var \DateTime
     */
    private $creacionfactura = 'now()';

    /**
     * @var \DateTime
     */
    private $modificacionfactura = 'now()';

    /**
     * @var integer
     */
    private $idfactura;

    /**
     * @var \ModeloBundle\Entity\Ttarifapuesto
     */
    private $fkidtarifapuesto;


    /**
     * Set numerofactura
     *
     * @param integer $numerofactura
     *
     * @return Tfactura
     */
    public function setNumerofactura($numerofactura)
    {
        $this->numerofactura = $numerofactura;

        return $this;
    }

    /**
     * Get numerofactura
     *
     * @return integer
     */
    public function getNumerofactura()
    {
        return $this->numerofactura;
    }

    /**
     * Set nombrebeneficiario
     *
     * @param string $nombrebeneficiario
     *
     * @return Tfactura
     */
    public function setNombrebeneficiario($nombrebeneficiario)
    {
        $this->nombrebeneficiario = $nombrebeneficiario;

        return $this;
    }

    /**
     * Get nombrebeneficiario
     *
     * @return string
     */
    public function getNombrebeneficiario()
    {
        return $this->nombrebeneficiario;
    }

    /**
     * Set identificacionbeneficiario
     *
     * @param string $identificacionbeneficiario
     *
     * @return Tfactura
     */
    public function setIdentificacionbeneficiario($identificacionbeneficiario)
    {
        $this->identificacionbeneficiario = $identificacionbeneficiario;

        return $this;
    }

    /**
     * Get identificacionbeneficiario
     *
     * @return string
     */
    public function getIdentificacionbeneficiario()
    {
        return $this->identificacionbeneficiario;
    }

    /**
     * Set mesfactura
     *
     * @param string $mesfactura
     *
     * @return Tfactura
     */
    public function setMesfactura($mesfactura)
    {
        $this->mesfactura = $mesfactura;

        return $this;
    }

    /**
     * Get mesfactura
     *
     * @return string
     */
    public function getMesfactura()
    {
        return $this->mesfactura;
    }

    /**
     * Set fechalimite
     *
     * @param \DateTime $fechalimite
     *
     * @return Tfactura
     */
    public function setFechalimite($fechalimite)
    {
        $this->fechalimite = $fechalimite;

        return $this;
    }

    /**
     * Get fechalimite
     *
     * @return \DateTime
     */
    public function getFechalimite()
    {
        return $this->fechalimite;
    }

    /**
     * Set saldo
     *
     * @param float $saldo
     *
     * @return Tfactura
     */
    public function setSaldo($saldo)
    {
        $this->saldo = $saldo;

        return $this;
    }

    /**
     * Get saldo
     *
     * @return float
     */
    public function getSaldo()
    {
        return $this->saldo;
    }

    /**
     * Set nombreusuario
     *
     * @param string $nombreusuario
     *
     * @return Tfactura
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
     * Set facturacancelada
     *
     * @param boolean $facturacancelada
     *
     * @return Tfactura
     */
    public function setFacturacancelada($facturacancelada)
    {
        $this->facturacancelada = $facturacancelada;

        return $this;
    }

    /**
     * Get facturacancelada
     *
     * @return boolean
     */
    public function getFacturacancelada()
    {
        return $this->facturacancelada;
    }

    /**
     * Set creacionfactura
     *
     * @param \DateTime $creacionfactura
     *
     * @return Tfactura
     */
    public function setCreacionfactura($creacionfactura)
    {
        $this->creacionfactura = $creacionfactura;

        return $this;
    }

    /**
     * Get creacionfactura
     *
     * @return \DateTime
     */
    public function getCreacionfactura()
    {
        return $this->creacionfactura;
    }

    /**
     * Set modificacionfactura
     *
     * @param \DateTime $modificacionfactura
     *
     * @return Tfactura
     */
    public function setModificacionfactura($modificacionfactura)
    {
        $this->modificacionfactura = $modificacionfactura;

        return $this;
    }

    /**
     * Get modificacionfactura
     *
     * @return \DateTime
     */
    public function getModificacionfactura()
    {
        return $this->modificacionfactura;
    }

    /**
     * Get idfactura
     *
     * @return integer
     */
    public function getIdfactura()
    {
        return $this->idfactura;
    }

    /**
     * Set fkidtarifapuesto
     *
     * @param \ModeloBundle\Entity\Ttarifapuesto $fkidtarifapuesto
     *
     * @return Tfactura
     */
    public function setFkidtarifapuesto(\ModeloBundle\Entity\Ttarifapuesto $fkidtarifapuesto = null)
    {
        $this->fkidtarifapuesto = $fkidtarifapuesto;

        return $this;
    }

    /**
     * Get fkidtarifapuesto
     *
     * @return \ModeloBundle\Entity\Ttarifapuesto
     */
    public function getFkidtarifapuesto()
    {
        return $this->fkidtarifapuesto;
    }
    /**
     * @var float
     */
    private $deudatotal;

    /**
     * @var float
     */
    private $tarifapuesto;

    /**
     * @var string
     */
    private $numeroacuerdo;

    /**
     * @var float
     */
    private $valorcuotaacuerdo;

    /**
     * @var float
     */
    private $numeromulta;

    /**
     * @var float
     */
    private $valorcuotamulta;

    /**
     * @var float
     */
    private $valorinteres;

    /**
     * @var boolean
     */
    private $facturapagada = '';

    /**
     * @var integer
     */
    private $pkidfactura;

    /**
     * @var \ModeloBundle\Entity\Tasignacionpuesto
     */
    private $fkidasignacionpuesto;


    /**
     * Set deudatotal
     *
     * @param float $deudatotal
     *
     * @return Tfactura
     */
    public function setDeudatotal($deudatotal)
    {
        $this->deudatotal = $deudatotal;

        return $this;
    }

    /**
     * Get deudatotal
     *
     * @return float
     */
    public function getDeudatotal()
    {
        return $this->deudatotal;
    }

    /**
     * Set tarifapuesto
     *
     * @param float $tarifapuesto
     *
     * @return Tfactura
     */
    public function setTarifapuesto($tarifapuesto)
    {
        $this->tarifapuesto = $tarifapuesto;

        return $this;
    }

    /**
     * Get tarifapuesto
     *
     * @return float
     */
    public function getTarifapuesto()
    {
        return $this->tarifapuesto;
    }

    /**
     * Set numeroacuerdo
     *
     * @param string $numeroacuerdo
     *
     * @return Tfactura
     */
    public function setNumeroacuerdo($numeroacuerdo)
    {
        $this->numeroacuerdo = $numeroacuerdo;

        return $this;
    }

    /**
     * Get numeroacuerdo
     *
     * @return string
     */
    public function getNumeroacuerdo()
    {
        return $this->numeroacuerdo;
    }

    /**
     * Set valorcuotaacuerdo
     *
     * @param float $valorcuotaacuerdo
     *
     * @return Tfactura
     */
    public function setValorcuotaacuerdo($valorcuotaacuerdo)
    {
        $this->valorcuotaacuerdo = $valorcuotaacuerdo;

        return $this;
    }

    /**
     * Get valorcuotaacuerdo
     *
     * @return float
     */
    public function getValorcuotaacuerdo()
    {
        return $this->valorcuotaacuerdo;
    }

    /**
     * Set numeromulta
     *
     * @param float $numeromulta
     *
     * @return Tfactura
     */
    public function setNumeromulta($numeromulta)
    {
        $this->numeromulta = $numeromulta;

        return $this;
    }

    /**
     * Get numeromulta
     *
     * @return float
     */
    public function getNumeromulta()
    {
        return $this->numeromulta;
    }

    /**
     * Set valorcuotamulta
     *
     * @param float $valorcuotamulta
     *
     * @return Tfactura
     */
    public function setValorcuotamulta($valorcuotamulta)
    {
        $this->valorcuotamulta = $valorcuotamulta;

        return $this;
    }

    /**
     * Get valorcuotamulta
     *
     * @return float
     */
    public function getValorcuotamulta()
    {
        return $this->valorcuotamulta;
    }

    /**
     * Set valorinteres
     *
     * @param float $valorinteres
     *
     * @return Tfactura
     */
    public function setValorinteres($valorinteres)
    {
        $this->valorinteres = $valorinteres;

        return $this;
    }

    /**
     * Get valorinteres
     *
     * @return float
     */
    public function getValorinteres()
    {
        return $this->valorinteres;
    }

    /**
     * Set facturapagada
     *
     * @param boolean $facturapagada
     *
     * @return Tfactura
     */
    public function setFacturapagada($facturapagada)
    {
        $this->facturapagada = $facturapagada;

        return $this;
    }

    /**
     * Get facturapagada
     *
     * @return boolean
     */
    public function getFacturapagada()
    {
        return $this->facturapagada;
    }

    /**
     * Get pkidfactura
     *
     * @return integer
     */
    public function getPkidfactura()
    {
        return $this->pkidfactura;
    }

    /**
     * Set fkidasignacionpuesto
     *
     * @param \ModeloBundle\Entity\Tasignacionpuesto $fkidasignacionpuesto
     *
     * @return Tfactura
     */
    public function setFkidasignacionpuesto(\ModeloBundle\Entity\Tasignacionpuesto $fkidasignacionpuesto = null)
    {
        $this->fkidasignacionpuesto = $fkidasignacionpuesto;

        return $this;
    }

    /**
     * Get fkidasignacionpuesto
     *
     * @return \ModeloBundle\Entity\Tasignacionpuesto
     */
    public function getFkidasignacionpuesto()
    {
        return $this->fkidasignacionpuesto;
    }
    /**
     * @var float
     */
    private $valormultas;

    /**
     * @var float
     */
    private $saldoasignacion;

    /**
     * @var float
     */
    private $saldomultas;

    /**
     * @var string
     */
    private $mesfacturaletras;

    /**
     * @var integer
     */
    private $year;

    /**
     * @var float
     */
    private $saldoacuerdo;

    /**
     * @var string
     */
    private $nombrepuesto;

    /**
     * @var integer
     */
    private $fkidplaza;

    /**
     * @var integer
     */
    private $fkidzona;

    /**
     * @var integer
     */
    private $fkidsector;

    /**
     * @var string
     */
    private $nombreplaza;

    /**
     * @var string
     */
    private $nombrezona;

    /**
     * @var string
     */
    private $nombresector;

    /**
     * @var float
     */
    private $totalpagado;

    /**
     * @var integer
     */
    private $mesfacturanumero;

    /**
     * @var integer
     */
    private $fkidpuesto;

    /**
     * @var integer
     */
    private $fkidacuerdo;

    /**
     * @var integer
     */
    private $cuotasincumplidas;

    /**
     * @var integer
     */
    private $cuotaspagadas;

    /**
     * @var float
     */
    private $totalapagarmes;

    /**
     * @var \DateTime
     */
    private $fechapagototal;

    /**
     * @var float
     */
    private $saldodeuda;

    /**
     * @var float
     */
    private $saldodeudaacuerdo;

    /**
     * @var float
     */
    private $saldoporpagar;

    /**
     * @var integer
     */
    private $debermes;

    /**
     * @var integer
     */
    private $deberyear;


    /**
     * Set valormultas
     *
     * @param float $valormultas
     *
     * @return Tfactura
     */
    public function setValormultas($valormultas)
    {
        $this->valormultas = $valormultas;

        return $this;
    }

    /**
     * Get valormultas
     *
     * @return float
     */
    public function getValormultas()
    {
        return $this->valormultas;
    }

    /**
     * Set saldoasignacion
     *
     * @param float $saldoasignacion
     *
     * @return Tfactura
     */
    public function setSaldoasignacion($saldoasignacion)
    {
        $this->saldoasignacion = $saldoasignacion;

        return $this;
    }

    /**
     * Get saldoasignacion
     *
     * @return float
     */
    public function getSaldoasignacion()
    {
        return $this->saldoasignacion;
    }

    /**
     * Set saldomultas
     *
     * @param float $saldomultas
     *
     * @return Tfactura
     */
    public function setSaldomultas($saldomultas)
    {
        $this->saldomultas = $saldomultas;

        return $this;
    }

    /**
     * Get saldomultas
     *
     * @return float
     */
    public function getSaldomultas()
    {
        return $this->saldomultas;
    }

    /**
     * Set mesfacturaletras
     *
     * @param string $mesfacturaletras
     *
     * @return Tfactura
     */
    public function setMesfacturaletras($mesfacturaletras)
    {
        $this->mesfacturaletras = $mesfacturaletras;

        return $this;
    }

    /**
     * Get mesfacturaletras
     *
     * @return string
     */
    public function getMesfacturaletras()
    {
        return $this->mesfacturaletras;
    }

    /**
     * Set year
     *
     * @param integer $year
     *
     * @return Tfactura
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
     * Set saldoacuerdo
     *
     * @param float $saldoacuerdo
     *
     * @return Tfactura
     */
    public function setSaldoacuerdo($saldoacuerdo)
    {
        $this->saldoacuerdo = $saldoacuerdo;

        return $this;
    }

    /**
     * Get saldoacuerdo
     *
     * @return float
     */
    public function getSaldoacuerdo()
    {
        return $this->saldoacuerdo;
    }

    /**
     * Set nombrepuesto
     *
     * @param string $nombrepuesto
     *
     * @return Tfactura
     */
    public function setNombrepuesto($nombrepuesto)
    {
        $this->nombrepuesto = $nombrepuesto;

        return $this;
    }

    /**
     * Get nombrepuesto
     *
     * @return string
     */
    public function getNombrepuesto()
    {
        return $this->nombrepuesto;
    }

    /**
     * Set fkidplaza
     *
     * @param integer $fkidplaza
     *
     * @return Tfactura
     */
    public function setFkidplaza($fkidplaza)
    {
        $this->fkidplaza = $fkidplaza;

        return $this;
    }

    /**
     * Get fkidplaza
     *
     * @return integer
     */
    public function getFkidplaza()
    {
        return $this->fkidplaza;
    }

    /**
     * Set fkidzona
     *
     * @param integer $fkidzona
     *
     * @return Tfactura
     */
    public function setFkidzona($fkidzona)
    {
        $this->fkidzona = $fkidzona;

        return $this;
    }

    /**
     * Get fkidzona
     *
     * @return integer
     */
    public function getFkidzona()
    {
        return $this->fkidzona;
    }

    /**
     * Set fkidsector
     *
     * @param integer $fkidsector
     *
     * @return Tfactura
     */
    public function setFkidsector($fkidsector)
    {
        $this->fkidsector = $fkidsector;

        return $this;
    }

    /**
     * Get fkidsector
     *
     * @return integer
     */
    public function getFkidsector()
    {
        return $this->fkidsector;
    }

    /**
     * Set nombreplaza
     *
     * @param string $nombreplaza
     *
     * @return Tfactura
     */
    public function setNombreplaza($nombreplaza)
    {
        $this->nombreplaza = $nombreplaza;

        return $this;
    }

    /**
     * Get nombreplaza
     *
     * @return string
     */
    public function getNombreplaza()
    {
        return $this->nombreplaza;
    }

    /**
     * Set nombrezona
     *
     * @param string $nombrezona
     *
     * @return Tfactura
     */
    public function setNombrezona($nombrezona)
    {
        $this->nombrezona = $nombrezona;

        return $this;
    }

    /**
     * Get nombrezona
     *
     * @return string
     */
    public function getNombrezona()
    {
        return $this->nombrezona;
    }

    /**
     * Set nombresector
     *
     * @param string $nombresector
     *
     * @return Tfactura
     */
    public function setNombresector($nombresector)
    {
        $this->nombresector = $nombresector;

        return $this;
    }

    /**
     * Get nombresector
     *
     * @return string
     */
    public function getNombresector()
    {
        return $this->nombresector;
    }

    /**
     * Set totalpagado
     *
     * @param float $totalpagado
     *
     * @return Tfactura
     */
    public function setTotalpagado($totalpagado)
    {
        $this->totalpagado = $totalpagado;

        return $this;
    }

    /**
     * Get totalpagado
     *
     * @return float
     */
    public function getTotalpagado()
    {
        return $this->totalpagado;
    }

    /**
     * Set mesfacturanumero
     *
     * @param integer $mesfacturanumero
     *
     * @return Tfactura
     */
    public function setMesfacturanumero($mesfacturanumero)
    {
        $this->mesfacturanumero = $mesfacturanumero;

        return $this;
    }

    /**
     * Get mesfacturanumero
     *
     * @return integer
     */
    public function getMesfacturanumero()
    {
        return $this->mesfacturanumero;
    }

    /**
     * Set fkidpuesto
     *
     * @param integer $fkidpuesto
     *
     * @return Tfactura
     */
    public function setFkidpuesto($fkidpuesto)
    {
        $this->fkidpuesto = $fkidpuesto;

        return $this;
    }

    /**
     * Get fkidpuesto
     *
     * @return integer
     */
    public function getFkidpuesto()
    {
        return $this->fkidpuesto;
    }

    /**
     * Set fkidacuerdo
     *
     * @param integer $fkidacuerdo
     *
     * @return Tfactura
     */
    public function setFkidacuerdo($fkidacuerdo)
    {
        $this->fkidacuerdo = $fkidacuerdo;

        return $this;
    }

    /**
     * Get fkidacuerdo
     *
     * @return integer
     */
    public function getFkidacuerdo()
    {
        return $this->fkidacuerdo;
    }

    /**
     * Set cuotasincumplidas
     *
     * @param integer $cuotasincumplidas
     *
     * @return Tfactura
     */
    public function setCuotasincumplidas($cuotasincumplidas)
    {
        $this->cuotasincumplidas = $cuotasincumplidas;

        return $this;
    }

    /**
     * Get cuotasincumplidas
     *
     * @return integer
     */
    public function getCuotasincumplidas()
    {
        return $this->cuotasincumplidas;
    }

    /**
     * Set cuotaspagadas
     *
     * @param integer $cuotaspagadas
     *
     * @return Tfactura
     */
    public function setCuotaspagadas($cuotaspagadas)
    {
        $this->cuotaspagadas = $cuotaspagadas;

        return $this;
    }

    /**
     * Get cuotaspagadas
     *
     * @return integer
     */
    public function getCuotaspagadas()
    {
        return $this->cuotaspagadas;
    }

    /**
     * Set totalapagarmes
     *
     * @param float $totalapagarmes
     *
     * @return Tfactura
     */
    public function setTotalapagarmes($totalapagarmes)
    {
        $this->totalapagarmes = $totalapagarmes;

        return $this;
    }

    /**
     * Get totalapagarmes
     *
     * @return float
     */
    public function getTotalapagarmes()
    {
        return $this->totalapagarmes;
    }

    /**
     * Set fechapagototal
     *
     * @param \DateTime $fechapagototal
     *
     * @return Tfactura
     */
    public function setFechapagototal($fechapagototal)
    {
        $this->fechapagototal = $fechapagototal;

        return $this;
    }

    /**
     * Get fechapagototal
     *
     * @return \DateTime
     */
    public function getFechapagototal()
    {
        return $this->fechapagototal;
    }

    /**
     * Set saldodeuda
     *
     * @param float $saldodeuda
     *
     * @return Tfactura
     */
    public function setSaldodeuda($saldodeuda)
    {
        $this->saldodeuda = $saldodeuda;

        return $this;
    }

    /**
     * Get saldodeuda
     *
     * @return float
     */
    public function getSaldodeuda()
    {
        return $this->saldodeuda;
    }

    /**
     * Set saldodeudaacuerdo
     *
     * @param float $saldodeudaacuerdo
     *
     * @return Tfactura
     */
    public function setSaldodeudaacuerdo($saldodeudaacuerdo)
    {
        $this->saldodeudaacuerdo = $saldodeudaacuerdo;

        return $this;
    }

    /**
     * Get saldodeudaacuerdo
     *
     * @return float
     */
    public function getSaldodeudaacuerdo()
    {
        return $this->saldodeudaacuerdo;
    }

    /**
     * Set saldoporpagar
     *
     * @param float $saldoporpagar
     *
     * @return Tfactura
     */
    public function setSaldoporpagar($saldoporpagar)
    {
        $this->saldoporpagar = $saldoporpagar;

        return $this;
    }

    /**
     * Get saldoporpagar
     *
     * @return float
     */
    public function getSaldoporpagar()
    {
        return $this->saldoporpagar;
    }

    /**
     * Set debermes
     *
     * @param integer $debermes
     *
     * @return Tfactura
     */
    public function setDebermes($debermes)
    {
        $this->debermes = $debermes;

        return $this;
    }

    /**
     * Get debermes
     *
     * @return integer
     */
    public function getDebermes()
    {
        return $this->debermes;
    }

    /**
     * Set deberyear
     *
     * @param integer $deberyear
     *
     * @return Tfactura
     */
    public function setDeberyear($deberyear)
    {
        $this->deberyear = $deberyear;

        return $this;
    }

    /**
     * Get deberyear
     *
     * @return integer
     */
    public function getDeberyear()
    {
        return $this->deberyear;
    }
    /**
     * @var boolean
     */
    private $facturaactivo;


    /**
     * Set facturaactivo
     *
     * @param boolean $facturaactivo
     *
     * @return Tfactura
     */
    public function setFacturaactivo($facturaactivo)
    {
        $this->facturaactivo = $facturaactivo;

        return $this;
    }

    /**
     * Get facturaactivo
     *
     * @return boolean
     */
    public function getFacturaactivo()
    {
        return $this->facturaactivo;
    }
    /**
     * @var float
     */
    private $abonototalacuerdo;

    /**
     * @var float
     */
    private $abonocuotaacuerdo;

    /**
     * @var float
     */
    private $abonodeudaacuerdo;

    /**
     * @var float
     */
    private $abonodeuda;

    /**
     * @var float
     */
    private $abonomultas;

    /**
     * @var float
     */
    private $abonocuotames;


    /**
     * Set abonototalacuerdo
     *
     * @param float $abonototalacuerdo
     *
     * @return Tfactura
     */
    public function setAbonototalacuerdo($abonototalacuerdo)
    {
        $this->abonototalacuerdo = $abonototalacuerdo;

        return $this;
    }

    /**
     * Get abonototalacuerdo
     *
     * @return float
     */
    public function getAbonototalacuerdo()
    {
        return $this->abonototalacuerdo;
    }

    /**
     * Set abonocuotaacuerdo
     *
     * @param float $abonocuotaacuerdo
     *
     * @return Tfactura
     */
    public function setAbonocuotaacuerdo($abonocuotaacuerdo)
    {
        $this->abonocuotaacuerdo = $abonocuotaacuerdo;

        return $this;
    }

    /**
     * Get abonocuotaacuerdo
     *
     * @return float
     */
    public function getAbonocuotaacuerdo()
    {
        return $this->abonocuotaacuerdo;
    }

    /**
     * Set abonodeudaacuerdo
     *
     * @param float $abonodeudaacuerdo
     *
     * @return Tfactura
     */
    public function setAbonodeudaacuerdo($abonodeudaacuerdo)
    {
        $this->abonodeudaacuerdo = $abonodeudaacuerdo;

        return $this;
    }

    /**
     * Get abonodeudaacuerdo
     *
     * @return float
     */
    public function getAbonodeudaacuerdo()
    {
        return $this->abonodeudaacuerdo;
    }

    /**
     * Set abonodeuda
     *
     * @param float $abonodeuda
     *
     * @return Tfactura
     */
    public function setAbonodeuda($abonodeuda)
    {
        $this->abonodeuda = $abonodeuda;

        return $this;
    }

    /**
     * Get abonodeuda
     *
     * @return float
     */
    public function getAbonodeuda()
    {
        return $this->abonodeuda;
    }

    /**
     * Set abonomultas
     *
     * @param float $abonomultas
     *
     * @return Tfactura
     */
    public function setAbonomultas($abonomultas)
    {
        $this->abonomultas = $abonomultas;

        return $this;
    }

    /**
     * Get abonomultas
     *
     * @return float
     */
    public function getAbonomultas()
    {
        return $this->abonomultas;
    }

    /**
     * Set abonocuotames
     *
     * @param float $abonocuotames
     *
     * @return Tfactura
     */
    public function setAbonocuotames($abonocuotames)
    {
        $this->abonocuotames = $abonocuotames;

        return $this;
    }

    /**
     * Get abonocuotames
     *
     * @return float
     */
    public function getAbonocuotames()
    {
        return $this->abonocuotames;
    }
    /**
     * @var string
     */
    private $numeroresolucionasignacion;

    /**
     * @var \ModeloBundle\Entity\Tbeneficiario
     */
    private $fkidbeneficiario;


    /**
     * Set numeroresolucionasignacion
     *
     * @param string $numeroresolucionasignacion
     *
     * @return Tfactura
     */
    public function setNumeroresolucionasignacion($numeroresolucionasignacion)
    {
        $this->numeroresolucionasignacion = $numeroresolucionasignacion;

        return $this;
    }

    /**
     * Get numeroresolucionasignacion
     *
     * @return string
     */
    public function getNumeroresolucionasignacion()
    {
        return $this->numeroresolucionasignacion;
    }

    /**
     * Set fkidbeneficiario
     *
     * @param \ModeloBundle\Entity\Tbeneficiario $fkidbeneficiario
     *
     * @return Tfactura
     */
    public function setFkidbeneficiario(\ModeloBundle\Entity\Tbeneficiario $fkidbeneficiario = null)
    {
        $this->fkidbeneficiario = $fkidbeneficiario;

        return $this;
    }

    /**
     * Get fkidbeneficiario
     *
     * @return \ModeloBundle\Entity\Tbeneficiario
     */
    public function getFkidbeneficiario()
    {
        return $this->fkidbeneficiario;
    }
    /**
     * @var string
     */
    private $numeroresolucionasignacionpuesto;


    /**
     * Set numeroresolucionasignacionpuesto
     *
     * @param string $numeroresolucionasignacionpuesto
     *
     * @return Tfactura
     */
    public function setNumeroresolucionasignacionpuesto($numeroresolucionasignacionpuesto)
    {
        $this->numeroresolucionasignacionpuesto = $numeroresolucionasignacionpuesto;

        return $this;
    }

    /**
     * Get numeroresolucionasignacionpuesto
     *
     * @return string
     */
    public function getNumeroresolucionasignacionpuesto()
    {
        return $this->numeroresolucionasignacionpuesto;
    }
}
