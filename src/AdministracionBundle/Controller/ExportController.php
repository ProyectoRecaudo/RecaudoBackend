<?php

namespace AdministracionBundle\Controller;

use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use SeguridadBundle\Services\PDF;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        //return $this->render('AdministracionBundle:Default:index.html.twig');
    }

    /*
    Esta funcion genera un excel a partir de los siguientes parametros:
    nombrereporte y como valor se debe enviar el nombre del reporte que quiere generar:
    nombrereporte="reportemodulo"
    filtros y como valores en json debe enviar los campos por los que desea filtrar el reporte, este es opcional:
    filtros=
    {nombremodulo:"usuario",
    moduloactivo:"true".
    "fechainicio":"2018-09-05",
    ,"fechafin":"2018-09-16"
    }
    también se debe enviar como parametro el token del usuario logueado con el nombre de authorization
     */
    //sql definidos
    /**
     * @Route("/excel")
     */
    public function excelAction(Request $request)
    {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

        try {
            if (!empty($request->get('authorization'))) {

                $token = $request->get('authorization', null);
                $authCheck = $jwt_auth->checkToken($token);

                if ($authCheck) {
                    $em = $this->getDoctrine()->getManager();
                    $db = $em->getConnection();

                    $identity = $jwt_auth->checkToken($token, true);

                    $permisosSerializados = $identity->permisos;
                    $permisosDeserializados = unserialize($permisosSerializados);

                    if (in_array("PERM_REPORTES", $permisosDeserializados)) {

                        $nombrereporte = $request->get('nombrereporte', null);

                        $filtros = $request->get("filtros", null);
                        $params_filtros = (array) json_decode($filtros);

                        $group = null;
                        $order = null;

                        //cambiar
                        if ($nombrereporte == 'reporterecibopuestoeventual') {

                            $sql = "SELECT identificacionterceropuestoeventual, nombreterceropuestoeventual, identificacionrecaudador,
                                            nombrerecaudador, apellidorecaudador,
                                            numerorecibopuestoeventual, valorecibopuestoeventual, nombreplaza, valortarifa,
                                            nombresector, recibopuestoeventualactivo,to_char(creacionrecibopuestoeventual,'yyyy-mm-dd') as creacionrecibopuestoeventual
                                    FROM public.trecibopuestoeventual";

                            $nombretabla = "recibopuestoeventual";

                        } elseif ($nombrereporte == 'reporteauditoria') {

                            $sql = "SELECT nombreusuario, identificacionusuario,
                                            tabla, valoresrelevantes, accion, to_char(creacionauditoria ,'yyyy-mm-dd') as creacionauditoria,
                                            origenauditoria
                                    FROM public.tauditoria";

                            $nombretabla = "auditoria";

                        } elseif ($nombrereporte == 'reportereciboanimal') {

                            $sql = "SELECT
                                        numeroreciboanimal,
                                        numeroguiaica,
                                        identificacionvendedor,
                                        nombrevendedor,
                                        identificacioncomprador,
                                        nombrecomprador,
                                        identificacionrecaudador,
                                        nombrerecaudador,
                                        apellidorecaudador,
                                        nombreplaza,
                                        nombresector,
                                        nombrecategoriaanimal,
                                        nombretipoanimal,
                                        nombreespecieanimal,
                                        edadanimal,
                                        caracteristicasanimal,
                                        cantidadanimales,
                                        valortarifa,
                                        valoreciboanimal,
                                        reciboanimalactivo,
                                        to_char(creacionreciboanimal ,'yyyy-mm-dd') as creacionreciboanimal
                                    FROM public.treciboanimal";

                            $nombretabla = "reciboanimal";

                        } elseif ($nombrereporte == 'reporterecibopesaje') {

                            $sql = "SELECT
                                        pkidrecibopesaje,
                                        numerorecibopesaje,
                                        identificacionterceropesaje,
                                        nombreterceropesaje,
                                        identificacionrecaudador,
                                        nombrerecaudador,
                                        apellidorecaudador,
                                        nombreplaza,
                                        nombrecategoriaanimal,
                                        nombretipoanimal,
                                        nombreespecieanimal,
                                        pesoanimal,
                                        valortarifa,
                                        valorecibopesaje,
                                        recibopesajeactivo,
                                        to_char(creacionrecibopesaje ,'yyyy-mm-dd') as creacionrecibopesaje
                                    FROM public.trecibopesaje";

                            $nombretabla = "recibopesaje";

                        } elseif ($nombrereporte == 'reporterecibovehiculo') {
                            $sql = "SELECT numerorecibovehiculo,numeroplaca,valorecibovehiculo,
                                        valortarifa,nombretercerovehiculo,identificaciontercerovehiculo,
                                        nombreplaza,recibovehiculoactivo,nombretipovehiculo,
                                        identificacionrecaudador,nombrerecaudador,apellidorecaudador,
                                        nombrepuerta,
                                        to_char(creacionrecibovehiculo,'yyyy-mm-dd') as creacionrecibovehiculo FROM public.trecibovehiculo";

                            $nombretabla = "recibovehiculo";

                        } elseif ($nombrereporte == 'reportefactura') {
                            $sql = "SELECT pkidfactura, numerofactura, nombrebeneficiario, identificacionbeneficiario,
                                            tarifapuesto, numeroacuerdo, valorcuotaacuerdo, valormultas,
                                            valorinteres,fkidasignacionpuesto,numeroresolucionasignacionpuesto,
                                            facturapagada, saldoasignacion, saldomultas,
                                            mesfacturaletras, year, saldoacuerdo, nombrepuesto, fkidplaza,
                                            fkidzona, fkidsector, nombreplaza, nombrezona, nombresector,
                                            totalpagado, mesfacturanumero, fkidpuesto, fkidacuerdo, cuotasincumplidas,
                                            cuotaspagadas, totalapagarmes, fechapagototal, saldodeuda, saldodeudaacuerdo,
                                            saldoporpagar, debermes, deberyear, facturaactivo, abonototalacuerdo,
                                            abonocuotaacuerdo, abonodeudaacuerdo, abonodeuda, abonomultas,
                                            abonocuotames,to_char(creacionfactura,'yyyy-mm-dd') as creacionfactura
                                    FROM public.tfactura";

                            $nombretabla = "factura";

                        } elseif ($nombrereporte == 'reportecartera') {

                            $sql = "SELECT
                                        sum(abonototalacuerdo) as recaudototalacuerdo,
                                        sum(abonocuotaacuerdo) as recaudocuotaacuerdo,
                                        sum(abonodeudaacuerdo) as recaudodeudaacuerdo,
                                        sum(abonodeuda) as recaudodeuda,
                                        sum(COALESCE(abonototalacuerdo,0) + COALESCE(abonodeuda,0)) as totalrecaudocartera,
                                        nombreplaza,
                                        nombrezona,
                                        nombresector,
                                        identificacionrecaudador,
                                        nombrerecaudador,
                                        apellidorecaudador,
                                        to_char(creacionrecibo, 'yyyy-mm-dd') as creacionrecibo
                                    FROM trecibopuesto";

                            $group = " GROUP BY
                                        nombreplaza,
                                        nombrezona,
                                        nombresector,
                                        identificacionrecaudador,
                                        nombrerecaudador,
                                        apellidorecaudador,
                                        to_char(creacionrecibo, 'yyyy-mm-dd')";

                            $nombretabla = "recibopuesto";

                        }elseif ($nombrereporte == "reportedeudoresmorosos"){
                            
                            /**
                             * Este reporte retorna los deudores con 2 o mas facturas sin pagar o deudas en acuerdo o multa
                             * Recibe un json llamado filtro en el que se pueden enviar los paramentros para filtrar
                             * mesessinpagar, numero de meses de atraso.
                             * pkidplaza, id de plaza
                             * pkidzona, id de zona
                             * pkidsector, id del sector,
                             * cualquiera de estos parametros es opcional, se puede enviar uno, o varios
                             * el parametro filtro tambien es opcional, si no se envia el reporte retorna todos los deudores
                             * con mas de dos meses sin pago, y deudas en acuerdo o multa. 
                             */
                            
                            $params_filtros = json_decode($filtros);
                            $mesessinpagar = ">= 2";
                            $cuotasacuerdo = "";
                            $where = "";
                            
                            $sql = "SELECT 
                                        tbeneficiario.identificacionbeneficiario,
                                        tbeneficiario.nombrebeneficiario,
                                        tpuesto.numeropuesto,
                                        tsector.nombresector,
                                        tzona.nombrezona, 
                                        tplaza.nombreplaza, 
                                        tasignacionpuesto.numeroresolucionasignacionpuesto,
                                        tacuerdo.resolucionacuerdo, 
                                        tacuerdo.numeroacuerdo, 
                                        tabogado.nombreabogado,
                                        tasignacionpuesto.saldo, 
                                        tasignacionpuesto.saldodeuda,
                                        tacuerdo.saldoacuerdo, 
                                        tacuerdo.saldodeudaacuerdo,
                                        sum(tmulta.saldomulta) as saldomulta, 
                                        tacuerdo.cuotasincumplidas, 
                                        (SELECT count(pkidfactura)
                                         FROM tfactura
                                         WHERE
                                            tasignacionpuesto.pkidasignacionpuesto = tfactura.fkidasignacionpuesto
                                            AND tfactura.facturapagada = false
                                            AND tfactura.facturaactivo = true) AS mesessinpagar
                                    FROM tasignacionpuesto
                                        JOIN tbeneficiario ON tasignacionpuesto.fkidbeneficiario = tbeneficiario.pkidbeneficiario
                                        JOIN tpuesto ON tasignacionpuesto.fkidpuesto = tpuesto.pkidpuesto
                                        JOIN tsector ON tpuesto.fkidsector = tsector.pkidsector
                                        JOIN tzona ON tsector.fkidzona = tzona.pkidzona
                                        JOIN tplaza ON tzona.fkidplaza = tplaza.pkidplaza
                                        LEFT JOIN tproceso ON tasignacionpuesto.pkidasignacionpuesto = tproceso.fkidasignacionpuesto
                                        LEFT JOIN tabogado ON tproceso.fkidabogado = tabogado.pkidabogado
                                        LEFT JOIN tacuerdo ON tproceso.pkidproceso = tacuerdo.fkidproceso
                                        LEFT JOIN tmulta ON tasignacionpuesto.pkidasignacionpuesto = tmulta.fkidasignacionpuesto";
                            
                            if($filtros != null){
                               
                                if(isset($params_filtros->mesessinpagar)) {
                                     $mesessinpagar = "= ".$params_filtros->mesessinpagar;
                                     $cuotasacuerdo = "AND tacuerdo.cuotasincumplidas = ".$params_filtros->mesessinpagar;
                                } 

                                if(isset($params_filtros->pkidplaza)){
                                    $where .= " AND tplaza.pkidplaza = ".$params_filtros->pkidplaza;
                                }
                                
                                if(isset($params_filtros->pkidzona)){
                                    $where .= " AND tzona.pkidzona = ".$params_filtros->pkidzona;
                                }

                                if(isset($params_filtros->pkidsector)){
                                    $where .= " AND tsector.pkidsector = ".$params_filtros->pkidsector;
                                }
                            }

                            $sql .= " WHERE 
                                        ((SELECT count(pkidfactura)
                                          FROM tfactura
                                          WHERE
                                            tasignacionpuesto.pkidasignacionpuesto = tfactura.fkidasignacionpuesto
                                            AND tfactura.facturapagada = false
                                            AND tfactura.facturaactivo = true) $mesessinpagar 
                                            OR tmulta.saldomulta > 0
                                            OR (tacuerdo.saldoacuerdo > 0 $cuotasacuerdo)) 
                                            $where";

                            $group = " GROUP BY
                                            tbeneficiario.identificacionbeneficiario,
                                            tbeneficiario.nombrebeneficiario,
                                            tpuesto.numeropuesto,
                                            tsector.nombresector,
                                            tzona.nombrezona, 
                                            tplaza.nombreplaza, 
                                            tasignacionpuesto.numeroresolucionasignacionpuesto,
                                            tacuerdo.resolucionacuerdo, 
                                            tacuerdo.numeroacuerdo,
                                            tabogado.nombreabogado,
                                            tasignacionpuesto.pkidasignacionpuesto, 
                                            tasignacionpuesto.saldo, 
                                            tasignacionpuesto.saldodeuda,
                                            tacuerdo.saldoacuerdo, 
                                            tacuerdo.saldodeudaacuerdo,
                                            tacuerdo.cuotasincumplidas ";
                            
                            $order = " ORDER BY tbeneficiario.identificacionbeneficiario";

                            //coloca los filtros en null para que no entre en el proceso de filtros dinamico 
                            $filtros = null;

                        }elseif ($nombrereporte == "reportepagoacuerdos"){
                            
                            $sql = "SELECT 
                                        sum(recaudototalacuerdo) as recaudototalacuerdo,
                                        sum(recaudocuotaacuerdo) as recaudocuotaacuerdo,
                                        sum(recaudodeudaacuerdo) as recaudodeudaacuerdo,
                                        nombreplaza,
                                        nombresector,
                                        identificacion,
                                        nombreusuario,
                                        apellido,
                                        to_char(creacioncierrediariosector, 'yyyy-mm-dd') as creacioncierrediariosector
                                    FROM tcierrediariosector
                                        JOIN tusuario ON tcierrediariosector.fkidusuariorecaudador = tusuario.pkidusuario
                                        JOIN tsector ON tcierrediariosector.fkidsector = tsector.pkidsector
                                        JOIN tplaza ON tcierrediariosector.fkidplaza = tplaza.pkidplaza";

                            $group = " GROUP BY
                                            fkidplaza,
                                            nombreplaza,
                                            fkidsector,
                                            nombresector,
                                            identificacion,
                                            nombreusuario,
                                            apellido,
                                            to_char(creacioncierrediariosector, 'yyyy-mm-dd')";
                            
                            $nombretabla = "cierrediariosector";

                        } else {
                            $data = array(
                                'status' => 'error',
                                'msg' => 'El nombre del reporte no existe',
                            );
                            return $helpers->json($data);
                        }

                        if ($filtros != null) {

                            $sql .= " WHERE ";
                            $count_fech = 0;

                            foreach ($params_filtros as $clave => $value) {

                                $tabla_filtro = $nombretabla;

                                //si las claves hacen parte de un join de otra tabla se cambia el nombre de la tabla para el filtro
                                if($clave == "identificacion" || $clave == "nombreusuario"){
                                    $tabla_filtro = "usuario";
                                }

                                $query = "SELECT column_name
                                            FROM information_schema.columns
                                            WHERE table_schema='public'
                                                and table_name= 't$tabla_filtro'
                                                and column_name='$clave'";
                                $stmt = $db->prepare($query);
                                $params = array();
                                $stmt->execute($params);
                                $check_filtro = $stmt->fetchAll();

                                if (count($check_filtro) != 0 || $clave == 'fechainicio' || $clave == 'fechafin') {

                                    if ($clave != 'fechainicio' && $clave != 'fechafin') {
                                        $query_type = "SELECT data_type
                                                        FROM information_schema.columns
                                                        WHERE TABLE_NAME='t$tabla_filtro'
                                                            AND COLUMN_NAME='$clave'";
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

                                        if ($nombretabla == 'recibopuesto') {$nombretabla = 'recibo';}

                                        if ($count_fech == 1) {
                                            $sql .= "to_char(creacion$nombretabla,'yyyy-mm-dd')" . $equals . "$indice" . " and ";
                                        } else {
                                            $sql .= "to_char(creacion$nombretabla,'yyyy-mm-dd')" . $equals . "$indice" . " and ";
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
                            //Con deuda y acuerdo
                            $deuda = $request->get('deuda', null);
                            $acuerdo = $request->get('acuerdo', null);
                            if ($deuda != null || $acuerdo != null) {
                                if ($deuda) {
                                    $sql .= " saldodeuda > 0 and ";
                                } else {
                                    $sql .= " saldodeuda = 0 and ";
                                }

                                if ($acuerdo) {
                                    $sql .= " saldoacuerdo > 0 and ";
                                } else {
                                    $sql .= " saldoacuerdo = 0 and ";
                                }
                            }
                            //Con deuda y acuerdo

                            $sql = substr($sql, 0, -4);

                            if ($clave != 'fechainicio' && $clave != 'fechafin') {
                                foreach ($params_filtros as $clave => $value) {
                                    $order = " ORDER BY " . $clave . " ASC";
                                    break;
                                }
                            }
                        }

                        /**
                         * se añade group by si las consultas lo requieren
                         */
                        if ($group != null) {
                            $sql .= $group;
                        }

                        /**
                         * se añade order by
                         */
                        if ($order != null) {
                            $sql .= $order;
                        } else {
                            if ($nombretabla == 'recibopuesto') {$nombretabla = 'recibo';}
                            $sql .= " ORDER BY creacion$nombretabla ASC";
                        }

                        $query = $sql;
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $modulo = $stmt->fetchAll();

                        $modulo_array = array();

                        if ($nombrereporte == 'reporterecibopuestoeventual') {

                            foreach ($modulo as $modulos) {
                                //cambiar
                                $modulo = array(
                                    "Identificacion Tercero" => $modulos['identificacionterceropuestoeventual']
                                    , "Nombre tercero" => $modulos['nombreterceropuestoeventual']
                                    , "Identificacionrecaudador" => $modulos['identificacionrecaudador']
                                    , "Numero recaudador" => $modulos['nombrerecaudador']
                                    , "Apellido recaudador" => $modulos['apellidorecaudador']
                                    , "Numero recibo puesto eventual" => $modulos['numerorecibopuestoeventual']
                                    , "Valor recibo puesto eventual" => $modulos['valorecibopuestoeventual']
                                    , "Nombre plaza" => $modulos['nombreplaza']
                                    , "Valor tarifa" => $modulos['valortarifa']
                                    , "Nombre Sector" => $modulos['nombresector']
                                    , "Estado" => $modulos['recibopuestoeventualactivo']
                                    , "Fecha" => $modulos['creacionrecibopuestoeventual'],
                                );
                                //cambiar
                                array_push($modulo_array, $modulo);
                            }

                        } elseif ($nombrereporte == 'reporteauditoria') {

                            foreach ($modulo as $modulos) {
                                //cambiar
                                $modulo = array(
                                    "Identificacion" => $modulos['identificacionusuario']
                                    , "Nombre de usuario" => $modulos['nombreusuario']
                                    , "Valores relevantes" => $modulos['valoresrelevantes']
                                    , "Accion" => $modulos['accion']
                                    , "Origen Auditoria" => $modulos['origenauditoria']
                                    , "Fecha" => $modulos['creacionauditoria'],
                                );
                                //cambiar
                                array_push($modulo_array, $modulo);
                            }

                        } elseif ($nombrereporte == 'reportereciboanimal') {

                            foreach ($modulo as $modulos) {
                                //cambiar
                                $modulo = array(
                                    "Numero de Recibo Animal" => $modulos['numeroreciboanimal'],
                                    "Numero de Guia ICA" => $modulos['numeroguiaica'],
                                    "Identificacion Vendedor" => $modulos['identificacionvendedor'],
                                    "Nombre Vendedor" => $modulos['nombrevendedor'],
                                    "Identificacion Comprador" => $modulos['identificacioncomprador'],
                                    "Nombre Comprador" => $modulos['nombrecomprador'],
                                    "Identificacion Recaudador" => $modulos['identificacionrecaudador'],
                                    "Nombre Recaudador" => $modulos['nombrerecaudador'],
                                    "Apellido Recaudador" => $modulos['apellidorecaudador'],
                                    "Plaza" => $modulos['nombreplaza'],
                                    "Sector" => $modulos['nombresector'],
                                    "Categoria de Animal" => $modulos['nombrecategoriaanimal'],
                                    "Tipo de Animal" => $modulos['nombretipoanimal'],
                                    "Especie de Animal" => $modulos['nombreespecieanimal'],
                                    "Edad del Animal" => $modulos['edadanimal'],
                                    "Caracterisitcas del Animal" => $modulos['caracteristicasanimal'],
                                    "Cantidad de Animales" => $modulos['cantidadanimales'],
                                    "Valor tarifa" => $modulos['valortarifa'],
                                    "Valor Recibo Animal" => $modulos['valoreciboanimal'],
                                    "Estado" => $modulos['reciboanimalactivo'],
                                    "Fecha" => $modulos['creacionreciboanimal'],
                                );
                                //cambiar
                                array_push($modulo_array, $modulo);
                            }

                        } elseif ($nombrereporte == 'reporterecibopesaje') {

                            foreach ($modulo as $modulos) {
                                //cambiar
                                $modulo = array(
                                    "Numero de Recibo Pesaje" => $modulos['numerorecibopesaje'],
                                    "Identificacion Tercero" => $modulos['identificacionterceropesaje'],
                                    "Nombre Tercero" => $modulos['nombreterceropesaje'],
                                    "Identificacion Recaudador" => $modulos['identificacionrecaudador'],
                                    "Nombre Recaudador" => $modulos['nombrerecaudador'],
                                    "Apellido Recaudador" => $modulos['apellidorecaudador'],
                                    "Plaza" => $modulos['nombreplaza'],
                                    "Categoria de Animal" => $modulos['nombrecategoriaanimal'],
                                    "Tipo de Animal" => $modulos['nombretipoanimal'],
                                    "Especie de Animal" => $modulos['nombreespecieanimal'],
                                    "Peso del Animal" => $modulos['pesoanimal'],
                                    "Valor tarifa" => $modulos['valortarifa'],
                                    "Valor Recibo Pesaje" => $modulos['valorecibopesaje'],
                                    "Estado" => $modulos['recibopesajeactivo'],
                                    "Fecha" => $modulos['creacionrecibopesaje'],
                                );
                                //cambiar
                                array_push($modulo_array, $modulo);
                            }

                        } else if ($nombrereporte == 'reporterecibovehiculo') {

                            $modulo = array(
                                "Creacion del recibo" => $modulos['creacionrecibovehiculo']
                                , "Numero del recibo" => $modulos['numerorecibovehiculo']
                                , "Numero de placa" => $modulos['numeroplaca']
                                , "Valores del recibo" => $modulos['valorecibovehiculo']
                                , "Valor de la tarifa" => $modulos['valortarifa']
                                , "Nombre tercero del vehiculo" => $modulos['nombretercerovehiculo']
                                , "Identificacion tercero del vehiculo" => $modulos['identificaciontercerovehiculo']
                                , "Nombre de la plaza" => $modulos['nombreplaza']
                                , "Recibo vehiculo activo" => $modulos['recibovehiculoactivo']
                                , "Nombre del tipo de vehiculo" => $modulos['nombretipovehiculo']
                                , "Identificación del recaudador" => $modulos['identificacionrecaudador']
                                , "Nombre del recaudador" => $modulos['nombrerecaudador']
                                , "Apellido del recaudador" => $modulos['apellidorecaudador']
                                , "Nombre de la puerta" => $modulos['nombrepuerta'],
                            );
                            //cambiar
                            array_push($modulo_array, $modulo);

                        } else if ($nombrereporte == 'reportefactura') {

                            $modulo = array(
                                "Número de factura" => $modulos['numerofactura'],
                                "Nombre beneficiario" => $modulos['nombrebeneficiario'],
                                "Identificacion beneficiario" => $modulos['identificacionbeneficiario'],
                                "Tarifa puesto" => $modulos['tarifapuesto'],
                                "Numero acuerdo" => $modulos['numeroacuerdo'],
                                "Valor cuota acuerdo" => $modulos['valorcuotaacuerdo'],
                                "Valor multas" => $modulos['valormultas'],
                                "Valor interes" => $modulos['valorinteres'],
                                "Resolucion Asignacion de Puesto" => $modulos['numeroresolucionasignacionpuesto'],
                                "Factura pagada" => $modulos['facturapagada'],
                                "Saldo asignacion" => $modulos['saldoasignacion'],
                                "Saldo multas" => $modulos['saldomultas'],
                                "Mes factura letras" => $modulos['mesfacturaletras'],
                                "Year" => $modulos['year'],
                                "Saldo acuerdo" => $modulos['saldoacuerdo'],
                                "Nombre puesto" => $modulos['nombrepuesto'],
                                "Nombre plaza" => $modulos['nombreplaza'],
                                "Nombre zona" => $modulos['nombrezona'],
                                "Nombre sector" => $modulos['nombresector'],
                                "Total pagado" => $modulos['totalpagado'],
                                "Mes factura numero" => $modulos['mesfacturanumero'],
                                "Cuotas incumplidas" => $modulos['cuotasincumplidas'],
                                "Cuotas pagadas" => $modulos['cuotaspagadas'],
                                "Total a pagarmes" => $modulos['totalapagarmes'],
                                "Fecha pago total" => $modulos['fechapagototal'],
                                "Saldo deuda" => $modulos['saldodeuda'],
                                "Saldo deuda acuerdo" => $modulos['saldodeudaacuerdo'],
                                "Saldo por pagar" => $modulos['saldoporpagar'],
                                "Deber mes" => $modulos['debermes'],
                                "Deber mes" => $modulos['debermes'],
                                "Deber year" => $modulos['deberyear'],
                                "Factura activo" => $modulos['facturaactivo'],
                                "Abono total acuerdo" => $modulos['abonototalacuerdo'],
                                "Abono cuota acuerdo" => $modulos['abonocuotaacuerdo'],
                                "Abono deuda acuerdo" => $modulos['abonodeudaacuerdo'],
                                "Abono deuda" => $modulos['abonodeuda'],
                                "Abono multas" => $modulos['abonomultas'],
                                "Abono cuota mes" => $modulos['abonocuotames'],
                            );
                            //cambiar
                            array_push($modulo_array, $modulo);

                        } elseif ($nombrereporte == 'reportecartera') {

                            foreach ($modulo as $modulos) {
                                //cambiar
                                $modulo = array(
                                    "Recaudo Total de Acuerdos" => $modulos['recaudototalacuerdo'],
                                    "Recaudo de Cuotas de Acuerdo" => $modulos['recaudocuotaacuerdo'],
                                    "Recaudo de Deudas Acuerdo" => $modulos['recaudodeudaacuerdo'],
                                    "Recaudo de Deudas" => $modulos['recaudodeuda'],
                                    "Total Recaudo de Cartera" => $modulos['totalrecaudocartera'],
                                    "Plaza" => $modulos['nombreplaza'],
                                    "Zona" => $modulos['nombrezona'],
                                    "Sector" => $modulos['nombresector'],
                                    "Identificacion Recaudador" => $modulos['identificacionrecaudador'],
                                    "Nombre Recaudador" => $modulos['nombrerecaudador'],
                                    "Apellido Recaudador" => $modulos['apellidorecaudador'],
                                    "Fecha" => $modulos['creacionrecibo'],
                                );
                                //cambiar
                                array_push($modulo_array, $modulo);
                            }
                        }elseif ($nombrereporte == 'reportedeudoresmorosos') {
                            
                            foreach ($modulo as $modulos) {
                               
                                $modulo = array(
                                    "Identificacion Beneficiario"           => $modulos['identificacionbeneficiario'],
                                    "Nombre Beneficiario"                   => $modulos['nombrebeneficiario'],
                                    "Puesto"                                => $modulos['numeropuesto'],
                                    "Sector"                                => $modulos['nombresector'],
                                    "Zona"                                  => $modulos['nombrezona'],
                                    "Plaza"                                 => $modulos['nombreplaza'],
                                    "Resolucion Asignacion de Puesto"       => $modulos['numeroresolucionasignacionpuesto'],
                                    "Resolucion de Acuerdo de Pago"         => $modulos['resolucionacuerdo'],                    
                                    "Numero de Acuerdo de Pago"             => $modulos['numeroacuerdo'],   
                                    "Nombre Abogado"                        => $modulos['nombreabogado'],
                                    "Saldo de Recaudo"                      => $modulos['saldo'],
                                    "Saldo de Deduda del Recaudo"           => $modulos['saldodeuda'],                        
                                    "Saldo de Acuerdo de Pago"              => $modulos['saldodeudaacuerdo'],
                                    "Saldo de Deuda de Acuerdo de Pago"     => $modulos['saldoacuerdo'],           
                                    "Saldo de Multas"                       => $modulos['saldomulta'],
                                    "Cuotas Incumplidas en Acuerdo de Pago" => $modulos['cuotasincumplidas'],                       
                                    "Meses de Deuda"                     => $modulos['mesessinpagar']
                                );
                                
                                array_push($modulo_array, $modulo);
                            }
                        }elseif ($nombrereporte == "reportepagoacuerdos"){
                            foreach ($modulo as $modulos) {
                                
                                $modulo = array(
                                    "Recaudo Total de Acuerdos"    => $modulos['recaudototalacuerdo'],
                                    "Recaudo de Cuotas de Acuerdo" => $modulos['recaudocuotaacuerdo'],
                                    "Recaudo de Deudas Acuerdo"    => $modulos['recaudodeudaacuerdo'],
                                    "Plaza"                        => $modulos['nombreplaza'],
                                    "Sector"                       => $modulos['nombresector'],
                                    "Identificacion Recaudador"    => $modulos['identificacion'],
                                    "Nombre Recaudador"            => $modulos['nombreusuario'],
                                    "Apellido Recaudador"          => $modulos['apellido'],
                                    "Fecha"                        => $modulos['creacioncierrediariosector']
                                );
                                
                                array_push($modulo_array, $modulo);
                            }
                        }
                        return ExportController::generateExcel($modulo_array, $token);

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
                'modulo' => "Modulo",
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

    public function generateExcel($modulo_array, $token)
    {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

        try {
            if (!empty($token)) {

                $token = $token;
                $authCheck = $jwt_auth->checkToken($token);

                if ($authCheck) {

                    $identity = $jwt_auth->checkToken($token, true);

                    $permisosSerializados = $identity->permisos;
                    $permisosDeserializados = unserialize($permisosSerializados);

                    if (in_array("PERM_REPORTES", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        $modulo_array = $modulo_array;

                        if (!empty($modulo_array)) {
                            $filename = "libros.xls";
                            header("Content-Type: application/vnd.ms-excel");
                            header("Content-Disposition: attachment; filename=" . $filename);
                            $mostrar_columnas = false;

                            foreach ($modulo_array as $modulo_arrays) {
                                if (!$mostrar_columnas) {
                                    echo implode("\t", array_keys($modulo_arrays)) . "\n";
                                    $mostrar_columnas = true;
                                }
                                echo implode("\t", array_values($modulo_arrays)) . "\n";
                            }
                            exit;

                        } else {
                            $data = array(
                                'status' => 'error',
                                'msg' => 'No hay registros para generar !!',
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
                'modulo' => "Modulo",
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

    //generar pdf

    /*
    Esta funcion genera un pdf a partir de los siguientes parametros:
    nombrereporte y como valor se debe enviar el nombre del reporte que quiere generar:
    nombrereporte="reportemodulo"
    filtros y como valores en json debe enviar los campos por los que desea filtrar el reporte, este es opcional:
    filtros=
    {nombremodulo:"usuario",
    moduloactivo:"true",
    "fechainicio":"2018-09-05",
    ,"fechafin":"2018-09-16"
    }
    también se debe enviar como parametro el token del usuario logueado con el nombre de authorization
     */
    //sql definidos
    /**
     * @Route("/pdf")
     */
    public function pdfAction(Request $request)
    {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

        try {
            if (!empty($request->get('authorization'))) {

                $token = $request->get('authorization', null);
                $authCheck = $jwt_auth->checkToken($token);

                if ($authCheck) {
                    $em = $this->getDoctrine()->getManager();
                    $db = $em->getConnection();

                    $identity = $jwt_auth->checkToken($token, true);

                    $permisosSerializados = $identity->permisos;
                    $permisosDeserializados = unserialize($permisosSerializados);

                    if (in_array("PERM_REPORTES", $permisosDeserializados)) {

                        $nombrereporte = $request->get('nombrereporte', null);

                        $filtros = $request->get("filtros", null);
                        $params_filtros = (array) json_decode($filtros);

                        $group = null;
                        $order = null;

                        //cambiar
                        if ($nombrereporte == 'reporterecibopuestoeventual') {

                            $sql = "SELECT identificacionterceropuestoeventual, nombreterceropuestoeventual, identificacionrecaudador,
                                            nombrerecaudador, apellidorecaudador,
                                            numerorecibopuestoeventual, valorecibopuestoeventual, nombreplaza, valortarifa,
                                            nombresector, recibopuestoeventualactivo,to_char(creacionrecibopuestoeventual,'yyyy-mm-dd') as creacionrecibopuestoeventual
                                    FROM public.trecibopuestoeventual";

                            $header = array(
                                "identificacionterceropuestoeventual" => "Identificacion tercero"
                                , "nombreterceropuestoeventual" => "Nombre tercero"
                                , "identificacionrecaudador" => "Identificacion recaudador"
                                , "nombrerecaudador" => "Nombre recaudador"
                                , "apellidorecaudador" => "Apellido recaudador"
                                , "numerorecibopuestoeventual" => "Numero recibo puesto eventual"
                                , "valorecibopuestoeventual" => "Valor recibo puesto eventual"
                                , "nombreplaza" => "Nombre plaza"
                                , "valortarifa" => "Valor tarifa"
                                , "nombresector" => "Nombre sector"
                                , "recibopuestoeventualactivo" => "Estado"
                                , "creacionrecibopuestoeventual" => "Fecha",
                            );

                            $nombretabla = "recibopuestoeventual";

                        } elseif ($nombrereporte == 'reporteauditoria') {
                            $sql = "SELECT nombreusuario, identificacionusuario,
                                            tabla, valoresrelevantes, accion, to_char(creacionauditoria,'yyyy-mm-dd') as creacionauditoria,
                                            origenauditoria
                                    FROM public.tauditoria";

                            $header = array(
                                "identificacionusuario" => "Identificacion"
                                , "nombreusuario" => "Nombre de usuario"
                                , "tabla" => "Tabla"
                                , "accion" => "Accion"
                                , "origenauditoria" => "Origen auditoria"
                                , "creacionauditoria" => "Fecha",
                            );

                            $nombretabla = "auditoria";

                        } elseif ($nombrereporte == 'reportereciboanimal') {
                            $sql = "SELECT
                                        numeroreciboanimal,
                                        numeroguiaica,
                                        identificacionvendedor,
                                        nombrevendedor,
                                        identificacioncomprador,
                                        nombrecomprador,
                                        identificacionrecaudador,
                                        nombrerecaudador,
                                        apellidorecaudador,
                                        nombreplaza,
                                        nombresector,
                                        nombrecategoriaanimal,
                                        nombretipoanimal,
                                        nombreespecieanimal,
                                        edadanimal,
                                        caracteristicasanimal,
                                        cantidadanimales,
                                        valortarifa,
                                        valoreciboanimal,
                                        reciboanimalactivo,
                                        to_char(creacionreciboanimal ,'yyyy-mm-dd') as creacionreciboanimal
                                    FROM public.treciboanimal";

                            $header = array(
                                "numeroreciboanimal" => "Numero de Recibo Animal",
                                "numeroguiaica" => "Numero de Guia ICA",
                                "identificacionvendedor" => "Identificacion Vendedor",
                                "nombrevendedor" => "Nombre Vendedor",
                                "identificacioncomprador" => "Identificacion Comprador",
                                "nombrecomprador" => "Nombre Comprador",
                                "identificacionrecaudador" => "Identificacion Recaudador",
                                "nombrerecaudador" => "Nombre Recaudador",
                                "apellidorecaudador" => "Apellido Recaudador",
                                "nombreplaza" => "Plaza",
                                "nombresector" => "Sector",
                                "nombrecategoriaanimal" => "Categoria de Animal",
                                "nombretipoanimal" => "Tipo de Animal",
                                "nombreespecieanimal" => "Especie de Animal",
                                "edadanimal" => "Edad del Animal",
                                "caracteristicasanimal" => "Caracterisitcas del Animal",
                                "cantidadanimales" => "Cantidad de Animales",
                                "valortarifa" => "Valor Tarifa",
                                "valoreciboanimal" => "Valor Recibo Animal",
                                "reciboanimalactivo" => "Estado",
                                "creacionreciboanimal" => "Fecha",
                            );

                            $nombretabla = "reciboanimal";

                        } elseif ($nombrereporte == 'reporterecibopesaje') {

                            $pkidrecibopesaje = $request->get('pkidrecibopesaje', null);

                            if ($pkidrecibopesaje == null) {
                                $data = array(
                                    'status' => 'error',
                                    'msg' => 'Envie el id del recibo pesaje para generar el reporte !!',
                                );
                                return $helpers->json($data);
                            }

                            $recibopesaje = $this->getDoctrine()->getRepository("ModeloBundle:Trecibopesaje")->findOneBy(array(
                                "pkidrecibopesaje" => $pkidrecibopesaje,
                            ));

                            if (!is_object($recibopesaje)) {
                                $data = array(
                                    'status' => 'error',
                                    'msg' => 'El id del recibo pesaje no existe !!',
                                );
                                return $helpers->json($data);
                            }

                            $sql = "SELECT
                                        pkidrecibopesaje,
                                        numerorecibopesaje,
                                        identificacionterceropesaje,
                                        nombreterceropesaje,
                                        identificacionrecaudador,
                                        nombrerecaudador,
                                        apellidorecaudador,
                                        nombreplaza,
                                        nombrecategoriaanimal,
                                        nombretipoanimal,
                                        nombreespecieanimal,
                                        pesoanimal,
                                        valortarifa,
                                        valorecibopesaje,
                                        recibopesajeactivo,
                                        to_char(creacionrecibopesaje ,'yyyy-mm-dd') as creacionrecibopesaje
                                    FROM public.trecibopesaje where pkidrecibopesaje=$pkidrecibopesaje";

                            $header = array(
                                "numerorecibopesaje" => "Recibo No.:",
                                "identificacionterceropesaje" => "C.C Usuario",
                                "nombreterceropesaje" => "Usuario",
                                "nombrerecaudador" => "Nombre Recaudador",
                                "apellidorecaudador" => "Apellido Recaudador",
                                "nombreplaza" => "Plaza",
                                "nombrecategoriaanimal" => "Cat. Animal",
                                "nombretipoanimal" => "Tipo",
                                "nombreespecieanimal" => "Especie",
                                "pesoanimal" => "Peso (kg)",
                                "valortarifa" => "Tarifa ($)",
                                "valorecibopesaje" => "Pago ($)",
                                "creacionrecibopesaje" => "Fecha",
                            );

                            $nombretabla = "recibopesaje";

                        } elseif ($nombrereporte == 'reportevehiculo') {

                            $sql = "SELECT numerorecibovehiculo,numeroplaca,valorecibovehiculo,
                                    valortarifa,nombretercerovehiculo,identificaciontercerovehiculo,
                                    nombreplaza,recibovehiculoactivo,nombretipovehiculo,
                                    identificacionrecaudador,nombrerecaudador,apellidorecaudador,
                                    nombrepuerta,
                                    to_char(creacionrecibovehiculo,'yyyy-mm-dd') as creacionrecibovehiculo FROM public.trecibovehiculo";
                            $campos_count = "SELECT COUNT(*) As NumeroCampos
                            FROM Information_Schema.Columns
                            WHERE Table_Name = 'trecibovehiculo'";
                            $header = array(
                                "numerorecibovehiculo" => "Numero del recibo"
                                , "numeroplaca" => "Numero de la placa"
                                , "valorecibovehiculo" => "Valor del recibo"
                                , "valortarifa" => "Tarifa"
                                , "nombretercerovehiculo" => "Nombre del tercero vehiculo"
                                , "identificaciontercerovehiculo" => "Id del tercero vehiculo"
                                , "nombreplaza" => "Valor nombreplaza"
                                , "recibovehiculoactivo" => "Estado"
                                , "nombretipovehiculo" => "Nombre del tipo de vehiculo"
                                , "identificacionrecaudador" => "Id del recaudador"
                                , "nombrerecaudador" => "Nombre del recaudador"
                                , "apellidorecaudador" => "Apellido del recaudador"
                                , "nombrepuerta" => "Nombre de la puerta"
                                , "creacionrecibovehiculo" => "Fecha",
                            );

                            $nombretabla = "recibovehiculo";

                        } elseif ($nombrereporte == 'reportefactura') {

                            $sql = "SELECT pkidfactura, numerofactura, nombrebeneficiario, identificacionbeneficiario,
                                            tarifapuesto, numeroacuerdo, valorcuotaacuerdo, valormultas,
                                            valorinteres,fkidasignacionpuesto,numeroresolucionasignacionpuesto,
                                            facturapagada, saldoasignacion, saldomultas,
                                            mesfacturaletras, year, saldoacuerdo, nombrepuesto, fkidplaza,
                                            fkidzona, fkidsector, nombreplaza, nombrezona, nombresector,
                                            totalpagado, mesfacturanumero, fkidpuesto, fkidacuerdo, cuotasincumplidas,
                                            cuotaspagadas, totalapagarmes, fechapagototal, saldodeuda, saldodeudaacuerdo,
                                            saldoporpagar, debermes, deberyear, facturaactivo, abonototalacuerdo,
                                            abonocuotaacuerdo, abonodeudaacuerdo, abonodeuda, abonomultas,
                                            abonocuotames,to_char(creacionfactura,'yyyy-mm-dd') as creacionfactura
                                    FROM public.tfactura";

                            $header = array(
                                'numerofactura' => "Número de factura",
                                'nombrebeneficiario' => "Nombre beneficiario",
                                'identificacionbeneficiario' => "Identificacion beneficiario",
                                'tarifapuesto' => "Tarifa puesto",
                                'numeroacuerdo' => "Numero acuerdo",
                                'valorcuotaacuerdo' => "Valor cuota acuerdo",
                                'valormultas' => "Valor multas",
                                'valorinteres' => "Valor interes",
                                'numeroresolucionasignacionpuesto' => "Resolucion Asignacion de Puesto",
                                'facturapagada' => "Factura pagada",
                                'saldoasignacion' => "Saldo asignacion",
                                'saldomultas' => "Saldo multas",
                                'mesfacturaletras' => "Mes factura letras",
                                'year' => "Year",
                                'saldoacuerdo' => "Saldo acuerdo",
                                'nombrepuesto' => "Nombre puesto",
                                'nombreplaza' => "Nombre plaza",
                                'nombrezona' => "Nombre zona",
                                'nombresector' => "Nombre sector",
                                'totalpagado' => "Total pagado",
                                'mesfacturanumero' => "Mes factura numero",
                                'cuotasincumplidas' => "Cuotas incumplidas",
                                'cuotaspagadas' => "Cuotas pagadas",
                                'totalapagarmes' => "Total a pagarmes",
                                'fechapagototal' => "Fecha pago total",
                                'saldodeuda' => "Saldo deuda",
                                'saldodeudaacuerdo' => "Saldo deuda acuerdo",
                                'saldoporpagar' => "Saldo por pagar",
                                'debermes' => "Deber mes",
                                'debermes' => "Deber mes",
                                'deberyear' => "Deber year",
                                'facturaactivo' => "Factura activo",
                                'abonototalacuerdo' => "Abono total acuerdo",
                                'abonocuotaacuerdo' => "Abono cuota acuerdo",
                                'abonodeudaacuerdo' => "Abono deuda acuerdo",
                                'abonodeuda' => "Abono deuda",
                                'abonomultas' => "Abono multas",
                                'abonocuotames' => "Abono cuota mes",
                            );

                            $nombretabla = "factura";

                        } elseif ($nombrereporte == 'reportecartera') {

                            $sql = "SELECT
                                        sum(abonototalacuerdo) as recaudototalacuerdo,
                                        sum(abonocuotaacuerdo) as recaudocuotaacuerdo,
                                        sum(abonodeudaacuerdo) as recaudodeudaacuerdo,
                                        sum(abonodeuda) as recaudodeuda,
                                        sum(COALESCE(abonototalacuerdo,0) + COALESCE(abonodeuda,0)) as totalrecaudocartera,
                                        nombreplaza,
                                        nombrezona,
                                        nombresector,
                                        identificacionrecaudador,
                                        nombrerecaudador,
                                        apellidorecaudador,
                                        to_char(creacionrecibo, 'yyyy-mm-dd') as creacionrecibo
                                    FROM trecibopuesto";

                            $group = " GROUP BY
                                        nombreplaza,
                                        nombrezona,
                                        nombresector,
                                        identificacionrecaudador,
                                        nombrerecaudador,
                                        apellidorecaudador,
                                        to_char(creacionrecibo, 'yyyy-mm-dd')";

                            $header = array(
                                'recaudototalacuerdo' => "Recaudo Total de Acuerdos",
                                'recaudocuotaacuerdo' => "Recaudo de Cuotas de Acuerdo",
                                'recaudodeudaacuerdo' => "Recaudo de Deudas Acuerdo",
                                'recaudodeuda' => "Recaudo de Deudas",
                                'totalrecaudocartera' => "Total Recaudo de Cartera",
                                'nombreplaza' => "Plaza",
                                'nombrezona' => "Zona",
                                'nombresector' => "Sector",
                                'identificacionrecaudador' => "Identificacion Recaudador",
                                'nombrerecaudador' => "Nombre Recaudador",
                                'apellidorecaudador' => "Apellido Recaudador",
                                'creacionrecibo' => "Fecha",
                            );

                            $nombretabla = "recibopuesto";

                        }elseif ($nombrereporte == "reportedeudoresmorosos"){
                            
                            /**
                             * Este reporte retorna los deudores con 2 o mas facturas sin pagar o deudas en acuerdo o multa
                             * Recibe un json llamado filtro en el que se pueden enviar los paramentros para filtrar
                             * mesessinpagar, numero de meses de atraso.
                             * pkidplaza, id de plaza
                             * pkidzona, id de zona
                             * pkidsector, id del sector,
                             * cualquiera de estos parametros es opcional, se puede enviar uno, o varios
                             * el parametro filtro tambien es opcional, si no se envia el reporte retorna todos los deudores
                             * con mas de dos meses sin pago, y deudas en acuerdo o multa. 
                             */
                            
                            $params_filtros = json_decode($filtros);
                            $mesessinpagar = ">= 2";
                            $cuotasacuerdo = "";
                            $where = "";
                            
                            $sql = "SELECT 
                                        tbeneficiario.identificacionbeneficiario,
                                        tbeneficiario.nombrebeneficiario,
                                        tpuesto.numeropuesto,
                                        tsector.nombresector,
                                        tzona.nombrezona, 
                                        tplaza.nombreplaza, 
                                        tasignacionpuesto.numeroresolucionasignacionpuesto,
                                        tacuerdo.resolucionacuerdo, 
                                        tacuerdo.numeroacuerdo, 
                                        tabogado.nombreabogado,
                                        tasignacionpuesto.saldo, 
                                        tasignacionpuesto.saldodeuda,
                                        tacuerdo.saldoacuerdo, 
                                        tacuerdo.saldodeudaacuerdo,
                                        sum(tmulta.saldomulta) as saldomulta, 
                                        tacuerdo.cuotasincumplidas, 
                                        (SELECT count(pkidfactura)
                                         FROM tfactura
                                         WHERE
                                            tasignacionpuesto.pkidasignacionpuesto = tfactura.fkidasignacionpuesto
                                            AND tfactura.facturapagada = false
                                            AND tfactura.facturaactivo = true) AS mesessinpagar
                                    FROM tasignacionpuesto
                                        JOIN tbeneficiario ON tasignacionpuesto.fkidbeneficiario = tbeneficiario.pkidbeneficiario
                                        JOIN tpuesto ON tasignacionpuesto.fkidpuesto = tpuesto.pkidpuesto
                                        JOIN tsector ON tpuesto.fkidsector = tsector.pkidsector
                                        JOIN tzona ON tsector.fkidzona = tzona.pkidzona
                                        JOIN tplaza ON tzona.fkidplaza = tplaza.pkidplaza
                                        LEFT JOIN tproceso ON tasignacionpuesto.pkidasignacionpuesto = tproceso.fkidasignacionpuesto
                                        LEFT JOIN tabogado ON tproceso.fkidabogado = tabogado.pkidabogado
                                        LEFT JOIN tacuerdo ON tproceso.pkidproceso = tacuerdo.fkidproceso
                                        LEFT JOIN tmulta ON tasignacionpuesto.pkidasignacionpuesto = tmulta.fkidasignacionpuesto";
                            
                            if($filtros != null){
                               
                                if(isset($params_filtros->mesessinpagar)) {
                                     $mesessinpagar = "= ".$params_filtros->mesessinpagar;
                                     $cuotasacuerdo = "AND tacuerdo.cuotasincumplidas = ".$params_filtros->mesessinpagar;
                                } 

                                if(isset($params_filtros->pkidplaza)){
                                    $where .= " AND tplaza.pkidplaza = ".$params_filtros->pkidplaza;
                                }
                                
                                if(isset($params_filtros->pkidzona)){
                                    $where .= " AND tzona.pkidzona = ".$params_filtros->pkidzona;
                                }

                                if(isset($params_filtros->pkidsector)){
                                    $where .= " AND tsector.pkidsector = ".$params_filtros->pkidsector;
                                }
                            }

                            $sql .= " WHERE 
                                        ((SELECT count(pkidfactura)
                                          FROM tfactura
                                          WHERE
                                            tasignacionpuesto.pkidasignacionpuesto = tfactura.fkidasignacionpuesto
                                            AND tfactura.facturapagada = false
                                            AND tfactura.facturaactivo = true) $mesessinpagar 
                                            OR tmulta.saldomulta > 0
                                            OR (tacuerdo.saldoacuerdo > 0 $cuotasacuerdo)) 
                                            $where";

                            $group = " GROUP BY
                                            tbeneficiario.identificacionbeneficiario,
                                            tbeneficiario.nombrebeneficiario,
                                            tpuesto.numeropuesto,
                                            tsector.nombresector,
                                            tzona.nombrezona, 
                                            tplaza.nombreplaza, 
                                            tasignacionpuesto.numeroresolucionasignacionpuesto,
                                            tacuerdo.resolucionacuerdo, 
                                            tacuerdo.numeroacuerdo,
                                            tabogado.nombreabogado,
                                            tasignacionpuesto.pkidasignacionpuesto, 
                                            tasignacionpuesto.saldo, 
                                            tasignacionpuesto.saldodeuda,
                                            tacuerdo.saldoacuerdo, 
                                            tacuerdo.saldodeudaacuerdo,
                                            tacuerdo.cuotasincumplidas ";
                            
                            $order = " ORDER BY tbeneficiario.identificacionbeneficiario";

                            $header = array(
                                "identificacionbeneficiario"        => "Identificacion Beneficiario",
                                "nombrebeneficiario"                => "Nombre Beneficiario",
                                "numeropuesto"                      => "Puesto",
                                "nombresector"                      => "Sector",
                                "nombrezona"                        => "Zona",
                                "nombreplaza"                       => "Plaza",
                                "numeroresolucionasignacionpuesto"  => "Resolucion Asignacion de Puesto",
                                "resolucionacuerdo"                 => "Resolucion de Acuerdo de Pago",
                                "numeroacuerdo"                     => "Numero de Acuerdo de Pago",
                                "nombreabogado"                     => "Nombre Abogado",
                                "saldo"                             => "Saldo de Recaudo",
                                "saldodeuda"                        => "Saldo de Deduda del Recaudo",
                                "saldoacuerdo"                      => "Saldo de Acuerdo de Pago",
                                "saldodeudaacuerdo"                 => "Saldo de Deuda de Acuerdo de Pago",
                                "saldomulta"                        => "Saldo de Multas",
                                "cuotasincumplidas"                 => "Cuotas Incumplidas en Acuerdo de Pago",
                                "mesessinpagar"                     => "Meses de Deuda"
                            );

                            //coloca los filtros en null para que no entre en el proceso de filtros dinamico 
                            $filtros = null;

                        }elseif ($nombrereporte == "reportepagoacuerdos"){
                            
                            $sql = "SELECT 
                                        sum(recaudototalacuerdo) as recaudototalacuerdo,
                                        sum(recaudocuotaacuerdo) as recaudocuotaacuerdo,
                                        sum(recaudodeudaacuerdo) as recaudadeudaacuerdo,
                                        nombreplaza,
                                        nombresector,
                                        identificacion,
                                        nombreusuario,
                                        apellido,
                                        to_char(creacioncierrediariosector, 'yyyy-mm-dd') as creacioncierrediariosector
                                    FROM tcierrediariosector
                                        JOIN tusuario ON tcierrediariosector.fkidusuariorecaudador = tusuario.pkidusuario
                                        JOIN tsector ON tcierrediariosector.fkidsector = tsector.pkidsector
                                        JOIN tplaza ON tcierrediariosector.fkidplaza = tplaza.pkidplaza";

                            $group = " GROUP BY
                                            fkidplaza,
                                            nombreplaza,
                                            fkidsector,
                                            nombresector,
                                            identificacion,
                                            nombreusuario,
                                            apellido,
                                            to_char(creacioncierrediariosector, 'yyyy-mm-dd')";
                            
                            $header = array(
                                'recaudototalacuerdo'        => "Recaudo Total de Acuerdos" ,
                                'recaudocuotaacuerdo'        => "Recaudo de Cuotas de Acuerdo", 
                                'recaudadeudaacuerdo'        => "Recaudo de Deudas Acuerdo",
                                'nombreplaza'                => "Plaza" ,
                                'nombresector'               => "Sector" ,
                                'identificacion'             => "Identificacion Recaudador" ,
                                'nombreusuario'              => "Nombre Recaudador",
                                'apellidorecaudador'         => "Apellido Recaudador" ,
                                'creacioncierrediariosector' => "Fecha" 
                            );

                            $nombretabla = "cierrediariosector";

                        } else {
                            $data = array(
                                'status' => 'error',
                                'msg' => 'El nombre del reporte no existe',
                            );
                            return $helpers->json($data);
                        }

                        $campos_count = "SELECT COUNT(*) As NumeroCampos
                                            FROM Information_Schema.Columns
                                            WHERE Table_Name = 't$nombretabla' ";

                        $stmt = $db->prepare($campos_count);
                        $params = array();
                        $stmt->execute($params);
                        $res_campos = $stmt->fetchAll();

                        foreach ($res_campos as $valor) {
                            foreach ($valor as $valor1) {
                                $campos_counts = $valor1;
                            }
                        }

                        if ($filtros != null) {

                            $sql .= " WHERE ";
                            $count_fech = 0;

                            foreach ($params_filtros as $clave => $value) {

                                $tabla_filtro = $nombretabla;

                                //si las claves hacen parte de un join de otra tabla se cambia el nombre de la tabla para el filtro
                                if($clave == "identificacion" || $clave == "nombreusuario"){
                                    $tabla_filtro = "usuario";
                                }

                                $query = "SELECT column_name FROM information_schema.columns WHERE table_schema='public'
                                        and table_name='$tabla_filtro'
                                        and column_name='$clave'";

                                $stmt = $db->prepare($query);
                                $params = array();
                                $stmt->execute($params);
                                $check_filtro = $stmt->fetchAll();

                                if (count($check_filtro) != 0 || $clave == 'fechainicio' || $clave == 'fechafin') {

                                    if ($clave != 'fechainicio' && $clave != 'fechafin') {
                                        $query_type = "SELECT data_type
                                                       FROM information_schema.columns
                                                       WHERE TABLE_NAME='t$tabla_filtro' AND COLUMN_NAME='$clave'";
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
                                        if ($nombretabla == 'recibopuesto') {$nombretabla = 'recibo';}
                                        if ($count_fech == 1) {
                                            $sql .= "to_char(creacion$nombretabla,'yyyy-mm-dd')" . $equals . "$indice" . " and ";
                                        } else {
                                            $sql .= "to_char(creacion$nombretabla,'yyyy-mm-dd')" . $equals . "$indice" . " and ";
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
                                    $order = " ORDER BY " . $clave . " ASC";
                                    break;
                                }
                            }
                        }

                        /**
                         * se añade group by si las consultas lo requieren
                         */
                        if ($group != null) {
                            $sql .= $group;
                        }

                        /**
                         * se añade order by
                         */
                        if ($order != null) {
                            $sql .= $order;
                        } else {
                            if ($nombretabla == 'recibopuesto') {$nombretabla = 'recibo';}
                            $sql .= " ORDER BY creacion$nombretabla ASC";
                        }

                        return ExportController::generatePdf($sql, $token, $header, $campos_counts, $nombrereporte);

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
                'modulo' => "Modulo",
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

    public function generatePdf($consulta, $token, $header, $campos_counts, $nombrereporte)
    {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

        try {
            if (!empty($token)) {

                $token = $token;
                $authCheck = $jwt_auth->checkToken($token);

                if ($authCheck) {

                    $identity = $jwt_auth->checkToken($token, true);

                    $permisosSerializados = $identity->permisos;
                    $permisosDeserializados = unserialize($permisosSerializados);

                    if (in_array("PERM_REPORTES", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        $query = $consulta;
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $modulo = $stmt->fetchAll();

                        if (!empty($modulo)) {

                            if ($nombrereporte != "reporterecibopesaje") {

                                $campos_counts = $campos_counts - 1;
                                if ($campos_counts >= 1 && $campos_counts <= 5) {
                                    $pdf = new \FPDF('P', 'mm', array(100, 100));
                                } else {
                                    if ($campos_counts >= 6 && $campos_counts <= 10) {
                                        $pdf = new \FPDF('P', 'mm', array(350, 350));
                                    } else {
                                        if ($campos_counts >= 11 && $campos_counts <= 15) {
                                            $pdf = new \FPDF('P', 'mm', array(750, 750));
                                        } else {
                                            if ($campos_counts >= 16 && $campos_counts <= 20) {
                                                $pdf = new \FPDF('P', 'mm', array(850, 850));
                                            } else {
                                                if ($campos_counts >= 21 && $campos_counts <= 25) {
                                                    $pdf = new \FPDF('P', 'mm', array(1150, 1150));
                                                } else {
                                                    $pdf = new \FPDF('P', 'mm', array(1250, 1250));
                                                }
                                            }
                                        }
                                    }
                                }
                            } else {
                                $pdf = new \FPDF('P', 'mm', array(120, 79));
                            }

                            $array_colum = array();

                            foreach ($modulo as $row) {
                                foreach ($header as $clave => $headers) {
                                    if (!empty($array_colum[$clave])) {
                                        if ($array_colum[$clave] < strlen($row[$clave])) {
                                            $array_colum[$clave] = strlen($row[$clave]);
                                        }
                                    } else {
                                        if (strlen($headers) < strlen($row[$clave])) {
                                            $array_colum[$clave] = strlen($row[$clave]);
                                        }
                                        if (strlen($headers) > strlen($row[$clave])) {
                                            $array_colum[$clave] = strlen($headers);
                                        }
                                        if (strlen($headers) == strlen($row[$clave])) {
                                            $array_colum[$clave] = isset($array_colum[$clave]) ? $array_colum[$clave] : strlen($headers);
                                        }
                                    }
                                }
                            }
                            if ($nombrereporte != "reporterecibopesaje") {

                                $pdf->AddPage();

                                $pdf->SetTextColor(255);
                                $pdf->SetFont('Arial', 'B', 15);

                                foreach ($header as $clave => $headers) {
                                    $pdf->Cell($array_colum[$clave] * 3.5, 15, $headers, 1, 0, 'C', true);
                                }

                                $pdf->Ln();

                                $pdf->SetFillColor(224, 235, 255);
                                $pdf->SetTextColor(0);
                                $pdf->SetFont('');

                                $fill = false;
                                foreach ($modulo as $row) {
                                    foreach ($header as $clave => $headers) {
                                        if (strpos($clave, "activ")) {
                                            if ($row["$clave"] == 1) {
                                                $pdf->Cell($array_colum[$clave] * 3.5, 6, 'ACTIVO', 'LR', 0, 'L', $fill);
                                            } else {
                                                $pdf->Cell($array_colum[$clave] * 3.5, 6, 'INACTIVO', 'LR', 0, 'L', $fill);
                                            }
                                        }
                                        if (strpos($clave, "activ") == false) {
                                            $pdf->Cell($array_colum[$clave] * 3.5, 6, $row["$clave"], 'LR', 0, 'L');
                                        }

                                        $fill = !$fill;
                                    }
                                    $pdf->Ln();
                                }
                            } else {

                                $pdf->AddPage();

                                $pdf->SetFillColor(255, 255, 255);
                                $pdf->SetTextColor(0, 0, 0);
                                $pdf->SetFont('Arial','',18);
                                $pdf->SetXY(40, 10);
                                $pdf->Cell(0.1,0,'ALCALDIA DE PASTO',0,0,'C');
                                $pdf->SetFont('Arial','',7);
                                $pdf->SetXY(40, 14);
                                $pdf->Cell(0.1,0,'Legitimidad, Participacion y Honestidad',0,0,'C');
                                $pdf->SetXY(40, 17);
                                $pdf->Cell(0.1,0,'DIRECCION ADMINISTRATIVA DE PLAZAS DE MERCADO',0,0,'C');
                                $pdf->SetXY(40, 20);
                                $pdf->Cell(0.1,0,'RECAUDO PESAJE',0,0,'C');

                                
                                $contyh = 30;
                                foreach ($header as $clave => $headers) {
                                    if (strlen(strstr($clave,'numerorecibo'))>0) {
                                        $pdf->SetFillColor(255, 255, 255);
                                        $pdf->SetTextColor(0, 0, 0);
                                        $pdf->SetFont('Arial','B',11);
                                        $pdf->SetXY(46, 24);
                                        $pdf->Cell(0.1, 4, $headers, 0, 0, 'L', true);
                                        $pdf->Ln();
                                    }elseif (strlen(strstr($clave,'creacionrecibo'))>0) {
                                        $pdf->SetFillColor(255, 255, 255);
                                        $pdf->SetTextColor(0, 0, 0);
                                        $pdf->SetFont('Arial','B',11);
                                        $pdf->SetXY(4, 71.5);
                                        $pdf->Cell(0.1, 4, $headers.":", 0, 0, 'L', true);
                                        $pdf->Ln();
                                    }elseif (strlen(strstr($clave,'nombrerecaudador'))>0) {
                                    }elseif (strlen(strstr($clave,'apellidorecaudador'))>0) {
                                    }else{
                                        $pdf->SetFillColor(255, 255, 255);
                                        $pdf->SetTextColor(0, 0, 0);
                                        $pdf->SetFont('Arial', 'B', 11);
                                        $pdf->SetXY(4, $contyh);
                                        $pdf->Cell(0.1, 4, $headers.":", 0, 0, 'L', true);
                                        $pdf->Ln();
                                        $contyh = $contyh + 3.85; 
                                    }
                                }

                                $pdf->Ln();

                                $conty = 30;
                                foreach ($modulo as $row) {
                                    foreach ($header as $clave => $headers) {
                                        if (strlen(strstr($clave,'numerorecibo'))>0) {
                                            $pdf->SetFillColor(255, 255, 255);
                                            $pdf->SetTextColor(0, 0, 0);
                                            $pdf->SetFont('Arial','B',11);
                                            $pdf->SetXY(68, 24);
                                            $pdf->Cell(0.1, 4, $row["$clave"], 0, 0, 'L', true);
                                            $pdf->Ln();
                                        }elseif (strlen(strstr($clave,'creacionrecibo'))>0) {
                                            $pdf->SetFillColor(255, 255, 255);
                                            $pdf->SetTextColor(0, 0, 0);
                                            $pdf->SetFont('Arial','',11);
                                            $pdf->SetXY(35, 71.5);
                                            $pdf->Cell(0.1, 4, $row["$clave"], 0, 0, 'L', true);
                                            $pdf->Ln();
                                        }elseif (strlen(strstr($clave,'nombrerecaudador'))>0) {
                                            $nombrerecaudador = $row["$clave"];
                                        }elseif (strlen(strstr($clave,'apellidorecaudador'))>0) {
                                            $nombreape = $nombrerecaudador ." ". $row["$clave"];
                                            $pdf->SetFillColor(255, 255, 255);
                                            $pdf->SetTextColor(0, 0, 0);
                                            $pdf->SetFont('Arial','B',11);
                                            $pdf->SetXY(4, 75.5);
                                            $pdf->Cell(0.1, 4, "RECAUDADOR:", 0, 0, 'L', true);
                                            $pdf->Ln();

                                            $pdf->SetFillColor(255, 255, 255);
                                            $pdf->SetTextColor(0, 0, 0);
                                            $pdf->SetFont('Arial','',11);
                                            $pdf->SetXY(35, 75.5);
                                            $pdf->Cell(0.1, 4, $nombreape, 0, 0, 'L', true);
                                            $pdf->Ln();
                                        }else{
                                            $pdf->SetFillColor(255, 255, 255);
                                            $pdf->SetTextColor(0, 0, 0);
                                            $pdf->SetFont('Arial', '', 11);
                                            $pdf->SetXY(35, $conty);
                                            if (strpos($clave, "activ")) {
                                                if ($row["$clave"] == 1) {
                                                    $pdf->Cell(0.1, 4, 'ACTIVO', 0, 0, 'L', false);
                                                } else {
                                                    $pdf->Cell(0.1, 4, 'INACTIVO', 0, 0, 'L', false);
                                                }
                                            }
                                            if (strpos($clave, "activ") == false) {
                                                $pdf->Cell(0.1, 4, $row["$clave"], 0, 0, 'L', false);
                                            }

                                            $pdf->Ln();
                                            $conty = $conty + 3.85;
                                        }
                                    }
                                }
                                $pdf->SetFont('Arial','',8);
                                $pdf->SetXY(40, 70);
                                $pdf->Cell(0.1,0,'- - -',0,0,'C');

                                $pdf->SetXY(40, 80);
                                $pdf->Cell(0.1,0,'- - -',0,0,'C');

                                $pdf->SetXY(40, 85);
                                $pdf->Cell(0.1,0,'Saque copia y guarde este recibo como soporte de pago',0,0,'C');
                            }

                            return new Response($pdf->Output(), 200, array(
                                'Content-Type' => 'application/pdf'));

                        } else {
                            $data = array(
                                'status' => 'error',
                                'msg' => 'No hay registros para generar !!',
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
                'modulo' => "Modulo",
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
