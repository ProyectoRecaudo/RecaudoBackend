<?php

namespace AdministracionBundle\Controller;


use ModeloBundle\Entity\Trecibopesaje;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TrecibopesajeController extends Controller
{
    /**
     * Esta funcion realiza la inserción de un recibopesaje,
     * como parametros se deben enviar los siguientes:
     * un parametro con el nombre json, y como datos del array de json los siguientes:
     * [
     *   {
     *       "numerorecibopesaje":"valor",
     *       "valorecibopesaje":"valor",
     *       "pesoanimal":"valor",
     *       "identificacionterceropesaje":"valor",
     *       "nombreterceropesaje":"valor",
     *       "valortarifa":"valor",
     *       "nombreplaza":"valor",
     *       "nombrecategoriaanimal":"valor",
     *       "nombretipoanimal":"valor",
     *       "nombreespecieanimal":"valor",
     *       "identificacionrecaudador":"valor",
     *       "nombrerecaudador":"valor",
     *       "apellidorecaudador":"valor",
     *       "recibopesajeactivo":"valor",
     *       "fkidtarifapesaje":"valor",
     *       "fkidplaza":"valor",
     *       "fkidcategoriaanimal":"valor",
     *       "fkidtipoanimal":"valor",
     *       "fkidespecieanimal":"valor",
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
                                
                                $numerorecibopesaje = (isset($valor->numerorecibopesaje)) ? $valor->numerorecibopesaje : null;
                                $valorecibopesaje = (isset($valor->valorecibopesaje)) ? $valor->valorecibopesaje : null;
                                $pesoanimal = (isset($valor->pesoanimal)) ? $valor->pesoanimal : null;
                                $identificacionterceropesaje = (isset($valor->identificacionterceropesaje)) ? $valor->identificacionterceropesaje : null;
                                $nombreterceropesaje = (isset($valor->nombreterceropesaje)) ? $valor->nombreterceropesaje : null;
                                $valortarifa = (isset($valor->valortarifa)) ? $valor->valortarifa : null;
                                $nombreplaza = (isset($valor->nombreplaza)) ? $valor->nombreplaza : null;
                                $nombrecategoriaanimal = (isset($valor->nombrecategoriaanimal)) ? $valor->nombrecategoriaanimal : null;
                                $nombretipoanimal = (isset($valor->nombretipoanimal)) ? $valor->nombretipoanimal : null;
                                $nombreespecieanimal = (isset($valor->nombreespecieanimal)) ? $valor->nombreespecieanimal : null;
                                $identificacionrecaudador = (isset($valor->identificacionrecaudador)) ? $valor->identificacionrecaudador : null;
                                $nombrerecaudador = (isset($valor->nombrerecaudador)) ? $valor->nombrerecaudador : null;
                                $apellidorecaudador = (isset($valor->apellidorecaudador)) ? $valor->apellidorecaudador : null;
                                $recibopesajeactivo = (isset($valor->recibopesajeactivo)) ? $valor->recibopesajeactivo : true;
                                $fkidtarifapesaje = (isset($valor->fkidtarifapesaje)) ? $valor->fkidtarifapesaje : null;
                                $fkidplaza = (isset($valor->fkidplaza)) ? $valor->fkidplaza : null;
                                $fkidcategoriaanimal = (isset($valor->fkidcategoriaanimal)) ? $valor->fkidcategoriaanimal : null;
                                $fkidtipoanimal = (isset($valor->fkidtipoanimal)) ? $valor->fkidtipoanimal : null;
                                $fkidespecieanimal = (isset($valor->fkidespecieanimal)) ? $valor->fkidespecieanimal : null;
                                $fkidusuariorecaudador = (isset($valor->fkidusuariorecaudador)) ? $valor->fkidusuariorecaudador : null;

                                if(
                                    $numerorecibopesaje != null &&
                                    $valorecibopesaje != null &&
                                    $pesoanimal != null &&
                                    $identificacionterceropesaje != null &&
                                    $nombreterceropesaje != null &&
                                    $valortarifa != null &&
                                    $nombreplaza != null &&
                                    $nombrecategoriaanimal != null &&
                                    $nombretipoanimal != null &&
                                    $nombreespecieanimal != null &&
                                    $identificacionrecaudador != null &&
                                    $nombrerecaudador != null &&
                                    $apellidorecaudador != null &&
                                    $fkidtarifapesaje != null &&
                                    $fkidplaza != null &&
                                    $fkidcategoriaanimal != null &&
                                    $fkidtipoanimal != null &&
                                    $fkidespecieanimal != null &&
                                    $fkidusuariorecaudador != null
                                ){

                                    $tarifapesaje = $this->getDoctrine()->getRepository("ModeloBundle:Ttarifapesaje")->findOneBy(array(
                                        "pkidtarifapesaje" => $fkidtarifapesaje,
                                    ));

                                    $plaza = $this->getDoctrine()->getRepository("ModeloBundle:Tplaza")->findOneBy(array(
                                        "pkidplaza" => $fkidplaza,
                                    ));

                                    $categoriaanimal = $this->getDoctrine()->getRepository("ModeloBundle:Tcategoriaanimal")->findOneBy(array(
                                        "pkidcategoriaanimal" => $fkidcategoriaanimal,
                                    ));

                                    $tipoanimal = $this->getDoctrine()->getRepository("ModeloBundle:Ttipoanimal")->findOneBy(array(
                                        "pkidtipoanimal" => $fkidtipoanimal,
                                    ));
                                    
                                    $especieanimal = $this->getDoctrine()->getRepository("ModeloBundle:Tespecieanimal")->findOneBy(array(
                                        "pkidespecieanimal" => $fkidespecieanimal,
                                    ));

                                    $usuario = $this->getDoctrine()->getRepository("ModeloBundle:Tusuario")->findOneBy(array(
                                        "pkidusuario" => $fkidusuariorecaudador,
                                    ));

                                    if ($tarifapesaje){

                                        if ($plaza){

                                            if ($categoriaanimal){

                                                if ($tipoanimal){
                                                    
                                                    if ($especieanimal) {
                        
                                                        if ($usuario) {

                                                            $recibopesaje = new Trecibopesaje();

                                                            $recibopesaje->setNumerorecibopesaje($numerorecibopesaje);
                                                            $recibopesaje->setValorecibopesaje($valorecibopesaje);
                                                            $recibopesaje->setPesoanimal($pesoanimal);
                                                            $recibopesaje->setIdentificacionterceropesaje($identificacionterceropesaje);
                                                            $recibopesaje->setNombreterceropesaje($nombreterceropesaje);
                                                            $recibopesaje->setValortarifa($valortarifa);
                                                            $recibopesaje->setNombreplaza($nombreplaza);
                                                            $recibopesaje->setNombrecategoriaanimal($nombrecategoriaanimal);
                                                            $recibopesaje->setNombretipoanimal($nombretipoanimal);
                                                            $recibopesaje->setNombreespecieanimal($nombreespecieanimal);
                                                            $recibopesaje->setIdentificacionrecaudador($identificacionrecaudador);
                                                            $recibopesaje->setNombrerecaudador($nombrerecaudador);
                                                            $recibopesaje->setApellidorecaudador($apellidorecaudador);
                                                            $recibopesaje->setRecibopesajeactivo($recibopesajeactivo);
                                                            $recibopesaje->setFkidtarifapesaje($tarifapesaje);
                                                            $recibopesaje->setFkidplaza($plaza);
                                                            $recibopesaje->setFkidcategoriaanimal($categoriaanimal);
                                                            $recibopesaje->setFkidtipoanimal($tipoanimal);
                                                            $recibopesaje->setFkidespecieanimal($especieanimal);
                                                            $recibopesaje->setFkidusuariorecaudador($usuario);
                                                            $recibopesaje->setCreacionrecibopesaje($today);
                                                            $recibopesaje->setModificacionrecibopesaje($today);

                                                            $usuario->setNumerorecibo($numerorecibopesaje+1);

                                                            $em->persist($recibopesaje);
                                                            
                                                            array_push($recibos, $recibopesaje);

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
                                                            'msg'    => 'El id '.$fkidespecieanimal.' de la especie animal no existe',
                                                        );
                                                        array_push($errores, $data);
                                                    }
                                                }else{
                                                    $data = array(
                                                        'status' => 'error',
                                                        'msg'    => 'El id '.$fkidtipoanimal.' del tipo de animal no existe',
                                                    );
                                                    array_push($errores, $data);
                                                }
                                            }else{
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg'    => 'El id '.$fkidcategoriaanimal.' de la categoria animal no existe',
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
                                            'msg'    => 'El id '.$fkidtarifapesaje.' de la tarifa pesaje no existe',
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
                                    'msg'      => 'Recibos de pesaje no creados!!',
                                    'errores'  => $errores
                                );
                            }else{
                                $em->flush();
                                $data = array(
                                    'status'   => 'Exito',
                                    'msg'      => 'Recibos de pesaje creados!!',
                                    'recibos'  => $recibos
                                );
                               
                                foreach($recibos as $recibopesaje){
                                    //una vez insertados los datos se realiza el proceso de auditoria
                                    $datos = array(
                                        'idusuario'             => $identity->sub,
                                        'nombreusuario'         => $identity->name,
                                        'identificacionusuario' => $identity->identificacion,
                                        'accion'                => 'insertar',
                                        "tabla"                 => 'Trecibopesaje',
                                        "valoresrelevantes"     => 'idRecibopesaje:'.$recibopesaje->getPkidrecibopesaje().',valorecibopesaje:'.$recibopesaje->getValorecibopesaje(),
                                        'idelemento'            => $recibopesaje->getPkidrecibopesaje(),
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
                'modulo'        => "recibopesaje",
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