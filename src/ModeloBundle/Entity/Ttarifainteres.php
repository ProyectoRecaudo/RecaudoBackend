<?php

namespace ModeloBundle\Entity;

/**
 * Ttarifainteres
 */
class Ttarifainteres
{
    /**
     * @var float
     */
    private $valortarifainteres = '0';

    /**
     * @var string
     */
    private $descripciontarifainteres;

    /**
     * @var string
     */
    private $numeroresoluciontarifainteres;

    /**
     * @var string
     */
    private $documentoresoluciontarifainteres;

    /**
     * @var \DateTime
     */
    private $craciontarifainteres = 'now()';

    /**
     * @var \DateTime
     */
    private $modificaciontarifainteres = 'now()';

    /**
     * @var integer
     */
    private $pkidtarifainteres;

    /**
     * @var \ModeloBundle\Entity\Tplaza
     */
    private $fkidplaza;


    /**
     * Set valortarifainteres
     *
     * @param float $valortarifainteres
     *
     * @return Ttarifainteres
     */
    public function setValortarifainteres($valortarifainteres)
    {
        $this->valortarifainteres = $valortarifainteres;

        return $this;
    }

    /**
     * Get valortarifainteres
     *
     * @return float
     */
    public function getValortarifainteres()
    {
        return $this->valortarifainteres;
    }

    /**
     * Set descripciontarifainteres
     *
     * @param string $descripciontarifainteres
     *
     * @return Ttarifainteres
     */
    public function setDescripciontarifainteres($descripciontarifainteres)
    {
        $this->descripciontarifainteres = $descripciontarifainteres;

        return $this;
    }

    /**
     * Get descripciontarifainteres
     *
     * @return string
     */
    public function getDescripciontarifainteres()
    {
        return $this->descripciontarifainteres;
    }

    /**
     * Set numeroresoluciontarifainteres
     *
     * @param string $numeroresoluciontarifainteres
     *
     * @return Ttarifainteres
     */
    public function setNumeroresoluciontarifainteres($numeroresoluciontarifainteres)
    {
        $this->numeroresoluciontarifainteres = $numeroresoluciontarifainteres;

        return $this;
    }

    /**
     * Get numeroresoluciontarifainteres
     *
     * @return string
     */
    public function getNumeroresoluciontarifainteres()
    {
        return $this->numeroresoluciontarifainteres;
    }

    /**
     * Set documentoresoluciontarifainteres
     *
     * @param string $documentoresoluciontarifainteres
     *
     * @return Ttarifainteres
     */
    public function setDocumentoresoluciontarifainteres($documentoresoluciontarifainteres)
    {
        $this->documentoresoluciontarifainteres = $documentoresoluciontarifainteres;

        return $this;
    }

    /**
     * Get documentoresoluciontarifainteres
     *
     * @return string
     */
    public function getDocumentoresoluciontarifainteres()
    {
        return $this->documentoresoluciontarifainteres;
    }

    /**
     * Set craciontarifainteres
     *
     * @param \DateTime $craciontarifainteres
     *
     * @return Ttarifainteres
     */
    public function setCraciontarifainteres($craciontarifainteres)
    {
        $this->craciontarifainteres = $craciontarifainteres;

        return $this;
    }

    /**
     * Get craciontarifainteres
     *
     * @return \DateTime
     */
    public function getCraciontarifainteres()
    {
        return $this->craciontarifainteres;
    }

    /**
     * Set modificaciontarifainteres
     *
     * @param \DateTime $modificaciontarifainteres
     *
     * @return Ttarifainteres
     */
    public function setModificaciontarifainteres($modificaciontarifainteres)
    {
        $this->modificaciontarifainteres = $modificaciontarifainteres;

        return $this;
    }

    /**
     * Get modificaciontarifainteres
     *
     * @return \DateTime
     */
    public function getModificaciontarifainteres()
    {
        return $this->modificaciontarifainteres;
    }

    /**
     * Get pkidtarifainteres
     *
     * @return integer
     */
    public function getPkidtarifainteres()
    {
        return $this->pkidtarifainteres;
    }

    /**
     * Set fkidplaza
     *
     * @param \ModeloBundle\Entity\Tplaza $fkidplaza
     *
     * @return Ttarifainteres
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
    private $tarifainteresactivo = '1';


    /**
     * Set tarifainteresactivo
     *
     * @param boolean $tarifainteresactivo
     *
     * @return Ttarifainteres
     */
    public function setTarifainteresactivo($tarifainteresactivo)
    {
        $this->tarifainteresactivo = $tarifainteresactivo;

        return $this;
    }

    /**
     * Get tarifainteresactivo
     *
     * @return boolean
     */
    public function getTarifainteresactivo()
    {
        return $this->tarifainteresactivo;
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
     * @return Ttarifainteres
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
     * @return Ttarifainteres
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
     * @return Ttarifainteres
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
