<?php

namespace AdministracionBundle\Controller;


use ModeloBundle\Entity\Treciboanimal;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TreciboanimalController extends Controller
{
    /**
     * Esta funcion realiza la inserción de un reciboanimal,
     * como parametros se deben enviar los siguientes:
     * un parametro con el nombre json, y como datos del array de json los siguientes:
     * [
     *   {
     *       "numeroreciboanimal":"valor",
     *       "valoreciboanimal":"valor",
     *       "edadanimal":"valor",
     *       "caracteristicasanimal":"valor", opcional
     *       "cantidadanimales":"valor",
     *       "numeroguiaica":"valor", opcional
     *       "identificacioncomprador":"valor",
     *       "nombrecomprador":"valor",
     *       "identificacionvendedor":"valor",
     *       "nombrevendedor":"valor",
     *       "valortarifa":"valor",
     *       "nombreplaza":"valor",
     *       "nombresector":"valor",
     *       "nombrecategoriaanimal":"valor",
     *       "nombretipoanimal":"valor",
     *       "nombreespecieanimal":"valor",
     *       "identificacionrecaudador":"valor",
     *       "nombrerecaudador":"valor",
     *       "apellidorecaudador":"valor",
     *       "reciboanimalactivo":"valor",
     *       "fkidtarifaanimal":"valor",
     *       "fkidplaza":"valor",
     *       "fkidsector":"valor",
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
                                
                                $numeroreciboanimal = (isset($valor->numeroreciboanimal)) ? $valor->numeroreciboanimal : null;
                                $valoreciboanimal = (isset($valor->valoreciboanimal)) ? $valor->valoreciboanimal : null;
                                $edadanimal = (isset($valor->edadanimal)) ? $valor->edadanimal : null;
                                $caracteristicasanimal = (isset($valor->caracteristicasanimal)) ? $valor->caracteristicasanimal : null;
                                $cantidadanimales = (isset($valor->cantidadanimales)) ? $valor->cantidadanimales : null;
                                $numeroguiaica = (isset($valor->numeroguiaica)) ? $valor->numeroguiaica : null;
                                $identificacioncomprador = (isset($valor->identificacioncomprador)) ? $valor->identificacioncomprador : null;
                                $nombrecomprador = (isset($valor->nombrecomprador)) ? $valor->nombrecomprador : null;
                                $identificacionvendedor = (isset($valor->identificacionvendedor)) ? $valor->identificacionvendedor : null;
                                $nombrevendedor = (isset($valor->nombrevendedor)) ? $valor->nombrevendedor : null;
                                $valortarifa = (isset($valor->valortarifa)) ? $valor->valortarifa : null;
                                $nombreplaza = (isset($valor->nombreplaza)) ? $valor->nombreplaza : null;
                                $nombresector = (isset($valor->nombresector)) ? $valor->nombresector : null;
                                $nombrecategoriaanimal = (isset($valor->nombrecategoriaanimal)) ? $valor->nombrecategoriaanimal : null;
                                $nombretipoanimal = (isset($valor->nombretipoanimal)) ? $valor->nombretipoanimal : null;
                                $nombreespecieanimal = (isset($valor->nombreespecieanimal)) ? $valor->nombreespecieanimal : null;
                                $identificacionrecaudador = (isset($valor->identificacionrecaudador)) ? $valor->identificacionrecaudador : null;
                                $nombrerecaudador = (isset($valor->nombrerecaudador)) ? $valor->nombrerecaudador : null;
                                $apellidorecaudador = (isset($valor->apellidorecaudador)) ? $valor->apellidorecaudador : null;
                                $reciboanimalactivo = (isset($valor->reciboanimalactivo)) ? $valor->reciboanimalactivo : true;
                                $fkidtarifaanimal = (isset($valor->fkidtarifaanimal)) ? $valor->fkidtarifaanimal : null;
                                $fkidplaza = (isset($valor->fkidplaza)) ? $valor->fkidplaza : null;
                                $fkidsector = (isset($valor->fkidsector)) ? $valor->fkidsector : null;
                                $fkidcategoriaanimal = (isset($valor->fkidcategoriaanimal)) ? $valor->fkidcategoriaanimal : null;
                                $fkidtipoanimal = (isset($valor->fkidtipoanimal)) ? $valor->fkidtipoanimal : null;
                                $fkidespecieanimal = (isset($valor->fkidespecieanimal)) ? $valor->fkidespecieanimal : null;
                                $fkidusuariorecaudador = (isset($valor->fkidusuariorecaudador)) ? $valor->fkidusuariorecaudador : null;

                                if(
                                    $numeroreciboanimal != null &&
                                    $valoreciboanimal != null &&
                                    $edadanimal != null &&
                                    $cantidadanimales != null &&
                                    $identificacioncomprador != null &&
                                    $nombrecomprador != null &&
                                    $identificacionvendedor != null &&
                                    $nombrevendedor != null &&
                                    $valortarifa != null &&
                                    $nombreplaza != null &&
                                    $nombresector != null &&
                                    $nombrecategoriaanimal != null &&
                                    $nombretipoanimal != null &&
                                    $nombreespecieanimal != null &&
                                    $identificacionrecaudador != null &&
                                    $nombrerecaudador != null &&
                                    $apellidorecaudador != null &&
                                    $fkidtarifaanimal != null &&
                                    $fkidplaza != null &&
                                    $fkidsector != null &&
                                    $fkidcategoriaanimal != null &&
                                    $fkidtipoanimal != null &&
                                    $fkidespecieanimal != null &&
                                    $fkidusuariorecaudador != null
                                ){

                                    $tarifaanimal = $this->getDoctrine()->getRepository("ModeloBundle:Ttarifaanimal")->findOneBy(array(
                                        "pkidtarifaanimal" => $fkidtarifaanimal,
                                    ));

                                    $plaza = $this->getDoctrine()->getRepository("ModeloBundle:Tplaza")->findOneBy(array(
                                        "pkidplaza" => $fkidplaza,
                                    ));

                                    $sector = $this->getDoctrine()->getRepository("ModeloBundle:Tsector")->findOneBy(array(
                                        "pkidsector" => $fkidsector,
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

                                    if ($tarifaanimal){

                                        if ($plaza){

                                            if ($sector){
                                                
                                                if ($categoriaanimal){

                                                    if ($tipoanimal){
                                                       
                                                        if ($especieanimal) {
                            
                                                            if ($usuario) {

                                                                $reciboanimal = new Treciboanimal();
    
                                                                $reciboanimal->setNumeroreciboanimal($numeroreciboanimal);
                                                                $reciboanimal->setValoreciboanimal($valoreciboanimal);
                                                                $reciboanimal->setEdadanimal($edadanimal);
                                                                $reciboanimal->setCaracteristicasanimal($caracteristicasanimal);
                                                                $reciboanimal->setCantidadanimales($numeroreciboanimal);
                                                                $reciboanimal->setNumeroguiaica($numeroguiaica);
                                                                $reciboanimal->setIdentificacioncomprador($identificacioncomprador);
                                                                $reciboanimal->setNombrecomprador($nombrecomprador);
                                                                $reciboanimal->setIdentificacionvendedor($identificacionvendedor);
                                                                $reciboanimal->setNombrevendedor($nombrevendedor);
                                                                $reciboanimal->setValortarifa($valortarifa);
                                                                $reciboanimal->setNombreplaza($nombreplaza);
                                                                $reciboanimal->setNombresector($nombresector);
                                                                $reciboanimal->setNombrecategoriaanimal($nombrecategoriaanimal);
                                                                $reciboanimal->setNombretipoanimal($nombretipoanimal);
                                                                $reciboanimal->setNombreespecieanimal($nombreespecieanimal);
                                                                $reciboanimal->setIdentificacionrecaudador($identificacionrecaudador);
                                                                $reciboanimal->setNombrerecaudador($nombrerecaudador);
                                                                $reciboanimal->setApellidorecaudador($apellidorecaudador);
                                                                $reciboanimal->setReciboanimalactivo($reciboanimalactivo);
                                                                $reciboanimal->setFkidtarifaanimal($tarifaanimal);
                                                                $reciboanimal->setFkidplaza($plaza);
                                                                $reciboanimal->setfkidsector($sector);
                                                                $reciboanimal->setFkidcategoriaanimal($categoriaanimal);
                                                                $reciboanimal->setFkidtipoanimal($tipoanimal);
                                                                $reciboanimal->setFkidespecieanimal($especieanimal);
                                                                $reciboanimal->setFkidusuariorecaudador($usuario);
                                                                $reciboanimal->setCreacionreciboanimal($today);
                                                                $reciboanimal->setModificacionreciboanimal($today);

                                                                $usuario->setNumerorecibo($numeroreciboanimal+1);

                                                                $em->persist($reciboanimal);
                                                                
                                                                array_push($recibos, $reciboanimal);

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
                                                    'msg'    => 'El id '.$fkidsector.' del sector no existe',
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
                                            'msg'    => 'El id '.$fkidtarifaanimal.' de la tarifa animal no existe',
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
                                    'msg'      => 'Recibos de animal no creados!!',
                                    'errores'  => $errores
                                );
                            }else{
                                $em->flush();
                                $data = array(
                                    'status'   => 'Exito',
                                    'msg'      => 'Recibos de animal creados!!',
                                    'recibos'  => $recibos
                                );
                               
                                foreach($recibos as $reciboanimal){
                                    //una vez insertados los datos se realiza el proceso de auditoria
                                    $datos = array(
                                        'idusuario'             => $identity->sub,
                                        'nombreusuario'         => $identity->name,
                                        'identificacionusuario' => $identity->identificacion,
                                        'accion'                => 'insertar',
                                        "tabla"                 => 'Treciboanimal',
                                        "valoresrelevantes"     => 'idReciboAnimal:'.$reciboanimal->getPkidreciboanimal().',valoreciboanimal:'.$reciboanimal->getValoreciboanimal(),
                                        'idelemento'            => $reciboanimal->getPkidreciboanimal(),
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
                'modulo'        => "reciboanimal",
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