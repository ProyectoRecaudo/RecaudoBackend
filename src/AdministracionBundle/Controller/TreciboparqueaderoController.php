<?php

namespace AdministracionBundle\Controller;


use ModeloBundle\Entity\Treciboparqueadero;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TreciboparqueaderoController extends Controller
{
    /**
     * Esta funcion realiza la inserción de un reciboparqueadero,
     * como parametros se deben enviar los siguientes:
     * un parametro con el nombre json, y como datos del array de json los siguientes:
     * [
     *   {
     *       "numeroreciboparqueadero":"valor",
     *       "valoreciboparqueadero":"valor",
     *       "nombreusuarioparqueadero":"valor",
     *       "nombreterceroparqueadero":"valor",
     *       "identificacionterceroparqueadero":"valor",
     *       "numeroparqueadero":"valor",
     *       "valortarifa":"valor",
     *       "nombreplaza":"valor",
     *       "nombretipoparqueadero":"valor"
     *       "identificacionrecaudador":"valor",
     *       "nombrerecaudador":"valor",
     *       "apellidorecaudador":"valor",
     *       "reciboparqueaderoactivo":"valor",
     *       "fkidtarifaparqueadero":"valor",
     *       "fkidplaza":"valor",
     *       "fkidparqueadero":"valor",
     *       "fkidtipoparqueadero":"valor",
     *       "fkidusuariorecaudador":"valor",
     *   },
     * ]
     * un parametro con el nombre authorization, y como valor del parametro el token correspondiente
     * al login del usuario.
     * @Route("/new")
     */
    public function newAction(Request $request)
    {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

        //inicia codigo bien
        try {

            if (!empty($request->get('authorization')) && !empty($request->get('json'))) {

                $token = $request->get('authorization', null);
                $authCheck = $jwt_auth->checkToken($token);

                if ($authCheck) {

                    $identity = $jwt_auth->checkToken($token, true);

                    $permisosSerializados = $identity->permisos;
                    $permisosDeserializados = unserialize($permisosSerializados);

                    if (in_array("PERM_RECAUDOS", $permisosDeserializados)) {
                       
                        //fecha y hora actuales en la zona horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {
                            
                            $recibos = array();
                            $errores = array();

                            foreach($params as $valor){
                                
                                $numeroreciboparqueadero = (isset($valor->numeroreciboparqueadero)) ? $valor->numeroreciboparqueadero : null;
                                $valoreciboparqueadero = (isset($valor->valoreciboparqueadero)) ? $valor->valoreciboparqueadero : null;
                                $nombreusuarioparqueadero = (isset($valor->nombreusuarioparqueadero)) ? $valor->nombreusuarioparqueadero : null;
                                $nombreterceroparqueadero = (isset($valor->nombreterceroparqueadero)) ? $valor->nombreterceroparqueadero : null;
                                $identificacionterceroparqueadero = (isset($valor->identificacionterceroparqueadero)) ? $valor->identificacionterceroparqueadero : null;
                                $numeroparqueadero = (isset($valor->numeroparqueadero)) ? $valor->numeroparqueadero : null;
                                $valortarifa = (isset($valor->valortarifa)) ? $valor->valortarifa : null;
                                $nombreplaza = (isset($valor->nombreplaza)) ? $valor->nombreplaza : null;
                                $nombretipoparqueadero = (isset($valor->nombretipoparqueadero)) ? $valor->nombretipoparqueadero : null;
                                $identificacionrecaudador = (isset($valor->identificacionrecaudador)) ? $valor->identificacionrecaudador : null;
                                $nombrerecaudador = (isset($valor->nombrerecaudador)) ? $valor->nombrerecaudador : null;
                                $apellidorecaudador = (isset($valor->apellidorecaudador)) ? $valor->apellidorecaudador : null;
                                $reciboparqueaderoactivo = (isset($valor->reciboparqueaderoactivo)) ? $valor->reciboparqueaderoactivo : true;
                                $fkidtarifaparqueadero = (isset($valor->fkidtarifaparqueadero)) ? $valor->fkidtarifaparqueadero : null;
                                $fkidplaza = (isset($valor->fkidplaza)) ? $valor->fkidplaza : null;
                                $fkidparqueadero = (isset($valor->fkidparqueadero)) ? $valor->fkidparqueadero : null;
                                $fkidtipoparqueadero = (isset($valor->fkidtipoparqueadero)) ? $valor->fkidtipoparqueadero : null;
                                $fkidusuariorecaudador = (isset($valor->fkidusuariorecaudador)) ? $valor->fkidusuariorecaudador : null;

                                if(
                                    $numeroreciboparqueadero != null &&
                                    $valoreciboparqueadero != null &&
                                    $nombreusuarioparqueadero != null &&
                                    $nombreterceroparqueadero != null &&
                                    $identificacionterceroparqueadero != null &&
                                    $numeroparqueadero != null &&
                                    $valortarifa != null &&
                                    $nombreplaza != null &&
                                    $nombretipoparqueadero != null &&
                                    $identificacionrecaudador != null &&
                                    $nombrerecaudador != null &&
                                    $apellidorecaudador != null &&
                                    $fkidtarifaparqueadero != null &&
                                    $fkidplaza != null &&
                                    $fkidparqueadero != null &&
                                    $fkidtipoparqueadero != null &&
                                    $fkidusuariorecaudador != null
                                ){

                                    $tarifaparqueadero = $this->getDoctrine()->getRepository("ModeloBundle:Ttarifaparqueadero")->findOneBy(array(
                                        "pkidtarifaparqueadero" => $fkidtarifaparqueadero,
                                    ));

                                    $plaza = $this->getDoctrine()->getRepository("ModeloBundle:Tplaza")->findOneBy(array(
                                        "pkidplaza" => $fkidplaza,
                                    ));

                                    $parqueadero = $this->getDoctrine()->getRepository("ModeloBundle:Tparqueadero")->findOneBy(array(
                                        "pkidparqueadero" => $fkidparqueadero,
                                    ));

                                    $tipoparqueadero = $this->getDoctrine()->getRepository("ModeloBundle:Ttipoparqueadero")->findOneBy(array(
                                        "pkidtipoparqueadero" => $fkidtipoparqueadero,
                                    ));
                                    
                                    $usuario = $this->getDoctrine()->getRepository("ModeloBundle:Tusuario")->findOneBy(array(
                                        "pkidusuario" => $fkidusuariorecaudador,
                                    ));

                                    if ($tarifaparqueadero){

                                        if ($plaza){

                                            if ($parqueadero){
                                                
                                               if ($tipoparqueadero){
                                                    
                                                    if ($usuario){

                                                        $reciboparqueadero = new Treciboparqueadero();

                                                        $reciboparqueadero->setNumeroreciboparqueadero($numeroreciboparqueadero);
                                                        $reciboparqueadero->setValoreciboparqueadero($valoreciboparqueadero);
                                                        $reciboparqueadero->setNombreusuarioparqueadero($nombreusuarioparqueadero);
                                                        $reciboparqueadero->setNombreterceroparqueadero($nombreterceroparqueadero);
                                                        $reciboparqueadero->setIdentificacionterceroparqueadero($numeroreciboparqueadero);
                                                        $reciboparqueadero->setNumeroparqueadero($numeroparqueadero);
                                                        $reciboparqueadero->setValortarifa($valortarifa);
                                                        $reciboparqueadero->setNombreplaza($nombreplaza);
                                                        $reciboparqueadero->setNombretipoparqueadero($nombretipoparqueadero);
                                                        $reciboparqueadero->setIdentificacionrecaudador($identificacionrecaudador);
                                                        $reciboparqueadero->setNombrerecaudador($nombrerecaudador);
                                                        $reciboparqueadero->setApellidorecaudador($apellidorecaudador);
                                                        $reciboparqueadero->setReciboparqueaderoactivo($reciboparqueaderoactivo);
                                                        $reciboparqueadero->setFkidtarifaparqueadero($tarifaparqueadero);
                                                        $reciboparqueadero->setFkidplaza($plaza);
                                                        $reciboparqueadero->setFkidparqueadero($parqueadero);
                                                        $reciboparqueadero->setFkidtipoparqueadero($tipoparqueadero);
                                                        $reciboparqueadero->setFkidusuariorecaudador($usuario);
                                                        $reciboparqueadero->setCreacionreciboparqueadero($today);
                                                        $reciboparqueadero->setModificacionreciboparqueadero($today);

                                                        $usuario->setNumerorecibo($numeroreciboparqueadero+1);

                                                        $em->persist($reciboparqueadero);
                                                        
                                                        array_push($recibos, $reciboparqueadero);

                                                    }else{
                                                        $data = array(
                                                            'status' => 'error',
                                                            'msg'    => 'El id '.$fkidusuariorecaudador.' del usuario no existe',
                                                        );
                                                        array_push($errores, $data);
                                                    }
                                                }else{
                                                    $data = array(
                                                        'status' => 'error',
                                                        'msg'    => 'El id '.$fkidtipoparqueadero.' del tipo de parqueadero no existe',
                                                    );
                                                    array_push($errores, $data);
                                                }
                                            }else{
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg'    => 'El id '.$fkidparqueadero.' del parqueadero no existe',
                                                );
                                                array_push($errores, $data);
                                            }
                                        }else{
                                            $data = array(
                                                'status' => 'error',
                                                'msg'    => 'El id '.$fkidplaza.' de la plaza no existe',
                                            );
                                            array_push($errores, $data);
                                        }
                                    }else{
                                        $data = array(
                                            'status' => 'error',
                                            'msg'    => 'El id '.$fkidtarifaparqueadero.' de la tarifa de parqueadero no existe',
                                        );
                                        array_push($errores, $data);
                                    }
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'Algunos campos enviados en el json son nulos',
                                    );
                                    array_push($errores, $data);
                                }
                            }
                                                        
                            if($errores){
                                $data = array(
                                    'status'   => 'Error',
                                    'msg'      => 'Recibos de parqueadero no creados!!',
                                    'errores'  => $errores
                                );
                            }else{
                                $em->flush();
                                $data = array(
                                    'status'   => 'Exito',
                                    'msg'      => 'Recibos de parqueadero creados!!',
                                    'recibos'  => $recibos
                                );
                               
                                foreach($recibos as $reciboparqueadero){
                                    //una vez insertados los datos se realiza el proceso de auditoria
                                    $datos = array(
                                        'idusuario'             => $identity->sub,
                                        'nombreusuario'         => $identity->name,
                                        'identificacionusuario' => $identity->identificacion,
                                        'accion'                => 'insertar',
                                        "tabla"                 => 'Treciboparqueadero',
                                        "valoresrelevantes"     => 'idreciboparqueadero:'.$reciboparqueadero->getPkidreciboparqueadero().',valoreciboparqueadero:'.$reciboparqueadero->getValoreciboparqueadero(),
                                        'idelemento'            => $reciboparqueadero->getPkidreciboparqueadero(),
                                        'origen'                => 'web'
                                    );
                                    
                                   $auditoria = $this->get(Auditoria::class);
                                   $auditoria->auditoria($helpers->json($datos));
                                }
                            }
                        }else {
                            $data = array(
                                'status' => 'error',
                                'msg' => 'Parametro json es nulo!!',
                            );
                        }
                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg' => 'No tiene los permisos!!',
                        );
                    }
                }else {
                    $data = array(
                        'status' => 'error',
                        'msg' => 'Token no valido !!',
                    );
                }
            }else {
                $data = array(
                    'status' => 'error',
                    'msg' => 'Envie los parametros, por favor!!',
                );
            }
            return $helpers->json($data);

        } catch (\Exception $e) {

            $trace = $e->getTrace();

            $exepcion = array(
                'idusuario'     => $identity->sub,
                'nombreusuario' => $identity->name,
                'modulo'        => "reciboparqueadero",
                'metodo'        => $trace[0]['function'],
                'mensaje'       => $e->getMessage(),
                'tipoExepcion'  => $trace[0]['class'],
                'pila'          => $e->getTraceAsString(),
                'origen'        => "Web",
            );

            try {
                $auditoria = $this->get(Auditoria::class);
                $auditoria->exepcion(json_encode($exepcion));
            } catch (\Exception $a) {

            }

            throw $e;
        }
    }
}