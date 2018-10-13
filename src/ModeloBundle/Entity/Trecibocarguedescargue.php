<?php

namespace ModeloBundle\Entity;

/**
 * Trecibocarguedescargue
 */
class Trecibocarguedescargue
{
    /**
     * @var string
     */
    private $numerorecibocarguedescargue;

    /**
     * @var string
     */
    private $numeroplacacarguedescargue;

    /**
     * @var float
     */
    private $valorecibocarguedescargue;

    /**
     * @var \DateTime
     */
    private $creacionrecibocarguedescargue = 'now()';

    /**
     * @var \DateTime
     */
    private $modificacionrecibocarguedescargue;

    /**
     * @var integer
     */
    private $pkidrecibocarguedescargue;

    /**
     * @var \ModeloBundle\Entity\Ttarifacarguedescargue
     */
    private $fkidtarifacarguedescargue;


    /**
     * Set numerorecibocarguedescargue
     *
     * @param string $numerorecibocarguedescargue
     *
     * @return Trecibocarguedescargue
     */
    public function setNumerorecibocarguedescargue($numerorecibocarguedescargue)
    {
        $this->numerorecibocarguedescargue = $numerorecibocarguedescargue;

        return $this;
    }

    /**
     * Get numerorecibocarguedescargue
     *
     * @return string
     */
    public function getNumerorecibocarguedescargue()
    {
        return $this->numerorecibocarguedescargue;
    }

    /**
     * Set numeroplacacarguedescargue
     *
     * @param string $numeroplacacarguedescargue
     *
     * @return Trecibocarguedescargue
     */
    public function setNumeroplacacarguedescargue($numeroplacacarguedescargue)
    {
        $this->numeroplacacarguedescargue = $numeroplacacarguedescargue;

        return $this;
    }

    /**
     * Get numeroplacacarguedescargue
     *
     * @return string
     */
    public function getNumeroplacacarguedescargue()
    {
        return $this->numeroplacacarguedescargue;
    }

    /**
     * Set valorecibocarguedescargue
     *
     * @param float $valorecibocarguedescargue
     *
     * @return Trecibocarguedescargue
     */
    public function setValorecibocarguedescargue($valorecibocarguedescargue)
    {
        $this->valorecibocarguedescargue = $valorecibocarguedescargue;

        return $this;
    }

    /**
     * Get valorecibocarguedescargue
     *
     * @return float
     */
    public function getValorecibocarguedescargue()
    {
        return $this->valorecibocarguedescargue;
    }

    /**
     * Set creacionrecibocarguedescargue
     *
     * @param \DateTime $creacionrecibocarguedescargue
     *
     * @return Trecibocarguedescargue
     */
    public function setCreacionrecibocarguedescargue($creacionrecibocarguedescargue)
    {
        $this->creacionrecibocarguedescargue = $creacionrecibocarguedescargue;

        return $this;
    }

    /**
     * Get creacionrecibocarguedescargue
     *
     * @return \DateTime
     */
    public function getCreacionrecibocarguedescargue()
    {
        return $this->creacionrecibocarguedescargue;
    }

    /**
     * Set modificacionrecibocarguedescargue
     *
     * @param \DateTime $modificacionrecibocarguedescargue
     *
     * @return Trecibocarguedescargue
     */
    public function setModificacionrecibocarguedescargue($modificacionrecibocarguedescargue)
    {
        $this->modificacionrecibocarguedescargue = $modificacionrecibocarguedescargue;

        return $this;
    }

    /**
     * Get modificacionrecibocarguedescargue
     *
     * @return \DateTime
     */
    public function getModificacionrecibocarguedescargue()
    {
        return $this->modificacionrecibocarguedescargue;
    }

    /**
     * Get pkidrecibocarguedescargue
     *
     * @return integer
     */
    public function getPkidrecibocarguedescargue()
    {
        return $this->pkidrecibocarguedescargue;
    }

    /**
     * Set fkidtarifacarguedescargue
     *
     * @param \ModeloBundle\Entity\Ttarifacarguedescargue $fkidtarifacarguedescargue
     *
     * @return Trecibocarguedescargue
     */
    public function setFkidtarifacarguedescargue(\ModeloBundle\Entity\Ttarifacarguedescargue $fkidtarifacarguedescargue = null)
    {
        $this->fkidtarifacarguedescargue = $fkidtarifacarguedescargue;

        return $this;
    }

    /**
     * Get fkidtarifacarguedescargue
     *
     * @return \ModeloBundle\Entity\Ttarifacarguedescargue
     */
    public function getFkidtarifacarguedescargue()
    {
        return $this->fkidtarifacarguedescargue;
    }
}
