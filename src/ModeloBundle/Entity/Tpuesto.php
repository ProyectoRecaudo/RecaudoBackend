<?php

namespace ModeloBundle\Entity;

/**
 * Tpuesto
 */
class Tpuesto
{
    /**
     * @var string
     */
    private $codigopuesto;

    /**
     * @var string
     */
    private $numeropuesto;

    /**
     * @var float
     */
    private $alto;

    /**
     * @var float
     */
    private $ancho;

    /**
     * @var string
     */
    private $imagenpuesto;

    /**
     * @var boolean
     */
    private $puestoactivo = '1';

    /**
     * @var \DateTime
     */
    private $creacionpuesto = 'now()';

    /**
     * @var \DateTime
     */
    private $modificacionpuesto = 'now()';

    /**
     * @var integer
     */
    private $tipopuesto;

    /**
     * @var integer
     */
    private $pkidpuesto;

    /**
     * @var \ModeloBundle\Entity\Testadoinfraestructura
     */
    private $fkidestado;

    /**
     * @var \ModeloBundle\Entity\Tactividadcomercial
     */
    private $fkidactividad;

    /**
     * @var \ModeloBundle\Entity\Tsector
     */
    private $fkidsector;

    /**
     * @var \ModeloBundle\Entity\Ttipopuesto
     */
    private $fkidtipopuesto;


    /**
     * Set codigopuesto
     *
     * @param string $codigopuesto
     *
     * @return Tpuesto
     */
    public function setCodigopuesto($codigopuesto)
    {
        $this->codigopuesto = $codigopuesto;

        return $this;
    }

    /**
     * Get codigopuesto
     *
     * @return string
     */
    public function getCodigopuesto()
    {
        return $this->codigopuesto;
    }

    /**
     * Set numeropuesto
     *
     * @param string $numeropuesto
     *
     * @return Tpuesto
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
     * Set alto
     *
     * @param float $alto
     *
     * @return Tpuesto
     */
    public function setAlto($alto)
    {
        $this->alto = $alto;

        return $this;
    }

    /**
     * Get alto
     *
     * @return float
     */
    public function getAlto()
    {
        return $this->alto;
    }

    /**
     * Set ancho
     *
     * @param float $ancho
     *
     * @return Tpuesto
     */
    public function setAncho($ancho)
    {
        $this->ancho = $ancho;

        return $this;
    }

    /**
     * Get ancho
     *
     * @return float
     */
    public function getAncho()
    {
        return $this->ancho;
    }

    /**
     * Set imagenpuesto
     *
     * @param string $imagenpuesto
     *
     * @return Tpuesto
     */
    public function setImagenpuesto($imagenpuesto)
    {
        $this->imagenpuesto = $imagenpuesto;

        return $this;
    }

    /**
     * Get imagenpuesto
     *
     * @return string
     */
    public function getImagenpuesto()
    {
        return $this->imagenpuesto;
    }

    /**
     * Set puestoactivo
     *
     * @param boolean $puestoactivo
     *
     * @return Tpuesto
     */
    public function setPuestoactivo($puestoactivo)
    {
        $this->puestoactivo = $puestoactivo;

        return $this;
    }

    /**
     * Get puestoactivo
     *
     * @return boolean
     */
    public function getPuestoactivo()
    {
        return $this->puestoactivo;
    }

    /**
     * Set creacionpuesto
     *
     * @param \DateTime $creacionpuesto
     *
     * @return Tpuesto
     */
    public function setCreacionpuesto($creacionpuesto)
    {
        $this->creacionpuesto = $creacionpuesto;

        return $this;
    }

    /**
     * Get creacionpuesto
     *
     * @return \DateTime
     */
    public function getCreacionpuesto()
    {
        return $this->creacionpuesto;
    }

    /**
     * Set modificacionpuesto
     *
     * @param \DateTime $modificacionpuesto
     *
     * @return Tpuesto
     */
    public function setModificacionpuesto($modificacionpuesto)
    {
        $this->modificacionpuesto = $modificacionpuesto;

        return $this;
    }

    /**
     * Get modificacionpuesto
     *
     * @return \DateTime
     */
    public function getModificacionpuesto()
    {
        return $this->modificacionpuesto;
    }

    /**
     * Set tipopuesto
     *
     * @param integer $tipopuesto
     *
     * @return Tpuesto
     */
    public function setTipopuesto($tipopuesto)
    {
        $this->tipopuesto = $tipopuesto;

        return $this;
    }

    /**
     * Get tipopuesto
     *
     * @return integer
     */
    public function getTipopuesto()
    {
        return $this->tipopuesto;
    }

    /**
     * Get pkidpuesto
     *
     * @return integer
     */
    public function getPkidpuesto()
    {
        return $this->pkidpuesto;
    }

    /**
     * Set fkidestado
     *
     * @param \ModeloBundle\Entity\Testadoinfraestructura $fkidestado
     *
     * @return Tpuesto
     */
    public function setFkidestado(\ModeloBundle\Entity\Testadoinfraestructura $fkidestado = null)
    {
        $this->fkidestado = $fkidestado;

        return $this;
    }

    /**
     * Get fkidestado
     *
     * @return \ModeloBundle\Entity\Testadoinfraestructura
     */
    public function getFkidestado()
    {
        return $this->fkidestado;
    }

    /**
     * Set fkidactividad
     *
     * @param \ModeloBundle\Entity\Tactividadcomercial $fkidactividad
     *
     * @return Tpuesto
     */
    public function setFkidactividad(\ModeloBundle\Entity\Tactividadcomercial $fkidactividad = null)
    {
        $this->fkidactividad = $fkidactividad;

        return $this;
    }

    /**
     * Get fkidactividad
     *
     * @return \ModeloBundle\Entity\Tactividadcomercial
     */
    public function getFkidactividad()
    {
        return $this->fkidactividad;
    }

    /**
     * Set fkidsector
     *
     * @param \ModeloBundle\Entity\Tsector $fkidsector
     *
     * @return Tpuesto
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
     * Set fkidtipopuesto
     *
     * @param \ModeloBundle\Entity\Ttipopuesto $fkidtipopuesto
     *
     * @return Tpuesto
     */
    public function setFkidtipopuesto(\ModeloBundle\Entity\Ttipopuesto $fkidtipopuesto = null)
    {
        $this->fkidtipopuesto = $fkidtipopuesto;

        return $this;
    }

    /**
     * Get fkidtipopuesto
     *
     * @return \ModeloBundle\Entity\Ttipopuesto
     */
    public function getFkidtipopuesto()
    {
        return $this->fkidtipopuesto;
    }
    /**
     * @var \ModeloBundle\Entity\Testadoinfraestructura
     */
    private $fkidestadoinfraestructura;

    /**
     * @var \ModeloBundle\Entity\Tactividadcomercial
     */
    private $fkidactividadcomercial;


    /**
     * Set fkidestadoinfraestructura
     *
     * @param \ModeloBundle\Entity\Testadoinfraestructura $fkidestadoinfraestructura
     *
     * @return Tpuesto
     */
    public function setFkidestadoinfraestructura(\ModeloBundle\Entity\Testadoinfraestructura $fkidestadoinfraestructura = null)
    {
        $this->fkidestadoinfraestructura = $fkidestadoinfraestructura;

        return $this;
    }

    /**
     * Get fkidestadoinfraestructura
     *
     * @return \ModeloBundle\Entity\Testadoinfraestructura
     */
    public function getFkidestadoinfraestructura()
    {
        return $this->fkidestadoinfraestructura;
    }

    /**
     * Set fkidactividadcomercial
     *
     * @param \ModeloBundle\Entity\Tactividadcomercial $fkidactividadcomercial
     *
     * @return Tpuesto
     */
    public function setFkidactividadcomercial(\ModeloBundle\Entity\Tactividadcomercial $fkidactividadcomercial = null)
    {
        $this->fkidactividadcomercial = $fkidactividadcomercial;

        return $this;
    }

    /**
     * Get fkidactividadcomercial
     *
     * @return \ModeloBundle\Entity\Tactividadcomercial
     */
    public function getFkidactividadcomercial()
    {
        return $this->fkidactividadcomercial;
    }
}
