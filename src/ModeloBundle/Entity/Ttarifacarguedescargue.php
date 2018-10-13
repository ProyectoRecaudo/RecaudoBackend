<?php

namespace ModeloBundle\Entity;

/**
 * Ttarifacarguedescargue
 */
class Ttarifacarguedescargue
{
    /**
     * @var float
     */
    private $valorcargue = '0';

    /**
     * @var string
     */
    private $numeroresoluciontarifacarguedescargue;

    /**
     * @var string
     */
    private $documentoresoluciontarifacarguedescargue;

    /**
     * @var \DateTime
     */
    private $craciontarifacarguedescargue = 'now()';

    /**
     * @var \DateTime
     */
    private $modificaciontarifacarguedescargue = 'now()';

    /**
     * @var float
     */
    private $valordescargue;

    /**
     * @var integer
     */
    private $pkidtarifacarguedescargue;

    /**
     * @var \ModeloBundle\Entity\Tplaza
     */
    private $fkidplaza;


    /**
     * Set valorcargue
     *
     * @param float $valorcargue
     *
     * @return Ttarifacarguedescargue
     */
    public function setValorcargue($valorcargue)
    {
        $this->valorcargue = $valorcargue;

        return $this;
    }

    /**
     * Get valorcargue
     *
     * @return float
     */
    public function getValorcargue()
    {
        return $this->valorcargue;
    }

    /**
     * Set numeroresoluciontarifacarguedescargue
     *
     * @param string $numeroresoluciontarifacarguedescargue
     *
     * @return Ttarifacarguedescargue
     */
    public function setNumeroresoluciontarifacarguedescargue($numeroresoluciontarifacarguedescargue)
    {
        $this->numeroresoluciontarifacarguedescargue = $numeroresoluciontarifacarguedescargue;

        return $this;
    }

    /**
     * Get numeroresoluciontarifacarguedescargue
     *
     * @return string
     */
    public function getNumeroresoluciontarifacarguedescargue()
    {
        return $this->numeroresoluciontarifacarguedescargue;
    }

    /**
     * Set documentoresoluciontarifacarguedescargue
     *
     * @param string $documentoresoluciontarifacarguedescargue
     *
     * @return Ttarifacarguedescargue
     */
    public function setDocumentoresoluciontarifacarguedescargue($documentoresoluciontarifacarguedescargue)
    {
        $this->documentoresoluciontarifacarguedescargue = $documentoresoluciontarifacarguedescargue;

        return $this;
    }

    /**
     * Get documentoresoluciontarifacarguedescargue
     *
     * @return string
     */
    public function getDocumentoresoluciontarifacarguedescargue()
    {
        return $this->documentoresoluciontarifacarguedescargue;
    }

    /**
     * Set craciontarifacarguedescargue
     *
     * @param \DateTime $craciontarifacarguedescargue
     *
     * @return Ttarifacarguedescargue
     */
    public function setCraciontarifacarguedescargue($craciontarifacarguedescargue)
    {
        $this->craciontarifacarguedescargue = $craciontarifacarguedescargue;

        return $this;
    }

    /**
     * Get craciontarifacarguedescargue
     *
     * @return \DateTime
     */
    public function getCraciontarifacarguedescargue()
    {
        return $this->craciontarifacarguedescargue;
    }

    /**
     * Set modificaciontarifacarguedescargue
     *
     * @param \DateTime $modificaciontarifacarguedescargue
     *
     * @return Ttarifacarguedescargue
     */
    public function setModificaciontarifacarguedescargue($modificaciontarifacarguedescargue)
    {
        $this->modificaciontarifacarguedescargue = $modificaciontarifacarguedescargue;

        return $this;
    }

    /**
     * Get modificaciontarifacarguedescargue
     *
     * @return \DateTime
     */
    public function getModificaciontarifacarguedescargue()
    {
        return $this->modificaciontarifacarguedescargue;
    }

    /**
     * Set valordescargue
     *
     * @param float $valordescargue
     *
     * @return Ttarifacarguedescargue
     */
    public function setValordescargue($valordescargue)
    {
        $this->valordescargue = $valordescargue;

        return $this;
    }

    /**
     * Get valordescargue
     *
     * @return float
     */
    public function getValordescargue()
    {
        return $this->valordescargue;
    }

    /**
     * Get pkidtarifacarguedescargue
     *
     * @return integer
     */
    public function getPkidtarifacarguedescargue()
    {
        return $this->pkidtarifacarguedescargue;
    }

    /**
     * Set fkidplaza
     *
     * @param \ModeloBundle\Entity\Tplaza $fkidplaza
     *
     * @return Ttarifacarguedescargue
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
