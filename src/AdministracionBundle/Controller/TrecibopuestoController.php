<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Trecibopuesto;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TrecibopuestoController extends Controller
{

    /*
    Esta funcion realiza una consulta de todos los tipos de sector a la base de datos
    se debe enviar como parametro el token del usuario logueado con el nombre de authorization
     */
    /**
     * @Route("/query")
     */
    public function queryAction(Request $request)
    {


        /*
            [{"numerofactura":"f001","nombrebeneficiario":"Juan Lopez",
                "identificacionbeneficiario":"108512345","saldo":0,
                "numeroacuerdo":"AC01","valorcuotaacuerdo":5000,"valormultas":0,
                "valorinteres":0,"mesfactura":"1","creacionrecibo":"9/10/2018 14:52:45",
                "modificacionrecibo":"9/10/2018 14:52:45","fkidfactura":1,"numerorecibo":"104",
                "nombreterceropuesto":"Elsa","identificacionterceropuesto":"123",
                "nombreplaza":"DOS PUENTES","recibopuestoactivo":1,
                "numeroresolucionasignacionpuesto":"RESOLUCION_01","numeropuesto":"Puesto 1",
                "nombresector":"SECTOR 1","fkidzona":17,"fkidsector":11,"fkidpuesto":6,
                "fkidasignacionpuesto":3,"fkidplaza":6,"fkidbeneficiario":1,"fkidacuerdo":1,
                "identificacionrecaudador":"1234","nombrerecaudador":"Alex"
                ,"apellidorecaudador":"Mera","fkidusuariorecaudador":100,
                "valorpagado":10000,"saldoporpagar":-10000,"nombrezona":"ZONA",
                "abonototalacuerdo":0,"abonocuotaacuerdo":0,"abonodeudaacuerdo":0,
                "abonodeuda":0,"abonomultas":0,"abonocuotames":0}]
        */

        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

            $em = $this->getDoctrine()->getManager();
            $db = $em->getConnection();

            $pkidasignacionpuesto = $request->get("pkidasignacionpuesto", null);
            $identificacionbeneficiario = $request->get("identificacionbeneficiario", null);
            $param = $request->get("param", null);

            if ($pkidasignacionpuesto != null) {
                if (!empty($request->get('authorization'))) {

                    $token = $request->get('authorization', null);
                    $authCheck = $jwt_auth->checkToken($token);

                    if ($authCheck) {

                        $identity = $jwt_auth->checkToken($token, true);

                        $permisosSerializados = $identity->permisos;
                        $permisosDeserializados = unserialize($permisosSerializados);

                        if (in_array("PERM_GENERICOS", $permisosDeserializados)) {
                            $asignacionpuesto = $this->getDoctrine()->getRepository("ModeloBundle:Tasignacionpuesto")->findOneBy(array(
                                "pkidasignacionpuesto" => $pkidasignacionpuesto,
                            ));

                            if (!is_object($asignacionpuesto)) {
                                $data = array(
                                    'status' => 'error',
                                    'msg' => 'El id de la asignacion de puesto no existe',
                                );
                                return $helpers->json($data);
                            }

                            $param = " fkidasignacionpuesto=" . $pkidasignacionpuesto;

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

            } elseif ($identificacionbeneficiario != null) {
                $beneficiario = $this->getDoctrine()->getRepository("ModeloBundle:Tbeneficiario")->findOneBy(array(
                    "identificacionbeneficiario" => $identificacionbeneficiario,
                ));

                if (!is_object($beneficiario)) {
                    $data = array(
                        'status' => 'error',
                        'msg' => 'La identificacion del beneficiario no existe',
                    );
                    return $helpers->json($data);
                }

                $param = " identificacionbeneficiario like '" . $identificacionbeneficiario . "'";

            }elseif ($param != null) {
                if($param =="false"){
                    $data = array(
                        'status' => 'Exito',
                        'recibopuesto' => array(),
                    );
                    
                    return $helpers->json($data); 
                }else{
                    $param = "true";
                }

            } else {
                $data = array(
                    'status' => 'error',
                    'msg' => 'Envie los parametros por favor !!',
                );
                return $helpers->json($data);
            }

            $query = "SELECT numerofactura, nombrebeneficiario, identificacionbeneficiario, 
            saldo, numeroacuerdo, valorcuotaacuerdo, valormultas, valorinteres, 
            mesfactura, creacionrecibo, modificacionrecibo, pkidrecibopuesto, 
            fkidfactura, numerorecibo, nombreterceropuesto, identificacionterceropuesto, 
            nombreplaza, recibopuestoactivo, numeroresolucionasignacionpuesto, 
            numeropuesto, nombresector, fkidzona, fkidsector, fkidpuesto, 
            fkidasignacionpuesto, fkidplaza, fkidbeneficiario, fkidacuerdo, 
            identificacionrecaudador, nombrerecaudador, apellidorecaudador, 
            fkidusuariorecaudador, valorpagado, saldoporpagar, nombrezona, 
            abonototalacuerdo, abonocuotaacuerdo, abonodeudaacuerdo, abonodeuda, 
            abonomultas, abonocuotames
                        FROM public.trecibopuesto WHERE $param
            ";


            $stmt = $db->prepare($query);
            $params = array();
            $stmt->execute($params);
            $recibopuestos = $stmt->fetchAll();

            $array_all = array();
            foreach ($recibopuestos as $recibopuesto) {
                $recibopuestos_arr = array("pkidrecibopuesto" => $recibopuesto['pkidrecibopuesto'],
                    "identificacionbeneficiario" => $recibopuesto['identificacionbeneficiario'],
                    "saldo" => $recibopuesto['saldo'],
                    "valorcuotaacuerdo" => $recibopuesto['valorcuotaacuerdo'],
                    "valormultas" => $recibopuesto['valormultas'],
                    "valorinteres" => $recibopuesto['valorinteres'],
                    "mesfactura" => $recibopuesto['mesfactura'],
                    "numerorecibo" => $recibopuesto['numerorecibo'],
                    "nombreterceropuesto" => $recibopuesto['nombreterceropuesto'],
                    "identificacionterceropuesto" => $recibopuesto['identificacionterceropuesto'],
                    "recibopuestoactivo" => $recibopuesto['recibopuestoactivo'],
                    "identificacionrecaudador" => $recibopuesto['identificacionrecaudador'],
                    "nombrerecaudador" => $recibopuesto['nombrerecaudador'],
                    "apellidorecaudador" => $recibopuesto['apellidorecaudador'],
                    "valorpagado" => $recibopuesto['valorpagado'],
                    "saldoporpagar" => $recibopuesto['saldoporpagar'],
                    "pkidplaza" => $recibopuesto['fkidplaza'],
                     "nombreplaza" => $recibopuesto['nombreplaza'],
                    "pkidsector" => $recibopuesto['fkidsector'], 
                    "nombresector" => $recibopuesto['nombresector'],
                    "pkidzona" => $recibopuesto['fkidzona'],
                     "nombrezona" => $recibopuesto['nombrezona'],
                    "pkidbeneficiario" => $recibopuesto['fkidbeneficiario'],
                     "nombrebeneficiario" => $recibopuesto['nombrebeneficiario'],
                    "pkidpuesto" => $recibopuesto['fkidpuesto'],
                     "numeropuesto" => $recibopuesto['numeropuesto'],
                    "pkidfactura" => $recibopuesto['fkidfactura'],
                     "numerofactura" => $recibopuesto['numerofactura'],
                    "pkidacuerdo" => $recibopuesto['fkidacuerdo'],
                     "numeroacuerdo" => $recibopuesto['numeroacuerdo'],
                    "pkidasignacionpuesto" => $recibopuesto['fkidasignacionpuesto'], 
                    "numeroresolucionasignacionpuesto" => $recibopuesto['numeroresolucionasignacionpuesto']
                );

                array_push($array_all, $recibopuestos_arr);
            }

            $data = array(
                'status' => 'Exito',
                'recibopuesto' => $array_all,
            );

            return $helpers->json($data);


    }

    /*Esta funcion realiza la inserccion de un recibopuesto,
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del array de json los siguientes:
    [
    {
    "numerofactura":"valor",
    "nombrebeneficiario":"valor",
    "identificacionbeneficiario":"valor",
    "saldo":"valor",
    "numeroacuerdo":"valor",
    "valorcuotaacuerdo":"valor",
    "valormultas":"valor",
    "valorinteres":"valor",
    "mesfactura":"valor",
    "fkidfactura":"valor",
    "numerorecibo":"valor",
    "nombreterceropuesto":"valor",
    "identificacionterceropuesto":"valor",
    "nombreplaza":"valor",
    "recibopuestoactivo":"valor",
    "numeroresolucionasignacionpuesto":"valor",
    "numeropuesto":"valor",
    "nombresector":"valor",
    "creacionrecibo":"valor",
    "modificacionrecibo":"valor",
    "abonototalacuerdo, ":"valor",
    "abonocuotaacuerdo, ":"valor",
    "abonodeudaacuerdo, ":"valor",
    "abonodeuda, ":"valor",
    "abonomultas, ":"valor",
    "abonocuotames ":"valor",
    "fkidzona":"valor",
    "fkidsector":"valor",
    "fkidpuesto":"valor",
    "fkidasignacionpuesto":"valor",
    "fkidplaza":"valor",
    "fkidbeneficiario":"valor",
    "fkidacuerdo":"valor",
    },
    {
    "numerofactura":"valor",
    "nombrebeneficiario":"valor",
    "identificacionbeneficiario":"valor",
    "saldo":"valor",
    "numeroacuerdo":"valor",
    "valorcuotaacuerdo":"valor",
    "valormultas":"valor",
    "valorinteres":"valor",
    "mesfactura":"valor",
    "fkidfactura":"valor",
    "numerorecibo":"valor",
    "nombreterceropuesto":"valor",
    "identificacionterceropuesto":"valor",
    "nombreplaza":"valor",
    "recibopuestoactivo":"valor",
    "numeroresolucionasignacionpuesto":"valor",
    "numeropuesto":"valor",
    "nombresector":"valor",
    "creacionrecibo":"valor",
    "modificacionrecibo":"valor",
    "abonototalacuerdo, ":"valor",
    "abonocuotaacuerdo, ":"valor",
    "abonodeudaacuerdo, ":"valor",
    "abonodeuda, ":"valor",
    "abonomultas, ":"valor",
    "abonocuotames ":"valor",
    "fkidzona":"valor",
    "fkidsector":"valor",
    "fkidpuesto":"valor",
    "fkidasignacionpuesto":"valor",
    "fkidplaza":"valor",
    "fkidbeneficiario":"valor",
    "fkidacuerdo":"valor",
    }
    ]
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

                    if (in_array("PERM_RECAUDOS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = (array) json_decode($json);

                        if ($json != null) {

                            foreach ($params as $valor) {
                                $convert = (array) $valor;

                                    if (
                                        !isset($convert['numerofactura']) ||
                                        !isset($convert['nombrebeneficiario']) ||
                                        !isset($convert['identificacionbeneficiario']) ||
                                        !isset($convert['saldo']) ||
                                        !isset($convert['numeroacuerdo']) ||
                                        !isset($convert['valorcuotaacuerdo']) ||
                                        !isset($convert['valormultas']) ||
                                        !isset($convert['valorinteres']) ||
                                        !isset($convert['mesfactura']) ||
                                        !isset($convert['fkidfactura']) ||
                                        !isset($convert['numerorecibo']) ||
                                        !isset($convert['nombreterceropuesto']) ||
                                        !isset($convert['identificacionterceropuesto']) ||
                                        !isset($convert['nombreplaza']) ||
                                        !isset($convert['valorpagado']) ||
                                        !isset($convert['recibopuestoactivo']) ||
                                        !isset($convert['numeroresolucionasignacionpuesto']) ||
                                        !isset($convert['numeropuesto']) ||
                                        !isset($convert['nombresector']) ||
                                        !isset($convert['creacionrecibo']) ||
                                        !isset($convert['modificacionrecibo']) ||
                                        !isset($convert['abonototalacuerdo']) ||
                                        !isset($convert['abonocuotaacuerdo']) ||
                                        !isset($convert['abonodeudaacuerdo']) ||
                                        !isset($convert['abonodeuda']) ||
                                        !isset($convert['abonomultas']) ||
                                        !isset($convert['abonocuotames']) ||
                                        !isset($convert['identificacionrecaudador']) ||
                                        !isset($convert['saldoporpagar']) ||
                                        !isset($convert['nombrezona']) ||
                                        !isset($convert['nombrerecaudador']) ||
                                        !isset($convert['apellidorecaudador']) ||
                                        !isset($convert['fkidusuariorecaudador']) ||
                                        !isset($convert['fkidzona']) ||
                                        !isset($convert['fkidsector']) ||
                                        !isset($convert['fkidpuesto']) ||
                                        !isset($convert['fkidasignacionpuesto']) ||
                                        !isset($convert['fkidplaza']) ||
                                        !isset($convert['fkidbeneficiario']) ||
                                        !isset($convert['fkidacuerdo'])
                                    ) {
                                        $data = array(
                                            'status' => 'error',
                                            'msg' => 'Algunos campos enviados en el json son nulos',
                                        );
                                        return $helpers->json($data);
                                    }
                            }

                            foreach ($params as $valor) {
                                $convert = (array) $valor;

                                $factura = $this->getDoctrine()->getRepository("ModeloBundle:Tfactura")->findOneBy(array(
                                    "pkidfactura" => $convert['fkidfactura'],
                                ));

                                if (!is_object($factura)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id de la factura no existe',
                                    );
                                    return $helpers->json($data);
                                }

                                $zona = $this->getDoctrine()->getRepository("ModeloBundle:Tzona")->findOneBy(array(
                                    "pkidzona" => $convert['fkidzona'],
                                ));

                                if (!is_object($zona)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id de la zona no existe',
                                    );
                                    return $helpers->json($data);
                                }

                                $sector = $this->getDoctrine()->getRepository("ModeloBundle:Tsector")->findOneBy(array(
                                    "pkidsector" => $convert['fkidsector'],
                                ));

                                if (!is_object($sector)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id del sector no existe',
                                    );
                                    return $helpers->json($data);
                                }

                                $puesto = $this->getDoctrine()->getRepository("ModeloBundle:Tpuesto")->findOneBy(array(
                                    "pkidpuesto" => $convert['fkidpuesto'],
                                ));

                                if (!is_object($puesto)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id del puesto no existe',
                                    );
                                    return $helpers->json($data);
                                }
                                $asignacionpuesto = $this->getDoctrine()->getRepository("ModeloBundle:Tasignacionpuesto")->findOneBy(array(
                                    "pkidasignacionpuesto" => $convert['fkidasignacionpuesto'],
                                ));

                                if (!is_object($asignacionpuesto)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id de la asignacion puesto no existe',
                                    );
                                    return $helpers->json($data);

                                }
                                $plaza = $this->getDoctrine()->getRepository("ModeloBundle:Tplaza")->findOneBy(array(
                                    "pkidplaza" => $convert['fkidplaza'],
                                ));

                                if (!is_object($plaza)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id de la plaza no existe',
                                    );
                                    return $helpers->json($data);
                                }
                                $beneficiario = $this->getDoctrine()->getRepository("ModeloBundle:Tbeneficiario")->findOneBy(array(
                                    "pkidbeneficiario" => $convert['fkidbeneficiario'],
                                ));

                                if (!is_object($beneficiario)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id del beneficiario no existe',
                                    );
                                    return $helpers->json($data);
                                }
                                if ($convert['fkidacuerdo'] != "") {
                                    $acuerdo = $this->getDoctrine()->getRepository("ModeloBundle:Tacuerdo")->findOneBy(array(
                                        "pkidacuerdo" => $convert['fkidacuerdo'],
                                    ));

                                    if (!is_object($acuerdo)) {
                                        $data = array(
                                            'status' => 'error',
                                            'msg' => 'El id del acuerdo no existe',
                                        );
                                        return $helpers->json($data);
                                    }
                                }

                                $usuario = $this->getDoctrine()->getRepository("ModeloBundle:Tusuario")->findOneBy(array(
                                    "pkidusuario" => $convert['fkidusuariorecaudador'],
                                ));

                                if (!is_object($usuario)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id del usuario no existe',
                                    );
                                    return $helpers->json($data);
                                }

                            }

                            foreach ($params as $valor) {
                                $convert = (array) $valor;

                                $recibopuesto = new Trecibopuesto();

                                $factura = $this->getDoctrine()->getRepository("ModeloBundle:Tfactura")->findOneBy(array(
                                    "pkidfactura" => $convert['fkidfactura'],
                                ));
                                $recibopuesto->setFkidfactura($factura);

                                $zona = $this->getDoctrine()->getRepository("ModeloBundle:Tzona")->findOneBy(array(
                                    "pkidzona" => $convert['fkidzona'],
                                ));
                                $recibopuesto->setFkidzona($zona);

                                $sector = $this->getDoctrine()->getRepository("ModeloBundle:Tsector")->findOneBy(array(
                                    "pkidsector" => $convert['fkidsector'],
                                ));
                                $recibopuesto->setFkidsector($sector);

                                $puesto = $this->getDoctrine()->getRepository("ModeloBundle:Tpuesto")->findOneBy(array(
                                    "pkidpuesto" => $convert['fkidpuesto'],
                                ));
                                $recibopuesto->setFkidpuesto($puesto);

                                $asignacionpuesto = $this->getDoctrine()->getRepository("ModeloBundle:Tasignacionpuesto")->findOneBy(array(
                                    "pkidasignacionpuesto" => $convert['fkidasignacionpuesto'],
                                ));
                                $recibopuesto->setFkidasignacionpuesto($asignacionpuesto);

                                $plaza = $this->getDoctrine()->getRepository("ModeloBundle:Tplaza")->findOneBy(array(
                                    "pkidplaza" => $convert['fkidplaza'],
                                ));
                                $recibopuesto->setFkidplaza($plaza);

                                $beneficiario = $this->getDoctrine()->getRepository("ModeloBundle:Tbeneficiario")->findOneBy(array(
                                    "pkidbeneficiario" => $convert['fkidbeneficiario'],
                                ));
                                $recibopuesto->setFkidbeneficiario($beneficiario);

                                $usuario = $this->getDoctrine()->getRepository("ModeloBundle:Tusuario")->findOneBy(array(
                                    "pkidusuario" => $convert['fkidusuariorecaudador'],
                                ));
                                $recibopuesto->setFkidusuariorecaudador($usuario);

                                if ($convert['fkidacuerdo'] != "") {
                                    $acuerdo = $this->getDoctrine()->getRepository("ModeloBundle:Tacuerdo")->findOneBy(array(
                                        "pkidacuerdo" => $convert['fkidacuerdo'],
                                    ));
                                    $recibopuesto->setFkidacuerdo($acuerdo);
                                }

                                $recibopuesto->setNumerofactura($convert['numerofactura']);
                                $recibopuesto->setNombrebeneficiario($convert['nombrebeneficiario']);
                                $recibopuesto->setIdentificacionbeneficiario($convert['identificacionbeneficiario']);
                                $recibopuesto->setSaldo($convert['saldo']);
                                $recibopuesto->setNumeroacuerdo($convert['numeroacuerdo']);
                                $recibopuesto->setValorcuotaacuerdo($convert['valorcuotaacuerdo']);
                                $recibopuesto->setValormultas($convert['valormultas']);
                                $recibopuesto->setValorinteres($convert['valorinteres']);
                                $recibopuesto->setMesfactura($convert['mesfactura']);
                                $recibopuesto->setNumerorecibo($convert['numerorecibo']);
                                $recibopuesto->setNombreterceropuesto($convert['nombreterceropuesto']);
                                $recibopuesto->setIdentificacionterceropuesto($convert['identificacionterceropuesto']);
                                $recibopuesto->setNombreplaza($convert['nombreplaza']);
                                $recibopuesto->setValorpagado($convert['valorpagado']);
                                $recibopuesto->setIdentificacionrecaudador($convert['identificacionrecaudador']);
                                $recibopuesto->setNombrerecaudador($convert['nombrerecaudador']);
                                $recibopuesto->setApellidorecaudador($convert['apellidorecaudador']);
                                $recibopuesto->setSaldoporpagar($convert['saldoporpagar']);
                                
                                $recibopuesto->setabonototalacuerdo($convert['abonototalacuerdo']);
                                $recibopuesto->setabonocuotaacuerdo($convert['abonocuotaacuerdo']);
                                $recibopuesto->setabonodeudaacuerdo($convert['abonodeudaacuerdo']);
                                $recibopuesto->setabonodeuda($convert['abonodeuda']);
                                $recibopuesto->setabonomultas($convert['abonomultas']);
                                $recibopuesto->setabonocuotames($convert['abonocuotames']);
                                if ($convert['recibopuestoactivo'] != false) {
                                    $factura = $this->getDoctrine()->getRepository("ModeloBundle:Tfactura")->findOneBy(array(
                                        "pkidfactura" => $convert['fkidfactura'],
                                    ));

                                    $factura->setTotalpagado($factura->getTotalpagado() + $convert['valorpagado']);

                                    $factura = $this->getDoctrine()->getRepository("ModeloBundle:Tfactura")->findOneBy(array(
                                        "pkidfactura" => $convert['fkidfactura'],
                                    ));

                                    $factura->setabonototalacuerdo($factura->getabonototalacuerdo() + $convert['abonototalacuerdo']);

                                    $factura = $this->getDoctrine()->getRepository("ModeloBundle:Tfactura")->findOneBy(array(
                                        "pkidfactura" => $convert['fkidfactura'],
                                    ));

                                    $factura->setabonocuotaacuerdo($factura->getabonocuotaacuerdo() + $convert['abonocuotaacuerdo']);

                                    $factura = $this->getDoctrine()->getRepository("ModeloBundle:Tfactura")->findOneBy(array(
                                        "pkidfactura" => $convert['fkidfactura'],
                                    ));

                                    $factura->setabonodeudaacuerdo($factura->getabonodeudaacuerdo() + $convert['abonodeudaacuerdo']);

                                    $factura = $this->getDoctrine()->getRepository("ModeloBundle:Tfactura")->findOneBy(array(
                                        "pkidfactura" => $convert['fkidfactura'],
                                    ));

                                    $factura->setabonodeuda($factura->getabonodeuda() + $convert['abonodeuda']);

                                    $factura = $this->getDoctrine()->getRepository("ModeloBundle:Tfactura")->findOneBy(array(
                                        "pkidfactura" => $convert['fkidfactura'],
                                    ));

                                    $factura->setabonomultas($factura->getabonomultas() + $convert['abonomultas']);

                                    $factura = $this->getDoctrine()->getRepository("ModeloBundle:Tfactura")->findOneBy(array(
                                        "pkidfactura" => $convert['fkidfactura'],
                                    ));

                                    $factura->setabonocuotames($factura->getabonocuotames() + $convert['abonocuotames']);
                                }

                                $recibopuesto->setRecibopuestoactivo($convert['recibopuestoactivo']);
                                $recibopuesto->setNumeroresolucionasignacionpuesto($convert['numeroresolucionasignacionpuesto']);
                                $recibopuesto->setNumeropuesto($convert['numeropuesto']);
                                $recibopuesto->setNombresector($convert['nombresector']);
                                $recibopuesto->setNombrezona($convert['nombrezona']);
                                if($convert['creacionrecibo'] !=false && $convert['modificacionrecibo'] != false){
                                $recibopuesto->setCreacionrecibo(new \Datetime($convert['creacionrecibo']));
                                $recibopuesto->setModificacionrecibo(new \Datetime($convert['modificacionrecibo']));
                            }else{
                                $recibopuesto->setCreacionrecibo(new \Datetime("now"));
                                $recibopuesto->setModificacionrecibo(new \Datetime("now"));
                                }

                                $usuario->setNumerorecibo($convert['numerorecibo']+1);

                                $em->persist($recibopuesto);
                                $em->flush();

                                $data = array(
                                    'status' => 'Exito',
                                    'msg' => 'Recibo Puesto  creado !!',
                                    'recibopuesto' => $recibopuesto,
                                );

                                $datos = array(
                                    "idusuario" => $identity->sub,
                                    "nombreusuario" => $identity->name,
                                    "identificacionusuario" => $identity->identificacion,
                                    "accion" => "editar",
                                    "tabla" => "recibopuesto",
                                    "valoresrelevantes" => "idrecibopuesto" . ":" . $recibopuesto->getPkidrecibopuesto(),
                                    "idelemento" => $recibopuesto->getPkidrecibopuesto(),
                                    "origen" => "web",
                                );

                                $auditoria = $this->get(Auditoria::class);
                                $auditoria->auditoria(json_encode($datos));

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
                'modulo' => "recibopuesto",
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
