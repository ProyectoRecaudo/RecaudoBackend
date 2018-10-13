<?php

namespace ModeloBundle\Entity;

/**
 * Treciboanimal
 */
class Treciboanimal
{
    /**
     * @var string
     */
    private $numeroreciboanimal;

    /**
     * @var float
     */
    private $valoreciboanimal;

    /**
     * @var \DateTime
     */
    private $creacionreciboanimal = 'now()';

    /**
     * @var \DateTime
     */
    private $modificacionreciboanimal = 'now()';

    /**
     * @var integer
     */
    private $edadanimal;

    /**
     * @var string
     */
    private $caracteristicasanimal;

    /**
     * @var string
     */
    private $nombrevendedor;

    /**
     * @var string
     */
    private $identificacionvendedor;

    /**
     * @var string
     */
    private $nombrecomprador;

    /**
     * @var string
     */
    private $identificacioncomprador;

    /**
     * @var integer
     */
    private $cantidadanimales;

    /**
     * @var string
     */
    private $numeroguiaica;

    /**
     * @var integer
     */
    private $pkidreciboanimal;

    /**
     * @var \ModeloBundle\Entity\Ttarifaanimal
     */
    private $fkidtarifaanimal;


    /**
     * Set numeroreciboanimal
     *
     * @param string $numeroreciboanimal
     *
     * @return Treciboanimal
     */
    public function setNumeroreciboanimal($numeroreciboanimal)
    {
        $this->numeroreciboanimal = $numeroreciboanimal;

        return $this;
    }

    /**
     * Get numeroreciboanimal
     *
     * @return string
     */
    public function getNumeroreciboanimal()
    {
        return $this->numeroreciboanimal;
    }

    /**
     * Set valoreciboanimal
     *
     * @param float $valoreciboanimal
     *
     * @return Treciboanimal
     */
    public function setValoreciboanimal($valoreciboanimal)
    {
        $this->valoreciboanimal = $valoreciboanimal;

        return $this;
    }

    /**
     * Get valoreciboanimal
     *
     * @return float
     */
    public function getValoreciboanimal()
    {
        return $this->valoreciboanimal;
    }

    /**
     * Set creacionreciboanimal
     *
     * @param \DateTime $creacionreciboanimal
     *
     * @return Treciboanimal
     */
    public function setCreacionreciboanimal($creacionreciboanimal)
    {
        $this->creacionreciboanimal = $creacionreciboanimal;

        return $this;
    }

    /**
     * Get creacionreciboanimal
     *
     * @return \DateTime
     */
    public function getCreacionreciboanimal()
    {
        return $this->creacionreciboanimal;
    }

    /**
     * Set modificacionreciboanimal
     *
     * @param \DateTime $modificacionreciboanimal
     *
     * @return Treciboanimal
     */
    public function setModificacionreciboanimal($modificacionreciboanimal)
    {
        $this->modificacionreciboanimal = $modificacionreciboanimal;

        return $this;
    }

    /**
     * Get modificacionreciboanimal
     *
     * @return \DateTime
     */
    public function getModificacionreciboanimal()
    {
        return $this->modificacionreciboanimal;
    }

    /**
     * Set edadanimal
     *
     * @param integer $edadanimal
     *
     * @return Treciboanimal
     */
    public function setEdadanimal($edadanimal)
    {
        $this->edadanimal = $edadanimal;

        return $this;
    }

    /**
     * Get edadanimal
     *
     * @return integer
     */
    public function getEdadanimal()
    {
        return $this->edadanimal;
    }

    /**
     * Set caracteristicasanimal
     *
     * @param string $caracteristicasanimal
     *
     * @return Treciboanimal
     */
    public function setCaracteristicasanimal($caracteristicasanimal)
    {
        $this->caracteristicasanimal = $caracteristicasanimal;

        return $this;
    }

    /**
     * Get caracteristicasanimal
     *
     * @return string
     */
    public function getCaracteristicasanimal()
    {
        return $this->caracteristicasanimal;
    }

    /**
     * Set nombrevendedor
     *
     * @param string $nombrevendedor
     *
     * @return Treciboanimal
     */
    public function setNombrevendedor($nombrevendedor)
    {
        $this->nombrevendedor = $nombrevendedor;

        return $this;
    }

    /**
     * Get nombrevendedor
     *
     * @return string
     */
    public function getNombrevendedor()
    {
        return $this->nombrevendedor;
    }

    /**
     * Set identificacionvendedor
     *
     * @param string $identificacionvendedor
     *
     * @return Treciboanimal
     */
    public function setIdentificacionvendedor($identificacionvendedor)
    {
        $this->identificacionvendedor = $identificacionvendedor;

        return $this;
    }

    /**
     * Get identificacionvendedor
     *
     * @return string
     */
    public function getIdentificacionvendedor()
    {
        return $this->identificacionvendedor;
    }

    /**
     * Set nombrecomprador
     *
     * @param string $nombrecomprador
     *
     * @return Treciboanimal
     */
    public function setNombrecomprador($nombrecomprador)
    {
        $this->nombrecomprador = $nombrecomprador;

        return $this;
    }

    /**
     * Get nombrecomprador
     *
     * @return string
     */
    public function getNombrecomprador()
    {
        return $this->nombrecomprador;
    }

    /**
     * Set identificacioncomprador
     *
     * @param string $identificacioncomprador
     *
     * @return Treciboanimal
     */
    public function setIdentificacioncomprador($identificacioncomprador)
    {
        $this->identificacioncomprador = $identificacioncomprador;

        return $this;
    }

    /**
     * Get identificacioncomprador
     *
     * @return string
     */
    public function getIdentificacioncomprador()
    {
        return $this->identificacioncomprador;
    }

    /**
     * Set cantidadanimales
     *
     * @param integer $cantidadanimales
     *
     * @return Treciboanimal
     */
    public function setCantidadanimales($cantidadanimales)
    {
        $this->cantidadanimales = $cantidadanimales;

        return $this;
    }

    /**
     * Get cantidadanimales
     *
     * @return integer
     */
    public function getCantidadanimales()
    {
        return $this->cantidadanimales;
    }

    /**
     * Set numeroguiaica
     *
     * @param string $numeroguiaica
     *
     * @return Treciboanimal
     */
    public function setNumeroguiaica($numeroguiaica)
    {
        $this->numeroguiaica = $numeroguiaica;

        return $this;
    }

    /**
     * Get numeroguiaica
     *
     * @return string
     */
    public function getNumeroguiaica()
    {
        return $this->numeroguiaica;
    }

    /**
     * Get pkidreciboanimal
     *
     * @return integer
     */
    public function getPkidreciboanimal()
    {
        return $this->pkidreciboanimal;
    }

    /**
     * Set fkidtarifaanimal
     *
     * @param \ModeloBundle\Entity\Ttarifaanimal $fkidtarifaanimal
     *
     * @return Treciboanimal
     */
    public function setFkidtarifaanimal(\ModeloBundle\Entity\Ttarifaanimal $fkidtarifaanimal = null)
    {
        $this->fkidtarifaanimal = $fkidtarifaanimal;

        return $this;
    }

    /**
     * Get fkidtarifaanimal
     *
     * @return \ModeloBundle\Entity\Ttarifaanimal
     */
    public function getFkidtarifaanimal()
    {
        return $this->fkidtarifaanimal;
    }
    /**
     * @var \ModeloBundle\Entity\Tcategoriaanmal
     */
    private $fkidcategoriaanimal;

    /**
     * @var \ModeloBundle\Entity\Ttercero
     */
    private $fkttercerocomprador;

    /**
     * @var \ModeloBundle\Entity\Ttercero
     */
    private $fkttercerovendedor;


    /**
     * Set fkidcategoriaanimal
     *
     * @param \ModeloBundle\Entity\Tcategoriaanmal $fkidcategoriaanimal
     *
     * @return Treciboanimal
     */
    public function setFkidcategoriaanimal(\ModeloBundle\Entity\Tcategoriaanmal $fkidcategoriaanimal = null)
    {
        $this->fkidcategoriaanimal = $fkidcategoriaanimal;

        return $this;
    }

    /**
     * Get fkidcategoriaanimal
     *
     * @return \ModeloBundle\Entity\Tcategoriaanmal
     */
    public function getFkidcategoriaanimal()
    {
        return $this->fkidcategoriaanimal;
    }

    /**
     * Set fkttercerocomprador
     *
     * @param \ModeloBundle\Entity\Ttercero $fkttercerocomprador
     *
     * @return Treciboanimal
     */
    public function setFkttercerocomprador(\ModeloBundle\Entity\Ttercero $fkttercerocomprador = null)
    {
        $this->fkttercerocomprador = $fkttercerocomprador;

        return $this;
    }

    /**
     * Get fkttercerocomprador
     *
     * @return \ModeloBundle\Entity\Ttercero
     */
    public function getFkttercerocomprador()
    {
        return $this->fkttercerocomprador;
    }

    /**
     * Set fkttercerovendedor
     *
     * @param \ModeloBundle\Entity\Ttercero $fkttercerovendedor
     *
     * @return Treciboanimal
     */
    public function setFkttercerovendedor(\ModeloBundle\Entity\Ttercero $fkttercerovendedor = null)
    {
        $this->fkttercerovendedor = $fkttercerovendedor;

        return $this;
    }

    /**
     * Get fkttercerovendedor
     *
     * @return \ModeloBundle\Entity\Ttercero
     */
    public function getFkttercerovendedor()
    {
        return $this->fkttercerovendedor;
    }
    /**
     * @var float
     */
    private $valortarifa;

    /**
     * @var string
     */
    private $nombreplaza;

    /**
     * @var boolean
     */
    private $reciboanimalactivo = '1';


    /**
     * Set valortarifa
     *
     * @param float $valortarifa
     *
     * @return Treciboanimal
     */
    public function setValortarifa($valortarifa)
    {
        $this->valortarifa = $valortarifa;

        return $this;
    }

    /**
     * Get valortarifa
     *
     * @return float
     */
    public function getValortarifa()
    {
        return $this->valortarifa;
    }

    /**
     * Set nombreplaza
     *
     * @param string $nombreplaza
     *
     * @return Treciboanimal
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
     * Set reciboanimalactivo
     *
     * @param boolean $reciboanimalactivo
     *
     * @return Treciboanimal
     */
    public function setReciboanimalactivo($reciboanimalactivo)
    {
        $this->reciboanimalactivo = $reciboanimalactivo;

        return $this;
    }

    /**
     * Get reciboanimalactivo
     *
     * @return boolean
     */
    public function getReciboanimalactivo()
    {
        return $this->reciboanimalactivo;
    }
    /**
     * @var string
     */
    private $nombrecategoriaanimal;

    /**
     * @var string
     */
    private $nombretipoanimal;

    /**
     * @var string
     */
    private $nombresector;

    /**
     * @var string
     */
    private $nombreespecieanimal;

    /**
     * @var \ModeloBundle\Entity\Tespecieanimal
     */
    private $fkidespecieanimal;

    /**
     * @var \ModeloBundle\Entity\Tplaza
     */
    private $fkidplaza;

    /**
     * @var \ModeloBundle\Entity\Tsector
     */
    private $fkidsector;

    /**
     * @var \ModeloBundle\Entity\Ttercero
     */
    private $fkidttercerocomprador;

    /**
     * @var \ModeloBundle\Entity\Ttercero
     */
    private $fkidttercerovendedor;

    /**
     * @var \ModeloBundle\Entity\Ttipoanimal
     */
    private $fkidtipoanimal;


    /**
     * Set nombrecategoriaanimal
     *
     * @param string $nombrecategoriaanimal
     *
     * @return Treciboanimal
     */
    public function setNombrecategoriaanimal($nombrecategoriaanimal)
    {
        $this->nombrecategoriaanimal = $nombrecategoriaanimal;

        return $this;
    }

    /**
     * Get nombrecategoriaanimal
     *
     * @return string
     */
    public function getNombrecategoriaanimal()
    {
        return $this->nombrecategoriaanimal;
    }

    /**
     * Set nombretipoanimal
     *
     * @param string $nombretipoanimal
     *
     * @return Treciboanimal
     */
    public function setNombretipoanimal($nombretipoanimal)
    {
        $this->nombretipoanimal = $nombretipoanimal;

        return $this;
    }

    /**
     * Get nombretipoanimal
     *
     * @return string
     */
    public function getNombretipoanimal()
    {
        return $this->nombretipoanimal;
    }

    /**
     * Set nombresector
     *
     * @param string $nombresector
     *
     * @return Treciboanimal
     */
    public function setNombresector($nombresector)
    {
        $this->nombresector = $nombresector;

        return $this;
    }

    /**
     * Get nombresector
     *
     * @return string
     */
    public function getNombresector()
    {
        return $this->nombresector;
    }

    /**
     * Set nombreespecieanimal
     *
     * @param string $nombreespecieanimal
     *
     * @return Treciboanimal
     */
    public function setNombreespecieanimal($nombreespecieanimal)
    {
        $this->nombreespecieanimal = $nombreespecieanimal;

        return $this;
    }

    /**
     * Get nombreespecieanimal
     *
     * @return string
     */
    public function getNombreespecieanimal()
    {
        return $this->nombreespecieanimal;
    }

    /**
     * Set fkidespecieanimal
     *
     * @param \ModeloBundle\Entity\Tespecieanimal $fkidespecieanimal
     *
     * @return Treciboanimal
     */
    public function setFkidespecieanimal(\ModeloBundle\Entity\Tespecieanimal $fkidespecieanimal = null)
    {
        $this->fkidespecieanimal = $fkidespecieanimal;

        return $this;
    }

    /**
     * Get fkidespecieanimal
     *
     * @return \ModeloBundle\Entity\Tespecieanimal
     */
    public function getFkidespecieanimal()
    {
        return $this->fkidespecieanimal;
    }

    /**
     * Set fkidplaza
     *
     * @param \ModeloBundle\Entity\Tplaza $fkidplaza
     *
     * @return Treciboanimal
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
     * Set fkidsector
     *
     * @param \ModeloBundle\Entity\Tsector $fkidsector
     *
     * @return Treciboanimal
     */
    public function setFkidsector(\ModeloBundle\Entity\Tsector $fkidsector = null)
    {
        $this->fkidsector = $fkidsector;

        return $this;
    }

    /**
     * Get fkidsector
     *
     * @return \ModeloBundle\Entity\Tsector
     */
    public function getFkidsector()
    {
        return $this->fkidsector;
    }

    /**
     * Set fkidttercerocomprador
     *
     * @param \ModeloBundle\Entity\Ttercero $fkidttercerocomprador
     *
     * @return Treciboanimal
     */
    public function setFkidttercerocomprador(\ModeloBundle\Entity\Ttercero $fkidttercerocomprador = null)
    {
        $this->fkidttercerocomprador = $fkidttercerocomprador;

        return $this;
    }

    /**
     * Get fkidttercerocomprador
     *
     * @return \ModeloBundle\Entity\Ttercero
     */
    public function getFkidttercerocomprador()
    {
        return $this->fkidttercerocomprador;
    }

    /**
     * Set fkidttercerovendedor
     *
     * @param \ModeloBundle\Entity\Ttercero $fkidttercerovendedor
     *
     * @return Treciboanimal
     */
    public function setFkidttercerovendedor(\ModeloBundle\Entity\Ttercero $fkidttercerovendedor = null)
    {
        $this->fkidttercerovendedor = $fkidttercerovendedor;

        return $this;
    }

    /**
     * Get fkidttercerovendedor
     *
     * @return \ModeloBundle\Entity\Ttercero
     */
    public function getFkidttercerovendedor()
    {
        return $this->fkidttercerovendedor;
    }

    /**
     * Set fkidtipoanimal
     *
     * @param \ModeloBundle\Entity\Ttipoanimal $fkidtipoanimal
     *
     * @return Treciboanimal
     */
    public function setFkidtipoanimal(\ModeloBundle\Entity\Ttipoanimal $fkidtipoanimal = null)
    {
        $this->fkidtipoanimal = $fkidtipoanimal;

        return $this;
    }

    /**
     * Get fkidtipoanimal
     *
     * @return \ModeloBundle\Entity\Ttipoanimal
     */
    public function getFkidtipoanimal()
    {
        return $this->fkidtipoanimal;
    }
    /**
     * @var string
     */
    private $identificacionrecaudador;

    /**
     * @var string
     */
    private $nombrerecaudador;

    /**
     * @var string
     */
    private $apellidorecaudador;

    /**
     * @var \ModeloBundle\Entity\Tusuario
     */
    private $fkidusuariorecaudador;


    /**
     * Set identificacionrecaudador
     *
     * @param string $identificacionrecaudador
     *
     * @return Treciboanimal
     */
    public function setIdentificacionrecaudador($identificacionrecaudador)
    {
        $this->identificacionrecaudador = $identificacionrecaudador;

        return $this;
    }

    /**
     * Get identificacionrecaudador
     *
     * @return string
     */
    public function getIdentificacionrecaudador()
    {
        return $this->identificacionrecaudador;
    }

    /**
     * Set nombrerecaudador
     *
     * @param string $nombrerecaudador
     *
     * @return Treciboanimal
     */
    public function setNombrerecaudador($nombrerecaudador)
    {
        $this->nombrerecaudador = $nombrerecaudador;

        return $this;
    }

    /**
     * Get nombrerecaudador
     *
     * @return string
     */
    public function getNombrerecaudador()
    {
        return $this->nombrerecaudador;
    }

    /**
     * Set apellidorecaudador
     *
     * @param string $apellidorecaudador
     *
     * @return Treciboanimal
     */
    public function setApellidorecaudador($apellidorecaudador)
    {
        $this->apellidorecaudador = $apellidorecaudador;

        return $this;
    }

    /**
     * Get apellidorecaudador
     *
     * @return string
     */
    public function getApellidorecaudador()
    {
        return $this->apellidorecaudador;
    }

    /**
     * Set fkidusuariorecaudador
     *
     * @param \ModeloBundle\Entity\Tusuario $fkidusuariorecaudador
     *
     * @return Treciboanimal
     */
    public function setFkidusuariorecaudador(\ModeloBundle\Entity\Tusuario $fkidusuariorecaudador = null)
    {
        $this->fkidusuariorecaudador = $fkidusuariorecaudador;

        return $this;
    }

    /**
     * Get fkidusuariorecaudador
     *
     * @return \ModeloBundle\Entity\Tusuario
     */
    public function getFkidusuariorecaudador()
    {
        return $this->fkidusuariorecaudador;
    }
}
