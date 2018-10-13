<?php

namespace ModeloBundle\Entity;

/**
 * Ttarifavehiculo
 */
class Ttarifavehiculo
{
    /**
     * @var float
     */
    private $valortarifavehiculo = '0';

    /**
     * @var string
     */
    private $descripciontarifavehiculo;

    /**
     * @var \DateTime
     */
    private $craciontarifavehiculo = 'now()';

    /**
     * @var \DateTime
     */
    private $modificaciontarifavehiculo = 'now()';

    /**
     * @var string
     */
    private $numeroresoluciontarifavehiculo;

    /**
     * @var string
     */
    private $documentoresoluciontarifavehiculo;

    /**
     * @var integer
     */
    private $pkidtarifavehiculo;

    /**
     * @var \ModeloBundle\Entity\Tplazatipovehiculo
     */
    private $fkidtipovehiculoplaza;


    /**
     * Set valortarifavehiculo
     *
     * @param float $valortarifavehiculo
     *
     * @return Ttarifavehiculo
     */
    public function setValortarifavehiculo($valortarifavehiculo)
    {
        $this->valortarifavehiculo = $valortarifavehiculo;

        return $this;
    }

    /**
     * Get valortarifavehiculo
     *
     * @return float
     */
    public function getValortarifavehiculo()
    {
        return $this->valortarifavehiculo;
    }

    /**
     * Set descripciontarifavehiculo
     *
     * @param string $descripciontarifavehiculo
     *
     * @return Ttarifavehiculo
     */
    public function setDescripciontarifavehiculo($descripciontarifavehiculo)
    {
        $this->descripciontarifavehiculo = $descripciontarifavehiculo;

        return $this;
    }

    /**
     * Get descripciontarifavehiculo
     *
     * @return string
     */
    public function getDescripciontarifavehiculo()
    {
        return $this->descripciontarifavehiculo;
    }

    /**
     * Set craciontarifavehiculo
     *
     * @param \DateTime $craciontarifavehiculo
     *
     * @return Ttarifavehiculo
     */
    public function setCraciontarifavehiculo($craciontarifavehiculo)
    {
        $this->craciontarifavehiculo = $craciontarifavehiculo;

        return $this;
    }

    /**
     * Get craciontarifavehiculo
     *
     * @return \DateTime
     */
    public function getCraciontarifavehiculo()
    {
        return $this->craciontarifavehiculo;
    }

    /**
     * Set modificaciontarifavehiculo
     *
     * @param \DateTime $modificaciontarifavehiculo
     *
     * @return Ttarifavehiculo
     */
    public function setModificaciontarifavehiculo($modificaciontarifavehiculo)
    {
        $this->modificaciontarifavehiculo = $modificaciontarifavehiculo;

        return $this;
    }

    /**
     * Get modificaciontarifavehiculo
     *
     * @return \DateTime
     */
    public function getModificaciontarifavehiculo()
    {
        return $this->modificaciontarifavehiculo;
    }

    /**
     * Set numeroresoluciontarifavehiculo
     *
     * @param string $numeroresoluciontarifavehiculo
     *
     * @return Ttarifavehiculo
     */
    public function setNumeroresoluciontarifavehiculo($numeroresoluciontarifavehiculo)
    {
        $this->numeroresoluciontarifavehiculo = $numeroresoluciontarifavehiculo;

        return $this;
    }

    /**
     * Get numeroresoluciontarifavehiculo
     *
     * @return string
     */
    public function getNumeroresoluciontarifavehiculo()
    {
        return $this->numeroresoluciontarifavehiculo;
    }

    /**
     * Set documentoresoluciontarifavehiculo
     *
     * @param string $documentoresoluciontarifavehiculo
     *
     * @return Ttarifavehiculo
     */
    public function setDocumentoresoluciontarifavehiculo($documentoresoluciontarifavehiculo)
    {
        $this->documentoresoluciontarifavehiculo = $documentoresoluciontarifavehiculo;

        return $this;
    }

    /**
     * Get documentoresoluciontarifavehiculo
     *
     * @return string
     */
    public function getDocumentoresoluciontarifavehiculo()
    {
        return $this->documentoresoluciontarifavehiculo;
    }

    /**
     * Get pkidtarifavehiculo
     *
     * @return integer
     */
    public function getPkidtarifavehiculo()
    {
        return $this->pkidtarifavehiculo;
    }

    /**
     * Set fkidtipovehiculoplaza
     *
     * @param \ModeloBundle\Entity\Tplazatipovehiculo $fkidtipovehiculoplaza
     *
     * @return Ttarifavehiculo
     */
    public function setFkidtipovehiculoplaza(\ModeloBundle\Entity\Tplazatipovehiculo $fkidtipovehiculoplaza = null)
    {
        $this->fkidtipovehiculoplaza = $fkidtipovehiculoplaza;

        return $this;
    }

    /**
     * Get fkidtipovehiculoplaza
     *
     * @return \ModeloBundle\Entity\Tplazatipovehiculo
     */
    public function getFkidtipovehiculoplaza()
    {
        return $this->fkidtipovehiculoplaza;
    }
    /**
     * @var \ModeloBundle\Entity\Ttipovehiculo
     */
    private $fkidtipovehiculo;

    /**
     * @var \ModeloBundle\Entity\Tplaza
     */
    private $fkidplaza;


    /**
     * Set fkidtipovehiculo
     *
     * @param \ModeloBundle\Entity\Ttipovehiculo $fkidtipovehiculo
     *
     * @return Ttarifavehiculo
     */
    public function setFkidtipovehiculo(\ModeloBundle\Entity\Ttipovehiculo $fkidtipovehiculo = null)
    {
        $this->fkidtipovehiculo = $fkidtipovehiculo;

        return $this;
    }

    /**
     * Get fkidtipovehiculo
     *
     * @return \ModeloBundle\Entity\Ttipovehiculo
     */
    public function getFkidtipovehiculo()
    {
        return $this->fkidtipovehiculo;
    }

    /**
     * Set fkidplaza
     *
     * @param \ModeloBundle\Entity\Tplaza $fkidplaza
     *
     * @return Ttarifavehiculo
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
     * @var boolean
     */
    private $tarifavehiculoactivo = '1';


    /**
     * Set tarifavehiculoactivo
     *
     * @param boolean $tarifavehiculoactivo
     *
     * @return Ttarifavehiculo
     */
    public function setTarifavehiculoactivo($tarifavehiculoactivo)
    {
        $this->tarifavehiculoactivo = $tarifavehiculoactivo;

        return $this;
    }

    /**
     * Get tarifavehiculoactivo
     *
     * @return boolean
     */
    public function getTarifavehiculoactivo()
    {
        return $this->tarifavehiculoactivo;
    }
    /**
     * @var float
     */
    private $valorincrementoporcentual = '0';


    /**
     * Set valorincrementoporcentual
     *
     * @param float $valorincrementoporcentual
     *
     * @return Ttarifavehiculo
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
     * @var \DateTime
     */
    private $fechainicio = 'now()';

    /**
     * @var \DateTime
     */
    private $fechafin = 'now()';


    /**
     * Set fechainicio
     *
     * @param \DateTime $fechainicio
     *
     * @return Ttarifavehiculo
     */
    public function setFechainicio($fechainicio)
    {
        $this->fechainicio = $fechainicio;

        return $this;
    }

    /**
     * Get fechainicio
     *
     * @return \DateTime
     */
    public function getFechainicio()
    {
        return $this->fechainicio;
    }

    /**
     * Set fechafin
     *
     * @param \DateTime $fechafin
     *
     * @return Ttarifavehiculo
     */
    public function setFechafin($fechafin)
    {
        $this->fechafin = $fechafin;

        return $this;
    }

    /**
     * Get fechafin
     *
     * @return \DateTime
     */
    public function getFechafin()
    {
        return $this->fechafin;
    }
}
