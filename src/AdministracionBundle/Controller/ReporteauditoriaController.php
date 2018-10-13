<?php

namespace AdministracionBundle\Controller;

use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ReporteauditoriaController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        //return $this->render('AdministracionBundle:Default:index.html.twig');
    }

    /*
    Esta funcion realiza una consulta de acuerdo al nombre del parametro auditoria
     true o false que se envie, si se envia true, adicionalmente se deben enviar un parametro en json 
     que se llame filtros y como valor los filtros, ejemplo:
     {
    "fechainicio":"2018-09-05"
    ,"fechafin":"2018-09-16"
    }
    y si el valor del parametro auditoria se envia false, se obtendra como resultado los filtros que debe tener ese reporte,
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

                        $auditoria = $request->get('reporteauditoria', null);
                        $filtros = $request->get('filtros', null);
                        $params_filtros = (array) json_decode($filtros);

                        if ($auditoria == "true") {

                            if ($filtros != null) {

                                $sql = "SELECT nombreusuario, identificacionusuario, 
                                tabla, valoresrelevantes, accion, tabla,creacionauditoria,
                                origenauditoria
                           FROM public.tauditoria";

                                $sql .= " WHERE ";
                                $count_fech = 0;
                                foreach ($params_filtros as $clave => $value) {

                                    $query = "SELECT column_name FROM information_schema.columns WHERE table_schema='public'
                                                    and table_name='tauditoria'
                                                    and column_name='$clave'";
                                    $stmt = $db->prepare($query);
                                    $params = array();
                                    $stmt->execute($params);
                                    $check_filtro = $stmt->fetchAll();

                                    if (count($check_filtro) != 0 || $clave == 'fechainicio' || $clave == 'fechafin') {

                                        if ($clave != 'fechainicio' && $clave != 'fechafin') {
                                            $query_type = "select data_type from information_schema.columns WHERE TABLE_NAME='tauditoria' AND COLUMN_NAME='$clave'";
                                            $stmt = $db->prepare($query_type);
                                            $params = array();
                                            $stmt->execute($params);
                                            $query_type_consul = $stmt->fetchAll();

                                            foreach ($query_type_consul as $valor) {
                                                foreach ($valor as $valor1) {
                                                    $type_campo = $valor1;
                                                    break;
                                                }
                                            }
                                        } else {
                                            $type_campo = "timestamp without time zone";
                                        }

                                        if ($type_campo == 'character varying') {
                                            $equals = " like ";
                                            $id = "UPPER(" . $clave . ")";
                                            $indice = "UPPER(" . "'%" . $value . "%'" . ")";
                                            $count_fech = 0;
                                        } else {
                                            if ($type_campo == 'integer') {
                                                $equals = " = ";
                                                $id = $clave;
                                                $indice = $value;
                                                $count_fech = 0;
                                            } else {
                                                $count_fech++;
                                                if ($count_fech == 1) {
                                                    $equals = " >= ";
                                                    $indice = "'" . $value . "'";
                                                } else {
                                                    $equals = " <= ";
                                                    $indice = "'" . $value . "'";
                                                }
                                            }
                                        }
                                        if ($count_fech == 0) {
                                            $sql .= "$id" . $equals . "$indice" . " and ";
                                        } else {
                                            if ($count_fech == 1) {
                                                $sql .= "creacionauditoria" . $equals . "$indice" . " and ";
                                            } else {
                                                $sql .= "creacionauditoria" . $equals . "$indice" . " and ";
                                            }
                                        }

                                    } else {
                                        $data = array(
                                            'status' => 'error',
                                            'msg' => 'El nombre del filtro ' . $clave . ' no existe en la tabla',
                                        );
                                        return $helpers->json($data);
                                    }
                                }
                                $sql = substr($sql, 0, -4);
                                if ($clave != 'fechainicio' && $clave != 'fechafin') {
                                foreach ($params_filtros as $clave => $value) {
                                    $sql .= " order by " . $clave . " ASC";
                                    break;
                                }
                                }

                                $stmt = $db->prepare($sql);
                                $params = array();
                                $stmt->execute($params);
                                $auditoria = $stmt->fetchAll();

                                $cabeceras = array();


                                $campo = array("nombrecampo" => "identificacionusuario", "nombreetiqueta" => "Identificacion");
                                array_push($cabeceras, $campo);
                                $campo = array("nombrecampo" => "nombreusuario", "nombreetiqueta" => "Nombre de usuario");
                                array_push($cabeceras, $campo);
                                $campo = array("nombrecampo" => "accion", "nombreetiqueta" => "Accion");
                                array_push($cabeceras, $campo);
                                $campo = array("nombrecampo" => "tabla", "nombreetiqueta" => "Tabla");
                                array_push($cabeceras, $campo);
                                $campo = array("nombrecampo" => "origenauditoria", "nombreetiqueta" => "Origen auditoria");
                                array_push($cabeceras, $campo);
                                $campo = array("nombrecampo" => "creacionauditoria", "nombreetiqueta" => "Fecha");
                                array_push($cabeceras, $campo);

                                $data = array(
                                    'status' => 'Exito',
                                    'cabeceras' => $cabeceras,
                                    'reporteauditoria' => $auditoria,

                                );
                            } else {
                                $data = array(
                                    'status' => 'error',
                                    'msg' => 'Envie los filtros, por favor !!',
                                );
                                return $helpers->json($data);
                            }
                        } else {
                            if ($auditoria == "false") {
                                $filtros_all = array();
                                /*
                                $filtros = array("nombrefiltro" => "Plazas de Mercado", "nombretabla" => "plaza", "nombreatributo" => "pkidplaza", "tipofiltro" => "select");
                                array_push($filtros_all, $filtros);
                                $filtros = array("nombrefiltro" => "Sectores", "nombretabla" => "sector", "nombreatributo" => "pkidsector", "tipofiltro" => "select");
                                array_push($filtros_all, $filtros);
                                $filtros = array("nombrefiltro" => "Nombre usuario", "nombreatributo" => "nombreusuario", "tipofiltro" => "input");
                                array_push($filtros_all, $filtros);
                                $filtros = array("nombrefiltro" => "Cedula", "nombreatributo" => "identificacionusuario", "tipofiltro" => "input");
                                array_push($filtros_all, $filtros);*/


                                $title = array("Reporte auditoria");

                                $data = array(
                                    'status' => 'Exito',
                                    'nombrereporte' => 'reporteauditoria',
                                    'nombretabla' => 'tauditoria',
                                    /*
                                    'filtros' => $filtros_all,
                                    */
                                    'title' => $title,
                                );
                            }
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
                'modulo' => "Reporte auditoria",
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
