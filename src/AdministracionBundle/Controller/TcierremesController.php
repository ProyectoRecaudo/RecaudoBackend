<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Tcierremes;
use ModeloBundle\Entity\Tcierrediario;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\DateTime;

class TcierremesController extends Controller
{
    /**
     * @Route("/")
    */
    public function indexAction()
    {
        //return $this->render('AdministracionBundle:Default:index.html.twig');
    }

    /**
     * @Route("/new")
     * Funcion para registrar una cierre de mes 
     * recibe los datos en un json llamado json con los datos
     * mes, mes en el que se genera el cierre de mes
     * pkidplaza, plaza en la que se genera el cierre de mes
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function newCierremesAction(Request $request)
    {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);
        
        try{

            if (!empty($request->get('authorization')) && !empty($request->get('json'))) {

                $token = $request->get('authorization', null);
                $authCheck = $jwt_auth->checkToken($token);

                if ($authCheck) {

                    $identity = $jwt_auth->checkToken($token, true);

                    $permisosSerializados = $identity->permisos;
                    $permisosDeserializados = unserialize($permisosSerializados);

                    if (in_array("PERM_GENERICOS", $permisosDeserializados)) {
                       
                        //fecha y hora actuales en la zona horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));
                             
                        //entiti manager
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        //array error por defecto
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'Cierre de mes no creado!!',
                        );

                        if ($json != null) {
                           
                            /** 
                             * se calculan los totales de valores de cierre diario por recaudador
                            */

                            $mes = (isset($params->mes)) ? $params->mes : null;
                            $idPlaza = (isset($params->pkidplaza)) ? $params->pkidplaza : null;
                            
                            $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto",
                            "Septiembre", "Octubre", "Noviembre", "Diciembre");
                            
                            $mesletras = $meses[$mes - 1];

                            if($mes !=null && $idPlaza != null){

                                $plaza = $em->getRepository('ModeloBundle:Tplaza')->find($idPlaza);

                                if($plaza){

                                    $query = "SELECT 
                                                sum(tcierrediario.recaudototalacuerdo) as recaudototalacuerdo, 
                                                sum(tcierrediario.recaudocuotaacuerdo) as recaudocuotaacuerdo, 
                                                sum(tcierrediario.recaudodeudaacuerdo) as recaudodeudaacuerdo, 
                                                sum(tcierrediario.recaudodeuda) as recaudodeuda, 
                                                sum(tcierrediario.recaudomultas) as recaudomultas, 
                                                sum(tcierrediario.recaudocuotames) as recaudocuotames, 
                                                sum(tcierrediario.recaudoanimales) as recaudoanimales, 
                                                sum(tcierrediario.recaudopesaje) as recaudopesaje, 
                                                sum(tcierrediario.recaudovehiculos) as recaudovehiculos, 
                                                sum(tcierrediario.recaudoparqueaderos) as recaudoparqueaderos, 
                                                sum(tcierrediario.recaudoeventuales) as recaudoeventuales, 
                                                tcierrediario.identificacionrecaudador, 
                                                tcierrediario.nombrerecaudador, 
                                                tcierrediario.apellidorecaudador,
                                                tcierrediario.fkidusuariorecaudador
                                            FROM 
                                                public.tcierrediario
                                            WHERE 
                                                fkidplaza = $idPlaza and
                                                date_part('month',tcierrediario.creacioncierrediario) = $mes and
                                                date_part('year',tcierrediario.creacioncierrediario) = ".$today->format('Y')."
                                            GROUP BY
                                                tcierrediario.identificacionrecaudador,
                                                tcierrediario.nombrerecaudador, 
                                                tcierrediario.apellidorecaudador,
                                                tcierrediario.fkidusuariorecaudador";

                                    $stmt = $db->prepare($query);
                                    $params = array();
                                    $stmt->execute($params);
                                    $cierresdiarios = $stmt->fetchAll();

                                    if($cierresdiarios){
                                        
                                        $cierresmes = array();
                                        
                                        foreach($cierresdiarios as $cierrediario){

                                            $cierres = $em->getRepository('ModeloBundle:Tcierremes')->findOneBy(array(
                                                "fkidusuariorecaudador" => $cierrediario['fkidusuariorecaudador'],
                                                "mes"                   => $mes
                                            ));
    
                                            /**
                                             * si ya existe una cierremes para el recaudador en el mismo mes, se modifica la existente, 
                                             * si no se crea una nueva
                                             */
                                            if(!$cierres){
                                                $cierremes = new Tcierremes();
                                                $cierremes->setCreacioncierremes($today);
                                            }

                                            $usuario = $em->getRepository('ModeloBundle:Tusuario')->find($cierrediario['fkidusuariorecaudador']);
                                          
                                            $cierremes->setRecaudototalacuerdo($cierrediario['recaudototalacuerdo']);
                                            $cierremes->setRecaudocuotaacuerdo($cierrediario['recaudocuotaacuerdo']);
                                            $cierremes->setRecaudodeudaacuerdo($cierrediario['recaudodeudaacuerdo']);
                                            $cierremes->setRecaudodeuda($cierrediario['recaudodeuda']);
                                            $cierremes->setRecaudomultas($cierrediario['recaudomultas']);
                                            $cierremes->setRecaudocuotames($cierrediario['recaudocuotames']);
                                            $cierremes->setRecaudoanimales($cierrediario['recaudoanimales']);
                                            $cierremes->setRecaudopesaje($cierrediario['recaudopesaje']);
                                            $cierremes->setRecaudovehiculos($cierrediario['recaudovehiculos']);
                                            $cierremes->setRecaudoparqueaderos($cierrediario['recaudoparqueaderos']);
                                            $cierremes->setRecaudoeventuales($cierrediario['recaudoeventuales']);
                                            $cierremes->setMes($mes);
                                            $cierremes->setMesletras($mesletras);
                                            $cierremes->setYear($today->format('Y'));
                                            $cierremes->setIdentificacionrecaudador($cierrediario['identificacionrecaudador']);
                                            $cierremes->setNombrerecaudador($cierrediario['nombrerecaudador']);
                                            $cierremes->setApellidorecaudador($cierrediario['apellidorecaudador']);
                                            $cierremes->setModificacioncierremes($today);
                                            $cierremes->setFkidplaza($plaza);
                                            $cierremes->setFkidusuariorecaudador($usuario);

                                            $em->persist($cierremes);

                                            array_push($cierresmes, $cierremes);
                                        }
                                        
                                        $em->flush();
                                        $data = array(
                                            'status'    => 'Exito',
                                            'msg'       => 'Cierres de mes creados!!',
                                            'cierremes' => $cierresmes
                                        );

                                        foreach($cierresmes as $cierremes){

                                            //una vez insertados los datos en la cierremes se realiza la insercion en auditoria
                                            $datos = array(
                                                'idusuario'             => $identity->sub,
                                                'nombreusuario'         => $identity->name,
                                                'identificacionusuario' => $identity->identificacion,
                                                'accion'                => 'insertar',
                                                "tabla"                 => 'Tcierremes',
                                                "valoresrelevantes"     => 'idCierremes:'.$cierremes->getPkidcierremes().',mes:'.$cierremes->getMes(),
                                                'idelemento'            => $cierremes->getPkidcierremes(),
                                                'origen'                => 'web'
                                            );

                                            $auditoria = $this->get(Auditoria::class);
                                            $auditoria->auditoria(json_encode($datos));
                                        }
                                    }else{
                                        $data = array(
                                            'status' => 'error',
                                            'msg'    => "No existen cierres diarios para el mes en la plaza"
                                        );
                                        return $helpers->json($data);
                                    }
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => "No existen la plaza"
                                    );
                                    return $helpers->json($data);
                                }

                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'se debe enviar el mes o la plaza, para generar las cierre!!',
                                );
                            }                         
                        } else {
                            $data = array(
                                'status' => 'error',
                                'msg'    => 'Parametro json es nulo!!',
                            );
                        }
                    } else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'No tiene los permisos!!',
                        );
                    }
                } else {
                    $data = array(
                        'status' => 'error',
                        'msg'    => 'Token no valido!!',
                    );
                }
            } else {
                $data = array(
                    'status' => 'error',
                    'msg'    => 'Envie los parametros, por favor!!',
                );
            }
            return $helpers->json($data);

        }catch(\Exception $e){

            $trace = $e->getTrace();

            $data = array(
                'idusuario'     => $identity->sub,
                'nombreusuario' => $identity->name,
                'modulo'        => 'Cierremes',
                'metodo'        => $trace[0]['function'],
                'mensaje'       => $e->getMessage(),
                'tipoExepcion'  => $trace[0]['class'],
                'pila'          => $e->getTraceAsString(),
                'origen'        => 'web'
            );

            try{
                $excepcion = $this->get(Auditoria::class);
                $excepcion->exepcion(json_encode($data));

            }catch (\Exception $a){}

            throw $e;
        }
    }

    /**
     * @Route("/query")
     * Funcion para las cierremes por recaudador y sector 
     * recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function queryCierremesAction(Request $request)
    {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

        try {
            if (!empty($request->get('authorization'))) {

                $token = $request->get('authorization', null);
                $authCheck = $jwt_auth->checkToken($token);

                if ($authCheck) {

                    $identity = $jwt_auth->checkToken($token, true);

                    $permisosSerializados = $identity->permisos;
                    $permisosDeserializados = unserialize($permisosSerializados);

                    if (in_array("PERM_GENERICOS", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();                        
                                            
                        //Consulta para traer los datos de la cierremes, los sectores y el recaudador
                        $cierres = $em->getRepository('ModeloBundle:Tcierremes')->findAll();
                        
                        $array_all = array();

                        foreach ($cierres as $cierremes) {
                            $cierremesList = array(
                                "pkidcierremes"            => $cierremes->getPkidcierremes(),
                                "recaudototalacuerdo"      => $cierremes->getRecaudototalacuerdo(),
                                "recaudocuotaacuerdo"      => $cierremes->getRecaudocuotaacuerdo(),
                                "recaudodeudaacuerdo"      => $cierremes->getRecaudodeudaacuerdo(),
                                "recaudodeuda"             => $cierremes->getRecaudoDeuda(),
                                "recaudomultas"            => $cierremes->getRecaudomultas(),
                                "recaudocuotames"          => $cierremes->getRecaudocuotames(),
                                "recaudoanimales"          => $cierremes->getRecaudoanimales(),
                                "recaudopesaje"            => $cierremes->getRecaudopesaje(),
                                "recaudovehiculos"         => $cierremes->getRecaudovehiculos(),
                                "recaudoparqueaderos"      => $cierremes->getRecaudoparqueaderos(),
                                "recaudoeventuales"        => $cierremes->getRecaudoeventuales(),
                                "mes"                      => $cierremes->getMes(),
                                "mesletras"                =>$cierremes->getMesletras(),
                                "year"                     => $cierremes->getYear(),
                                "identificacionrecaudador" => $cierremes->getIdentificacionrecaudador(),
                                "nombrerecaudador"         => $cierremes->getNombrerecaudador(),
                                "apellidorecaudador"       => $cierremes->getApellidorecaudador(),
                                "fkidplaza"                => $cierremes->getFkidplaza()->getPkidplaza(),
                                "nombreplaza"              => $cierremes->getFkidplaza()->getNombreplaza()
                            );
                            array_push($array_all, $cierremesList);
                        }

                        $data = array(
                            'status'    => 'Exito',
                            'cierremes' => $array_all,
                        );

                    } else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'El usuario no tiene permisos !!',
                        );
                    }

                } else {
                    $data = array(
                        'status' => 'error',
                        'msg'    => 'Acceso no autorizado !!',
                    );
                }

            } else {
                $data = array(
                    'status' => 'error',
                    'msg'    => 'Envie los parametros, por favor !!',
                );
            }

            return $helpers->json($data);

        } catch (\Exception $e) {

            $trace = $e->getTrace();

            $data = array(
                'idusuario'     => $identity->sub,
                'nombreusuario' => $identity->name,
                'modulo'        => "Cierremes",
                'metodo'        => $trace[0]['function'],
                'mensaje'       => $e->getMessage(),
                'tipoExepcion'  => $trace[0]['class'],
                'pila'          => $e->getTraceAsString(),
                'origen'        => "Web",
            );

            try {

                $excepcion = $this->get(Auditoria::class);
                $excepcion->exepcion(json_encode($data));

            } catch (\Exception $a) {

            }

            throw $e;
        }

    }
}