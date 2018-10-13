<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Ttiporecaudo;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TtiporecaudoController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        //return $this->render('AdministracionBundle:Default:index.html.twig');
    }

    /*
    Esta funcion realiza una consulta de todos los tipos recaudo a la base de datos
    se debe enviar como parametro el token del usuario logueado con el nombre de authorization
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

                    $activo = $request->get('activo', null);   
                    if($activo != null && $activo == "true"){
                    if(in_array("PERM_GENERICOS", $permisosDeserializados)){
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();
                        
                        $query = "SELECT pkidtiporecaudo, codigotiporecaudo, nombretiporecaudo, tiporecaudoactivo, 
                        creaciontiporecaudo, modificaciontiporecaudo FROM ttiporecaudo where tiporecaudoactivo=true  order by nombretiporecaudo ASC;
                ";
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $tiporecaudo = $stmt->fetchAll();

			        $data = array(
                            'status'    => 'Success',
                            'tiporecaudo'     => $tiporecaudo,
                        );
                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'El usuario no tiene permisos genericos !!',
                        );
                    }
                    return $helpers->json($data);
                    }

                    if (in_array("PERM_PLAZAS", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        $query = "SELECT pkidtiporecaudo, codigotiporecaudo, nombretiporecaudo, tiporecaudoactivo, 
                        creaciontiporecaudo, modificaciontiporecaudo FROM ttiporecaudo  order by nombretiporecaudo ASC;
                ";
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $tiporecaudo = $stmt->fetchAll();

                        $data = array(
                            'status' => 'Exito',
                            'tiporecaudo' => $tiporecaudo
                        );

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
                'modulo' => "Tipo Recaudo",
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

    /*Este funcion realiza la inserccion de un tipo recaudo nuevo
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"codigotiporecaudo":"valor",
    "nombretiporecaudo":"valor",
    "tiporecaudoactivo":"valor"}
    un parametro con el nombre authorization, y como valor del parametro el token correspondiente
    al login del usuario.
     */
    /**
     * @Route("/new")
     */

    /* 
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

                    if (in_array("PERM_USUARIOS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $creaciontiporecaudo = new \Datetime("now");
                            $modificaciontiporecaudo = new \Datetime("now");

                            $codigotiporecaudo = (isset($params->codigotiporecaudo)) ? $params->codigotiporecaudo : null;
                            $nombretiporecaudo = (isset($params->nombretiporecaudo)) ? $params->nombretiporecaudo : null;
                            $tiporecaudoactivo = (isset($params->tiporecaudoactivo)) ? $params->tiporecaudoactivo : null;

                            if ($nombretiporecaudo != null && $tiporecaudoactivo != null) {

                                $tiporecaudo = new Ttiporecaudo();

                                if($codigotiporecaudo!=null){
                                $tiporecaudo->setCodigotiporecaudo($codigotiporecaudo);
                                }
                                $tiporecaudo->setNombretiporecaudo($nombretiporecaudo);
                                $tiporecaudo->setTiporecaudoactivo($tiporecaudoactivo);
                                $tiporecaudo->setCreaciontiporecaudo($creaciontiporecaudo);
                                $tiporecaudo->setModificaciontiporecaudo($modificaciontiporecaudo);

                                $isset_tiporecaudo = $em->getRepository('ModeloBundle:Ttiporecaudo')->findBy(array(
                                    "nombretiporecaudo" => $nombretiporecaudo,
                                ));

                                if (count($isset_tiporecaudo) == 0) {

                                    $em->persist($tiporecaudo);
                                    $em->flush();

                                    $data = array(
                                        'status' => 'Exito',
                                        'msg' => 'Tipo Recaudo creado !!',
                                        'tiporecaudo' => $tiporecaudo,
                                    );

                                    $datos = array(
                                        "idusuario" => $identity->sub,
                                        "nombreusuario" => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        "accion" => "insertar",
                                        "tabla" => "Tipo Recaudo",
                                        "valoresrelevantes" => "idtiporecaudo" . ":" . $tiporecaudo->getPkidtiporecaudo(),
                                        "idelemento" => $tiporecaudo->getPkidtiporecaudo(),
                                        "origen" => "web",
                                    );

                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos));

                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'Tipo Recaudo no creado, Duplicado !!',
                                    );
                                }
                            } else {
                                $data = array(
                                    'status' => 'error',
                                    'msg' => 'Algunos campos son nulos !!',
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
                            'msg' => 'No tiene los permisos!!',
                        );
                    }
                } else {
                    $data = array(
                        'status' => 'error',
                        'msg' => 'Token no valido !!',
                    );

                }

            } else {
                $data = array(
                    'status' => 'error',
                    'msg' => 'Envie los parametros, por favor!!',
                );
            }
            return $helpers->json($data);

        } catch (\Exception $e) {

            $trace = $e->getTrace();

            $exepcion = array(
                'idusuario' => $identity->sub,
                'nombreusuario' => $identity->name,
                'modulo' => "Tipo Recaudo",
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
    */

    /*Esta funcion realiza la actualizacion de un tipo recaudo,
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"pkidtiporecaudo":"valor",
    "codigotiporecaudo":"valor",
    "nombretiporecaudo":"valor",
    "tiporecaudoactivo":"valor"}
    un parametro con el nombre authorization, y como valor del parametro el token correspondiente
    al login del usuario.
     */
    /**
     * @Route("/edit")
     */
    /*
    public function editAction(Request $request)
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

                    if (in_array("PERM_USUARIOS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            //$creaciontiporecaudo = new \Datetime("now");
                            $modificaciontiporecaudo = new \Datetime("now");
                            $pkidtiporecaudo = (isset($params->pkidtiporecaudo)) ? $params->pkidtiporecaudo : null;
                            $codigotiporecaudo = (isset($params->codigotiporecaudo)) ? $params->codigotiporecaudo : null;
                            $nombretiporecaudo = (isset($params->nombretiporecaudo)) ? $params->nombretiporecaudo : null;
                            $tiporecaudoactivo = (isset($params->tiporecaudoactivo)) ? $params->tiporecaudoactivo : null;

                            if ($nombretiporecaudo != null && $tiporecaudoactivo != null) {

                                $tiporecaudo = $this->getDoctrine()->getRepository("ModeloBundle:Ttiporecaudo")->findOneBy(array(
                                    "pkidtiporecaudo" => $pkidtiporecaudo,
                                ));

                                if($codigotiporecaudo!=null){
                                    $tiporecaudo->setCodigotiporecaudo($codigotiporecaudo);
                                }
                                if ($nombretiporecaudo != null) {
                                    $nombretiporecaudo_old = $tiporecaudo->getNombretiporecaudo();
                                    $tiporecaudo_id = $tiporecaudo->getPkidtiporecaudo();

                                    $tiporecaudo->setNombretiporecaudo("p");
                                    $em->persist($tiporecaudo);
                                    $em->flush();

                                    $isset_tiporecaudo = $em->getRepository('ModeloBundle:Ttiporecaudo')->findBy(array(
                                        "nombretiporecaudo" => $nombretiporecaudo,
                                    ));

                                    if (count($isset_tiporecaudo) == 0) {
                                        $tiporecaudo->setNombretiporecaudo($nombretiporecaudo);
                                    } else {
                                        $tiporecaudo_old_id = $em->getRepository('ModeloBundle:Ttiporecaudo')->findOneBy(array(
                                            "pkidtiporecaudo" => $tiporecaudo_id,
                                        ));

                                        $tiporecaudo_old_id->setNombretiporecaudo($nombretiporecaudo_old);
                                        $em->persist($tiporecaudo_old_id);
                                        $em->flush();

                                        $data = array(
                                            'status' => 'error',
                                            'msg' => 'Tipo Recaudo no actualizado,  el nombre enviado ya existe !!',
                                        );
                                        return $helpers->json($data);
                                    }

                                }

                                $tiporecaudo->setTiporecaudoactivo($tiporecaudoactivo);
                                //$tiporecaudo->setCreaciontiporecaudo($creaciontiporecaudo);
                                $tiporecaudo->setModificaciontiporecaudo($modificaciontiporecaudo);

                                $em->persist($tiporecaudo);
                                $em->flush();

                                $data = array(
                                    'status' => 'Exito',
                                    'msg' => 'Tipo Recaudo actualizado !!',
                                    'tiporecaudo' => $tiporecaudo,
                                );

                                $datos = array(
                                    "idusuario" => $identity->sub,
                                    "nombreusuario" => $identity->name,
                                    "identificacionusuario" => $identity->identificacion,
                                    "accion" => "editar",
                                    "tabla" => "Tipo Recaudo",
                                    "valoresrelevantes" => "idtiporecaudo" . ":" . $tiporecaudo->getPkidtiporecaudo(),
                                    "idelemento" => $tiporecaudo->getPkidtiporecaudo(),
                                    "origen" => "web",
                                );

                                $auditoria = $this->get(Auditoria::class);
                                $auditoria->auditoria(json_encode($datos));

                            } else {
                                $data = array(
                                    'status' => 'error',
                                    'msg' => 'Algunos campos son nulos !!',
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
                            'msg' => 'No tiene los permisos!!',
                        );
                    }
                } else {
                    $data = array(
                        'status' => 'error',
                        'msg' => 'Token no valido !!',
                    );

                }

            } else {
                $data = array(
                    'status' => 'error',
                    'msg' => 'Envie los parametros, por favor!!',
                );
            }
            return $helpers->json($data);

        } catch (\Exception $e) {

            $trace = $e->getTrace();

            $exepcion = array(
                'idusuario' => $identity->sub,
                'nombreusuario' => $identity->name,
                'modulo' => "Tipo Recaudo",
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
    */

    /*Este funcion realiza la eliminacion de un tipo recaudo
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"pkidtiporecaudo":"valor"}
    un parametro con el nombre authorization, y como valor del parametro el token correspondiente
    al login del usuario.
     */
    /**
     * @Route("/remove")
     */
    /*
    public function removeAction(Request $request)
    {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);
        try {

            if (!empty($request->get('authorization')) && !empty($request->get('json'))) {

                $token = $request->get('authorization', null);
                $authCheck = $jwt_auth->checkToken($token);

                if ($authCheck) {

                    $identity = $jwt_auth->checkToken($token, true);

                    $permisosSerializados = $identity->permisos;
                    $permisosDeserializados = unserialize($permisosSerializados);

                    if (in_array("PERM_USUARIOS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $pkidtiporecaudo = (isset($params->pkidtiporecaudo)) ? $params->pkidtiporecaudo : null;

                            if ($pkidtiporecaudo != null) {

                                $tiporecaudo = $this->getDoctrine()->getRepository("ModeloBundle:Ttiporecaudo")->findOneBy(array(
                                    "pkidtiporecaudo" => $pkidtiporecaudo,
                                ));

                                $isset_tiporecaudo = $this->getDoctrine()->getRepository("ModeloBundle:Ttiporecaudo")->findOneBy(array(
                                    "pkidtiporecaudo" => $pkidtiporecaudo,
                                ));
                                $isset_tiporecaudo_plazatiporecaudo = $this->getDoctrine()->getRepository("ModeloBundle:Tplazatiporecaudo")->findOneBy(array(
                                    "fkidtiporecaudo" => $pkidtiporecaudo,
                                ));

                                if (!is_object($isset_tiporecaudo_plazatiporecaudo)) {

                                            if (is_object($isset_tiporecaudo)) {

                                                $datos = array(
                                                    "idusuario" => $identity->sub,
                                                    "nombreusuario" => $identity->name,
                                                    "identificacionusuario" => $identity->identificacion,
                                                    "accion" => "eliminar",
                                                    "tabla" => "Tipo Recaudo",
                                                    "valoresrelevantes" => "idtiporecaudo" . ":" . $tiporecaudo->getPkidtiporecaudo(),
                                                    "idelemento" => $tiporecaudo->getPkidtiporecaudo(),
                                                    "origen" => "web",
                                                );

                                                $auditoria = $this->get(Auditoria::class);
                                                $auditoria->auditoria(json_encode($datos));

                                                $em->remove($tiporecaudo);
                                                $em->flush();

                                                $data = array(
                                                    'status' => 'Exito',
                                                    'msg' => 'El Tipo Recaudo se ha eliminado correctamente !!',
                                                );

                                            } else {
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg' => 'El Tipo Recaudo a eliminar no existe !!',
                                                );
                                            }
                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'No se puede eliminar el Tipo Recaudo, pertenece a una plaza tipo recaudo !!',
                                    );
                                }

                            } else {
                                $data = array(
                                    'status' => 'error',
                                    'msg' => 'Algunos campos son nulos !!',
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
                            'msg' => 'No tiene los permisos!!',
                        );
                    }
                } else {
                    $data = array(
                        'status' => 'error',
                        'msg' => 'Token no valido !!',
                    );

                }

            } else {
                $data = array(
                    'status' => 'error',
                    'msg' => 'Envie los parametros, por favor!!',
                );
            }
            return $helpers->json($data);

        } catch (\Exception $e) {

            $trace = $e->getTrace();

            $exepcion = array(
                'idusuario' => $identity->sub,
                'nombreusuario' => $identity->name,
                'modulo' => "Usuarios",
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
*/
    //Fin clase
}
