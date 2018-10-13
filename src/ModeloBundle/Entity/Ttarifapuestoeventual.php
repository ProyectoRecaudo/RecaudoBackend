<?php

namespace ModeloBundle\Entity;

/**
 * Ttarifapuestoeventual
 */
class Ttarifapuestoeventual
{
    /**
     * @var float
     */
    private $valortarifapuestoeventual = '0';

    /**
     * @var string
     */
    private $descripciontarifapuestoeventual;

    /**
     * @var string
     */
    private $numeroresoluciontarifapuestoeventual;

    /**
     * @var string
     */
    private $documentoresoluciontarifapuestoeventual;

    /**
     * @var \DateTime
     */
    private $craciontarifapuestoeventual = 'now()';

    /**
     * @var \DateTime
     */
    private $modificaciontarifapuestoeventual = 'now()';

    /**
     * @var integer
     */
    private $pkidtarifapuestoeventual;

    /**
     * @var \ModeloBundle\Entity\Tplaza
     */
    private $fkidplaza;


    /**
     * Set valortarifapuestoeventual
     *
     * @param float $valortarifapuestoeventual
     *
     * @return Ttarifapuestoeventual
     */
    public function setValortarifapuestoeventual($valortarifapuestoeventual)
    {
        $this->valortarifapuestoeventual = $valortarifapuestoeventual;

        return $this;
    }

    /**
     * Get valortarifapuestoeventual
     *
     * @return float
     */
    public function getValortarifapuestoeventual()
    {
        return $this->valortarifapuestoeventual;
    }

    /**
     * Set descripciontarifapuestoeventual
     *
     * @param string $descripciontarifapuestoeventual
     *
     * @return Ttarifapuestoeventual
     */
    public function setDescripciontarifapuestoeventual($descripciontarifapuestoeventual)
    {
        $this->descripciontarifapuestoeventual = $descripciontarifapuestoeventual;

        return $this;
    }

    /**
     * Get descripciontarifapuestoeventual
     *
     * @return string
     */
    public function getDescripciontarifapuestoeventual()
    {
        return $this->descripciontarifapuestoeventual;
    }

    /**
     * Set numeroresoluciontarifapuestoeventual
     *
     * @param string $numeroresoluciontarifapuestoeventual
     *
     * @return Ttarifapuestoeventual
     */
    public function setNumeroresoluciontarifapuestoeventual($numeroresoluciontarifapuestoeventual)
    {
        $this->numeroresoluciontarifapuestoeventual = $numeroresoluciontarifapuestoeventual;

        return $this;
    }

    /**
     * Get numeroresoluciontarifapuestoeventual
     *
     * @return string
     */
    public function getNumeroresoluciontarifapuestoeventual()
    {
        return $this->numeroresoluciontarifapuestoeventual;
    }

    /**
     * Set documentoresoluciontarifapuestoeventual
     *
     * @param string $documentoresoluciontarifapuestoeventual
     *
     * @return Ttarifapuestoeventual
     */
    public function setDocumentoresoluciontarifapuestoeventual($documentoresoluciontarifapuestoeventual)
    {
        $this->documentoresoluciontarifapuestoeventual = $documentoresoluciontarifapuestoeventual;

        return $this;
    }

    /**
     * Get documentoresoluciontarifapuestoeventual
     *
     * @return string
     */
    public function getDocumentoresoluciontarifapuestoeventual()
    {
        return $this->documentoresoluciontarifapuestoeventual;
    }

    /**
     * Set craciontarifapuestoeventual
     *
     * @param \DateTime $craciontarifapuestoeventual
     *
     * @return Ttarifapuestoeventual
     */
    public function setCraciontarifapuestoeventual($craciontarifapuestoeventual)
    {
        $this->craciontarifapuestoeventual = $craciontarifapuestoeventual;

        return $this;
    }

    /**
     * Get craciontarifapuestoeventual
     *
     * @return \DateTime
     */
    public function getCraciontarifapuestoeventual()
    {
        return $this->craciontarifapuestoeventual;
    }

    /**
     * Set modificaciontarifapuestoeventual
     *
     * @param \DateTime $modificaciontarifapuestoeventual
     *
     * @return Ttarifapuestoeventual
     */
    public function setModificaciontarifapuestoeventual($modificaciontarifapuestoeventual)
    {
        $this->modificaciontarifapuestoeventual = $modificaciontarifapuestoeventual;

        return $this;
    }

    /**
     * Get modificaciontarifapuestoeventual
     *
     * @return \DateTime
     */
    public function getModificaciontarifapuestoeventual()
    {
        return $this->modificaciontarifapuestoeventual;
    }

    /**
     * Get pkidtarifapuestoeventual
     *
     * @return integer
     */
    public function getPkidtarifapuestoeventual()
    {
        return $this->pkidtarifapuestoeventual;
    }

    /**
     * Set fkidplaza
     *
     * @param \ModeloBundle\Entity\Tplaza $fkidplaza
     *
     * @return Ttarifapuestoeventual
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
    private $tarifapuestoeventualactivo = '1';


    /**
     * Set tarifapuestoeventualactivo
     *
     * @param boolean $tarifapuestoeventualactivo
     *
     * @return Ttarifapuestoeventual
     */
    public function setTarifapuestoeventualactivo($tarifapuestoeventualactivo)
    {
        $this->tarifapuestoeventualactivo = $tarifapuestoeventualactivo;

        return $this;
    }

    /**
     * Get tarifapuestoeventualactivo
     *
     * @return boolean
     */
    public function getTarifapuestoeventualactivo()
    {
        return $this->tarifapuestoeventualactivo;
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
     * @return Ttarifapuestoeventual
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
     * @return Ttarifapuestoeventual
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
     * @return Ttarifapuestoeventual
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
