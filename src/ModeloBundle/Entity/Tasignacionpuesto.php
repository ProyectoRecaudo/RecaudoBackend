<?php

namespace ModeloBundle\Entity;

/**
 * Tasignacionpuesto
 */
class Tasignacionpuesto
{
    /**
     * @var string
     */
    private $numeroresolucion;

    /**
     * @var string
     */
    private $rutaresolucion;

    /**
     * @var string
     */
    private $estadoasignacion;

    /**
     * @var boolean
     */
    private $asignacionactivo = '1';

    /**
     * @var integer
     */
    private $pkidasignacion;

    /**
     * @var \ModeloBundle\Entity\Tbeneficiario
     */
    private $fkidbeneficiario;

    /**
     * @var \ModeloBundle\Entity\Tpuesto
     */
    private $fkidpuesto;


    /**
     * Set numeroresolucion
     *
     * @param string $numeroresolucion
     *
     * @return Tasignacionpuesto
     */
    public function setNumeroresolucion($numeroresolucion)
    {
        $this->numeroresolucion = $numeroresolucion;

        return $this;
    }

    /**
     * Get numeroresolucion
     *
     * @return string
     */
    public function getNumeroresolucion()
    {
        return $this->numeroresolucion;
    }

    /**
     * Set rutaresolucion
     *
     * @param string $rutaresolucion
     *
     * @return Tasignacionpuesto
     */
    public function setRutaresolucion($rutaresolucion)
    {
        $this->rutaresolucion = $rutaresolucion;

        return $this;
    }

    /**
     * Get rutaresolucion
     *
     * @return string
     */
    public function getRutaresolucion()
    {
        return $this->rutaresolucion;
    }

    /**
     * Set estadoasignacion
     *
     * @param string $estadoasignacion
     *
     * @return Tasignacionpuesto
     */
    public function setEstadoasignacion($estadoasignacion)
    {
        $this->estadoasignacion = $estadoasignacion;

        return $this;
    }

    /**
     * Get estadoasignacion
     *
     * @return string
     */
    public function getEstadoasignacion()
    {
        return $this->estadoasignacion;
    }

    /**
     * Set asignacionactivo
     *
     * @param boolean $asignacionactivo
     *
     * @return Tasignacionpuesto
     */
    public function setAsignacionactivo($asignacionactivo)
    {
        $this->asignacionactivo = $asignacionactivo;

        return $this;
    }

    /**
     * Get asignacionactivo
     *
     * @return boolean
     */
    public function getAsignacionactivo()
    {
        return $this->asignacionactivo;
    }

    /**
     * Get pkidasignacion
     *
     * @return integer
     */
    public function getPkidasignacion()
    {
        return $this->pkidasignacion;
    }

    /**
     * Set fkidbeneficiario
     *
     * @param \ModeloBundle\Entity\Tbeneficiario $fkidbeneficiario
     *
     * @return Tasignacionpuesto
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
     * Set fkidpuesto
     *
     * @param \ModeloBundle\Entity\Tpuesto $fkidpuesto
     *
     * @return Tasignacionpuesto
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
     * @var string
     */
    private $numeroresolucionasignacionpuesto;

    /**
     * @var string
     */
    private $rutaresolucionasignacionpuesto;

    /**
     * @var string
     */
    private $estadoasignacionpuesto;

    /**
     * @var boolean
     */
    private $asignacionpuestoactivo = '1';

    /**
     * @var \DateTime
     */
    private $creacionasignacionpuesto = 'now()';

    /**
     * @var \DateTime
     */
    private $modificacionasignacionpuesto = 'now()';

    /**
     * @var integer
     */
    private $pkidasignacionpuesto;

    /**
     * @var \ModeloBundle\Entity\Ttarifapuesto
     */
    private $fkidtarifapuesto;


    /**
     * Set numeroresolucionasignacionpuesto
     *
     * @param string $numeroresolucionasignacionpuesto
     *
     * @return Tasignacionpuesto
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
     * Set rutaresolucionasignacionpuesto
     *
     * @param string $rutaresolucionasignacionpuesto
     *
     * @return Tasignacionpuesto
     */
    public function setRutaresolucionasignacionpuesto($rutaresolucionasignacionpuesto)
    {
        $this->rutaresolucionasignacionpuesto = $rutaresolucionasignacionpuesto;

        return $this;
    }

    /**
     * Get rutaresolucionasignacionpuesto
     *
     * @return string
     */
    public function getRutaresolucionasignacionpuesto()
    {
        return $this->rutaresolucionasignacionpuesto;
    }

    /**
     * Set estadoasignacionpuesto
     *
     * @param string $estadoasignacionpuesto
     *
     * @return Tasignacionpuesto
     */
    public function setEstadoasignacionpuesto($estadoasignacionpuesto)
    {
        $this->estadoasignacionpuesto = $estadoasignacionpuesto;

        return $this;
    }

    /**
     * Get estadoasignacionpuesto
     *
     * @return string
     */
    public function getEstadoasignacionpuesto()
    {
        return $this->estadoasignacionpuesto;
    }

    /**
     * Set asignacionpuestoactivo
     *
     * @param boolean $asignacionpuestoactivo
     *
     * @return Tasignacionpuesto
     */
    public function setAsignacionpuestoactivo($asignacionpuestoactivo)
    {
        $this->asignacionpuestoactivo = $asignacionpuestoactivo;

        return $this;
    }

    /**
     * Get asignacionpuestoactivo
     *
     * @return boolean
     */
    public function getAsignacionpuestoactivo()
    {
        return $this->asignacionpuestoactivo;
    }

    /**
     * Set creacionasignacionpuesto
     *
     * @param \DateTime $creacionasignacionpuesto
     *
     * @return Tasignacionpuesto
     */
    public function setCreacionasignacionpuesto($creacionasignacionpuesto)
    {
        $this->creacionasignacionpuesto = $creacionasignacionpuesto;

        return $this;
    }

    /**
     * Get creacionasignacionpuesto
     *
     * @return \DateTime
     */
    public function getCreacionasignacionpuesto()
    {
        return $this->creacionasignacionpuesto;
    }

    /**
     * Set modificacionasignacionpuesto
     *
     * @param \DateTime $modificacionasignacionpuesto
     *
     * @return Tasignacionpuesto
     */
    public function setModificacionasignacionpuesto($modificacionasignacionpuesto)
    {
        $this->modificacionasignacionpuesto = $modificacionasignacionpuesto;

        return $this;
    }

    /**
     * Get modificacionasignacionpuesto
     *
     * @return \DateTime
     */
    public function getModificacionasignacionpuesto()
    {
        return $this->modificacionasignacionpuesto;
    }

    /**
     * Get pkidasignacionpuesto
     *
     * @return integer
     */
    public function getPkidasignacionpuesto()
    {
        return $this->pkidasignacionpuesto;
    }

    /**
     * Set fkidtarifapuesto
     *
     * @param \ModeloBundle\Entity\Ttarifapuesto $fkidtarifapuesto
     *
     * @return Tasignacionpuesto
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
    private $saldodeuda;


    /**
     * Set saldodeuda
     *
     * @param float $saldodeuda
     *
     * @return Tasignacionpuesto
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
     * @var float
     */
    private $saldo;

    /**
     * @var string
     */
    private $concobro = 'Ninguno';


    /**
     * Set saldo
     *
     * @param float $saldo
     *
     * @return Tasignacionpuesto
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
     * Set concobro
     *
     * @param string $concobro
     *
     * @return Tasignacionpuesto
     */
    public function setConcobro($concobro)
    {
        $this->concobro = $concobro;

        return $this;
    }

    /**
     * Get concobro
     *
     * @return string
     */
    public function getConcobro()
    {
        return $this->concobro;
    }
    /**
     * @var float
     */
    private $valorincrementoporcentual = '0';

    /**
     * @var float
     */
    private $valortarifapuesto = '0';


    /**
     * Set valorincrementoporcentual
     *
     * @param float $valorincrementoporcentual
     *
     * @return Tasignacionpuesto
     */
    public function setValorincrementoporcentual($valorincrementoporcentual)
    {
        $this->valorincrementoporcentual = $valorincrementoporcentual;

        return $this;
    }

    /**
     * Get valorincrementoporcentual
     *
     * @return float
     */
    public function getValorincrementoporcentual()
    {
        return $this->valorincrementoporcentual;
    }

    /**
     * Set valortarifapuesto
     *
     * @param float $valortarifapuesto
     *
     * @return Tasignacionpuesto
     */
    public function setValortarifapuesto($valortarifapuesto)
    {
        $this->valortarifapuesto = $valortarifapuesto;

        return $this;
    }

    /**
     * Get valortarifapuesto
     *
     * @return float
     */
    public function getValortarifapuesto()
    {
        return $this->valortarifapuesto;
    }
    /**
     * @var float
     */
    private $saldofavor;


    /**
     * Set saldofavor
     *
     * @param float $saldofavor
     *
     * @return Tasignacionpuesto
     */
    public function setSaldofavor($saldofavor)
    {
        $this->saldofavor = $saldofavor;

        return $this;
    }

    /**
     * Get saldofavor
     *
     * @return float
     */
    public function getSaldofavor()
    {
        return $this->saldofavor;
    }
}
