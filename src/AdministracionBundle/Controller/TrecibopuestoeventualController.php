<?php

namespace AdministracionBundle\Controller;


use ModeloBundle\Entity\Trecibopuestoeventual;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TrecibopuestoeventualController extends Controller
{
    /**
     * Esta funcion realiza la inserción de un recibopuestoeventual,
     * como parametros se deben enviar los siguientes:
     * un parametro con el nombre json, y como datos del array de json los siguientes:
     * [
     *   {
     *       "numerorecibopuestoeventual":"valor",
     *       "valorecibopuestoeventual":"valor",
     *       "identificacionterceropuestoeventual":"valor",
     *       "nombreterceropuestoeventual":"valor",
     *       "valortarifa":"valor",
     *       "nombreplaza":"valor",
     *       "nombresector":"valor",
     *       "identificacionrecaudador":"valor",
     *       "nombrerecaudador":"valor",
     *       "apellidorecaudador":"valor",
     *       "recibopuestoeventualactivo":"valor",
     *       "creacionrecibopuestoevemtual":"valor",
     *       "modificacionpuestoeventualactivo":"valor",
     *       "fkidtarifapuestoeventual":"valor",
     *       "fkidplaza":"valor",
     *       "fkidsector":"valor",
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
                                
                                $numerorecibopuestoeventual = (isset($valor->numerorecibopuestoeventual)) ? $valor->numerorecibopuestoeventual : null;
                                $valorecibopuestoeventual = (isset($valor->valorecibopuestoeventual)) ? $valor->valorecibopuestoeventual : null;
                                $identificacionterceropuestoeventual = (isset($valor->identificacionterceropuestoeventual)) ? $valor->identificacionterceropuestoeventual : null;
                                $nombreterceropuestoeventual = (isset($valor->nombreterceropuestoeventual)) ? $valor->nombreterceropuestoeventual : null;
                                $valortarifa = (isset($valor->valortarifa)) ? $valor->valortarifa : null;
                                $nombreplaza = (isset($valor->nombreplaza)) ? $valor->nombreplaza : null;
                                $nombresector = (isset($valor->nombresector)) ? $valor->nombresector : null;
                                $identificacionrecaudador = (isset($valor->identificacionrecaudador)) ? $valor->identificacionrecaudador : null;
                                $nombrerecaudador = (isset($valor->nombrerecaudador)) ? $valor->nombrerecaudador : null;
                                $apellidorecaudador = (isset($valor->apellidorecaudador)) ? $valor->apellidorecaudador : null;
                                $recibopuestoeventualactivo = (isset($valor->recibopuestoeventualactivo)) ? $valor->recibopuestoeventualactivo : true;
                                $fkidtarifapuestoeventual = (isset($valor->fkidtarifapuestoeventual)) ? $valor->fkidtarifapuestoeventual : null;
                                $fkidplaza = (isset($valor->fkidplaza)) ? $valor->fkidplaza : null;
                                $fkidsector = (isset($valor->fkidsector)) ? $valor->fkidsector : null;
                                $fkidusuariorecaudador = (isset($valor->fkidusuariorecaudador)) ? $valor->fkidusuariorecaudador : null;
                                $creacionrecibopuestoeventual = (isset($valor->creacionrecibopuestoeventual)) ? $valor->creacionrecibopuestoeventual : null;
                                $modificacionrecibopuestoeventual = (isset($valor->modificacionrecibopuestoeventual)) ? $valor->modificacionrecibopuestoeventual : null;

                                if(
                                    $numerorecibopuestoeventual != null &&
                                    $valorecibopuestoeventual != null &&
                                    $identificacionterceropuestoeventual != null &&
                                    $nombreterceropuestoeventual != null &&
                                    $valortarifa != null &&
                                    $nombreplaza != null &&
                                    $nombresector != null &&
                                    $identificacionrecaudador != null &&
                                    $nombrerecaudador != null &&
                                    $apellidorecaudador != null &&
                                    $fkidtarifapuestoeventual != null &&
                                    $creacionrecibopuestoeventual != null &&
                                    $modificacionrecibopuestoeventual != null &&
                                    $fkidplaza != null &&
                                    $fkidsector != null &&
                                    $fkidusuariorecaudador != null
                                ){

                                    $tarifapuestoeventual = $this->getDoctrine()->getRepository("ModeloBundle:Ttarifapuestoeventual")->findOneBy(array(
                                        "pkidtarifapuestoeventual" => $fkidtarifapuestoeventual,
                                    ));

                                    $plaza = $this->getDoctrine()->getRepository("ModeloBundle:Tplaza")->findOneBy(array(
                                        "pkidplaza" => $fkidplaza,
                                    ));

                                    $sector = $this->getDoctrine()->getRepository("ModeloBundle:Tsector")->findOneBy(array(
                                        "pkidsector" => $fkidsector,
                                    ));

                                    $usuario = $this->getDoctrine()->getRepository("ModeloBundle:Tusuario")->findOneBy(array(
                                        "pkidusuario" => $fkidusuariorecaudador,
                                    ));

                                    if ($tarifapuestoeventual){

                                        if ($plaza){

                                            if ($sector){
                                                
                                                if ($usuario) {

                                                    $recibopuestoeventual = new Trecibopuestoeventual();

                                                    $recibopuestoeventual->setNumerorecibopuestoeventual($numerorecibopuestoeventual);
                                                    $recibopuestoeventual->setValorecibopuestoeventual($valorecibopuestoeventual);
                                                    $recibopuestoeventual->setIdentificacionterceropuestoeventual($identificacionterceropuestoeventual);
                                                    $recibopuestoeventual->setNombreterceropuestoeventual($nombreterceropuestoeventual);
                                                    $recibopuestoeventual->setValortarifa($valortarifa);
                                                    $recibopuestoeventual->setNombreplaza($nombreplaza);
                                                    $recibopuestoeventual->setNombresector($nombresector);
                                                    $recibopuestoeventual->setIdentificacionrecaudador($identificacionrecaudador);
                                                    $recibopuestoeventual->setNombrerecaudador($nombrerecaudador);
                                                    $recibopuestoeventual->setApellidorecaudador($apellidorecaudador);
                                                    $recibopuestoeventual->setRecibopuestoeventualactivo($recibopuestoeventualactivo);
                                                    $recibopuestoeventual->setFkidtarifapuestoeventual($tarifapuestoeventual);
                                                    $recibopuestoeventual->setFkidplaza($plaza);
                                                    $recibopuestoeventual->setfkidsector($sector);
                                                    $recibopuestoeventual->setFkidusuariorecaudador($usuario);
                                                    $recibopuestoeventual->setCreacionrecibopuestoeventual(new \Datetime($creacionrecibopuestoeventual));
                                                    $recibopuestoeventual->setModificacionrecibopuestoeventual(new \Datetime($modificacionrecibopuestoeventual));

                                                    $usuario->setNumerorecibo($numerorecibopuestoeventual+1);

                                                    $em->persist($recibopuestoeventual);
                                                    
                                                    array_push($recibos, $recibopuestoeventual);

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
                                            'msg'    => 'El id '.$fkidtarifapuestoeventual.' de la tarifa puesto eventual no existe',
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
                                    'msg'      => 'Recibos de puesto eventual no creados!!',
                                    'errores'  => $errores
                                );
                            }else{
                                $em->flush();
                                $data = array(
                                    'status'   => 'Exito',
                                    'msg'      => 'Recibos de puesto eventual creados!!',
                                    'recibos'  => $recibos
                                );
                               
                                foreach($recibos as $recibopuestoeventual){
                                    //una vez insertados los datos se realiza el proceso de auditoria
                                    $datos = array(
                                        'idusuario'             => $identity->sub,
                                        'nombreusuario'         => $identity->name,
                                        'identificacionusuario' => $identity->identificacion,
                                        'accion'                => 'insertar',
                                        "tabla"                 => 'Trecibopuestoeventual',
                                        "valoresrelevantes"     => 'idRecibopuestoeventual:'.$recibopuestoeventual->getPkidrecibopuestoeventual().',valorecibopuestoeventual:'.$recibopuestoeventual->getValorecibopuestoeventual(),
                                        'idelemento'            => $recibopuestoeventual->getPkidrecibopuestoeventual(),
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
                'modulo'        => "recibopuestoeventual",
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