<?php

namespace ModeloBundle\Entity;

/**
 * Tconfiguracion
 */
class Tconfiguracion
{
    /**
     * @var string
     */
    private $claveconfiguracion;

    /**
     * @var string
     */
    private $valorconfiguracion;

    /**
     * @var \DateTime
     */
    private $fechaconfiguracion = 'now()';

    /**
     * @var string
     */
    private $valoranteriorconfiguracion;

    /**
     * @var integer
     */
    private $pkidconfiguracion;


    /**
     * Set claveconfiguracion
     *
     * @param string $claveconfiguracion
     *
     * @return Tconfiguracion
     */
    public function setClaveconfiguracion($claveconfiguracion)
    {
        $this->claveconfiguracion = $claveconfiguracion;

        return $this;
    }

    /**
     * Get claveconfiguracion
     *
     * @return string
     */
    public function getClaveconfiguracion()
    {
        return $this->claveconfiguracion;
    }

    /**
     * Set valorconfiguracion
     *
     * @param string $valorconfiguracion
     *
     * @return Tconfiguracion
     */
    public function setValorconfiguracion($valorconfiguracion)
    {
        $this->valorconfiguracion = $valorconfiguracion;

        return $this;
    }

    /**
     * Get valorconfiguracion
     *
     * @return string
     */
    public function getValorconfiguracion()
    {
        return $this->valorconfiguracion;
    }

    /**
     * Set fechaconfiguracion
     *
     * @param \DateTime $fechaconfiguracion
     *
     * @return Tconfiguracion
     */
    public function setFechaconfiguracion($fechaconfiguracion)
    {
        $this->fechaconfiguracion = $fechaconfiguracion;

        return $this;
    }

    /**
     * Get fechaconfiguracion
     *
     * @return \DateTime
     */
    public function getFechaconfiguracion()
    {
        return $this->fechaconfiguracion;
    }

    /**
     * Set valoranteriorconfiguracion
     *
     * @param string $valoranteriorconfiguracion
     *
     * @return Tconfiguracion
     */
    public function setValoranteriorconfiguracion($valoranteriorconfiguracion)
    {
        $this->valoranteriorconfiguracion = $valoranteriorconfiguracion;

        return $this;
    }

    /**
     * Get valoranteriorconfiguracion
     *
     * @return string
     */
    public function getValoranteriorconfiguracion()
    {
        return $this->valoranteriorconfiguracion;
    }

    /**
     * Get pkidconfiguracion
     *
     * @return integer
     */
    public function getPkidconfiguracion()
    {
        return $this->pkidconfiguracion;
    }
}
