<?php

namespace ModeloBundle\Entity;

/**
 * Tplaza
 */
class Tplaza
{
    /**
     * @var integer
     */
    private $pkidplaza;

    /**
     * @var string
     */
    private $codigoplaza;

    /**
     * @var string
     */
    private $nombreplaza;

    /**
     * @var boolean
     */
    private $plazaactivo = true;

    /**
     * @var \DateTime
     */
    private $creacionplaza = 'now()';

    /**
     * @var \DateTime
     */
    private $modificacionplaza = 'now()';


    /**
     * Get pkidplaza
     *
     * @return integer
     */
    public function getPkidplaza()
    {
        return $this->pkidplaza;
    }

    /**
     * Set codigoplaza
     *
     * @param string $codigoplaza
     *
     * @return Tplaza
     */
    public function setCodigoplaza($codigoplaza)
    {
        $this->codigoplaza = $codigoplaza;

        return $this;
    }

    /**
     * Get codigoplaza
     *
     * @return string
     */
    public function getCodigoplaza()
    {
        return $this->codigoplaza;
    }

    /**
     * Set nombreplaza
     *
     * @param string $nombreplaza
     *
     * @return Tplaza
     */
    public function setNombreplaza($nombreplaza)
    {
        $this->nombreplaza = $nombreplaza;

        return $this;
    }

    /**
     * Get nombreplaza
     *
     * @return string
     */
    public function getNombreplaza()
    {
        return $this->nombreplaza;
    }

    /**
     * Set plazaactivo
     *
     * @param boolean $plazaactivo
     *
     * @return Tplaza
     */
    public function setPlazaactivo($plazaactivo)
    {
        $this->plazaactivo = $plazaactivo;

        return $this;
    }

    /**
     * Get plazaactivo
     *
     * @return boolean
     */
    public function getPlazaactivo()
    {
        return $this->plazaactivo;
    }

    /**
     * Set creacionplaza
     *
     * @param \DateTime $creacionplaza
     *
     * @return Tplaza
     */
    public function setCreacionplaza($creacionplaza)
    {
        $this->creacionplaza = $creacionplaza;

        return $this;
    }

    /**
     * Get creacionplaza
     *
     * @return \DateTime
     */
    public function getCreacionplaza()
    {
        return $this->creacionplaza;
    }

    /**
     * Set modificacionplaza
     *
     * @param \DateTime $modificacionplaza
     *
     * @return Tplaza
     */
    public function setModificacionplaza($modificacionplaza)
    {
        $this->modificacionplaza = $modificacionplaza;

        return $this;
    }

    /**
     * Get modificacionplaza
     *
     * @return \DateTime
     */
    public function getModificacionplaza()
    {
        return $this->modificacionplaza;
    }
}
