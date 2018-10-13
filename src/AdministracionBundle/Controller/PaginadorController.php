<?php

namespace AdministracionBundle\Controller;

use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PaginadorController extends Controller
{

    /*
    Esta funcion realiza la paginacion de datos:
    como parametros se deben enviar:
    Un parametro con el nombre nombretabla o reporte, y como valor pasar el nombre de la tabla que se quiere paginar (ejemplo: tusuario,tzona...)
    se debe enviar como parametro el token del usuario logueado con el nombre de authorization
     */
    /**
     * @Route("/")
     */
    public function paginarAction(Request $request)
    {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

        try {
            if (!empty($request->get('authorization')) && !empty($request->get('nombretabla'))) {

                $token = $request->get('authorization', null);
                $authCheck = $jwt_auth->checkToken($token);

                if ($authCheck) {

                    $identity = $jwt_auth->checkToken($token, true);

                    $permisosSerializados = $identity->permisos;
                    $permisosDeserializados = unserialize($permisosSerializados);

                    if (in_array("PERM_REPORTES", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();
                        
                        $tabla = $request->get('nombretabla', null);
                        $tablapk = substr($tabla, 1);
                        
                        $filtros = $request->get('filtros', null);
                        $params_filtros = (array) json_decode($filtros);
                        
                        $group = null;
                        $order = null;
                        
                        $cabeceras = array();
                        
                        if($tabla== "tauditoria"){

                            $sql = "SELECT nombreusuario, identificacionusuario,
                                            tabla, valoresrelevantes, accion, to_char(creacionauditoria ,'yyyy-mm-dd') as creacionauditoria,
                                            origenauditoria
                                    FROM public.tauditoria";

                            $campo = array("nombrecampo" => "identificacionusuario", "nombreetiqueta" => "Identificacion");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombreusuario", "nombreetiqueta" => "Nombre de usuario");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "accion", "nombreetiqueta" => "Accion");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "origenauditoria", "nombreetiqueta" => "Origen auditoria");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "creacionauditoria", "nombreetiqueta" => "Fecha");
                            array_push($cabeceras, $campo);

                        }elseif($tabla== "trecibopuestoeventual"){

                            $sql = "SELECT identificacionterceropuestoeventual, nombreterceropuestoeventual, identificacionrecaudador,
                                            nombrerecaudador, apellidorecaudador,
                                            numerorecibopuestoeventual, valorecibopuestoeventual, nombreplaza, valortarifa,
                                            nombresector, recibopuestoeventualactivo,to_char(creacionrecibopuestoeventual,'yyyy-mm-dd') as creacionrecibopuestoeventual
                                    FROM public.trecibopuestoeventual";

                            $campo = array("nombrecampo" => "identificacionterceropuestoeventual", "nombreetiqueta" => "Identificacion Tercero");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombreterceropuestoeventual", "nombreetiqueta" => "Nombre tercero");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "identificacionrecaudador", "nombreetiqueta" => "Identificacion recaudador");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombrerecaudador", "nombreetiqueta" => "Nombre recaudador");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "apellidorecaudador", "nombreetiqueta" => "Apellido recaudador");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "numerorecibopuestoeventual", "nombreetiqueta" => "Numero recibo puesto eventual");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "valorecibopuestoeventual", "nombreetiqueta" => "Valor recibo puesto eventual");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombreplaza", "nombreetiqueta" => "Nombre plaza");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "valortarifa", "nombreetiqueta" => "Valor tarifa");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombresector", "nombreetiqueta" => "Nombre Sector");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "recibopuestoeventualactivo", "nombreetiqueta" => "Estado");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "creacionrecibopuestoeventual", "nombreetiqueta" => "Fecha");
                            array_push($cabeceras, $campo);

                        }elseif($tabla == "treciboanimal"){

                            $sql= "SELECT
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
                            
                            $campo = array("nombrecampo" => "numeroreciboanimal", "nombreetiqueta" => "Numero de Recibo Animal");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "numeroguiaica", "nombreetiqueta" => "Numero de Guia ICA");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "identificacionvendedor", "nombreetiqueta" => "Identificacion Vendedor");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombrevendedor", "nombreetiqueta" => "Nombre Vendedor");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "identificacioncomprador", "nombreetiqueta" => "Identificacion Comprador");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombrecomprador", "nombreetiqueta" => "Nombre Comprador");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "identificacionrecaudador", "nombreetiqueta" => "Identificacion Recaudador");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombrerecaudador", "nombreetiqueta" => "Nombre Recaudador");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "apellidorecaudador", "nombreetiqueta" => "Apellido Recaudador");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombreplaza", "nombreetiqueta" => "Plaza");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombresector", "nombreetiqueta" => "Sector");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombrecategoriaanimal", "nombreetiqueta" => "Categoria de Animal");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombretipoanimal", "nombreetiqueta" => "Tipo de Animal");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombreespecieanimal", "nombreetiqueta" => "Especie de Animal");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "edadanimal", "nombreetiqueta" => "Edad del Animal");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "caracteristicasanimal", "nombreetiqueta" => "Caracteristicas del Animal");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "cantidadanimales", "nombreetiqueta" => "Cantidad de Animales");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "valortarifa", "nombreetiqueta" => "Valor tarifa");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "valoreciboanimal", "nombreetiqueta" => "Valor Recibo Animal");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "reciboanimalactivo", "nombreetiqueta" => "Estado");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "creacionreciboanimal", "nombreetiqueta" => "Fecha");
                            array_push($cabeceras, $campo);

                        }elseif($tabla == "trecibopesaje"){

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

                            $campo = array("nombrecampo" => "numerorecibopesaje", "nombreetiqueta" => "Numero de Recibo Pesaje");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "identificacionterceropesaje", "nombreetiqueta" => "Identificacion Tercero");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombreterceropesaje", "nombreetiqueta" => "Nombre Tercero");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "identificacionrecaudador", "nombreetiqueta" => "Identificacion Recaudador");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombrerecaudador", "nombreetiqueta" => "Nombre Recaudador");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "apellidorecaudador", "nombreetiqueta" => "Apellido Recaudador");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombreplaza", "nombreetiqueta" => "Plaza");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombrecategoriaanimal", "nombreetiqueta" => "Categoria de Animal");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombretipoanimal", "nombreetiqueta" => "Tipo de Animal");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombreespecieanimal", "nombreetiqueta" => "Especie de Animal");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "pesoanimal", "nombreetiqueta" => "Peso del Animal");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "valortarifa", "nombreetiqueta" => "Valor tarifa");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "valorecibopesaje", "nombreetiqueta" => "Valor Recibo Pesaje");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "recibopesajeactivo", "nombreetiqueta" => "Estado");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "creacionrecibopesaje", "nombreetiqueta" => "Fecha");
                            array_push($cabeceras, $campo);
                        
                        }elseif($tabla== "trecibovehiculo"){

                            $sql = "SELECT numerorecibovehiculo,numeroplaca,valorecibovehiculo,
                                        valortarifa,nombretercerovehiculo,identificaciontercerovehiculo,
                                        nombreplaza,recibovehiculoactivo,nombretipovehiculo,
                                        identificacionrecaudador,nombrerecaudador,apellidorecaudador,
                                        nombrepuerta,
                                        to_char(creacionrecibovehiculo,'yyyy-mm-dd') as creacionrecibovehiculo 
                                    FROM public.trecibovehiculo";

                            $campo = array("nombrecampo" => "numerorecibovehiculo", "nombreetiqueta" => "Número de recibo");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "numeroplaca", "nombreetiqueta" => "Placa");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "valorecibovehiculo", "nombreetiqueta" => "Valor recibo");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "valortarifa", "nombreetiqueta" => "Valor tarifa");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombretercerovehiculo", "nombreetiqueta" => "Nombre tercero vehiculo");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "identificaciontercerovehiculo", "nombreetiqueta" => "Identificacion Tercero vehiculo");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "identificaicionrecaudador", "nombreetiqueta" => "Identificaión del recaudador");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombrerecaudador", "nombreetiqueta" => "Nombre del recaudador");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "apellidorecaudador", "nombreetiqueta" => "Apellido del recaudador");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombrepuerta", "nombreetiqueta" => "Nombre de la puerta");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "creacionrecibovehiculo", "nombreetiqueta" => "Fecha");
                            array_push($cabeceras, $campo);

                        }elseif($tabla== "tfactura"){

                            $sql = "SELECT pkidfactura, numerofactura, nombrebeneficiario, identificacionbeneficiario,
                                            tarifapuesto, numeroacuerdo, valorcuotaacuerdo, valormultas,
                                            valorinteres,fkidasignacionpuesto, numeroresolucionasignacionpuesto,
                                            facturapagada, saldoasignacion, saldomultas,
                                            mesfacturaletras, year, saldoacuerdo, nombrepuesto, fkidplaza,
                                            fkidzona, fkidsector, nombreplaza, nombrezona, nombresector,
                                            totalpagado, mesfacturanumero, fkidpuesto, fkidacuerdo, cuotasincumplidas,
                                            cuotaspagadas, totalapagarmes, fechapagototal, saldodeuda, saldodeudaacuerdo,
                                            saldoporpagar, debermes, deberyear, facturaactivo, abonototalacuerdo,
                                            abonocuotaacuerdo, abonodeudaacuerdo, abonodeuda, abonomultas,
                                            abonocuotames,to_char(creacionfactura,'yyyy-mm-dd') as creacionfactura
                                    FROM public.tfactura";

                            $campo = array("nombrecampo" => "numerofactura", "nombreetiqueta" => "Número de factura");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombrebeneficiario", "nombreetiqueta" => "Nombre beneficiario");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "identificacionbeneficiario", "nombreetiqueta" => "Identificacion beneficiario");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "tarifapuesto", "nombreetiqueta" => "Tarifa puesto");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "numeroacuerdo", "nombreetiqueta" => "Numero acuerdo");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "valorcuotaacuerdo", "nombreetiqueta" => "Valor cuota acuerdo");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "valormultas", "nombreetiqueta" => "Valor multas");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "valorinteres", "nombreetiqueta" => "Valor interes");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "numeroresolucionasignacionpuesto", "nombreetiqueta" => "Resolucion Asignacion de Puesto");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "facturapagada", "nombreetiqueta" => "Factura pagada");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "saldoasignacion", "nombreetiqueta" => "Saldo asignacion");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "saldomultas", "nombreetiqueta" => "Saldo multas");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "mesfacturaletras", "nombreetiqueta" => "Mes factura letras");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "year", "nombreetiqueta" => "Year");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "saldoacuerdo", "nombreetiqueta" => "Saldo acuerdo");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombrepuesto", "nombreetiqueta" => "Nombre puesto");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombreplaza", "nombreetiqueta" => "Nombre plaza");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombrezona", "nombreetiqueta" => "Nombre zona");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombresector", "nombreetiqueta" => "Nombre sector");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "totalpagado", "nombreetiqueta" => "Total pagado");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "mesfacturanumero", "nombreetiqueta" => "Mes factura numero");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "cuotasincumplidas", "nombreetiqueta" => "Cuotas incumplidas");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "cuotaspagadas", "nombreetiqueta" => "Cuotas pagadas");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "totalapagarmes", "nombreetiqueta" => "Total a pagarmes");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "fechapagototal", "nombreetiqueta" => "Fecha pago total");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "saldodeuda", "nombreetiqueta" => "Saldo deuda");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "saldodeudaacuerdo", "nombreetiqueta" => "Saldo deuda acuerdo");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "saldoporpagar", "nombreetiqueta" => "Saldo por pagar");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "debermes", "nombreetiqueta" => "Deber mes");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "debermes", "nombreetiqueta" => "Deber mes");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "deberyear", "nombreetiqueta" => "Deber year");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "facturaactivo", "nombreetiqueta" => "Factura activo");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "abonototalacuerdo", "nombreetiqueta" => "Abono total acuerdo");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "abonocuotaacuerdo", "nombreetiqueta" => "Abono cuota acuerdo");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "abonodeudaacuerdo", "nombreetiqueta" => "Abono deuda acuerdo");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "abonodeuda", "nombreetiqueta" => "Abono deuda");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "abonomultas", "nombreetiqueta" => "Abono multas");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "abonocuotames", "nombreetiqueta" => "Abono cuota mes");
                            array_push($cabeceras, $campo);

                        }elseif($tabla == "trecibopuesto"){
                            
                            $tablapk = "recibo";
                           
                            $sql = "SELECT 
                                        numerofactura, nombrebeneficiario, identificacionbeneficiario, 
                                        saldo, numeroacuerdo, valorcuotaacuerdo, valormultas, valorinteres, 
                                        mesfactura,pkidrecibopuesto, fkidfactura, numerorecibo, 
                                        nombreterceropuesto, identificacionterceropuesto, nombreplaza, 
                                        recibopuestoactivo, numeroresolucionasignacionpuesto, 
                                        numeropuesto, nombresector, fkidzona, fkidsector, fkidpuesto, 
                                        fkidasignacionpuesto, fkidplaza, fkidbeneficiario, fkidacuerdo, 
                                        identificacionrecaudador, nombrerecaudador, apellidorecaudador, 
                                        fkidusuariorecaudador, valorpagado, saldoporpagar, nombrezona, 
                                        abonototalacuerdo, abonocuotaacuerdo, abonodeudaacuerdo, abonodeuda, 
                                        abonomultas, abonocuotames, 
                                        to_char(creacionrecibo, 'yyyy-mm-dd') as creacionrecibo, 
                                        to_char(modificacionrecibo, 'yyyy-mm-dd') as modificacionrecibo
                                    FROM public.trecibopuesto";
                                    
                        }elseif($tabla == "reporteacuerdopagos"){

                            $sql = "SELECT
                                        tproceso.pkidproceso,
                                        tproceso.procesoactivo,
                                        tabogado.pkidabogado,
                                        tabogado.nombreabogado,
                                        tasignacionpuesto.pkidasignacionpuesto,
                                        tasignacionpuesto.numeroresolucionasignacionpuesto,
                                        tbeneficiario.pkidbeneficiario,
                                        tbeneficiario.identificacionbeneficiario,
                                        tbeneficiario.nombrebeneficiario,
                                        tpuesto.pkidpuesto,
                                        tpuesto.numeropuesto,
                                        tsector.pkidsector,
                                        tsector.nombresector,
                                        tzona.pkidzona,
                                        tzona.nombrezona,
                                        tplaza.pkidplaza,
                                        tplaza.nombreplaza,
                                        tacuerdo.pkidacuerdo,
                                        tacuerdo.resolucionacuerdo,
                                        tacuerdo.acuerdoactivo,
                                        (SELECT count(pkidfactura)
                                            FROM tfactura
                                            WHERE
                                                tasignacionpuesto.pkidasignacionpuesto = tfactura.fkidasignacionpuesto
                                                AND tfactura.facturapagada = false
                                                AND tfactura.facturaactivo = true) AS mesessinpagar
                                    FROM tacuerdo
                                        JOIN tproceso ON tproceso.pkidproceso = tacuerdo.fkidproceso
                                        JOIN tasignacionpuesto ON tasignacionpuesto.pkidasignacionpuesto = tproceso.fkidasignacionpuesto
                                        JOIN tbeneficiario ON tbeneficiario.pkidbeneficiario = tasignacionpuesto.fkidbeneficiario
                                        JOIN tpuesto ON tpuesto.pkidpuesto = tasignacionpuesto.fkidpuesto
                                        JOIN tsector ON tsector.pkidsector = tpuesto.fkidsector
                                        JOIN tzona ON tzona.pkidzona = tsector.fkidzona
                                        JOIN tplaza ON tplaza.pkidplaza = tzona.fkidplaza
                                        JOIN tabogado ON tabogado.pkidabogado = tproceso.fkidabogado";

                        }elseif($tabla == "reportecartera"){
                            
                            $tabla = "trecibopuesto";
                            $tablapk = "recibo";

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

                            $campo = array("nombrecampo" => "recaudototalacuerdo", "nombreetiqueta" => "Recaudo Total de Acuerdos");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "recaudocuotaacuerdo", "nombreetiqueta" => "Recaudo de Cuotas de Acuerdo");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "recaudodeudaacuerdo", "nombreetiqueta" => "Recaudo de Deudas Acuerdo");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "recaudodeuda", "nombreetiqueta" => "Recaudo de Deudas");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "totalrecaudocartera", "nombreetiqueta" => "Total Recaudo de Cartera");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombreplaza", "nombreetiqueta" => "Plaza");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombrezona", "nombreetiqueta" => "Zona");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombresector", "nombreetiqueta" => "Sector");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "identificacionrecaudador", "nombreetiqueta" => "Identificacion Recaudador");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombrerecaudador", "nombreetiqueta" => "Nombre Recaudador");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "apellidorecaudador", "nombreetiqueta" => "Apellido Recaudador");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "creacionrecibo", "nombreetiqueta" => "Fecha");
                            array_push($cabeceras, $campo);

                        }elseif($tabla == "reportedeudoresmorosos"){
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

                            $campo = array("nombrecampo" => "identificacionbeneficiario", "nombreetiqueta" => "Identificacion Beneficiario");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombrebeneficiario", "nombreetiqueta" => "Nombre Beneficiario");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "numeropuesto", "nombreetiqueta" => "Puesto");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombresector", "nombreetiqueta" => "Sector");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombrezona", "nombreetiqueta" => "Zona");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombreplaza", "nombreetiqueta" => "Plaza");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "numeroresolucionasignacionpuesto", "nombreetiqueta" => "Resolucion Asignacion de Puesto");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "resolucionacuerdo", "nombreetiqueta" => "Resolucion de Acuerdo de Pago");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "numeroacuerdo", "nombreetiqueta" => "Numero de Acuerdo de Pago");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombreabogado", "nombreetiqueta" => "Nombre Abogado");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "saldo", "nombreetiqueta" => "Saldo de Recaudo");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "saldodeuda", "nombreetiqueta" => "Saldo de Deduda del Recaudo");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "saldoacuerdo", "nombreetiqueta" => "Saldo de Acuerdo de Pago");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "saldodeudaacuerdo", "nombreetiqueta" => "Saldo de Deuda de Acuerdo de Pago");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "saldomulta", "nombreetiqueta" => "Saldo de Multas");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "cuotasincumplidas", "nombreetiqueta" => "Cuotas Incumplidas en Acuerdo de Pago");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "mesessinpagar", "nombreetiqueta" => "Meses de Deuda");
                            array_push($cabeceras, $campo);

                            //coloca los filtros en null para que no entre en el proceso de filtros dinamico 
                            $filtros = null;

                        }elseif($tabla == "reportepagoacuerdos"){
                            
                            $tabla = "tcierrediariosector";
                            $tablapk = "cierrediariosector";

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

                            $campo = array("nombrecampo" => "recaudototalacuerdo", "nombreetiqueta" => "Recaudo Total de Acuerdos");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "recaudocuotaacuerdo", "nombreetiqueta" => "Recaudo de Cuotas de Acuerdo");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "recaudodeudaacuerdo", "nombreetiqueta" => "Recaudo de Deudas Acuerdo");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombreplaza", "nombreetiqueta" => "Plaza");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombresector", "nombreetiqueta" => "Sector");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "identificacion", "nombreetiqueta" => "Identificacion Recaudador");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "nombreusuario", "nombreetiqueta" => "Nombre Recaudador");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "apellido", "nombreetiqueta" => "Apellido Recaudador");
                            array_push($cabeceras, $campo);
                            $campo = array("nombrecampo" => "creacioncierrediariosector", "nombreetiqueta" => "Fecha");
                            array_push($cabeceras, $campo);
                        }else{
                            $data = array(
                                'status' => 'error',
                                'msg' => 'El reporte no existe !!',
                            );
                            return $helpers->json($data);
                        }       

                        if ($filtros != null) {
                        
                            $sql .= " WHERE ";
                            $count_fech = 0;
                            foreach ($params_filtros as $clave => $value) {

                                $tabla_filtro = $tabla;

                                //si las claves hacen parte de un join de otra tabla se cambia el nombre de la tabla para el filtro
                                if($clave == "identificacion" || $clave == "nombreusuario"){
                                    $tabla_filtro = "tusuario";
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
                                        $query_type = "select data_type from information_schema.columns WHERE TABLE_NAME='$tabla_filtro' AND COLUMN_NAME='$clave'";
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
                                    } else if ($type_campo == 'integer') {
                                        $equals = " = ";
                                        $id = $clave;
                                        $indice = $value;
                                        $count_fech = 0;
                                    } else if ($type_campo == 'boolean') {
                                        $equals = " = ";
                                        $id = $clave;
                                        $indice = $value;
                                        $count_fech = 0;
                                    }else {
                                        $count_fech++;
                                        if ($count_fech == 1) {
                                            $equals = " >= ";
                                            $indice = "'" . $value . "'";
                                        } else {
                                            $equals = " <= ";
                                            $indice = "'" . $value . "'";
                                        }
                                    }
                                    
                                    if ($count_fech == 0) {
                                        $sql .= "$id" . $equals . "$indice" . " and ";
                                    } else { 
                                        if ($count_fech == 1) {
                                            $sql .= "to_char(creacion$tablapk, 'yyyy-mm-dd')" . $equals . "$indice" . " and ";
                                        } else {
                                            $sql .= "to_char(creacion$tablapk, 'yyyy-mm-dd')" . $equals . "$indice" . " and ";
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
                            $deuda=$request->get('deuda',null);
                            $acuerdo=$request->get('acuerdo',null);

                            if($deuda != null || $acuerdo != null){
                                if($deuda){
                                    $sql .=" saldodeuda > 0 and ";
                                }else{
                                    $sql .=" saldodeuda = 0 and ";    
                                }

                                if($acuerdo){
                                    $sql .=" saldoacuerdo > 0 and ";
                                }else{
                                    $sql .=" saldoacuerdo = 0 and ";    
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
                        if($group != null){
                            $sql .= $group;
                        }

                        /**
                         * se añade order by si las consultas lo requieren
                         */
                        if($order != null){
                            $sql .= $order;
                        }else{
                            $sql .= " ORDER BY to_char(creacion$tablapk, 'yyyy-mm-dd') ASC";
                        }

                        /**
                         * Genera la consulta
                         */
                        $q = $sql;
                        $stmt = $db->prepare($q);
                        $params = array();
                        $stmt->execute($params);
                        $query = $stmt->fetchAll();    

                        /**
                         * Opciones de paginador
                         */
                        $page = $request->query->getInt("page",1);
                        $paginator = $this->get("knp_paginator");
                        $items_per_page=10;
                        $options = array("distinct" => false);
                       
                        /**
                         * Pagina los datos de la consulta
                         */
                        $pagination= $paginator->paginate($query,$page,$items_per_page,$options);
                        $total_items_count = $pagination->getTotalItemCount();

                        //sumatoria
                        if($tabla == "trecibopuestoeventual"){

                            $query = "select sum(valorecibopuestoeventual) as sumatoria from trecibopuestoeventual;";
                            $stmt = $db->prepare($query);
                            $params = array();
                            $stmt->execute($params);
                            $sumatoria = $stmt->fetchAll();

                            $data = array(
                                'status' => 'Exito',
                                'msg' => 'Se hizo con exito',
                                'total_items_count'=> $total_items_count,
                                'page_actual' => $page,
                                'item_per_page' => $items_per_page,
                                'total_pages' => ceil($total_items_count/$items_per_page),
                                'cabeceras' => $cabeceras,
                                'datos' => $pagination,
                                ' '=>$sumatoria[0]['sumatoria']
                            ); 

                        }else{
                            $data = array(
                                'status' => 'Exito',
                                'msg' => 'Se hizo con exito',
                                'total_items_count'=> $total_items_count,
                                'page_actual' => $page,
                                'item_per_page' => $items_per_page,
                                'total_pages' => ceil($total_items_count/$items_per_page),
                                'cabeceras' => $cabeceras,
                                'datos' => $pagination
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
                'paginador' => "Paginador",
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
