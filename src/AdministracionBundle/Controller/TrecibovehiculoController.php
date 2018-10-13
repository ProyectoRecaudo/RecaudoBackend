<?php

namespace AdministracionBundle\Controller;


use ModeloBundle\Entity\Trecibovehiculo;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TrecibovehiculoController extends Controller
{
    /**
     * Esta funcion realiza la inserción de un recibovehiculo,
     * como parametros se deben enviar los siguientes:
     * un parametro con el nombre json, y como datos del array de json los siguientes:
     * [
     *   {
     *       "numerorecibovehiculo":"valor",
     *       "valorecibovehiculo":"valor",
     *       "numeroplaca":"valor",
     *       "identificaciontercerovehiculo":"valor",
     *       "nombretercerovehiculo":"valor",
     *       "valortarifa":"valor",
     *       "nombreplaza":"valor",
     *       "nombretipovehiculo":"valor",
     *       "nombrepuerta":"valor",opcional
     *       "identificacionrecaudador":"valor",
     *       "nombrerecaudador":"valor",
     *       "apellidorecaudador":"valor",
     *       "recibovehiculoactivo":"valor",
     *       "fkidtarifavehiculo":"valor",
     *       "fkidplaza":"valor",
     *       "fkidtipovehiculo":"valor",
     *       "fkidpuerta":"valor",
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

                    //if (in_array("PERM_RECAUDOS", $permisosDeserializados)) {
                       
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
                                
                                $numerorecibovehiculo = (isset($valor->numerorecibovehiculo)) ? $valor->numerorecibovehiculo : null;
                                $valorecibovehiculo = (isset($valor->valorecibovehiculo)) ? $valor->valorecibovehiculo : null;
                                $numeroplaca = (isset($valor->numeroplaca)) ? $valor->numeroplaca : null;
                                $identificaciontercerovehiculo = (isset($valor->identificaciontercerovehiculo)) ? $valor->identificaciontercerovehiculo : null;
                                $nombretercerovehiculo = (isset($valor->nombretercerovehiculo)) ? $valor->nombretercerovehiculo : null;
                                $valortarifa = (isset($valor->valortarifa)) ? $valor->valortarifa : null;
                                $nombreplaza = (isset($valor->nombreplaza)) ? $valor->nombreplaza : null;
                                $nombretipovehiculo = (isset($valor->nombretipovehiculo)) ? $valor->nombretipovehiculo : null;
                                $nombrepuerta = (isset($valor->nombrepuerta)) ? $valor->nombrepuerta : null;
                                $identificacionrecaudador = (isset($valor->identificacionrecaudador)) ? $valor->identificacionrecaudador : null;
                                $nombrerecaudador = (isset($valor->nombrerecaudador)) ? $valor->nombrerecaudador : null;
                                $apellidorecaudador = (isset($valor->apellidorecaudador)) ? $valor->apellidorecaudador : null;
                                $recibovehiculoactivo = (isset($valor->recibovehiculoactivo)) ? $valor->recibovehiculoactivo : true;
                                $fkidtarifavehiculo = (isset($valor->fkidtarifavehiculo)) ? $valor->fkidtarifavehiculo : null;
                                $fkidplaza = (isset($valor->fkidplaza)) ? $valor->fkidplaza : null;
                                $fkidtipovehiculo = (isset($valor->fkidtipovehiculo)) ? $valor->fkidtipovehiculo : null;
                                $fkidpuerta = (isset($valor->fkidpuerta)) ? $valor->fkidpuerta : null;
                                $fkidusuariorecaudador = (isset($valor->fkidusuariorecaudador)) ? $valor->fkidusuariorecaudador : null;

                                if(
                                    $numerorecibovehiculo != null &&
                                    $valorecibovehiculo != null &&
                                    $numeroplaca != null &&
                                    $identificaciontercerovehiculo != null &&
                                    $nombretercerovehiculo != null &&
                                    $valortarifa != null &&
                                    $nombreplaza != null &&
                                    $nombretipovehiculo != null &&
                                    $identificacionrecaudador != null &&
                                    $nombrerecaudador != null &&
                                    $apellidorecaudador != null &&
                                    $fkidtarifavehiculo != null &&
                                    $fkidplaza != null &&
                                    $fkidtipovehiculo != null &&
                                    $fkidusuariorecaudador != null
                                ){

                                    $tarifavehiculo = $this->getDoctrine()->getRepository("ModeloBundle:Ttarifavehiculo")->findOneBy(array(
                                        "pkidtarifavehiculo" => $fkidtarifavehiculo,
                                    ));

                                    $plaza = $this->getDoctrine()->getRepository("ModeloBundle:Tplaza")->findOneBy(array(
                                        "pkidplaza" => $fkidplaza,
                                    ));

                                    $tipovehiculo = $this->getDoctrine()->getRepository("ModeloBundle:Ttipovehiculo")->findOneBy(array(
                                        "pkidtipovehiculo" => $fkidtipovehiculo,
                                    ));
                                    
                                    $puerta = $this->getDoctrine()->getRepository("ModeloBundle:Tpuerta")->findOneBy(array(
                                        "pkidpuerta" => $fkidpuerta,
                                    ));
                                    
                                    $usuario = $this->getDoctrine()->getRepository("ModeloBundle:Tusuario")->findOneBy(array(
                                        "pkidusuario" => $fkidusuariorecaudador,
                                    ));

                                    if ($tarifavehiculo){

                                        if ($plaza){

                                            if ($tipovehiculo){

                                                if($puerta || $fkidpuerta == null){
                                                
                                                    if ($usuario) {

                                                        $recibovehiculo = new Trecibovehiculo();

                                                        $recibovehiculo->setNumerorecibovehiculo($numerorecibovehiculo);
                                                        $recibovehiculo->setValorecibovehiculo($valorecibovehiculo);
                                                        $recibovehiculo->setNumeroplaca($numeroplaca);
                                                        $recibovehiculo->setIdentificaciontercerovehiculo($identificaciontercerovehiculo);
                                                        $recibovehiculo->setNombretercerovehiculo($nombretercerovehiculo);
                                                        $recibovehiculo->setValortarifa($valortarifa);
                                                        $recibovehiculo->setNombreplaza($nombreplaza);
                                                        $recibovehiculo->setNombretipovehiculo($nombretipovehiculo);
                                                        $recibovehiculo->setNombrepuerta($nombrepuerta);
                                                        $recibovehiculo->setIdentificacionrecaudador($identificacionrecaudador);
                                                        $recibovehiculo->setNombrerecaudador($nombrerecaudador);
                                                        $recibovehiculo->setApellidorecaudador($apellidorecaudador);
                                                        $recibovehiculo->setRecibovehiculoactivo($recibovehiculoactivo);
                                                        $recibovehiculo->setFkidtarifavehiculo($tarifavehiculo);
                                                        $recibovehiculo->setFkidplaza($plaza);
                                                        $recibovehiculo->setFkidtipovehiculo($tipovehiculo);
                                                        $recibovehiculo->setFkidpuerta($puerta);
                                                        $recibovehiculo->setFkidusuariorecaudador($usuario);
                                                        $recibovehiculo->setCreacionrecibovehiculo($today);
                                                        $recibovehiculo->setModificacionrecibovehiculo($today);

                                                        $usuario->setNumerorecibo($numerorecibovehiculo+1);

                                                        $em->persist($recibovehiculo);
                                                        
                                                        array_push($recibos, $recibovehiculo);

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
                                                        'msg'    => 'El id '.$fkidpuerta.' de puerta no existe',
                                                    );
                                                    array_push($errores, $data);
                                                }
                                            }else{
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg'    => 'El id '.$fkidtipovehiculo.' del tipo de vehiculo no existe',
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
                                            'msg'    => 'El id '.$fkidtarifavehiculo.' de la tarifa vehiculo no existe',
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
                                    'msg'      => 'Recibos de vehiculo no creados!!',
                                    'errores'  => $errores
                                );
                            }else{
                                $em->flush();
                                $data = array(
                                    'status'   => 'Exito',
                                    'msg'      => 'Recibos de vehiculo creados!!',
                                    'recibos'  => $recibos
                                );
                               
                                foreach($recibos as $recibovehiculo){
                                    //una vez insertados los datos se realiza el proceso de auditoria
                                    $datos = array(
                                        'idusuario'             => $identity->sub,
                                        'nombreusuario'         => $identity->name,
                                        'identificacionusuario' => $identity->identificacion,
                                        'accion'                => 'insertar',
                                        "tabla"                 => 'Trecibovehiculo',
                                        "valoresrelevantes"     => 'idRecibovehiculo:'.$recibovehiculo->getPkidrecibovehiculo().',valorecibovehiculo:'.$recibovehiculo->getValorecibovehiculo(),
                                        'idelemento'            => $recibovehiculo->getPkidrecibovehiculo(),
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

                   /* }else {
                        $data = array(
                            'status' => 'error',
                            'msg' => 'No tiene los permisos!!',
                        );
                    }*/
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
                'modulo'        => "recibovehiculo",
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