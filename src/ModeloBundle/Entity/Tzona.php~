<?php

namespace ModeloBundle\Entity;

/**
 * Tzona
 */
class Tzona
{
    /**
     * @var integer
     */
    private $pkidzona;

    /**
     * @var string
     */
    private $codigozona;

    /**
     * @var string
     */
    private $nombrezona;

    /**
     * @var boolean
     */
    private $zonaactivo = true;

    /**
     * @var \DateTime
     */
    private $creacionzona = 'now()';

    /**
     * @var \DateTime
     */
    private $modificacionzona = 'now()';

    /**
     * @var \ModeloBundle\Entity\Tplaza
     */
    private $fkidplaza;

    /**
     * @var \ModeloBundle\Entity\Tusuario
     */
    private $fkidusuario;


    /**
     * Get pkidzona
     *
     * @return integer
     */
    public function getPkidzona()
    {
        return $this->pkidzona;
    }

    /**
     * Set codigozona
     *
     * @param string $codigozona
     *
     * @return Tzona
     */
    public function setCodigozona($codigozona)
    {
        $this->codigozona = $codigozona;

        return $this;
    }

    /**
     * Get codigozona
     *
     * @return string
     */
    public function getCodigozona()
    {
        return $this->codigozona;
    }

    /**
     * Set nombrezona
     *
     * @param string $nombrezona
     *
     * @return Tzona
     */
    public function setNombrezona($nombrezona)
    {
        $this->nombrezona = $nombrezona;

        return $this;
    }

    /**
     * Get nombrezona
     *
     * @return string
     */
    public function getNombrezona()
    {
        return $this->nombrezona;
    }

    /**
     * Set zonaactivo
     *
     * @param boolean $zonaactivo
     *
     * @return Tzona
     */
    public function setZonaactivo($zonaactivo)
    {
        $this->zonaactivo = $zonaactivo;

        return $this;
    }

    /**
     * Get zonaactivo
     *
     * @return boolean
     */
    public function getZonaactivo()
    {
        return $this->zonaactivo;
    }

    /**
     * Set creacionzona
     *
     * @param \DateTime $creacionzona
     *
     * @return Tzona
     */
    public function setCreacionzona($creacionzona)
    {
        $this->creacionzona = $creacionzona;

        return $this;
    }

    /**
     * Get creacionzona
     *
     * @return \DateTime
     */
    public function getCreacionzona()
    {
        return $this->creacionzona;
    }

    /**
     * Set modificacionzona
     *
     * @param \DateTime $modificacionzona
     *
     * @return Tzona
     */
    public function setModificacionzona($modificacionzona)
    {
        $this->modificacionzona = $modificacionzona;

        return $this;
    }

    /**
     * Get modificacionzona
     *
     * @return \DateTime
     */
    public function getModificacionzona()
    {
        return $this->modificacionzona;
    }

    /**
     * Set fkidplaza
     *
     * @param \ModeloBundle\Entity\Tplaza $fkidplaza
     *
     * @return Tzona
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
     * Set fkidusuario
     *
     * @param \ModeloBundle\Entity\Tusuario $fkidusuario
     *
     * @return Tzona
     */
    public function setFkidusuario(\ModeloBundle\Entity\Tusuario $fkidusuario = null)
    {
        $this->fkidusuario = $fkidusuario;

        return $this;
    }

    /**
     * Get fkidusuario
     *
     * @return \ModeloBundle\Entity\Tusuario
     */
    public function getFkidusuario()
    {
        return $this->fkidusuario;
    }
}
