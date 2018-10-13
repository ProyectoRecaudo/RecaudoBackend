<?php

namespace ModeloBundle\Entity;

/**
 * Trecibopuesto
 */
class Trecibopuesto
{
    /**
     * @var string
     */
    private $numerorecibopuesto;

    /**
     * @var float
     */
    private $valorecibopuesto;

    /**
     * @var string
     */
    private $numerofacturaabonopuesto;

    /**
     * @var float
     */
    private $valorabonopuesto;

    /**
     * @var string
     */
    private $numerocuerdopuesto;

    /**
     * @var float
     */
    private $valorabonoacuerdopuesto;

    /**
     * @var string
     */
    private $mesrecibopuesto;

    /**
     * @var string
     */
    private $nombrebeneficiario;

    /**
     * @var string
     */
    private $identificacionbeneficiario;

    /**
     * @var \DateTime
     */
    private $creacionrecibopuesto = 'now()';

    /**
     * @var \DateTime
     */
    private $modificacionrecibopuesto;

    /**
     * @var integer
     */
    private $pkidrecibopuesto;

    /**
     * @var \ModeloBundle\Entity\Trecaudopuesto
     */
    private $fkidrecaudopuesto;


    /**
     * Set numerorecibopuesto
     *
     * @param string $numerorecibopuesto
     *
     * @return Trecibopuesto
     */
    public function setNumerorecibopuesto($numerorecibopuesto)
    {
        $this->numerorecibopuesto = $numerorecibopuesto;

        return $this;
    }

    /**
     * Get numerorecibopuesto
     *
     * @return string
     */
    public function getNumerorecibopuesto()
    {
        return $this->numerorecibopuesto;
    }

    /**
     * Set valorecibopuesto
     *
     * @param float $valorecibopuesto
     *
     * @return Trecibopuesto
     */
    public function setValorecibopuesto($valorecibopuesto)
    {
        $this->valorecibopuesto = $valorecibopuesto;

        return $this;
    }

    /**
     * Get valorecibopuesto
     *
     * @return float
     */
    public function getValorecibopuesto()
    {
        return $this->valorecibopuesto;
    }

    /**
     * Set numerofacturaabonopuesto
     *
     * @param string $numerofacturaabonopuesto
     *
     * @return Trecibopuesto
     */
    public function setNumerofacturaabonopuesto($numerofacturaabonopuesto)
    {
        $this->numerofacturaabonopuesto = $numerofacturaabonopuesto;

        return $this;
    }

    /**
     * Get numerofacturaabonopuesto
     *
     * @return string
     */
    public function getNumerofacturaabonopuesto()
    {
        return $this->numerofacturaabonopuesto;
    }

    /**
     * Set valorabonopuesto
     *
     * @param float $valorabonopuesto
     *
     * @return Trecibopuesto
     */
    public function setValorabonopuesto($valorabonopuesto)
    {
        $this->valorabonopuesto = $valorabonopuesto;

        return $this;
    }

    /**
     * Get valorabonopuesto
     *
     * @return float
     */
    public function getValorabonopuesto()
    {
        return $this->valorabonopuesto;
    }

    /**
     * Set numerocuerdopuesto
     *
     * @param string $numerocuerdopuesto
     *
     * @return Trecibopuesto
     */
    public function setNumerocuerdopuesto($numerocuerdopuesto)
    {
        $this->numerocuerdopuesto = $numerocuerdopuesto;

        return $this;
    }

    /**
     * Get numerocuerdopuesto
     *
     * @return string
     */
    public function getNumerocuerdopuesto()
    {
        return $this->numerocuerdopuesto;
    }

    /**
     * Set valorabonoacuerdopuesto
     *
     * @param float $valorabonoacuerdopuesto
     *
     * @return Trecibopuesto
     */
    public function setValorabonoacuerdopuesto($valorabonoacuerdopuesto)
    {
        $this->valorabonoacuerdopuesto = $valorabonoacuerdopuesto;

        return $this;
    }

    /**
     * Get valorabonoacuerdopuesto
     *
     * @return float
     */
    public function getValorabonoacuerdopuesto()
    {
        return $this->valorabonoacuerdopuesto;
    }

    /**
     * Set mesrecibopuesto
     *
     * @param string $mesrecibopuesto
     *
     * @return Trecibopuesto
     */
    public function setMesrecibopuesto($mesrecibopuesto)
    {
        $this->mesrecibopuesto = $mesrecibopuesto;

        return $this;
    }

    /**
     * Get mesrecibopuesto
     *
     * @return string
     */
    public function getMesrecibopuesto()
    {
        return $this->mesrecibopuesto;
    }

    /**
     * Set nombrebeneficiario
     *
     * @param string $nombrebeneficiario
     *
     * @return Trecibopuesto
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
     * @return Trecibopuesto
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
     * Set creacionrecibopuesto
     *
     * @param \DateTime $creacionrecibopuesto
     *
     * @return Trecibopuesto
     */
    public function setCreacionrecibopuesto($creacionrecibopuesto)
    {
        $this->creacionrecibopuesto = $creacionrecibopuesto;

        return $this;
    }

    /**
     * Get creacionrecibopuesto
     *
     * @return \DateTime
     */
    public function getCreacionrecibopuesto()
    {
        return $this->creacionrecibopuesto;
    }

    /**
     * Set modificacionrecibopuesto
     *
     * @param \DateTime $modificacionrecibopuesto
     *
     * @return Trecibopuesto
     */
    public function setModificacionrecibopuesto($modificacionrecibopuesto)
    {
        $this->modificacionrecibopuesto = $modificacionrecibopuesto;

        return $this;
    }

    /**
     * Get modificacionrecibopuesto
     *
     * @return \DateTime
     */
    public function getModificacionrecibopuesto()
    {
        return $this->modificacionrecibopuesto;
    }

    /**
     * Get pkidrecibopuesto
     *
     * @return integer
     */
    public function getPkidrecibopuesto()
    {
        return $this->pkidrecibopuesto;
    }

    /**
     * Set fkidrecaudopuesto
     *
     * @param \ModeloBundle\Entity\Trecaudopuesto $fkidrecaudopuesto
     *
     * @return Trecibopuesto
     */
    public function setFkidrecaudopuesto(\ModeloBundle\Entity\Trecaudopuesto $fkidrecaudopuesto = null)
    {
        $this->fkidrecaudopuesto = $fkidrecaudopuesto;

        return $this;
    }

    /**
     * Get fkidrecaudopuesto
     *
     * @return \ModeloBundle\Entity\Trecaudopuesto
     */
    public function getFkidrecaudopuesto()
    {
        return $this->fkidrecaudopuesto;
    }
    /**
     * @var string
     */
    private $numerofactura;

    /**
     * @var float
     */
    private $saldo;

    /**
     * @var float
     */
    private $valorrecaudo;

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
     * @var string
     */
    private $mesfactura;

    /**
     * @var \DateTime
     */
    private $creacionrecibo = 'now()';

    /**
     * @var \DateTime
     */
    private $modificacionrecibo = 'now()';

    /**
     * @var float
     */
    private $valorpagado;

    /**
     * @var string
     */
    private $nombrepagador;

    /**
     * @var string
     */
    private $identificacionpagador;

    /**
     * @var string
     */
    private $numerorecibo;

    /**
     * @var \ModeloBundle\Entity\Tfactura
     */
    private $fkidfactura;

    /**
     * @var \ModeloBundle\Entity\Ttercero
     */
    private $fkidtercero;


    /**
     * Set numerofactura
     *
     * @param string $numerofactura
     *
     * @return Trecibopuesto
     */
    public function setNumerofactura($numerofactura)
    {
        $this->numerofactura = $numerofactura;

        return $this;
    }

    /**
     * Get numerofactura
     *
     * @return string
     */
    public function getNumerofactura()
    {
        return $this->numerofactura;
    }

    /**
     * Set saldo
     *
     * @param float $saldo
     *
     * @return Trecibopuesto
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
     * Set valorrecaudo
     *
     * @param float $valorrecaudo
     *
     * @return Trecibopuesto
     */
    public function setValorrecaudo($valorrecaudo)
    {
        $this->valorrecaudo = $valorrecaudo;

        return $this;
    }

    /**
     * Get valorrecaudo
     *
     * @return float
     */
    public function getValorrecaudo()
    {
        return $this->valorrecaudo;
    }

    /**
     * Set numeroacuerdo
     *
     * @param string $numeroacuerdo
     *
     * @return Trecibopuesto
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
     * @return Trecibopuesto
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
     * @return Trecibopuesto
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
     * @return Trecibopuesto
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
     * @return Trecibopuesto
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
     * Set mesfactura
     *
     * @param string $mesfactura
     *
     * @return Trecibopuesto
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
     * Set creacionrecibo
     *
     * @param \DateTime $creacionrecibo
     *
     * @return Trecibopuesto
     */
    public function setCreacionrecibo($creacionrecibo)
    {
        $this->creacionrecibo = $creacionrecibo;

        return $this;
    }

    /**
     * Get creacionrecibo
     *
     * @return \DateTime
     */
    public function getCreacionrecibo()
    {
        return $this->creacionrecibo;
    }

    /**
     * Set modificacionrecibo
     *
     * @param \DateTime $modificacionrecibo
     *
     * @return Trecibopuesto
     */
    public function setModificacionrecibo($modificacionrecibo)
    {
        $this->modificacionrecibo = $modificacionrecibo;

        return $this;
    }

    /**
     * Get modificacionrecibo
     *
     * @return \DateTime
     */
    public function getModificacionrecibo()
    {
        return $this->modificacionrecibo;
    }

    /**
     * Set valorpagado
     *
     * @param float $valorpagado
     *
     * @return Trecibopuesto
     */
    public function setValorpagado($valorpagado)
    {
        $this->valorpagado = $valorpagado;

        return $this;
    }

    /**
     * Get valorpagado
     *
     * @return float
     */
    public function getValorpagado()
    {
        return $this->valorpagado;
    }

    /**
     * Set nombrepagador
     *
     * @param string $nombrepagador
     *
     * @return Trecibopuesto
     */
    public function setNombrepagador($nombrepagador)
    {
        $this->nombrepagador = $nombrepagador;

        return $this;
    }

    /**
     * Get nombrepagador
     *
     * @return string
     */
    public function getNombrepagador()
    {
        return $this->nombrepagador;
    }

    /**
     * Set identificacionpagador
     *
     * @param string $identificacionpagador
     *
     * @return Trecibopuesto
     */
    public function setIdentificacionpagador($identificacionpagador)
    {
        $this->identificacionpagador = $identificacionpagador;

        return $this;
    }

    /**
     * Get identificacionpagador
     *
     * @return string
     */
    public function getIdentificacionpagador()
    {
        return $this->identificacionpagador;
    }

    /**
     * Set numerorecibo
     *
     * @param string $numerorecibo
     *
     * @return Trecibopuesto
     */
    public function setNumerorecibo($numerorecibo)
    {
        $this->numerorecibo = $numerorecibo;

        return $this;
    }

    /**
     * Get numerorecibo
     *
     * @return string
     */
    public function getNumerorecibo()
    {
        return $this->numerorecibo;
    }

    /**
     * Set fkidfactura
     *
     * @param \ModeloBundle\Entity\Tfactura $fkidfactura
     *
     * @return Trecibopuesto
     */
    public function setFkidfactura(\ModeloBundle\Entity\Tfactura $fkidfactura = null)
    {
        $this->fkidfactura = $fkidfactura;

        return $this;
    }

    /**
     * Get fkidfactura
     *
     * @return \ModeloBundle\Entity\Tfactura
     */
    public function getFkidfactura()
    {
        return $this->fkidfactura;
    }

    /**
     * Set fkidtercero
     *
     * @param \ModeloBundle\Entity\Ttercero $fkidtercero
     *
     * @return Trecibopuesto
     */
    public function setFkidtercero(\ModeloBundle\Entity\Ttercero $fkidtercero = null)
    {
        $this->fkidtercero = $fkidtercero;

        return $this;
    }

    /**
     * Get fkidtercero
     *
     * @return \ModeloBundle\Entity\Ttercero
     */
    public function getFkidtercero()
    {
        return $this->fkidtercero;
    }
    /**
     * @var string
     */
    private $mesrecibo;

    /**
     * @var float
     */
    private $valorrecaudopagado;

    /**
     * @var float
     */
    private $valoracuerdopagado;

    /**
     * @var float
     */
    private $valormultapagado;

    /**
     * @var boolean
     */
    private $reciboanulado = '';


    /**
     * Set mesrecibo
     *
     * @param string $mesrecibo
     *
     * @return Trecibopuesto
     */
    public function setMesrecibo($mesrecibo)
    {
        $this->mesrecibo = $mesrecibo;

        return $this;
    }

    /**
     * Get mesrecibo
     *
     * @return string
     */
    public function getMesrecibo()
    {
        return $this->mesrecibo;
    }

    /**
     * Set valorrecaudopagado
     *
     * @param float $valorrecaudopagado
     *
     * @return Trecibopuesto
     */
    public function setValorrecaudopagado($valorrecaudopagado)
    {
        $this->valorrecaudopagado = $valorrecaudopagado;

        return $this;
    }

    /**
     * Get valorrecaudopagado
     *
     * @return float
     */
    public function getValorrecaudopagado()
    {
        return $this->valorrecaudopagado;
    }

    /**
     * Set valoracuerdopagado
     *
     * @param float $valoracuerdopagado
     *
     * @return Trecibopuesto
     */
    public function setValoracuerdopagado($valoracuerdopagado)
    {
        $this->valoracuerdopagado = $valoracuerdopagado;

        return $this;
    }

    /**
     * Get valoracuerdopagado
     *
     * @return float
     */
    public function getValoracuerdopagado()
    {
        return $this->valoracuerdopagado;
    }

    /**
     * Set valormultapagado
     *
     * @param float $valormultapagado
     *
     * @return Trecibopuesto
     */
    public function setValormultapagado($valormultapagado)
    {
        $this->valormultapagado = $valormultapagado;

        return $this;
    }

    /**
     * Get valormultapagado
     *
     * @return float
     */
    public function getValormultapagado()
    {
        return $this->valormultapagado;
    }

    /**
     * Set reciboanulado
     *
     * @param boolean $reciboanulado
     *
     * @return Trecibopuesto
     */
    public function setReciboanulado($reciboanulado)
    {
        $this->reciboanulado = $reciboanulado;

        return $this;
    }

    /**
     * Get reciboanulado
     *
     * @return boolean
     */
    public function getReciboanulado()
    {
        return $this->reciboanulado;
    }
    /**
     * @var string
     */
    private $nombreterceropuesto;

    /**
     * @var string
     */
    private $identificacionterceropuesto;

    /**
     * @var string
     */
    private $nombreplaza;

    /**
     * @var boolean
     */
    private $recibopuestoactivo = '1';


    /**
     * Set nombreterceropuesto
     *
     * @param string $nombreterceropuesto
     *
     * @return Trecibopuesto
     */
    public function setNombreterceropuesto($nombreterceropuesto)
    {
        $this->nombreterceropuesto = $nombreterceropuesto;

        return $this;
    }

    /**
     * Get nombreterceropuesto
     *
     * @return string
     */
    public function getNombreterceropuesto()
    {
        return $this->nombreterceropuesto;
    }

    /**
     * Set identificacionterceropuesto
     *
     * @param string $identificacionterceropuesto
     *
     * @return Trecibopuesto
     */
    public function setIdentificacionterceropuesto($identificacionterceropuesto)
    {
        $this->identificacionterceropuesto = $identificacionterceropuesto;

        return $this;
    }

    /**
     * Get identificacionterceropuesto
     *
     * @return string
     */
    public function getIdentificacionterceropuesto()
    {
        return $this->identificacionterceropuesto;
    }

    /**
     * Set nombreplaza
     *
     * @param string $nombreplaza
     *
     * @return Trecibopuesto
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
     * Set recibopuestoactivo
     *
     * @param boolean $recibopuestoactivo
     *
     * @return Trecibopuesto
     */
    public function setRecibopuestoactivo($recibopuestoactivo)
    {
        $this->recibopuestoactivo = $recibopuestoactivo;

        return $this;
    }

    /**
     * Get recibopuestoactivo
     *
     * @return boolean
     */
    public function getRecibopuestoactivo()
    {
        return $this->recibopuestoactivo;
    }
    /**
     * @var string
     */
    private $numeroresolucionasignacionpuesto;

    /**
     * @var string
     */
    private $numeropuesto;

    /**
     * @var string
     */
    private $nombresector;

    /**
     * @var \ModeloBundle\Entity\Tacuerdo
     */
    private $fkidacuerdo;

    /**
     * @var \ModeloBundle\Entity\Tasignacionpuesto
     */
    private $fkidasignacionpuesto;

    /**
     * @var \ModeloBundle\Entity\Tbeneficiario
     */
    private $fkidbeneficiario;

    /**
     * @var \ModeloBundle\Entity\Tmulta
     */
    private $fkidmulta;

    /**
     * @var \ModeloBundle\Entity\Tplaza
     */
    private $fkidplaza;

    /**
     * @var \ModeloBundle\Entity\Tpuesto
     */
    private $fkidpuesto;

    /**
     * @var \ModeloBundle\Entity\Tsector
     */
    private $fkidsector;

    /**
     * @var \ModeloBundle\Entity\Ttarifapuesto
     */
    private $fkidtarifapuesto;

    /**
     * @var \ModeloBundle\Entity\Tzona
     */
    private $fkidzona;


    /**
     * Set numeroresolucionasignacionpuesto
     *
     * @param string $numeroresolucionasignacionpuesto
     *
     * @return Trecibopuesto
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

    /**
     * Set numeropuesto
     *
     * @param string $numeropuesto
     *
     * @return Trecibopuesto
     */
    public function setNumeropuesto($numeropuesto)
    {
        $this->numeropuesto = $numeropuesto;

        return $this;
    }

    /**
     * Get numeropuesto
     *
     * @return string
     */
    public function getNumeropuesto()
    {
        return $this->numeropuesto;
    }

    /**
     * Set nombresector
     *
     * @param string $nombresector
     *
     * @return Trecibopuesto
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
     * Set fkidacuerdo
     *
     * @param \ModeloBundle\Entity\Tacuerdo $fkidacuerdo
     *
     * @return Trecibopuesto
     */
    public function setFkidacuerdo(\ModeloBundle\Entity\Tacuerdo $fkidacuerdo = null)
    {
        $this->fkidacuerdo = $fkidacuerdo;

        return $this;
    }

    /**
     * Get fkidacuerdo
     *
     * @return \ModeloBundle\Entity\Tacuerdo
     */
    public function getFkidacuerdo()
    {
        return $this->fkidacuerdo;
    }

    /**
     * Set fkidasignacionpuesto
     *
     * @param \ModeloBundle\Entity\Tasignacionpuesto $fkidasignacionpuesto
     *
     * @return Trecibopuesto
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
     * Set fkidbeneficiario
     *
     * @param \ModeloBundle\Entity\Tbeneficiario $fkidbeneficiario
     *
     * @return Trecibopuesto
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
     * Set fkidmulta
     *
     * @param \ModeloBundle\Entity\Tmulta $fkidmulta
     *
     * @return Trecibopuesto
     */
    public function setFkidmulta(\ModeloBundle\Entity\Tmulta $fkidmulta = null)
    {
        $this->fkidmulta = $fkidmulta;

        return $this;
    }

    /**
     * Get fkidmulta
     *
     * @return \ModeloBundle\Entity\Tmulta
     */
    public function getFkidmulta()
    {
        return $this->fkidmulta;
    }

    /**
     * Set fkidplaza
     *
     * @param \ModeloBundle\Entity\Tplaza $fkidplaza
     *
     * @return Trecibopuesto
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
     * Set fkidpuesto
     *
     * @param \ModeloBundle\Entity\Tpuesto $fkidpuesto
     *
     * @return Trecibopuesto
     */
    public function setFkidpuesto(\ModeloBundle\Entity\Tpuesto $fkidpuesto = null)
    {
        $this->fkidpuesto = $fkidpuesto;

        return $this;
    }

    /**
     * Get fkidpuesto
     *
     * @return \ModeloBundle\Entity\Tpuesto
     */
    public function getFkidpuesto()
    {
        return $this->fkidpuesto;
    }

    /**
     * Set fkidsector
     *
     * @param \ModeloBundle\Entity\Tsector $fkidsector
     *
     * @return Trecibopuesto
     */
    public function setFkidsector(\ModeloBundle\Entity\Tsector $fkidsector = null)
    {
        $this->fkidsector = $fkidsector;

        return $this;
    }

    /**
     * Get fkidsector
     *
     * @return \ModeloBundle\Entity\Tsector
     */
    public function getFkidsector()
    {
        return $this->fkidsector;
    }

    /**
     * Set fkidtarifapuesto
     *
     * @param \ModeloBundle\Entity\Ttarifapuesto $fkidtarifapuesto
     *
     * @return Trecibopuesto
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
     * Set fkidzona
     *
     * @param \ModeloBundle\Entity\Tzona $fkidzona
     *
     * @return Trecibopuesto
     */
    public function setFkidzona(\ModeloBundle\Entity\Tzona $fkidzona = null)
    {
        $this->fkidzona = $fkidzona;

        return $this;
    }

    /**
     * Get fkidzona
     *
     * @return \ModeloBundle\Entity\Tzona
     */
    public function getFkidzona()
    {
        return $this->fkidzona;
    }
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
     * @var \ModeloBundle\Entity\Tusuario
     */
    private $fkidusuariorecaudador;


    /**
     * Set identificacionrecaudador
     *
     * @param string $identificacionrecaudador
     *
     * @return Trecibopuesto
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
     * @return Trecibopuesto
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
     * @return Trecibopuesto
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
     * Set fkidusuariorecaudador
     *
     * @param \ModeloBundle\Entity\Tusuario $fkidusuariorecaudador
     *
     * @return Trecibopuesto
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
     * @var float
     */
    private $valormultas;

    /**
     * @var float
     */
    private $saldoporpagar;


    /**
     * Set valormultas
     *
     * @param float $valormultas
     *
     * @return Trecibopuesto
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
     * Set saldoporpagar
     *
     * @param float $saldoporpagar
     *
     * @return Trecibopuesto
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
     * @var string
     */
    private $nombrezona;


    /**
     * Set nombrezona
     *
     * @param string $nombrezona
     *
     * @return Trecibopuesto
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
     * @return Trecibopuesto
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
     * @return Trecibopuesto
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
     * @return Trecibopuesto
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
     * @return Trecibopuesto
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
     * @return Trecibopuesto
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
     * @return Trecibopuesto
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
}
