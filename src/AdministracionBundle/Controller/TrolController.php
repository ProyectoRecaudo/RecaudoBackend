<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Trol;
use ModeloBundle\Entity\Trolmodulo;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TrolController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        //return $this->render('AdministracionBundle:Default:index.html.twig');
    }

    /*
    Esta funcion realiza una consulta de todos los roles a la base de datos
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
                        
                        $query = "SELECT pkidrol, codigorol, nombrerol, rolactivo, creacionrol,
                        modificacionrol,descripcionrol,permiso FROM trol where rolactivo=true  order by nombrerol ASC;
                ";
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $roles = $stmt->fetchAll();

			            $data = array(
                            'status'    => 'Success',
                            'rol'     => $roles,
                        );
                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'El usuario no tiene permisos genericos !!',
                        );
                    }
                    return $helpers->json($data);
                    }

                    if (in_array("PERM_USUARIOS", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        $query = "SELECT pkidrol, codigorol, nombrerol, rolactivo, creacionrol,
                        modificacionrol,descripcionrol,permiso FROM trol  order by nombrerol ASC;
                ";
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $roles = $stmt->fetchAll();

                        $array_all = array();
                        foreach ($roles as $rol) {
                            $users_roles = array("pkidrol" => $rol['pkidrol'],
                                "codigorol" => $rol['codigorol'],
                                "nombrerol" => $rol['nombrerol'],
                                "rolactivo" => $rol['rolactivo'],
                                "creacionrol" => $rol['creacionrol'],
                                "modificacionrol" => $rol['modificacionrol'],
                                "descripcionrol" => $rol['descripcionrol'],
                                "permiso" => unserialize($rol['permiso']),
                            );
                            array_push($array_all, $users_roles);
                        }

                        $cabeceras = array("Nombre Rol", "Descripción Rol", "Permisos", "Rol Activo/Inactivo", "Creación", "Modificación");

                        $data = array(
                            'status' => 'Exito',
                            'cabeceras' => $cabeceras,
                            'rol' => $array_all,
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
                'modulo' => "Rol",
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

    /*Este funcion realiza la inserccion de un Rol nuevo
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"codigorol":"valor",
    "nombrerol":"valor",
    "descripcionrol":"valor",
    "rolactivo":"valor"}
    un parametro con el nombre permisos, y enviar dentro de el los permisos en json, de esta forma:
    {"1":"PERM_USUARIOS",
    "2":"PERM_ZONAS"}
    un parametro con el nombre authorization, y como valor del parametro el token correspondiente
    al login del usuario.
     */
    /**
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

                    if (in_array("PERM_USUARIOS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);
                        //permisos serializados
                        $permisos = $request->get("permisos", null);
                        if ($permisos != null) {
                            $params_permisos = (array) json_decode($permisos);
                            $params_permisos[27]="PERM_GENERICOS";
                            $permisos_serializados = serialize($params_permisos);
                        }

                        if ($json != null) {

                            $creacionrol = new \Datetime("now");
                            $modificacionrol = new \Datetime("now");

                            $codigorol = (isset($params->codigorol)) ? $params->codigorol : null;
                            $nombrerol = (isset($params->nombrerol)) ? $params->nombrerol : null;
                            $rolactivo = (isset($params->rolactivo)) ? $params->rolactivo : true;
                            $descripcionrol = (isset($params->descripcionrol)) ? $params->descripcionrol : null;
                            if ($permisos != null) {
                                $permisosrol = $permisos_serializados;
                            }

                            if ($nombrerol != null ) {

                                $rol = new Trol();
                                if ($codigorol != null) {
                                    $rol->setCodigorol($codigorol);
                                }
                                if ($descripcionrol != null) {
                                    $rol->setDescripcionrol($descripcionrol);
                                }
                                //aqui quede
                                $rol->setNombrerol($nombrerol);
                                $rol->setRolactivo($rolactivo);
                                $rol->setCreacionrol($creacionrol);
                                $rol->setModificacionrol($modificacionrol);
                                if ($permisos != null) {
                                    $rol->setPermiso($permisosrol);
                                }

                                $isset_rol = $em->getRepository('ModeloBundle:Trol')->findOneBy(array(
                                    "nombrerol" => $nombrerol,
                                ));

                                if (!is_object($isset_rol)) {

                                    if ($permisos != null) {

                                        $permisos_array = $params_permisos;
                                        foreach ($permisos_array as $clave_pm => $valor_pm) {
                                            $isset_modulo = $em->getRepository('ModeloBundle:Tmodulo')->findOneBy(array(
                                                "pkidmodulo" => $clave_pm,
                                            ));
                                            if (!is_object($isset_modulo)) {
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg' => 'Los id de los permisos no existen !!',
                                                );
                                                return $helpers->json($data);
                                            }
                                        }
                                    }

                                    $em->persist($rol);
                                    $em->flush();

                                    if ($permisos != null) {
                                        foreach ($permisos_array as $clave_pm => $valor_pm) {
                                            $isset_modulo = $em->getRepository('ModeloBundle:Tmodulo')->findOneBy(array(
                                                "pkidmodulo" => $clave_pm,
                                            ));
                                            if (is_object($isset_modulo)) {
                                                $rolmodulo = new Trolmodulo();

                                                $rolmodulo->setCreacionrolmodulo($creacionrol);
                                                $isset_modulo_rol = $em->getRepository('ModeloBundle:Trol')->find($rol->getPkidrol());
                                                $rolmodulo->setFkidrol($isset_modulo_rol);
                                                $isset_modulo_m = $em->getRepository('ModeloBundle:Tmodulo')->find($clave_pm);
                                                $rolmodulo->setFkidmodulo($isset_modulo_m);

                                                $em->persist($rolmodulo);
                                                $em->flush();
                                            }else{
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg' => 'Los id del modulo no existen !!',
                                                );
                                                return $helpers->json($data);
                                            }
                                        }

                                    }
                                    $data = array(
                                        'status' => 'Exito',
                                        'msg' => 'Rol creado !!',
                                        'rol' => $rol,
                                    );

                                    $datos = array(
                                        "idusuario" => $identity->sub,
                                        "nombreusuario" => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        "accion" => "insertar",
                                        "tabla" => "rol",
                                        "valoresrelevantes" => "idrol" . ":" . $rol->getPkidrol(),
                                        "idelemento" => $rol->getPkidrol(),
                                        "origen" => "web",
                                    );

                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos));

                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'Rol no creado, Duplicado !!',
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
                'modulo' => "Rol",
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

    /*Esta funcion realiza la actualizacion de un rol,
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"pkidrol":"valor",
    "codigorol":"valor",
    "nombrerol":"valor",
    "descripcionrol":"valor",
    "rolactivo":"valor"}
    un parametro con el nombre permisosantiguos, y enviar dentro de el los permisos en json, de esta forma:
    {"1":"PERM_USUARIOS",
    "2":"PERM_ZONAS"}
    un parametro con el nombre permisos, y enviar dentro de el los permisos en json, de esta forma:
    {"1":"PERM_USUARIOS",
    "2":"PERM_ZONAS"}
    otro un parametro con el nombre authorization, y como valor del parametro el token correspondiente
    al login del usuario.
     */
    /**
     * @Route("/edit")
     */
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
                        //permisos serializados
                        $permisos = $request->get("permisos", null);
                        if ($permisos != null) {
                            $params_permisos = (array) json_decode($permisos);
                            $permisos_serializados = serialize($params_permisos);
                        }
                        //permisos antiguos serializados
                        $permisosantiguos = $request->get("permisosantiguos", null);
                        if ($permisosantiguos != null) {
                            $params_permisosantiguos = (array) json_decode($permisosantiguos);
                        }

                        if ($json != null) {

                            //$creacionrol = new \Datetime("now");
                            $modificacionrol = new \Datetime("now");
                            $pkidrol = (isset($params->pkidrol)) ? $params->pkidrol : null;
                            $codigorol = (isset($params->codigorol)) ? $params->codigorol : null;
                            $nombrerol = (isset($params->nombrerol)) ? $params->nombrerol : null;
                            $rolactivo = (isset($params->rolactivo)) ? $params->rolactivo : true;
                            $descripcionrol = (isset($params->descripcionrol)) ? $params->descripcionrol : null;
                            if ($permisos != null) {
                                $permisosrol = $permisos_serializados;
                            }

                            if ($nombrerol != null) {

                                $rol = $this->getDoctrine()->getRepository("ModeloBundle:Trol")->findOneBy(array(
                                    "pkidrol" => $pkidrol,
                                ));

                                if(!is_object($rol)){
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id del rol no existe !!',
                                    );
                                    return $helpers->json($data);
                                }

                                if ($codigorol != null) {
                                    $rol->setCodigorol($codigorol);
                                }
                                if ($descripcionrol != null) {
                                    $rol->setDescripcionrol($descripcionrol);
                                }
                                if ($nombrerol != null) {
                                    $nombrerol_old = $rol->getNombrerol();
                                    $rol_id = $rol->getPkidrol();

                                    $rol->setNombrerol("p");
                                    $em->persist($rol);
                                    $em->flush();

                                    $isset_rol = $em->getRepository('ModeloBundle:Trol')->findOneBy(array(
                                        "nombrerol" => $nombrerol,
                                    ));

                                    if (!is_object($isset_rol)) {
                                        $rol->setNombrerol($nombrerol);
                                    } else {
                                        $rol_old_id = $em->getRepository('ModeloBundle:Trol')->findOneBy(array(
                                            "pkidrol" => $rol_id,
                                        ));

                                        $rol_old_id->setNombrerol($nombrerol_old);
                                        $em->persist($rol_old_id);
                                        $em->flush();

                                        $data = array(
                                            'status' => 'error',
                                            'msg' => 'Rol no actualizado,  el nombre enviado ya existe !!',
                                        );
                                        return $helpers->json($data);
                                    }

                                }

                                $rol->setRolactivo($rolactivo);
                                //$rol->setCreacionrol($creacionrol);
                                $rol->setModificacionrol($modificacionrol);
                                if ($permisos != null) {
                                    $rol->setPermiso($permisosrol);
                                }
                                //permisos
                                if ($permisos != null) {

                                    $permisos_array = $params_permisos;
                                    foreach ($permisos_array as $clave_pm => $valor_pm) {
                                        $isset_modulo = $em->getRepository('ModeloBundle:Tmodulo')->findOneBy(array(
                                            "pkidmodulo" => $clave_pm,
                                        ));
                                        if (!is_object($isset_modulo)) {
                                            $data = array(
                                                'status' => 'error',
                                                'msg' => 'Los id de los permisos nuevos no existen !!',
                                            );
                                            return $helpers->json($data);
                                        }
                                    }
                                }

                                if ($permisosantiguos != null) {

                                    $permisosantiguos_array = $params_permisosantiguos;
                                    foreach ($permisosantiguos_array as $clave_pm => $valor_pm) {
                                        $isset_modulo = $em->getRepository('ModeloBundle:Tmodulo')->findOneBy(array(
                                            "pkidmodulo" => $clave_pm,
                                        ));
                                        if (!is_object($isset_modulo)) {
                                            $data = array(
                                                'status' => 'error',
                                                'msg' => 'Los id de los permisos antiguos no existen !!',
                                            );
                                            return $helpers->json($data);
                                        }
                                    }
                                }

                                $em->persist($rol);
                                $em->flush();

                                if ($permisos != null) {
                                    foreach ($permisos_array as $clave_pm => $valor_pm) {
                                        $isset_rolmodulo = $em->getRepository('ModeloBundle:Trolmodulo')->findOneBy(array(
                                            "fkidmodulo" => $clave_pm, "fkidrol" => $rol->getPkidrol(),
                                        ));
                                        if (!is_object($isset_rolmodulo)) {
                                            $rolmodulo = new Trolmodulo();

                                            $rolmodulo->setCreacionrolmodulo($modificacionrol);
                                            $isset_modulo_rol = $em->getRepository('ModeloBundle:Trol')->find($rol->getPkidrol());
                                            $rolmodulo->setFkidrol($isset_modulo_rol);
                                            $isset_modulo_m = $em->getRepository('ModeloBundle:Tmodulo')->find($clave_pm);
                                            $rolmodulo->setFkidmodulo($isset_modulo_m);

                                            $em->persist($rolmodulo);
                                            $em->flush();
                                        }
                                    }

                                }
                                $diff = array_diff_assoc($params_permisosantiguos, $params_permisos);

                                foreach ($diff as $clave => $valor) {
                                    $rolmodulo_rm_remove = $em->getRepository('ModeloBundle:Trolmodulo')->findOneBy(array(
                                        "fkidmodulo" => $clave, "fkidrol" => $rol->getPkidrol(),
                                    ));
                                    if (is_object($rolmodulo_rm_remove)) {
                                        $em->remove($rolmodulo_rm_remove);
                                        $em->flush();
                                    } else {
                                        $data = array(
                                            'status' => 'error',
                                            'msg' => 'Error en la eliminacion de los antiguos id !!',
                                        );
                                        return $helpers->json($data);
                                    }

                                }
                                //recilcado

                                $data = array(
                                    'status' => 'Exito',
                                    'msg' => 'Rol actualizado !!',
                                    'rol' => $rol,
                                );

                                $datos = array(
                                    "idusuario" => $identity->sub,
                                    "nombreusuario" => $identity->name,
                                    "identificacionusuario" => $identity->identificacion,
                                    "accion" => "editar",
                                    "tabla" => "Rol",
                                    "valoresrelevantes" => "idrol" . ":" . $rol->getPkidrol(),
                                    "idelemento" => $rol->getPkidrol(),
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
                'modulo' => "Rol",
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

    /*Este funcion realiza la eliminacion de un tipo de sector
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"pkidrol":"valor"}
    un parametro con el nombre authorization, y como valor del parametro el token correspondiente
    al login del usuario.
     */
    /**
     * @Route("/remove")
     */
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

                            $pkidrol = (isset($params->pkidrol)) ? $params->pkidrol : null;

                            if ($pkidrol != null) {

                                $rol = $this->getDoctrine()->getRepository("ModeloBundle:Trol")->findOneBy(array(
                                    "pkidrol" => $pkidrol,
                                ));
                                $isset_rol_usuario = $this->getDoctrine()->getRepository("ModeloBundle:Tusuario")->findOneBy(array(
                                    "fkidrol" => $pkidrol,
                                ));

                                if (!is_object($isset_rol_usuario)) {

                                    $isset_rol_rolmodulo = $this->getDoctrine()->getRepository("ModeloBundle:Trolmodulo")->findOneBy(array(
                                        "fkidrol" => $pkidrol,
                                    ));

                                    if (!is_object($isset_rol_rolmodulo)) {

                                        if (is_object($rol)) {

                                            $datos = array(
                                                "idusuario" => $identity->sub,
                                                "nombreusuario" => $identity->name,
                                                "identificacionusuario" => $identity->identificacion,
                                                "accion" => "eliminar",
                                                "tabla" => "Rol",
                                                "valoresrelevantes" => "idrol" . ":" . $rol->getPkidrol(),
                                                "idelemento" => $rol->getPkidrol(),
                                                "origen" => "web",
                                            );

                                            $auditoria = $this->get(Auditoria::class);
                                            $auditoria->auditoria(json_encode($datos));

                                            $em->remove($rol);
                                            $em->flush();

                                            $data = array(
                                                'status' => 'Exito',
                                                'msg' => 'El Rol se ha eliminado correctamente !!',
                                            );

                                        } else {
                                            $data = array(
                                                'status' => 'error',
                                                'msg' => 'El Rol a eliminar no existe !!',
                                            );
                                        }

                                    } else {
                                        $data = array(
                                            'status' => 'error',
                                            'msg' => 'No se puede eliminar el Rol, pertenece a un modulo !!',
                                        );
                                    }

                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'No se puede eliminar el Rol, pertenece a un usuario !!',
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
                'modulo' => "Rol",
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

    //Fin clase
}
