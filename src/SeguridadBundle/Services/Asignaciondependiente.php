<?php
namespace SeguridadBundle\Services;

use ModeloBundle\Entity\Tasignaciondependiente;
use ModeloBundle\Entity\Ttercero;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class Asignaciondependiente
{

    public $manager;

    public function __construct(\SeguridadBundle\Services\Auditoria $auditoria, \SeguridadBundle\Services\JwtAuth $jwt_auth, \SeguridadBundle\Services\Helpers $helpers, $manager)
    {
        $this->manager = $manager;

        $this->auditoria = $auditoria;

        $this->jwt_auth = $jwt_auth;

        $this->helpers = $helpers;

    }

    /*
    Esta funcion realiza una consulta del dependiente con su identificacion para verificar si existe en la base de datos
    se debe enviar un parametro con el nombre de identificaciontercero, y cmo valor la identificacion o cedula del tercero,
    como tambien se debe enviar como parametro el token del usuario logueado con el nombre de authorization
     */
    /*
    Si desea obtener solo el listado de dependientes asignados puesto se debe enviar como parametro el fkidasignacionpuesto,
     y como valor el id de la asignacion de puesto a buscar
    */

    /**
     * @Route("/query")
     */
    public function queryAction(Request $request)
    {

        try {
            if (!empty($request->get('authorization'))) {

                $token = $request->get('authorization', null);
                $authCheck = $this->jwt_auth->checkToken($token);

                if ($authCheck) {

                    $identity = $this->jwt_auth->checkToken($token, true);

                    $permisosSerializados = $identity->permisos;
                    $permisosDeserializados = unserialize($permisosSerializados);

                    if (in_array("PERM_USUARIOS", $permisosDeserializados)) {
                        $em = $this->manager;
                        $db = $em->getConnection();

                        $fkidasignacionpuesto = $request->get('fkidasignacionpuesto', null);

                        $tercero = $request->get('tercero', null);
                        if($tercero == true){
                            $query = "SELECT * FROM ttercero order by nombretercero ASC;
                        ";
                            $stmt = $db->prepare($query);
                            $params = array();
                            $stmt->execute($params);
                            $tercero = $stmt->fetchAll();

                            if (!empty($tercero)) {
                                $data = array(
                                    'status' => 'Exito',
                                    'tercero' => $tercero,
                                );
                            } else {
                                $data = array(
                                    'status' => 'error',
                                    'msg' => 'No se encontraron terceros  !!',
                                );
                            }
                            return $this->helpers->json($data);
                        }


                        //listado dependientes asignados al puesto
                        if ($fkidasignacionpuesto != null) {

                            $query = "SELECT pkidasignaciondependiente,nombretercero, identificaciontercero, 
                            telefonotercero,tipotercero   FROM tasignaciondependiente join tasignacionpuesto on tasignaciondependiente.fkidasignacionpuesto=tasignacionpuesto.pkidasignacionpuesto join ttercero on tasignaciondependiente.fkidtercero=ttercero.pkidtercero  where pkidasignacionpuesto=$fkidasignacionpuesto  order by nombretercero ASC;
                        ";
                            $stmt = $db->prepare($query);
                            $params = array();
                            $stmt->execute($params);
                            $beneficiario = $stmt->fetchAll();

                            if (!empty($beneficiario)) {
                                $data = array(
                                    'status' => 'Exito',
                                    'beneficiario' => $beneficiario,
                                );
                            } else {
                                $data = array(
                                    'status' => 'error',
                                    'msg' => 'No se encontraron dependientes asignados al puesto  !!',
                                );
                            }
                            return $this->helpers->json($data);
                        }

                        $identificaciontercero = $request->get('identificaciontercero', null);

                        if ($identificaciontercero != null) {

                            $query = "SELECT nombretercero, identificaciontercero, telefonotercero, creaciontercero,
                        modificaciontercero, pkidtercero, tipotercero FROM ttercero where identificaciontercero='$identificaciontercero'  order by nombretercero ASC;
                        ";
                            $stmt = $db->prepare($query);
                            $params = array();
                            $stmt->execute($params);
                            $tercero = $stmt->fetchAll();

                            if (!empty($tercero)) {
                                $data = array(
                                    'status' => 'Exito',
                                    'tercero' => $tercero,
                                );
                            } else {
                                $data = array(
                                    'status' => 'error',
                                    'msg' => 'No existe el dependiente !!',
                                );
                            }
                        } else {
                            $data = array(
                                'status' => 'error',
                                'msg' => 'Envie la identificacion del dependiente !!',
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

            return $this->helpers->json($data);

        } catch (\Exception $e) {

            $trace = $e->getTrace();

            $exepcion = array(
                'idusuario' => $identity->sub,
                'nombreusuario' => $identity->name,
                'modulo' => "Tipo Sector",
                'metodo' => $trace[0]['function'],
                'mensaje' => $e->getMessage(),
                'tipoExepcion' => $trace[0]['class'],
                'pila' => $e->getTraceAsString(),
                'origen' => "Web",
            );

            try {
                $this->auditoria->exepcion(json_encode($exepcion));
            } catch (\Exception $a) {

            }

            throw $e;

        }

    }

    /*Este funcion realiza la inserccion o actualizacion de un dependiente,
    como parametros se deben enviar los siguientes:
    un parametro con el nombre datosdependiente, y como datos del json los siguientes:
    {
    "identificaciontercero":1234,
    "nombretercero":"hola",
    "tipotercero":"123"
    }
    un parametro con el nombre datosasignaciondependiente, y como datos del json los siguientes:
    {
    "numeroresolucionasignaciondependiente":1234,
    "fkidasignacionpuesto":1
    }
    un parametro con el nombre de tabla, y como valor el nombre de la tabla en el que va a insertar o actualizar el dependiente,
    un parametro con el nombre authorization, y como valor del parametro el token correspondiente
    al login del usuario, y opcionalmente se debe enviar un documento de la resolucion de la asignacion del dependiente , con el nombre de
    fichero_usuario, y cargar debidamente el documento.
     */

    /**
     * @Route("/dependiente")
     */
    public function dependienteAction(Request $request)
    {

        try {
            if (!empty($request->get('authorization'))) {

                $token = $request->get('authorization', null);
                $authCheck = $this->jwt_auth->checkToken($token);

                if ($authCheck) {

                    $identity = $this->jwt_auth->checkToken($token, true);

                    $permisosSerializados = $identity->permisos;
                    $permisosDeserializados = unserialize($permisosSerializados);

                    if (in_array("PERM_USUARIOS", $permisosDeserializados)) {
                        $em = $this->manager;

                        $datostercero = $request->get("datosdependiente", null);
                        $datosasignaciondependiente = $request->get("datosasignaciondependiente", null);
                        $tabla = $request->get("tabla", null);
                        $paramstercero = json_decode($datostercero);
                        $documentoasignaciondependiente = isset($_FILES['fichero_usuario']) ? $_FILES['fichero_usuario'] : null;

                        if ($datostercero != null) {

                            $identificaciontercero = (isset($paramstercero->identificaciontercero)) ? $paramstercero->identificaciontercero : null;

                            if ($identificaciontercero != null) {

                                $tercero = $em->getRepository("ModeloBundle:Ttercero")->findOneBy(array(
                                    "identificaciontercero" => $identificaciontercero,
                                ));

                                $check = true;
                                if (!is_object($tercero)) {

                                    $tercero = new Ttercero();
                                    $check = false;
                                }
                                if ($check == false) {
                                    $creaciontercero = new \Datetime("now");
                                }
                                $modificaciontercero = new \Datetime("now");
                                $nombretercero = (isset($paramstercero->nombretercero)) ? $paramstercero->nombretercero : null;
                                $telefonotercero = (isset($paramstercero->telefonotercero)) ? $paramstercero->telefonotercero : null;
                                $tipotercero = (isset($paramstercero->tipotercero)) ? $paramstercero->tipotercero : null;

                                if ($nombretercero != null && $tipotercero != null) {

                                    if ($check == false) {
                                        $tercero->SetIdentificaciontercero($identificaciontercero);
                                    }

                                    $tercero->SetNombretercero($nombretercero);
                                    if ($telefonotercero != null) {
                                        $tercero->SetTelefonotercero($telefonotercero);
                                    }

                                    $tercero->SetTipotercero($tipotercero);
                                    if ($check == false) {
                                        $tercero->SetCreaciontercero($creaciontercero);
                                    }

                                    $tercero->SetModificaciontercero($modificaciontercero);

                                    if ($tabla == "tasignaciondependiente") {

                                        $em->persist($tercero);
                                        $em->flush();

                                        $datos = array(
                                            "idusuario" => $identity->sub,
                                            "nombreusuario" => $identity->name,
                                            "identificacionusuario" => $identity->identificacion,
                                            "accion" => "insertar",
                                            "tabla" => "tercero",
                                            "valoresrelevantes" => "idtercero" . ":" . $tercero->getPkidtercero(),
                                            "idelemento" => $tercero->getPkidtercero(),
                                            "origen" => "web",
                                        );

                                        $this->auditoria->auditoria(json_encode($datos));

                                        return Asignaciondependiente::asignardependiente($datosasignaciondependiente, $documentoasignaciondependiente, $tercero->getPkidtercero(), $token,$check);
                                        

                                    }elseif ($tabla == "trecibopesaje") {

                                        $em->persist($tercero);
                                        $em->flush();

                                        $datos = array(
                                            "idusuario" => $identity->sub,
                                            "nombreusuario" => $identity->name,
                                            "identificacionusuario" => $identity->identificacion,
                                            "accion" => "insertar",
                                            "tabla" => "tercero",
                                            "valoresrelevantes" => "idtercero" . ":" . $tercero->getPkidtercero(),
                                            "idelemento" => $tercero->getPkidtercero(),
                                            "origen" => "web",
                                        );

                                        $this->auditoria->auditoria(json_encode($datos));

                                        if($check){
                                        $data = array(
                                            'status' => 'Exito',
                                            'msg' => 'Se actualizo correctamente el tercero',

                                        );
                                    }else{
                                        $data = array(
                                            'status' => 'Exito',
                                            'msg' => 'Se inserto correctamente el tercero',

                                        );
                                    }
                                        

                                    } else {
                                        $data = array(
                                            'status' => 'error',
                                            'msg' => 'Envie el nombre de la tabla a asignar, por favor !!',

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
                                    'msg' => 'Envie la identificacion del dependiente, por favor !!',

                                );
                            }

                        } else {
                            $data = array(
                                'status' => 'error',
                                'msg' => 'Envie los parametros, por favor !!',
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

            return $this->helpers->json($data);

        } catch (\Exception $e) {

            $trace = $e->getTrace();

            $exepcion = array(
                'idusuario' => $identity->sub,
                'nombreusuario' => $identity->name,
                'modulo' => "Tipo Sector",
                'metodo' => $trace[0]['function'],
                'mensaje' => $e->getMessage(),
                'tipoExepcion' => $trace[0]['class'],
                'pila' => $e->getTraceAsString(),
                'origen' => "Web",
            );

            try {
                $this->auditoria->exepcion(json_encode($exepcion));
            } catch (\Exception $a) {

            }

            throw $e;

        }

    }

    /*
    Esta funcion realiza la asignacion del dependiente,
    se debe enviar como parametros,
    un parametro con el nombre datosasignaciondependiente, y como datos del json los siguientes:
    {
    "numeroresolucionasignaciondependiente":1234,
    "fkidasignacionpuesto":1
    }
    un parametro con el nombre fkidtercero, y como valor el id del tercero que se quiere asignar,
    un parametro con el nombre check, el cual contienen al valor true si el tercero existe en la bd, o false si no existe
    como tambien se debe enviar como parametro el token del usuario logueado con el nombre de authorization,
    y opcionalmente se debe enviar un documento de la resolucion de la asignacion del dependiente , con el nombre de
    fichero_usuario, y cargar debidamente el documento.
     */

    public function asignardependiente($datosasignaciondependiente, $documentoasignaciondependiente, $fkidtercero, $token,$check)
    {

        //aui quede, asignar dependiente, validar fkidasignacionpuesto,
        try {
            if ($datosasignaciondependiente != null && $fkidtercero != null
                && $token != null) {

                $token = $token;
                $authCheck = $this->jwt_auth->checkToken($token);

                if ($authCheck) {

                    $identity = $this->jwt_auth->checkToken($token, true);

                    $permisosSerializados = $identity->permisos;
                    $permisosDeserializados = unserialize($permisosSerializados);

                    if (in_array("PERM_USUARIOS", $permisosDeserializados)) {
                        $em = $this->manager;

                        $json = $datosasignaciondependiente;
                        $params = json_decode($json);

                        if ($json != null) {

                            $creacionasignaciondependiente = new \Datetime("now");
                            $modificacionasignaciondependiente = new \Datetime("now");

                            $numeroresolucionasignaciondependiente = (isset($params->numeroresolucionasignaciondependiente)) ? $params->numeroresolucionasignaciondependiente : null;
                            $fkidasignacionpuesto = (isset($params->fkidasignacionpuesto)) ? $params->fkidasignacionpuesto : null;
                            $fkidtercero = $fkidtercero;

                            if ($numeroresolucionasignaciondependiente != null && $fkidasignacionpuesto != null && $fkidtercero != null) {
                                //validacion si el dependiente ya esta asignado
                                $db = $em->getConnection();
                                $query = "SELECT * FROM tasignaciondependiente where fkidtercero = $fkidtercero;
                                    ";
                                $stmt = $db->prepare($query);
                                $params = array();
                                $stmt->execute($params);
                                $asignaciondependiente_count_tercero = $stmt->fetchAll();

                                if (count($asignaciondependiente_count_tercero) != 0) {
                                    
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'No se creo la asignacion, el dependiente ya esta asignado a un puesto !!',
                                    );
                                    return $this->helpers->json($data);
                                }
                                //


                                $asignaciondependiente = new Tasignaciondependiente();

                                $asignaciondependiente->setNumeroresolucionasignaciondependiente($numeroresolucionasignaciondependiente);
                                $asignaciondependiente->setResolucionasignaciondependiente("sin documento");
                                $isset_asignacionpuesto = $em->getRepository('ModeloBundle:Tasignacionpuesto')->findOneBy(array("pkidasignacionpuesto" => $fkidasignacionpuesto));
                                if (is_object($isset_asignacionpuesto)) {
                                    $asignaciondependiente->setFkidasignacionpuesto($isset_asignacionpuesto);
                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id de la asignacion puesto no existe !!',
                                    );
                                    return $this->helpers->json($data);
                                }
                                $isset_tercero = $em->getRepository('ModeloBundle:Ttercero')->findOneBy(array("pkidtercero" => $fkidtercero));
                                if (is_object($isset_tercero)) {
                                    $asignaciondependiente->setFkidtercero($isset_tercero);
                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id del tercero no existe !!',
                                    );
                                    return $this->helpers->json($data);
                                }
                                $asignaciondependiente->setCreacionasignaciondependiente($creacionasignaciondependiente);
                                $asignaciondependiente->setModficacionasignaciondependiente($modificacionasignaciondependiente);

                                //validacion 2 beneficiarios
                                $query = "SELECT * FROM tasignaciondependiente where fkidasignacionpuesto = $fkidasignacionpuesto;
                                            ";
                                $stmt = $db->prepare($query);
                                $params = array();
                                $stmt->execute($params);
                                $asignaciondependiente_count = $stmt->fetchAll();
                                if (count($asignaciondependiente_count) >= 2) {
                                    if ($check == false) {
                                        $tercero_remove = $em->getRepository('ModeloBundle:Ttercero')->findOneBy(array(
                                            "pkidtercero" => $fkidtercero,
                                        ));

                                        $em->remove($tercero_remove);
                                        $em->flush();
                                    }

                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'No se creo la asignacion, solo se pueden asignar maximo 2 dependientes por puesto !!',
                                    );
                                    return $this->helpers->json($data);
                                }
                                //

                                $isset_asignaciondependiente = $em->getRepository('ModeloBundle:Tasignaciondependiente')->findOneBy(array(
                                    "numeroresolucionasignaciondependiente" => $numeroresolucionasignaciondependiente,
                                ));

                                //editar desde aqui depronto
                                if (!is_object($isset_asignaciondependiente)) {

                                    //documento
                                    if (isset($documentoasignaciondependiente)) {

                                        if ($documentoasignaciondependiente['size'] <= 5242880) {

                                            if ($documentoasignaciondependiente['type'] == "application/pdf") {

                                                $em->persist($asignaciondependiente);
                                                $em->flush();

                                                $dir_subida = '../web/documentos/';
                                                $extension = explode("/", $documentoasignaciondependiente['type'])[1];
                                                $fichero_subido = $dir_subida . basename($asignaciondependiente->getPkidasignaciondependiente() . "_asignaciondependiente_" . $creacionasignaciondependiente->format('Y-m-d_H-i-s') . "." . $extension);

                                                if (move_uploaded_file($documentoasignaciondependiente['tmp_name'], $fichero_subido)) {
                                                    $asignaciondependiente_doc = $em->getRepository('ModeloBundle:Tasignaciondependiente')->findOneBy(array(
                                                        "pkidasignaciondependiente" => $asignaciondependiente->getPkidasignaciondependiente(),
                                                    ));

                                                    $asignaciondependiente_doc->setResolucionasignaciondependiente($fichero_subido);
                                                    $em->persist($asignaciondependiente_doc);
                                                    $em->flush();

                                                    if($check){
                                                        $data = array(
                                                        'status' => 'Exito',
                                                        'status' => 'Se actualizo y se asigno el dependiente !!',
                                                        'asignaciondependiente' => $asignaciondependiente_doc,
                                                        );
                                                        }else{
                                                        $data = array(
                                                        'status' => 'Exito',
                                                        'status' => 'Se ingreso y se asigno el dependiente',
                                                        'asignaciondependiente' => $asignaciondependiente_doc,
                                                        );
                                                    }

                                                    $datos = array(
                                                        "idusuario" => $identity->sub,
                                                        "nombreusuario" => $identity->name,
                                                        "identificacionusuario" => $identity->identificacion,
                                                        "accion" => "insertar",
                                                        "tabla" => "asignaciondependiente",
                                                        "valoresrelevantes" => "idasignaciondependiente" . ":" . $asignaciondependiente->getPkidasignaciondependiente(),
                                                        "idelemento" => $asignaciondependiente->getPkidasignaciondependiente(),
                                                        "origen" => "web",
                                                    );

                                                    $this->auditoria->auditoria(json_encode($datos));

                                                } else {
                                                    $em->remove($asignaciondependiente);
                                                    $em->flush();

                                                    $data = array(
                                                        'status' => 'Error',
                                                        'msg' => 'No se ha podido ingresar el documento pdf, intente nuevamente !!',
                                                    );
                                                }
                                            } else {
                                                $data = array(
                                                    'status' => 'Error',
                                                    'msg' => 'Solo se aceptan archivos en formato PDF !!',
                                                );
                                            }
                                        } else {
                                            $data = array(
                                                'status' => 'Error',
                                                'msg' => 'El tamaño del documento debe ser MAX 5MB !!',
                                            );
                                        }
                                    } else {
                                        $em->persist($asignaciondependiente);
                                        $em->flush();

                                        if($check){
                                            $data = array(
                                            'status' => 'Exito',
                                            'msg' => 'Se actualizo y se asigno el dependiente !!',
                                            'asignaciondependiente' => $asignaciondependiente,
                                            );
                                            }else{
                                            $data = array(
                                            'status' => 'Exito',
                                            'msg' => 'Se ingreso y se asigno el dependiente',
                                            'asignaciondependiente' => $asignaciondependiente,
                                            );
                                        }

                                        $datos = array(
                                            "idusuario" => $identity->sub,
                                            "nombreusuario" => $identity->name,
                                            "identificacionusuario" => $identity->identificacion,
                                            "accion" => "insertar",
                                            "tabla" => "asignaciondependiente",
                                            "valoresrelevantes" => "idasignaciondependiente" . ":" . $asignaciondependiente->getPkidasignaciondependiente(),
                                            "idelemento" => $asignaciondependiente->getPkidasignaciondependiente(),
                                            "origen" => "web",
                                        );

                                        $this->auditoria->auditoria(json_encode($datos));
                                    }

                                    //documento

                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'Asignacion dependiente no creado, numero resolucion Duplicado !!',
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
                                'msg' => 'Parametro datosasignaciondependiente es nulo!!',
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

            return $this->helpers->json($data);

        } catch (\Exception $e) {

            $trace = $e->getTrace();

            $exepcion = array(
                'idusuario' => $identity->sub,
                'nombreusuario' => $identity->name,
                'modulo' => "Tipo Sector",
                'metodo' => $trace[0]['function'],
                'mensaje' => $e->getMessage(),
                'tipoExepcion' => $trace[0]['class'],
                'pila' => $e->getTraceAsString(),
                'origen' => "Web",
            );

            try {
                $this->auditoria->exepcion(json_encode($exepcion));
            } catch (\Exception $a) {

            }

            throw $e;

        }

    }

    /*
    Esta funcion realiza la eliminacion de una asignacion de un dependiente
    se debe enviar un parametro con el nombre de pkidasignaciondependiente, y cmo valor el id de la asignacion a eliminar,
    como tambien se debe enviar como parametro el token del usuario logueado con el nombre de authorization
     */

    /**
     * @Route("/remove")
     */
    public function removeAction(Request $request)
    {

        try {
            if (!empty($request->get('authorization'))) {

                $token = $request->get('authorization', null);
                $authCheck = $this->jwt_auth->checkToken($token);

                if ($authCheck) {

                    $identity = $this->jwt_auth->checkToken($token, true);

                    $permisosSerializados = $identity->permisos;
                    $permisosDeserializados = unserialize($permisosSerializados);

                    if (in_array("PERM_USUARIOS", $permisosDeserializados)) {

                        $pkidasignaciondependiente = $request->get('pkidasignaciondependiente', null);

                        if ($pkidasignaciondependiente != null) {

                            $em = $this->manager;

                            $isset_asignaciondependiente_rem = $em->getRepository('ModeloBundle:Tasignaciondependiente')->findOneBy(array(
                                "pkidasignaciondependiente" => $pkidasignaciondependiente,
                            ));

                            if (!is_object($isset_asignaciondependiente_rem)) {
                                $data = array(
                                    'status' => 'error',
                                    'msg' => 'El id de la asignacion del dependiente no existe !!',
                                );
                                return $this->helpers->json($data);
                            } 

                            $data = array(
                                'status' => 'Exito',
                                'msg' => 'Se elimino la asignacion correctamente !!'
                                );
                            

                            $datos = array(
                                "idusuario" => $identity->sub,
                                "nombreusuario" => $identity->name,
                                "identificacionusuario" => $identity->identificacion,
                                "accion" => "eliminar",
                                "tabla" => "asignaciondependiente",
                                "valoresrelevantes" => "idasignaciondependiente" . ":" . $isset_asignaciondependiente_rem->getPkidasignaciondependiente(),
                                "idelemento" => $isset_asignaciondependiente_rem->getPkidasignaciondependiente(),
                                "origen" => "web",
                            );

                            $this->auditoria->auditoria(json_encode($datos));

                            $em->remove($isset_asignaciondependiente_rem);
                            $em->flush();
                            

                        } else {
                            $data = array(
                                'status' => 'error',
                                'msg' => 'Envie el id de la asignacion del dependiente a eliminar !!',
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

            return $this->helpers->json($data);

        } catch (\Exception $e) {

            $trace = $e->getTrace();

            $exepcion = array(
                'idusuario' => $identity->sub,
                'nombreusuario' => $identity->name,
                'modulo' => "Tipo Sector",
                'metodo' => $trace[0]['function'],
                'mensaje' => $e->getMessage(),
                'tipoExepcion' => $trace[0]['class'],
                'pila' => $e->getTraceAsString(),
                'origen' => "Web",
            );

            try {
                $this->auditoria->exepcion(json_encode($exepcion));
            } catch (\Exception $a) {

            }

            throw $e;

        }

    }

}
