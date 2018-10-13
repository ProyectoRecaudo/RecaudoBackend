<?php

namespace ModeloBundle\Entity;

/**
 * Tcartera
 */
class Tcartera
{
    /**
     * @var integer
     */
    private $mesesdeuda;

    /**
     * @var float
     */
    private $valordeuda;

    /**
     * @var boolean
     */
    private $carteraactiva = '1';

    /**
     * @var \DateTime
     */
    private $creacioncartera = 'now()';

    /**
     * @var \DateTime
     */
    private $modificacioncartera = 'now()';

    /**
     * @var integer
     */
    private $pkidcartera;

    /**
     * @var \ModeloBundle\Entity\Talerta
     */
    private $fkidalerta;

    /**
     * @var \ModeloBundle\Entity\Tbeneficiario
     */
    private $fkidbeneficiario;


    /**
     * Set mesesdeuda
     *
     * @param integer $mesesdeuda
     *
     * @return Tcartera
     */
    public function setMesesdeuda($mesesdeuda)
    {
        $this->mesesdeuda = $mesesdeuda;

        return $this;
    }

    /**
     * Get mesesdeuda
     *
     * @return integer
     */
    public function getMesesdeuda()
    {
        return $this->mesesdeuda;
    }

    /**
     * Set valordeuda
     *
     * @param float $valordeuda
     *
     * @return Tcartera
     */
    public function setValordeuda($valordeuda)
    {
        $this->valordeuda = $valordeuda;

        return $this;
    }

    /**
     * Get valordeuda
     *
     * @return float
     */
    public function getValordeuda()
    {
        return $this->valordeuda;
    }

    /**
     * Set carteraactiva
     *
     * @param boolean $carteraactiva
     *
     * @return Tcartera
     */
    public function setCarteraactiva($carteraactiva)
    {
        $this->carteraactiva = $carteraactiva;

        return $this;
    }

    /**
     * Get carteraactiva
     *
     * @return boolean
     */
    public function getCarteraactiva()
    {
        return $this->carteraactiva;
    }

    /**
     * Set creacioncartera
     *
     * @param \DateTime $creacioncartera
     *
     * @return Tcartera
     */
    public function setCreacioncartera($creacioncartera)
    {
        $this->creacioncartera = $creacioncartera;

        return $this;
    }

    /**
     * Get creacioncartera
     *
     * @return \DateTime
     */
    public function getCreacioncartera()
    {
        return $this->creacioncartera;
    }

    /**
     * Set modificacioncartera
     *
     * @param \DateTime $modificacioncartera
     *
     * @return Tcartera
     */
    public function setModificacioncartera($modificacioncartera)
    {
        $this->modificacioncartera = $modificacioncartera;

        return $this;
    }

    /**
     * Get modificacioncartera
     *
     * @return \DateTime
     */
    public function getModificacioncartera()
    {
        return $this->modificacioncartera;
    }

    /**
     * Get pkidcartera
     *
     * @return integer
     */
    public function getPkidcartera()
    {
        return $this->pkidcartera;
    }

    /**
     * Set fkidalerta
     *
     * @param \ModeloBundle\Entity\Talerta $fkidalerta
     *
     * @return Tcartera
     */
    public function setFkidalerta(\ModeloBundle\Entity\Talerta $fkidalerta = null)
    {
        $this->fkidalerta = $fkidalerta;

        return $this;
    }

    /**
     * Get fkidalerta
     *
     * @return \ModeloBundle\Entity\Talerta
     */
    public function getFkidalerta()
    {
        return $this->fkidalerta;
    }

    /**
     * Set fkidbeneficiario
     *
     * @param \ModeloBundle\Entity\Tbeneficiario $fkidbeneficiario
     *
     * @return Tcartera
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
}
