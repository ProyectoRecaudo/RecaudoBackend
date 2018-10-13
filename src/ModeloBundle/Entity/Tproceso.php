<?php

namespace ModeloBundle\Entity;

/**
 * Tproceso
 */
class Tproceso
{
    /**
     * @var string
     */
    private $resolucionproceso;

    /**
     * @var string
     */
    private $documentoproceso;

    /**
     * @var boolean
     */
    private $procesoactivo = '1';

    /**
     * @var string
     */
    private $tipocobro;

    /**
     * @var integer
     */
    private $pkidproceso;

    /**
     * @var \ModeloBundle\Entity\Tcartera
     */
    private $fkidcartera;


    /**
     * Set resolucionproceso
     *
     * @param string $resolucionproceso
     *
     * @return Tproceso
     */
    public function setResolucionproceso($resolucionproceso)
    {
        $this->resolucionproceso = $resolucionproceso;

        return $this;
    }

    /**
     * Get resolucionproceso
     *
     * @return string
     */
    public function getResolucionproceso()
    {
        return $this->resolucionproceso;
    }

    /**
     * Set documentoproceso
     *
     * @param string $documentoproceso
     *
     * @return Tproceso
     */
    public function setDocumentoproceso($documentoproceso)
    {
        $this->documentoproceso = $documentoproceso;

        return $this;
    }

    /**
     * Get documentoproceso
     *
     * @return string
     */
    public function getDocumentoproceso()
    {
        return $this->documentoproceso;
    }

    /**
     * Set procesoactivo
     *
     * @param boolean $procesoactivo
     *
     * @return Tproceso
     */
    public function setProcesoactivo($procesoactivo)
    {
        $this->procesoactivo = $procesoactivo;

        return $this;
    }

    /**
     * Get procesoactivo
     *
     * @return boolean
     */
    public function getProcesoactivo()
    {
        return $this->procesoactivo;
    }

    /**
     * Set tipocobro
     *
     * @param string $tipocobro
     *
     * @return Tproceso
     */
    public function setTipocobro($tipocobro)
    {
        $this->tipocobro = $tipocobro;

        return $this;
    }

    /**
     * Get tipocobro
     *
     * @return string
     */
    public function getTipocobro()
    {
        return $this->tipocobro;
    }

    /**
     * Get pkidproceso
     *
     * @return integer
     */
    public function getPkidproceso()
    {
        return $this->pkidproceso;
    }

    /**
     * Set fkidcartera
     *
     * @param \ModeloBundle\Entity\Tcartera $fkidcartera
     *
     * @return Tproceso
     */
    public function setFkidcartera(\ModeloBundle\Entity\Tcartera $fkidcartera = null)
    {
        $this->fkidcartera = $fkidcartera;

        return $this;
    }

    /**
     * Get fkidcartera
     *
     * @return \ModeloBundle\Entity\Tcartera
     */
    public function getFkidcartera()
    {
        return $this->fkidcartera;
    }
    /**
     * @var string
     */
    private $tipoproceso;

    /**
     * @var \ModeloBundle\Entity\Tbeneficiario
     */
    private $fkidbeneficiario;


    /**
     * Set tipoproceso
     *
     * @param string $tipoproceso
     *
     * @return Tproceso
     */
    public function setTipoproceso($tipoproceso)
    {
        $this->tipoproceso = $tipoproceso;

        return $this;
    }

    /**
     * Get tipoproceso
     *
     * @return string
     */
    public function getTipoproceso()
    {
        return $this->tipoproceso;
    }

    /**
     * Set fkidbeneficiario
     *
     * @param \ModeloBundle\Entity\Tbeneficiario $fkidbeneficiario
     *
     * @return Tproceso
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
     * @var \DateTime
     */
    private $creacionproceso = 'now()';

    /**
     * @var \DateTime
     */
    private $modificacionproceso = 'now()';

    /**
     * @var \ModeloBundle\Entity\Tabogado
     */
    private $fkidabogado;

    /**
     * @var \ModeloBundle\Entity\Tasignacionpuesto
     */
    private $fkidasignacionpuesto;


    /**
     * Set creacionproceso
     *
     * @param \DateTime $creacionproceso
     *
     * @return Tproceso
     */
    public function setCreacionproceso($creacionproceso)
    {
        $this->creacionproceso = $creacionproceso;

        return $this;
    }

    /**
     * Get creacionproceso
     *
     * @return \DateTime
     */
    public function getCreacionproceso()
    {
        return $this->creacionproceso;
    }

    /**
     * Set modificacionproceso
     *
     * @param \DateTime $modificacionproceso
     *
     * @return Tproceso
     */
    public function setModificacionproceso($modificacionproceso)
    {
        $this->modificacionproceso = $modificacionproceso;

        return $this;
    }

    /**
     * Get modificacionproceso
     *
     * @return \DateTime
     */
    public function getModificacionproceso()
    {
        return $this->modificacionproceso;
    }

    /**
     * Set fkidabogado
     *
     * @param \ModeloBundle\Entity\Tabogado $fkidabogado
     *
     * @return Tproceso
     */
    public function setFkidabogado(\ModeloBundle\Entity\Tabogado $fkidabogado = null)
    {
        $this->fkidabogado = $fkidabogado;

        return $this;
    }

    /**
     * Get fkidabogado
     *
     * @return \ModeloBundle\Entity\Tabogado
     */
    public function getFkidabogado()
    {
        return $this->fkidabogado;
    }

    /**
     * Set fkidasignacionpuesto
     *
     * @param \ModeloBundle\Entity\Tasignacionpuesto $fkidasignacionpuesto
     *
     * @return Tproceso
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
}
