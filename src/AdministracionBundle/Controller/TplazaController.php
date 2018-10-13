<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Tplaza;
use ModeloBundle\Entity\Tplazatiporecaudo;
use ModeloBundle\Entity\Tzona;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TplazaController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        //return $this->render('AdministracionBundle:Default:index.html.twig');
    }

    /*
    Esta funcion realiza una consulta de todos las plazas a la base de datos
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

                    $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                    $activo = $request->get('activo', null);   
                    if($activo != null && $activo == "true"){
                    if(in_array("PERM_GENERICOS", $permisosDeserializados)){
                        
                        $query = "SELECT DISTINCT pkidplaza, codigoplaza, nombreplaza, plazaactivo FROM tplaza join tplazatiporecaudo on tplaza.pkidplaza=tplazatiporecaudo.fkidplaza join ttiporecaudo on ttiporecaudo.pkidtiporecaudo=tplazatiporecaudo.fkidtiporecaudo where plazaactivo=true
                        ";

                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $plazas = $stmt->fetchAll();
                        $array_all = array();
                        //aqui quede con error
                        foreach ($plazas as $plaza) {
                                $plazas_roles = array("pkidplaza" => $plaza['pkidplaza'],
                                    "codigoplaza" => $plaza['codigoplaza'],
                                    "nombreplaza" => $plaza['nombreplaza'],
                                    "plazaactivo" => $plaza['plazaactivo']
                                );
                                array_push($array_all, $plazas_roles);
                        }
                        

			            $data = array(
                            'status'    => 'Success',
                            'plaza'     => $array_all,
                        );
                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'El usuario no tiene permisos genericos !!',
                        );
                    }
                    return $helpers->json($data);
                    }

                        $conzonas = $request->get('conzonas', null);
                        $params = json_decode($conzonas);

                        if ($conzonas != null) {
                            if(in_array("PERM_GENERICOS", $permisosDeserializados)){
                            $conzonas = (isset($params->conzonas)) ? $params->conzonas : null;

                            if ($conzonas != null) {
                                if ($conzonas == "true") {
                                    $query = "SELECT DISTINCT pkidplaza, codigoplaza, nombreplaza, plazaactivo, creacionplaza,
                                    modificacionplaza FROM tplaza join tzona on tplaza.pkidplaza=tzona.fkidplaza where nombrezona <> 'SIN ZONA' and plazaactivo = true
                        ";
                                } else {
                                    if ($conzonas == "false") {
                                        /*$query = "SELECT pkidplaza,count(pkidzona) as numerodezonas,nombreplaza
                                        FROM tplaza join tzona on tplaza.pkidplaza=tzona.fkidplaza group by pkidplaza,nombreplaza having count(pkidzona) <= 1
                                        ";*/
                                        $query = "SELECT DISTINCT pkidplaza,fkidplaza, codigoplaza, nombreplaza, plazaactivo, creacionplaza,
                                    modificacionplaza FROM tplaza join tplazatiporecaudo on tplaza.pkidplaza=tplazatiporecaudo.fkidplaza join ttiporecaudo on ttiporecaudo.pkidtiporecaudo=tplazatiporecaudo.fkidtiporecaudo where plazaactivo = true
                        ";

                                    }
                                }
                            }

                            $stmt = $db->prepare($query);
                            $params = array();
                            $stmt->execute($params);
                            $plazas = $stmt->fetchAll();

                            $data = array(
                                'status' => 'Exito',
                                'plaza' => $plazas,
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

                        $query = "SELECT pkidplaza, codigoplaza, nombreplaza, plazaactivo, creacionplaza,
                        modificacionplaza,pkidtiporecaudo,nombretiporecaudo FROM tplaza join tplazatiporecaudo on tplaza.pkidplaza=tplazatiporecaudo.fkidplaza join ttiporecaudo on ttiporecaudo.pkidtiporecaudo=tplazatiporecaudo.fkidtiporecaudo
                        ";

                        $query .= " ORDER BY nombreplaza ASC;";

                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $plazas = $stmt->fetchAll();
                        $array_all = array();
                        $acum = "algo";
                        //aqui quede con error
                        foreach ($plazas as $plaza) {
                            if ($acum != $plaza['pkidplaza']) {

                                $array_tipos = array();
                                foreach ($plazas as $plaza1) {
                                    if ($plaza1['pkidplaza'] == $plaza['pkidplaza']) {

                                        $array_tipo_all = array("pkidtiporecaudo" => $plaza1['pkidtiporecaudo'], "nombretiporecaudo" => $plaza1['nombretiporecaudo']);
                                        array_push($array_tipos, $array_tipo_all);

                                    }
                                }
                                $plazas_roles = array("pkidplaza" => $plaza['pkidplaza'],
                                    "codigoplaza" => $plaza['codigoplaza'],
                                    "nombreplaza" => $plaza['nombreplaza'],
                                    "plazaactivo" => $plaza['plazaactivo'],
                                    "tiporecaudo" => $array_tipos,
                                );
                                array_push($array_all, $plazas_roles);
                            }
                            $acum = $plaza['pkidplaza'];
                        }

                        $cabeceras = array("Nombre Plaza", "Plaza Activa/Inactiva", "Creación", "Modificación");

                        $data = array(
                            'status' => 'Exito',
                            'cabeceras' => $cabeceras,
                            'plaza' => $array_all,
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
                'modulo' => "Plazas de mercado",
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

    /*Este funcion realiza la inserccion de una plaza nueva
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"codigoplaza":"valor",
    "nombreplaza":"valor",
    "plazaactivo":"valor"}
    otro parametro con el nombre de tiporecaudo de esta manera:
    {"1":idtiporecaudo
    ,"2":idtiporecaudo}
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

                    if (in_array("PERM_PLAZAS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);
                        //permisos serializados
                        $tiporecaudo = $request->get("tiporecaudo", null);
                        if ($tiporecaudo != null) {
                            $params_tiporecaudo = (array) json_decode($tiporecaudo);
                        } else {
                            $data = array(
                                'status' => 'error',
                                'msg' => 'Envie los tipos de recaudo seleccionados !!',
                            );
                            return $helpers->json($data);
                        }

                        if ($json != null) {

                            $creacionplaza = new \Datetime("now");
                            $modificacionplaza = new \Datetime("now");

                            $codigoplaza = (isset($params->codigoplaza)) ? $params->codigoplaza : null;
                            $nombreplaza = (isset($params->nombreplaza)) ? $params->nombreplaza : null;
                            $plazaactivo = (isset($params->plazaactivo)) ? $params->plazaactivo : true;
                            $idtiporecaudo = $params_tiporecaudo;

                            if ($codigoplaza != null && $nombreplaza != null ) {

                                $plaza = new Tplaza();

                                $plaza->setCodigoplaza($codigoplaza);
                                $plaza->setNombreplaza($nombreplaza);
                                $plaza->setPlazaactivo($plazaactivo);
                                $plaza->setCreacionplaza($creacionplaza);
                                $plaza->setModificacionplaza($modificacionplaza);

                                $isset_plaza = $em->getRepository('ModeloBundle:Tplaza')->findOneBy(array(
                                    "nombreplaza" => $nombreplaza,
                                ));

                                if (!is_object($isset_plaza)) {
                                    foreach ($idtiporecaudo as $idtr) {
                                        $isset_tiporecaudo = $em->getRepository('ModeloBundle:Ttiporecaudo')->findOneBy(array(
                                            "pkidtiporecaudo" => $idtr,
                                        ));

                                        if (!is_object($isset_tiporecaudo)) {
                                            $data = array(
                                                'status' => 'error',
                                                'msg' => 'Los id de los tipos de recaudo no existen !!',
                                            );
                                            return $helpers->json($data);
                                        }
                                    }

                                    $em->persist($plaza);
                                    $em->flush();

                                    foreach ($idtiporecaudo as $idtr) {

                                        $plazatiporecaudo = new Tplazatiporecaudo();

                                        $isset_tiporecaudo = $em->getRepository('ModeloBundle:Ttiporecaudo')->findOneBy(array(
                                            "pkidtiporecaudo" => $idtr,
                                        ));

                                        if (is_object($isset_tiporecaudo)) {

                                            $identiporecaudo = $em->getRepository('ModeloBundle:Ttiporecaudo')->find($idtr);
                                            $plaza_fk = $em->getRepository('ModeloBundle:Tplaza')->find($plaza->getPkidplaza());

                                            $plazatiporecaudo->setCreacionplazatiporecaudo($creacionplaza);
                                            $plazatiporecaudo->setFkidplaza($plaza_fk);

                                            $plazatiporecaudo->setFkidtiporecaudo($identiporecaudo);

                                            $em->persist($plazatiporecaudo);
                                            $em->flush();
                                        } else {
                                            $em->remove($plazatiporecaudo);
                                            $em->flush();

                                            $em->remove($plaza);
                                            $em->flush();

                                            $data = array(
                                                'status' => 'error',
                                                'msg' => 'Los id de los tipos de recaudo no existen !!',
                                            );
                                            return $helpers->json($data);
                                        }
                                    }
                                    //agregar zona por defecto

                                    $zona = new Tzona();
                                    $zona->setCodigozona("123");
                                    $zona->setNombrezona("SIN ZONA");
                                    $zona->setZonaactivo(true);
                                    $zona->setCreacionzona($creacionplaza);
                                    $zona->setModificacionzona($modificacionplaza);
                                    $v_plaza = $em->getRepository('ModeloBundle:Tplaza')->find($plaza->getPkidplaza());
                                    $zona->setFkidplaza($v_plaza);
                                    $v_usuario = $em->getRepository('ModeloBundle:Tusuario')->find($identity->sub);
                                    $zona->setFkidusuario($v_usuario);

                                    $em->persist($zona);
                                    $em->flush();

                                    $data = array(
                                        'status' => 'Exito',
                                        'msg' => 'Plaza creada !!',
                                        'tiporecuado' => 'Agregados !!',
                                        'plaza' => $plaza,
                                    );

                                    $datos = array(
                                        "idusuario" => $identity->sub,
                                        "nombreusuario" => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        "accion" => "insertar",
                                        "tabla" => "Plazas",
                                        "valoresrelevantes" => "idplaza" . ":" . $plaza->getPkidplaza(),
                                        "idelemento" => $plaza->getPkidplaza(),
                                        "origen" => "web",
                                    );

                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos));
                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'Plaza no creada, Duplicada !!',
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
            foreach ($idtiporecaudo as $idtr) {

                $isset_plazatiporecaudo = $em->getRepository('ModeloBundle:Tplazatiporecaudo')->findOneBy(array(
                    "fkidtiporecaudo" => $idtr, "fkidplaza" => $plaza->getPkidplaza(),
                ));

                if (is_object($isset_plazatiporecaudo)) {
                    $em->remove($isset_plazatiporecaudo);
                    $em->flush();
                }
            }

            $em->remove($plaza);
            $em->flush();

            $trace = $e->getTrace();

            $exepcion = array(
                'idusuario' => $identity->sub,
                'nombreusuario' => $identity->name,
                'modulo' => "PLazas",
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

    /*Esta funcion realiza la actualizacion de una plaza,
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"pkidplaza":"valor",
    "codigoplaza":"valor",
    "nombreplaza":"valor",
    "plazaactivo":"valor"}
    otro parametro con el nombre de tiporecaudoantiguos de esta manera:
    {"1":idtiporecaudo
    ,"2":idtiporecaudo}
    otro parametro con el nombre de tiporecaudonuevos de esta manera:
    {"2":idtiporecaudo
    ,"3":idtiporecaudo}
    un parametro con el nombre authorization, y como valor del parametro el token correspondiente
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

                    if (in_array("PERM_PLAZAS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);
                        //tiporecaudoantiguos serializados
                        $tiporecaudoantiguos = $request->get("tiporecaudoantiguos", null);
                        if ($tiporecaudoantiguos != null) {
                            $params_tiporecaudoantiguos = (array) json_decode($tiporecaudoantiguos);
                        } else {
                            $data = array(
                                'status' => 'error',
                                'msg' => 'Envie los tipos de recaudo antiguos del usuario !!',
                            );
                            return $helpers->json($data);
                        }
                        //tiporecaudonuevos serializados
                        $tiporecaudonuevos = $request->get("tiporecaudonuevos", null);
                        if ($tiporecaudonuevos != null) {
                            $params_tiporecaudonuevos = (array) json_decode($tiporecaudonuevos);
                        } else {
                            $data = array(
                                'status' => 'error',
                                'msg' => 'Envie los tipos de recaudo seleccionados !!',
                            );
                            return $helpers->json($data);
                        }

                        if ($json != null) {

                            //$creacionplaza = new \Datetime("now");
                            $modificacionplaza = new \Datetime("now");
                            $pkidplaza = (isset($params->pkidplaza)) ? $params->pkidplaza : null;
                            $codigoplaza = (isset($params->codigoplaza)) ? $params->codigoplaza : null;
                            $nombreplaza = (isset($params->nombreplaza)) ? $params->nombreplaza : null;
                            $plazaactivo = (isset($params->plazaactivo)) ? $params->plazaactivo : true;
                            $idtiporecaudoantiguos = $params_tiporecaudoantiguos;
                            $idtiporecaudonuevos = $params_tiporecaudonuevos;

                            if ($codigoplaza != null && $nombreplaza != null) {

                                $plaza = $this->getDoctrine()->getRepository("ModeloBundle:Tplaza")->findOneBy(array(
                                    "pkidplaza" => $pkidplaza,
                                ));

                                if (!is_object($plaza)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id de la plaza no existe !!',
                                    );
                                    return $helpers->json($data);
                                }

                                $plaza->setCodigoplaza($codigoplaza);

                                if ($nombreplaza != null) {
                                    $nombreplaza_old = $plaza->getNombreplaza();
                                    $plaza_id = $plaza->getPkidplaza();

                                    $plaza->setNombreplaza("p");
                                    $em->persist($plaza);
                                    $em->flush();

                                    $isset_plaza = $em->getRepository('ModeloBundle:Tplaza')->findOneBy(array(
                                        "nombreplaza" => $nombreplaza,
                                    ));

                                    if (!is_object($isset_plaza)) {
                                        $plaza->setNombreplaza($nombreplaza);
                                    } else {
                                        $plaza_old_id = $em->getRepository('ModeloBundle:Tplaza')->findOneBy(array(
                                            "pkidplaza" => $plaza_id,
                                        ));

                                        $plaza_old_id->setNombreplaza($nombreplaza_old);
                                        $em->persist($plaza_old_id);
                                        $em->flush();

                                        $data = array(
                                            'status' => 'error',
                                            'msg' => 'Plaza no actualizada,  el nombre enviado ya existe !!',
                                        );
                                        return $helpers->json($data);
                                    }

                                }

                                $plaza->setPlazaactivo($plazaactivo);
                                //$plaza->setCreacionplaza($creacionplaza);
                                $plaza->setModificacionplaza($modificacionplaza);

                                //reciclado para roles

                                foreach ($idtiporecaudonuevos as $idtr) {
                                    $isset_tiporecaudo = $em->getRepository('ModeloBundle:Ttiporecaudo')->findOneBy(array(
                                        "pkidtiporecaudo" => $idtr,
                                    ));

                                    if (!is_object($isset_tiporecaudo)) {
                                        $data = array(
                                            'status' => 'error',
                                            'msg' => 'Los id de los tipos de recaudo seleccionados no existen !!',
                                        );
                                        return $helpers->json($data);
                                    }
                                }

                                foreach ($idtiporecaudoantiguos as $idtr) {
                                    $isset_tiporecaudo = $em->getRepository('ModeloBundle:Ttiporecaudo')->findOneBy(array(
                                        "pkidtiporecaudo" => $idtr,
                                    ));

                                    if (!is_object($isset_tiporecaudo)) {
                                        $data = array(
                                            'status' => 'error',
                                            'msg' => 'Los id de los tipos de recaudo antiguos del usuario no existen !!',
                                        );
                                        return $helpers->json($data);
                                    }
                                }

                                $em->persist($plaza);
                                $em->flush();

                                foreach ($idtiporecaudonuevos as $idtr) {

                                    $plazatiporecaudo_tr = $em->getRepository('ModeloBundle:Tplazatiporecaudo')->findOneBy(array(
                                        "fkidtiporecaudo" => $idtr, "fkidplaza" => $pkidplaza,
                                    ));

                                    if (!is_object($plazatiporecaudo_tr)) {

                                        $plazatiporecaudo = new Tplazatiporecaudo();

                                        $identiporecaudo = $em->getRepository('ModeloBundle:Ttiporecaudo')->find($idtr);
                                        $plaza_fk = $em->getRepository('ModeloBundle:Tplaza')->find($plaza->getPkidplaza());

                                        $plazatiporecaudo->setCreacionplazatiporecaudo($modificacionplaza);
                                        $plazatiporecaudo->setFkidplaza($plaza_fk);

                                        $plazatiporecaudo->setFkidtiporecaudo($identiporecaudo);

                                        $em->persist($plazatiporecaudo);
                                        $em->flush();
                                    }
                                }

                                $diff = array_diff($idtiporecaudoantiguos, $idtiporecaudonuevos);
                                foreach ($diff as $value) {
                                    $plazatiporecaudo_tr_remove = $em->getRepository('ModeloBundle:Tplazatiporecaudo')->findOneBy(array(
                                        "fkidtiporecaudo" => $value, "fkidplaza" => $plaza,
                                    ));
                                    if (is_object($plazatiporecaudo_tr_remove)) {
                                        $em->remove($plazatiporecaudo_tr_remove);
                                        $em->flush();
                                    } else {
                                        $data = array(
                                            'status' => 'error',
                                            'msg' => 'Error en la eliminacion de los antiguos id !!',
                                        );
                                        return $helpers->json($data);
                                    }
                                }
                                //reciclado

                                //termina aqui

                                $data = array(
                                    'status' => 'Exito',
                                    'msg' => 'Plaza actualizada !!',
                                    'plaza' => $plaza,
                                );

                                $datos = array(
                                    "idusuario" => $identity->sub,
                                    "nombreusuario" => $identity->name,
                                    "identificacionusuario" => $identity->identificacion,
                                    "accion" => "editar",
                                    "tabla" => "Plazas",
                                    "valoresrelevantes" => "idplaza" . ":" . $plaza->getPkidplaza(),
                                    "idelemento" => $plaza->getPkidplaza(),
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
                'modulo' => "PLazas",
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

    /*Este funcion realiza la eliminacion de una plaza
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"pkidplaza":"valor"}
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

                    if (in_array("PERM_PLAZAS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $pkidplaza = (isset($params->pkidplaza)) ? $params->pkidplaza : null;

                            if ($pkidplaza != null) {

                                $plaza = $this->getDoctrine()->getRepository("ModeloBundle:Tplaza")->findOneBy(array(
                                    "pkidplaza" => $pkidplaza,
                                ));
                                $isset_plaza_zona = $this->getDoctrine()->getRepository("ModeloBundle:Tzona")->findOneBy(array(
                                    "fkidplaza" => $pkidplaza,
                                ));

                                if (!is_object($isset_plaza_zona)) {

                                    $isset_plaza_trecaudo = $this->getDoctrine()->getRepository("ModeloBundle:Tplazatiporecaudo")->findOneBy(array(
                                        "fkidplaza" => $pkidplaza,
                                    ));

                                    if (!is_object($isset_plaza_trecaudo)) {

                                        if (is_object($plaza)) {

                                            $datos = array(
                                                "idusuario" => $identity->sub,
                                                "nombreusuario" => $identity->name,
                                                "identificacionusuario" => $identity->identificacion,
                                                "accion" => "eliminar",
                                                "tabla" => "Plazas",
                                                "valoresrelevantes" => "idplaza" . ":" . $plaza->getPkidplaza(),
                                                "idelemento" => $plaza->getPkidplaza(),
                                                "origen" => "web",
                                            );

                                            $auditoria = $this->get(Auditoria::class);
                                            $auditoria->auditoria(json_encode($datos));

                                            $em->remove($plaza);
                                            $em->flush();

                                            $data = array(
                                                'status' => 'Exito',
                                                'msg' => 'La plaza se ha eliminado correctamente !!',
                                            );

                                        } else {
                                            $data = array(
                                                'status' => 'error',
                                                'msg' => 'La plaza a eliminar no existe !!',
                                            );
                                        }

                                    } else {
                                        $data = array(
                                            'status' => 'error',
                                            'msg' => 'No se puede eliminar la plaza, pertenece a un tipo de recaudo !!',
                                        );
                                    }

                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'No se puede eliminar la plaza, pertenece a una zona !!',
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
                'modulo' => "Plazas",
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
