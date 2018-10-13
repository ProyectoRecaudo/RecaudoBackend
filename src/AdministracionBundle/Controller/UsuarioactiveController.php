<?php

namespace AdministracionBundle\Controller;

use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UsuarioactiveController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        //return $this->render('AdministracionBundle:Default:index.html.twig');
    }

    /*
    Esta funcion realiza la activacion o desactivacion de un registro de una tabla,
    se debe enviar un parametro con el nombre de json con lo siguiente:
    {"pkid":"valor"
    ,"active":"true" (true or false)
    ,"nombretabla":"tabla"}
    también se debe enviar un parametro con el nombre de authorization
    y como valor el token generado a la hora de loguearse como usuario
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

                    if (in_array("PERM_GENERICOS", $permisosDeserializados)) {
                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $pkid = (isset($params->pkid)) ? $params->pkid : null;
                            $nombretabla = (isset($params->nombretabla)) ? $params->nombretabla : null;
                            $active = (isset($params->active)) ? $params->active : true;

                            if ($pkid != null && $nombretabla != null) {

                                $em = $this->getDoctrine()->getManager();

                                $db = $em->getConnection();

                                $query = "SELECT t.table_name FROM information_schema.tables t WHERE t.table_schema = 'public' ORDER BY t.table_name;
                ";
                                $stmt = $db->prepare($query);
                                $params = array();
                                $stmt->execute($params);
                                $tablas_exist = $stmt->fetchAll();

                                foreach ($tablas_exist as $tablas_exist_b) {
                                    if ($tablas_exist_b['table_name'] == $nombretabla) {
                                        $exits = true;
                                    }
                                }

                                if (isset($exits)) {

                                    $isset_tabla = $em->getRepository("ModeloBundle:" . ucfirst($nombretabla))->find($pkid);

                                    if (is_object($isset_tabla)) {

                                        $tabla = $em->getRepository("ModeloBundle:" . ucfirst($nombretabla))->find($pkid);

                                        $nombretabla_substr = substr($nombretabla, 1);
                                        $nombretabla_ucfirst = ucfirst($nombretabla_substr);
                                        $set = "set" . $nombretabla_ucfirst . "activo";
                                        $tabla->$set($active);

                                        $em->persist($tabla);
                                        $em->flush();


                                        $data = array(
                                            'status' => 'Exito',
                                            'msg' => 'Se realizo correctamente',
                                        );

                                    } else {
                                        $data = array(
                                            'status' => 'error',
                                            'msg' => 'El id enviado no existe !!',
                                        );
                                    }

                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El nombre de tabla enviado, no existe en la base de datos',
                                    );
                                }
                            } else {
                                $data = array(
                                    'status' => 'error',
                                    'msg' => 'Envie el id,nombre tabla y la activacion(true)/desactivacion(false) dentro del parametro json',
                                );
                            }

                        } else {
                            $data = array(
                                'status' => 'error',
                                'msg' => 'Parametro json es nulo!!',
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
                'modulo' => "Active Inactive Registros",
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
