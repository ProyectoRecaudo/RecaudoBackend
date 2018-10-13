<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Tcenso;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TcensoController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        //return $this->render('AdministracionBundle:Default:index.html.twig');
    }

    /*
    Esta funcion realiza una consulta de todos los tipos de sector a la base de datos
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


                    if (in_array("PERM_USUARIOS", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        $query = "SELECT pkidcenso, fkidplaza, fkidsector, nombres, apellidos, tipodocumento, 
                        genero, relacionpuesto, identificacion, fechanacimiento, nombrebeneficiario, 
                        fkidbeneficiario, numeropuesto, fkidpuesto, edad, niveleducativo, 
                        estadocivil, numeropersonasacargo, grupofamiliar, personascondiscapacidad, 
                        gruposocialperteneciente, procedencia, personasadictasasustancias, 
                        tiempopermanenciapuesto, ingresos, ocupacionlocal, infraestructura, 
                        tipovivienda, prestacionserviciomercado, direccion, telefono,nombreplaza,nombresector, 
                        email FROM tcenso  order by nombres ASC;
                ";
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $censos = $stmt->fetchAll();

                        $array_all = array();
                        foreach ($censos as $censo) {
                            $censos_arr = array("pkidcenso" => $censo['pkidcenso'],
                                "nombres" => $censo['nombres'],
                                "apellidos" => $censo['apellidos'],
                                "genero" => $censo['genero'],
                                "relacionpuesto" => $censo['relacionpuesto'],
                                "identificacion" => $censo['identificacion'],
                                "fechanacimiento" => $censo['fechanacimiento'],
                                "edad" => $censo['edad'],
                                "niveleducativo" => $censo['niveleducativo'],
                                "estadocivil" => $censo['estadocivil'],
                                "numeropersonasacargo" => $censo['numeropersonasacargo'],
                                "grupofamiliar" => $censo['grupofamiliar'],
                                "personascondiscapacidad" => $censo['personascondiscapacidad'],
                                "gruposocialperteneciente" => $censo['gruposocialperteneciente'],
                                "procedencia" => $censo['procedencia'],
                                "personasadictasasustancias" => $censo['personasadictasasustancias'],
                                "tiempopermanenciapuesto" => $censo['tiempopermanenciapuesto'],
                                "ingresos" => $censo['ingresos'],
                                "ocupacionlocal" => $censo['ocupacionlocal'],
                                "infraestructura" => $censo['infraestructura'],
                                "tipovivienda" => $censo['tipovivienda'],
                                "prestacionserviciomercado" => $censo['prestacionserviciomercado'],
                                "direccion" => $censo['direccion'],
                                "telefono" => $censo['telefono'],
                                "email" => $censo['email'],
                                "plaza" => array("pkidplaza" => $censo['fkidplaza'], "nombreplaza" => $censo['nombreplaza']),
                                "sector" => array("pkidsector" => $censo['fkidsector'], "nombresector" => $censo['nombresector']),
                                "beneficiario" => array("pkidbeneficiario" => $censo['fkidbeneficiario'], "nombrebeneficiario" => $censo['nombrebeneficiario']),
                                "puesto" => array("pkidpuesto" => $censo['fkidpuesto'], "nombrepuesto" => $censo['nombrepuesto']),
                            );
                            array_push($array_all, $censos_arr);
                        }

                        $data = array(
                            'status' => 'Exito',
                            'censo' => $array_all,
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
                'modulo' => "Censo",
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

    /*Este funcion realiza la inserccion de un Censo nuevo
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"codigocenso":"valor",
    "nombrecenso":"valor",
    "descripcioncenso":"valor",
    "censoactivo":"valor"}
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

                        if ($json != null) {

                            $creacioncenso = new \Datetime("now");
                            $modificacioncenso = new \Datetime("now");


                            $fkidplaza = (isset($params->fkidplaza)) ? $params->fkidplaza : null;
                            $fkidsector = (isset($params->fkidsector)) ? $params->fkidsector : null;
                            $nombres = (isset($params->nombres)) ? $params->nombres : true;
                            $apellidos = (isset($params->apellidos)) ? $params->apellidos : null;
                            $tipodocumento = (isset($params->tipodocumento)) ? $params->tipodocumento : null;
                            $genero = (isset($params->genero)) ? $params->genero : null;
                            $relacionpuesto = (isset($params->relacionpuesto)) ? $params->relacionpuesto : null;
                            $identificacion = (isset($params->identificacion)) ? $params->identificacion : null;
                            $fechanacimiento = (isset($params->fechanacimiento)) ? $params->fechanacimiento : null;
                            $nombrebeneficiario = (isset($params->nombrebeneficiario)) ? $params->nombrebeneficiario : null;
                            $fkidbeneficiario = (isset($params->fkidbeneficiario)) ? $params->fkidbeneficiario : null;
                            $numeropuesto = (isset($params->numeropuesto)) ? $params->numeropuesto : null;
                            $fkidpuesto = (isset($params->fkidpuesto)) ? $params->fkidpuesto : null;
                            $edad = (isset($params->edad)) ? $params->edad : null;
                            $niveleducativo = (isset($params->niveleducativo)) ? $params->niveleducativo : null;
                            $estadocivil = (isset($params->estadocivil)) ? $params->estadocivil : null;
                            $numeropersonasacargo = (isset($params->numeropersonasacargo)) ? $params->numeropersonasacargo : null;
                            $grupofamiliar = (isset($params->grupofamiliar)) ? $params->grupofamiliar : null;
                            $personascondiscapacidad = (isset($params->personascondiscapacidad)) ? $params->personascondiscapacidad : null;
                            $gruposocialperteneciente = (isset($params->gruposocialperteneciente)) ? $params->gruposocialperteneciente : null;
                            $procedencia = (isset($params->procedencia)) ? $params->procedencia : null;
                            $personasadictasasustancias = (isset($params->personasadictasasustancias)) ? $params->personasadictasasustancias : null;
                            $tiempopermanenciapuesto = (isset($params->tiempopermanenciapuesto)) ? $params->tiempopermanenciapuesto : null;
                            $ingresos = (isset($params->ingresos)) ? $params->ingresos : null;
                            $ocupacionlocal = (isset($params->ocupacionlocal)) ? $params->ocupacionlocal : null;
                            $infraestructura = (isset($params->infraestructura)) ? $params->infraestructura : null;
                            $tipovivienda = (isset($params->tipovivienda)) ? $params->tipovivienda : null;
                            $prestacionserviciomercado = (isset($params->prestacionserviciomercado)) ? $params->prestacionserviciomercado : null;
                            $direccion = (isset($params->direccion)) ? $params->direccion : null;
                            $telefono = (isset($params->telefono)) ? $params->telefono : null;
                            $nombreplaza = (isset($params->nombreplaza)) ? $params->nombreplaza : null;
                            $nombresector = (isset($params->nombresector)) ? $params->nombresector : null;
                            $email = (isset($params->email)) ? $params->email : null;

                            if ($fkidplaza != null && $fkidsector != null && $nombres != null && $apellidos != null && $tipodocumento != null && $genero != null && $relacionpuesto != null &&
                            $identificacion != null && $fechanacimiento != null && $nombrebeneficiario != null && $fkidbeneficiario != null && $numeropuesto != null && $fkidpuesto != null && $edad != null && $niveleducativo != null &&
                            $estadocivil != null && $numeropersonasacargo != null && $grupofamiliar != null && $personascondiscapacidad != null && $gruposocialperteneciente != null && $procedencia != null && $personasadictasasustancias != null && $tiempopermanenciapuesto != null &&
                            $ingresos != null && $ocupacionlocal != null && $infraestructura != null && $tipovivienda != null && $prestacionserviciomercado != null && $direccion != null && $telefono != null && $nombreplaza != null && $nombresector != null) {

                                $isset_puesto = $em->getRepository('ModeloBundle:Tpuesto')->findOneBy(array(
                                    "pkidpuesto" => $fkidpuesto,
                                ));

                                if (!is_object($isset_puesto)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id del puesto no existe !!',
                                    );
                                     return $helpers->json($data);
                                 }

                                 $isset_beneficiario = $em->getRepository('ModeloBundle:Tbeneficiario')->findOneBy(array(
                                    "pkidbeneficiario" => $fkidbeneficiario,
                                ));

                                if (!is_object($isset_beneficiario)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id del beneficiario no existe !!',
                                    );
                                     return $helpers->json($data);
                                 }

                                 $isset_plaza = $em->getRepository('ModeloBundle:Tplaza')->findOneBy(array(
                                    "pkidplaza" => $fkidplaza,
                                ));

                                if (!is_object($isset_plaza)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id del plaza no existe !!',
                                    );
                                     return $helpers->json($data);
                                 }

                                 $isset_sector = $em->getRepository('ModeloBundle:Tsector')->findOneBy(array(
                                    "pkidsector" => $fkidsector,
                                ));

                                if (!is_object($isset_sector)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id del sector no existe !!',
                                    );
                                     return $helpers->json($data);
                                 }
                                
                                
                                $censo = new Tcenso();

                                $censo->setnombres($nombres);
                                $censo->setapellidos($apellidos);
                                $censo->settipodocumento($tipodocumento);
                                $censo->setgenero($genero);
                                $censo->setrelacionpuesto($relacionpuesto);
                                $censo->setidentificacion($identificacion);
                                $censo->setfechanacimiento(new \Datetime($fechanacimiento));
                                $censo->setnombrebeneficiario($nombrebeneficiario);
                                $censo->setnumeropuesto($numeropuesto);
                                $censo->setedad($edad);
                                $censo->setniveleducativo($niveleducativo);
                                $censo->setestadocivil($estadocivil);
                                $censo->setnumeropersonasacargo($numeropersonasacargo);
                                $censo->setgrupofamiliar($grupofamiliar);
                                $censo->setpersonascondiscapacidad($personascondiscapacidad);
                                $censo->setgruposocialperteneciente($gruposocialperteneciente);
                                $censo->setprocedencia($procedencia);
                                $censo->setpersonasadictasasustancias($personasadictasasustancias);
                                $censo->settiempopermanenciapuesto($tiempopermanenciapuesto);
                                $censo->setingresos($ingresos);
                                $censo->setocupacionlocal($ocupacionlocal);
                                $censo->setinfraestructura($infraestructura);
                                $censo->settipovivienda($tipovivienda);
                                $censo->setprestacionserviciomercado($prestacionserviciomercado);
                                $censo->setdireccion($direccion);
                                $censo->settelefono($telefono);
                                $censo->setnombreplaza($nombreplaza);
                                $censo->setnombresector($nombresector);
                                $censo->setemail($email);
                                $censo->setcreacioncenso(new \Datetime("now"));
                                $censo->setmodificacioncenso(new \Datetime("now"));

                                $isset_puesto = $em->getRepository('ModeloBundle:Tpuesto')->findOneBy(array(
                                    "pkidpuesto" => $fkidpuesto,
                                ));

                                 $isset_beneficiario = $em->getRepository('ModeloBundle:Tbeneficiario')->findOneBy(array(
                                    "pkidbeneficiario" => $fkidbeneficiario,
                                ));

                                 $isset_plaza = $em->getRepository('ModeloBundle:Tplaza')->findOneBy(array(
                                    "pkidplaza" => $fkidplaza,
                                ));

                                 $isset_sector = $em->getRepository('ModeloBundle:Tsector')->findOneBy(array(
                                    "pkidsector" => $fkidsector,
                                ));
                                

                                $censo->setfkidpuesto($isset_puesto);
                                $censo->setfkidbeneficiario($isset_beneficiario);
                                $censo->setfkidplaza($isset_plaza);
                                $censo->setfkidsector($isset_sector);
                                


                                    $em->persist($censo);
                                    $em->flush();

                                    $data = array(
                                        'status' => 'Exito',
                                        'msg' => 'Censo creado !!',
                                        'censo' => $censo,
                                    );

                                    $datos = array(
                                        "idusuario" => $identity->sub,
                                        "nombreusuario" => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        "accion" => "insertar",
                                        "tabla" => "censo",
                                        "valoresrelevantes" => "idcenso" . ":" . $censo->getPkidcenso(),
                                        "idelemento" => $censo->getPkidcenso(),
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
                'modulo' => "Censo",
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

    /*Esta funcion realiza la actualizacion de un tipo de sector,
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"pkidcenso":"valor",
    "codigocenso":"valor",
    "nombrecenso":"valor",
    "descripcioncenso":"valor",
    "censoactivo":"valor"}
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

                    if (in_array("PERM_USUARIOS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $creacioncenso = new \Datetime("now");
                            $modificacioncenso = new \Datetime("now");

                            $pkidcenso = (isset($params->pkidcenso)) ? $params->pkidcenso : null;
                            $fkidplaza = (isset($params->fkidplaza)) ? $params->fkidplaza : null;
                            $fkidsector = (isset($params->fkidsector)) ? $params->fkidsector : null;
                            $nombres = (isset($params->nombres)) ? $params->nombres : true;
                            $apellidos = (isset($params->apellidos)) ? $params->apellidos : null;
                            $tipodocumento = (isset($params->tipodocumento)) ? $params->tipodocumento : null;
                            $genero = (isset($params->genero)) ? $params->genero : null;
                            $relacionpuesto = (isset($params->relacionpuesto)) ? $params->relacionpuesto : null;
                            $identificacion = (isset($params->identificacion)) ? $params->identificacion : null;
                            $fechanacimiento = (isset($params->fechanacimiento)) ? $params->fechanacimiento : null;
                            $nombrebeneficiario = (isset($params->nombrebeneficiario)) ? $params->nombrebeneficiario : null;
                            $fkidbeneficiario = (isset($params->fkidbeneficiario)) ? $params->fkidbeneficiario : null;
                            $numeropuesto = (isset($params->numeropuesto)) ? $params->numeropuesto : null;
                            $fkidpuesto = (isset($params->fkidpuesto)) ? $params->fkidpuesto : null;
                            $edad = (isset($params->edad)) ? $params->edad : null;
                            $niveleducativo = (isset($params->niveleducativo)) ? $params->niveleducativo : null;
                            $estadocivil = (isset($params->estadocivil)) ? $params->estadocivil : null;
                            $numeropersonasacargo = (isset($params->numeropersonasacargo)) ? $params->numeropersonasacargo : null;
                            $grupofamiliar = (isset($params->grupofamiliar)) ? $params->grupofamiliar : null;
                            $personascondiscapacidad = (isset($params->personascondiscapacidad)) ? $params->personascondiscapacidad : null;
                            $gruposocialperteneciente = (isset($params->gruposocialperteneciente)) ? $params->gruposocialperteneciente : null;
                            $procedencia = (isset($params->procedencia)) ? $params->procedencia : null;
                            $personasadictasasustancias = (isset($params->personasadictasasustancias)) ? $params->personasadictasasustancias : null;
                            $tiempopermanenciapuesto = (isset($params->tiempopermanenciapuesto)) ? $params->tiempopermanenciapuesto : null;
                            $ingresos = (isset($params->ingresos)) ? $params->ingresos : null;
                            $ocupacionlocal = (isset($params->ocupacionlocal)) ? $params->ocupacionlocal : null;
                            $infraestructura = (isset($params->infraestructura)) ? $params->infraestructura : null;
                            $tipovivienda = (isset($params->tipovivienda)) ? $params->tipovivienda : null;
                            $prestacionserviciomercado = (isset($params->prestacionserviciomercado)) ? $params->prestacionserviciomercado : null;
                            $direccion = (isset($params->direccion)) ? $params->direccion : null;
                            $telefono = (isset($params->telefono)) ? $params->telefono : null;
                            $nombreplaza = (isset($params->nombreplaza)) ? $params->nombreplaza : null;
                            $nombresector = (isset($params->nombresector)) ? $params->nombresector : null;
                            $email = (isset($params->email)) ? $params->email : null;

                            if ($fkidplaza != null && $fkidsector != null && $nombres != null && $apellidos != null && $tipodocumento != null && $genero != null && $relacionpuesto != null &&
                            $identificacion != null && $fechanacimiento != null && $nombrebeneficiario != null && $fkidbeneficiario != null && $numeropuesto != null && $fkidpuesto != null && $edad != null && $niveleducativo != null &&
                            $estadocivil != null && $numeropersonasacargo != null && $grupofamiliar != null && $personascondiscapacidad != null && $gruposocialperteneciente != null && $procedencia != null && $personasadictasasustancias != null && $tiempopermanenciapuesto != null &&
                            $ingresos != null && $ocupacionlocal != null && $infraestructura != null && $tipovivienda != null && $prestacionserviciomercado != null && $direccion != null && $telefono != null && $nombreplaza != null && $nombresector != null) {

                                $isset_censo = $em->getRepository('ModeloBundle:Tcenso')->findOneBy(array(
                                    "pkidcenso" => $pkidcenso,
                                ));

                                if (!is_object($isset_censo)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'Los id del censo a actualizar no existe !!',
                                    );
                                     return $helpers->json($data);
                                 }
                                
                                $isset_puesto = $em->getRepository('ModeloBundle:Tpuesto')->findOneBy(array(
                                    "pkidpuesto" => $fkidpuesto,
                                ));

                                if (!is_object($isset_puesto)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'Los id del puesto no existe !!',
                                    );
                                     return $helpers->json($data);
                                 }

                                 $isset_beneficiario = $em->getRepository('ModeloBundle:Tbeneficiario')->findOneBy(array(
                                    "pkidbeneficiario" => $fkidbeneficiario,
                                ));

                                if (!is_object($isset_beneficiario)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'Los id del beneficiario no existe !!',
                                    );
                                     return $helpers->json($data);
                                 }

                                 $isset_plaza = $em->getRepository('ModeloBundle:Tplaza')->findOneBy(array(
                                    "pkidplaza" => $fkidplaza,
                                ));

                                if (!is_object($isset_plaza)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'Los id del plaza no existe !!',
                                    );
                                     return $helpers->json($data);
                                 }

                                 $isset_sector = $em->getRepository('ModeloBundle:Tsector')->findOneBy(array(
                                    "pkidsector" => $fkidsector,
                                ));

                                if (!is_object($isset_sector)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'Los id del sector no existe !!',
                                    );
                                     return $helpers->json($data);
                                 }
                                
                                
                                 $censo = $em->getRepository('ModeloBundle:Tcenso')->findOneBy(array(
                                    "pkidcenso" => $pkidcenso,
                                ));

                                $censo->setnombres($nombres);
                                $censo->setapellidos($apellidos);
                                $censo->settipodocumento($tipodocumento);
                                $censo->setgenero($genero);
                                $censo->setrelacionpuesto($relacionpuesto);
                                $censo->setidentificacion($identificacion);
                                $censo->setfechanacimiento(new \Datetime($fechanacimiento));
                                $censo->setnombrebeneficiario($nombrebeneficiario);
                                $censo->setnumeropuesto($numeropuesto);
                                $censo->setedad($edad);
                                $censo->setniveleducativo($niveleducativo);
                                $censo->setestadocivil($estadocivil);
                                $censo->setnumeropersonasacargo($numeropersonasacargo);
                                $censo->setgrupofamiliar($grupofamiliar);
                                $censo->setpersonascondiscapacidad($personascondiscapacidad);
                                $censo->setgruposocialperteneciente($gruposocialperteneciente);
                                $censo->setprocedencia($procedencia);
                                $censo->setpersonasadictasasustancias($personasadictasasustancias);
                                $censo->settiempopermanenciapuesto($tiempopermanenciapuesto);
                                $censo->setingresos($ingresos);
                                $censo->setocupacionlocal($ocupacionlocal);
                                $censo->setinfraestructura($infraestructura);
                                $censo->settipovivienda($tipovivienda);
                                $censo->setprestacionserviciomercado($prestacionserviciomercado);
                                $censo->setdireccion($direccion);
                                $censo->settelefono($telefono);
                                $censo->setnombreplaza($nombreplaza);
                                $censo->setnombresector($nombresector);
                                $censo->setemail($email);
                                //$censo->setcreacioncenso(new \Datetime("now"));
                                $censo->setmodificacioncenso(new \Datetime("now"));

                                $isset_puesto = $em->getRepository('ModeloBundle:Tpuesto')->findOneBy(array(
                                    "pkidpuesto" => $fkidpuesto,
                                ));

                                 $isset_beneficiario = $em->getRepository('ModeloBundle:Tbeneficiario')->findOneBy(array(
                                    "pkidbeneficiario" => $fkidbeneficiario,
                                ));

                                 $isset_plaza = $em->getRepository('ModeloBundle:Tplaza')->findOneBy(array(
                                    "pkidplaza" => $fkidplaza,
                                ));

                                 $isset_sector = $em->getRepository('ModeloBundle:Tsector')->findOneBy(array(
                                    "pkidsector" => $fkidsector,
                                ));
                                

                                $censo->setfkidpuesto($isset_puesto);
                                $censo->setfkidbeneficiario($isset_beneficiario);
                                $censo->setfkidplaza($isset_plaza);
                                $censo->setfkidsector($isset_sector);
                                


                                    $em->persist($censo);
                                    $em->flush();

                                    $data = array(
                                        'status' => 'Exito',
                                        'msg' => 'Censo actualizado !!',
                                        'censo' => $censo,
                                    );

                                    $datos = array(
                                        "idusuario" => $identity->sub,
                                        "nombreusuario" => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        "accion" => "insertar",
                                        "tabla" => "censo",
                                        "valoresrelevantes" => "idcenso" . ":" . $censo->getPkidcenso(),
                                        "idelemento" => $censo->getPkidcenso(),
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
                'modulo' => "Censo",
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
    {"pkidcenso":"valor"}
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

                            $pkidcenso = (isset($params->pkidcenso)) ? $params->pkidcenso : null;

                            if ($pkidcenso != null) {

                                $censo = $this->getDoctrine()->getRepository("ModeloBundle:Tcenso")->findOneBy(array(
                                    "pkidcenso" => $pkidcenso,
                                ));

                                            if (is_object($censo)) {

                                                $datos = array(
                                                    "idusuario" => $identity->sub,
                                                    "nombreusuario" => $identity->name,
                                                    "identificacionusuario" => $identity->identificacion,
                                                    "accion" => "eliminar",
                                                    "tabla" => "Censo",
                                                    "valoresrelevantes" => "idcenso" . ":" . $censo->getPkidcenso(),
                                                    "idelemento" => $censo->getPkidcenso(),
                                                    "origen" => "web",
                                                );

                                                $auditoria = $this->get(Auditoria::class);
                                                $auditoria->auditoria(json_encode($datos));

                                                $em->remove($censo);
                                                $em->flush();

                                                $data = array(
                                                    'status' => 'Exito',
                                                    'msg' => 'El Censo se ha eliminado correctamente !!',
                                                );

                                            } else {
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg' => 'El Censo a eliminar no existe !!',
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
                'modulo' => "Censo",
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
