<?php

namespace ModeloBundle\Entity;

/**
 * Ttarifapuesto
 */
class Ttarifapuesto
{
    /**
     * @var float
     */
    private $valortarifapuesto = '0';

    /**
     * @var string
     */
    private $descripciontarifapuesto;

    /**
     * @var \DateTime
     */
    private $craciontarifapuesto = 'now()';

    /**
     * @var \DateTime
     */
    private $modificaciontarifapuesto = 'now()';

    /**
     * @var string
     */
    private $numeroresoluciontarifapuesto;

    /**
     * @var string
     */
    private $documentoresoluciontarifapuesto;

    /**
     * @var integer
     */
    private $pkidtarifapuesto;

    /**
     * @var \ModeloBundle\Entity\Tpuesto
     */
    private $fkidpuesto;


    /**
     * Set valortarifapuesto
     *
     * @param float $valortarifapuesto
     *
     * @return Ttarifapuesto
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
     * Set descripciontarifapuesto
     *
     * @param string $descripciontarifapuesto
     *
     * @return Ttarifapuesto
     */
    public function setDescripciontarifapuesto($descripciontarifapuesto)
    {
        $this->descripciontarifapuesto = $descripciontarifapuesto;

        return $this;
    }

    /**
     * Get descripciontarifapuesto
     *
     * @return string
     */
    public function getDescripciontarifapuesto()
    {
        return $this->descripciontarifapuesto;
    }

    /**
     * Set craciontarifapuesto
     *
     * @param \DateTime $craciontarifapuesto
     *
     * @return Ttarifapuesto
     */
    public function setCraciontarifapuesto($craciontarifapuesto)
    {
        $this->craciontarifapuesto = $craciontarifapuesto;

        return $this;
    }

    /**
     * Get craciontarifapuesto
     *
     * @return \DateTime
     */
    public function getCraciontarifapuesto()
    {
        return $this->craciontarifapuesto;
    }

    /**
     * Set modificaciontarifapuesto
     *
     * @param \DateTime $modificaciontarifapuesto
     *
     * @return Ttarifapuesto
     */
    public function setModificaciontarifapuesto($modificaciontarifapuesto)
    {
        $this->modificaciontarifapuesto = $modificaciontarifapuesto;

        return $this;
    }

    /**
     * Get modificaciontarifapuesto
     *
     * @return \DateTime
     */
    public function getModificaciontarifapuesto()
    {
        return $this->modificaciontarifapuesto;
    }

    /**
     * Set numeroresoluciontarifapuesto
     *
     * @param string $numeroresoluciontarifapuesto
     *
     * @return Ttarifapuesto
     */
    public function setNumeroresoluciontarifapuesto($numeroresoluciontarifapuesto)
    {
        $this->numeroresoluciontarifapuesto = $numeroresoluciontarifapuesto;

        return $this;
    }

    /**
     * Get numeroresoluciontarifapuesto
     *
     * @return string
     */
    public function getNumeroresoluciontarifapuesto()
    {
        return $this->numeroresoluciontarifapuesto;
    }

    /**
     * Set documentoresoluciontarifapuesto
     *
     * @param string $documentoresoluciontarifapuesto
     *
     * @return Ttarifapuesto
     */
    public function setDocumentoresoluciontarifapuesto($documentoresoluciontarifapuesto)
    {
        $this->documentoresoluciontarifapuesto = $documentoresoluciontarifapuesto;

        return $this;
    }

    /**
     * Get documentoresoluciontarifapuesto
     *
     * @return string
     */
    public function getDocumentoresoluciontarifapuesto()
    {
        return $this->documentoresoluciontarifapuesto;
    }

    /**
     * Get pkidtarifapuesto
     *
     * @return integer
     */
    public function getPkidtarifapuesto()
    {
        return $this->pkidtarifapuesto;
    }

    /**
     * Set fkidpuesto
     *
     * @param \ModeloBundle\Entity\Tpuesto $fkidpuesto
     *
     * @return Ttarifapuesto
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
     * @var \DateTime
     */
    private $creaciontarifapuesto = 'now()';


    /**
     * Set creaciontarifapuesto
     *
     * @param \DateTime $creaciontarifapuesto
     *
     * @return Ttarifapuesto
     */
    public function setCreaciontarifapuesto($creaciontarifapuesto)
    {
        $this->creaciontarifapuesto = $creaciontarifapuesto;

        return $this;
    }

    /**
     * Get creaciontarifapuesto
     *
     * @return \DateTime
     */
    public function getCreaciontarifapuesto()
    {
        return $this->creaciontarifapuesto;
    }
    /**
     * @var boolean
     */
    private $tarifapuestoactivo = '1';


    /**
     * Set tarifapuestoactivo
     *
     * @param boolean $tarifapuestoactivo
     *
     * @return Ttarifapuesto
     */
    public function setTarifapuestoactivo($tarifapuestoactivo)
    {
        $this->tarifapuestoactivo = $tarifapuestoactivo;

        return $this;
    }

    /**
     * Get tarifapuestoactivo
     *
     * @return boolean
     */
    public function getTarifapuestoactivo()
    {
        return $this->tarifapuestoactivo;
    }
    /**
     * @var float
     */
    private $valorincrementoporcentual = '0';

    /**
     * @var \ModeloBundle\Entity\Tplaza
     */
    private $fkidplaza;


    /**
     * Set valorincrementoporcentual
     *
     * @param float $valorincrementoporcentual
     *
     * @return Ttarifapuesto
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
     * Set fkidplaza
     *
     * @param \ModeloBundle\Entity\Tplaza $fkidplaza
     *
     * @return Ttarifapuesto
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
}
