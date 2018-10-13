<?php

namespace AdministracionBundle\Controller;

use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ReporterecibopuestoeventualController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        //return $this->render('AdministracionBundle:Default:index.html.twig');
    }

    /*
    Esta funcion realiza una consulta de acuerdo al nombre del parametro recibopuestoeventual
     true o false que se envie, si se envia true, adicionalmente se deben enviar un parametro en json 
     que se llame filtros y como valor los filtros, ejemplo:
     {
         "fechainicio":"2018-09-05"
         ,"fechafin":"2018-09-16",
    ...
    }
    y si el valor del parametro recibopuestoeventual se envia false, se obtendra como resultado los filtros que debe tener ese reporte,
    esto es para generarlos dinamicamente.

     */
    /**
     * @Route("/query")
     */
    public function queryAction(Request $request)
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

                    if (in_array("PERM_REPORTES", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        $recibopuestoeventual = $request->get('reporterecibopuestoeventual', null);
                        $filtros = $request->get('filtros', null);
                        $params_filtros = (array) json_decode($filtros);

                        if ($recibopuestoeventual == "false") {
                            $filtros_all = array();
                            /*
                            $filtros = array("nombrefiltro" => "Plazas de Mercado", "nombretabla" => "plaza", "nombreatributo" => "fkidplaza", "tipofiltro" => "select");
                            array_push($filtros_all, $filtros);
                            $filtros = array("nombrefiltro" => "Sectores", "nombretabla" => "sector", "nombreatributo" => "fkidsector", "tipofiltro" => "select");
                            array_push($filtros_all, $filtros);*/
                            $filtros = array("nombrefiltro" => "Nombre recaudador", "nombreatributo" => "nombrerecaudador", "tipofiltro" => "input");
                            array_push($filtros_all, $filtros);
                            $filtros = array("nombrefiltro" => "Cedula", "nombreatributo" => "identificacionrecaudador", "tipofiltro" => "input");
                            array_push($filtros_all, $filtros);

                            $title = array("Reporte recaudo eventual de puestos");

                            $data = array(
                                'status' => 'Exito',
                                'nombrereporte' => 'reporterecibopuestoeventual',
                                'nombretabla' => 'trecibopuestoeventual',
                                'numerofiltro' => '2',
                                'filtros' => $filtros_all,
                                'title' => $title,
                            );
                        }
                    } else {
                        $data = array(
                            'status' => 'error',
                            'msg' => 'El usuario no tiene permisos !!',
                        );
                    }

                } else {
                    $data = array(
                        'status' => 'error',
                        'msg' => 'Acceso no autorizado !!',
                    );
                }

            } else {
                $data = array(
                    'status' => 'error',
                    'msg' => 'Envie los parametros, por favor !!',
                );
            }

            return $helpers->json($data);

        } catch (\Exception $e) {

            $trace = $e->getTrace();

            $exepcion = array(
                'idusuario' => $identity->sub,
                'nombreusuario' => $identity->name,
                'modulo' => "recibopuestoeventual",
                'metodo' => $trace[0]['function'],
                'mensaje' => $e->getMessage(),
                'tipoExepcion' => $trace[0]['class'],
                'pila' => $e->getTraceAsString(),
                'origen' => "Web",
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
