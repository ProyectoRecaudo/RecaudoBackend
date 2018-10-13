<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Tfactura;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RecaudoController extends Controller
{

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
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        $pkidplaza = $request->get('pkidplaza', null);
                        $pkidsector = $request->get('pkidsector', null);
                        $nombrepuesto = $request->get('nombrepuesto', null);
                        $identificacionbeneficiario = $request->get('identificacionbeneficiario', null);
                        $mesfactura = $request->get('mesfactura', null);
                        $year = $request->get('year', null);
                        $filtro = $request->get('filtro', null);

                        if ($pkidplaza != null || $pkidsector != null || $nombrepuesto != null
                            || $identificacionbeneficiario != null || ($mesfactura != null && $year != null)) {

                            $filtro = "";

                            if ($mesfactura != null && $year != null) {
                                $filtro .= " mesfactura ='" . $mesfactura . "' and year='" . $year . "'" . " and ";
                            }

                            if ($pkidplaza != null) {
                                $plaza = $this->getDoctrine()->getRepository("ModeloBundle:Tplaza")->findOneBy(array(
                                    "pkidplaza" => $pkidplaza,
                                ));

                                if (!is_object($plaza)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id de la plaza no existe',
                                    );
                                    return $helpers->json($data);
                                }

                                $filtro .= " fkidplaza =" . $pkidplaza . " and ";
                            }

                            if ($pkidsector != null) {
                                $sector = $this->getDoctrine()->getRepository("ModeloBundle:Tsector")->findOneBy(array(
                                    "pkidsector" => $pkidsector,
                                ));

                                if (!is_object($sector)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id del sector no existe',
                                    );
                                    return $helpers->json($data);
                                }

                                $filtro .= " fkidsector =" . $pkidsector . " and ";
                            }

                            if ($nombrepuesto != null) {
                                $filtro .= " nombrepuesto =upper('%" . $nombrepuesto . "%')" . " and ";
                            }

                            if ($identificacionbeneficiario != null) {
                                $filtro .= " identificacionbeneficiario ='" . $identificacionbeneficiario . "'" . "  and ";
                            }

                            if ($filtro == "") {
                                $filtro = true;
                            }

                            $filtro = substr($filtro, 0, -4);

                            $query = "SELECT *
                                        FROM tfactura
                                        WHERE $filtro;";
                            $stmt = $db->prepare($query);
                            $params = array();
                            $stmt->execute($params);
                            $factura = $stmt->fetchAll();

                            $data = array(
                                'status' => 'Success',
                                'factura' => $factura,
                            );
                        } else {
                            if ($filtro != null) {
                                if (!$filtro) {
                                    $filtro = $filtro;
                                }else{
                                    $filtro = $filtro;
                                }
                            }else{
                                $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto",
                                        "Septiembre", "Octubre", "Noviembre", "Diciembre");

                                    $mes = date("n");
                                    $mesLetras = $meses[$mes - 1];
                                    $filtro = "upper(mesfacturaletras) like upper('$mesLetras')";
                            }

                            $query = "SELECT *
                                        FROM tfactura
                                        WHERE $filtro ;";

                            $stmt = $db->prepare($query);
                            $params = array();
                            $stmt->execute($params);
                            $factura = $stmt->fetchAll();

                            $data = array(
                                'status' => 'Success',
                                'factura' => $factura,
                            );
                        }

                    } else {
                        $data = array(
                            'status' => 'error',
                            'msg' => 'El usuario no tiene permisos genericos !!',
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
                'modulo' => "Recaudo",
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

    /// Este programa genera las facturas para el mes actual, para esto se toman en cuenta 4 registros:
    /// -La asignación del puesto
    /// -El acuerdo activo del puesto
    /// -Las multas para ese puesto
    /// -La factura anterior
    ///
    /// Todos los saldos van cambiando y siempre que representan deuda son negativos
    ///
    /// Los valores de las cuotas, los acuerdos, totales a pagar se muestran como positivos y no se modifican
    ///
    /// La factura es un reflejo de la situación actual del puesto, por eso debe ser rendundante y quedar con esos valores históricos
    /**
     * @Route("/")
     */
    public function recaudoAction(Request $request)
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

                        $mes = date("n");
                        $año = date("Y");

                        //Si existen facturas

                        $yaExistenFacturas = $em->getRepository('ModeloBundle:Tfactura')->findOneBy(array(
                            "mesfacturanumero" => $mes, "year" => $año,
                        ));

                        //Bandera: verifica si existe al menos una factura ya generada para este mes y este año
                        if (is_object($yaExistenFacturas)) {
                            $yaExisten = true;
                        } else {
                            $yaExisten = false;
                        }

                        $pkidplaza = $request->get("pkidplaza", null);

                        $plaza = $em->getRepository('ModeloBundle:Tplaza')->findOneBy(array(
                            "pkidplaza" => $pkidplaza,
                        ));
                        //si no ha seleccionado ninguna plaza retorna
                        if ($pkidplaza != null) {

                            $asignacionpuesto = $em->createQueryBuilder()
                                ->select("t")
                                ->from('ModeloBundle:Tasignacionpuesto', 't')
                                ->innerJoin('t.fkidpuesto', 'b')
                                ->innerJoin('b.fkidsector', 'c')
                                ->innerJoin('c.fkidzona', 'd')
                                ->innerJoin('d.fkidplaza', 'e')
                                ->where('e.pkidplaza = :pkidplaza')
                                ->andwhere('t.asignacionpuestoactivo = :asignacionpuestoactivo')
                                ->setParameter('pkidplaza', $pkidplaza)
                                ->setParameter('asignacionpuestoactivo', true)
                                ->getQuery()
                                ->getResult();

                            foreach ($asignacionpuesto as $asignacion) {
                                ///*********************************EL SIGUIENTE CÓDIGO SE COMENTA PARA FACILITAR LAS PRUEBAS*******************************************/
                                /*if ($yaExisten) {
                                $factura = $em->getRepository('ModeloBundle:Tfactura')->findOneBy(array(
                                "mesfacturanumero" => $mes, "year" => $año, "fkidasignacionpuesto" => $asignacion->getPkidasignacionpuesto(),
                                ));

                                if (is_object($factura)) {
                                continue;
                                }
                                }*/
                                ///*************************************************************************************************************************************/
                                RecaudoController::generarFacturaPorAsignacion($plaza, $asignacion, $token);
                            }

                            $data = array(
                                'msg' => 'Se generó la facturación con éxito !!',
                            );

                        } else {
                            $data = array(
                                'status' => 'error',
                                'msg' => 'Envie la plaza seleccionada !!',
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
                'modulo' => "Recaudo",
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

    public function generarFacturaPorAsignacion($plaza, $asignacion, $token)
    {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

        try {
            if ($token != null) {

                $token = $token;
                $authCheck = $jwt_auth->checkToken($token);

                if ($authCheck) {

                    $identity = $jwt_auth->checkToken($token, true);

                    $permisosSerializados = $identity->permisos;
                    $permisosDeserializados = unserialize($permisosSerializados);

                    if (in_array("PERM_USUARIOS", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        //verifica si tiene un acuerdo activo

                        $acuerdo = $em->createQueryBuilder()
                            ->select("t")
                            ->from('ModeloBundle:Tacuerdo', 't')
                            ->innerJoin('t.fkidproceso', 'b')
                            ->innerJoin('b.fkidasignacionpuesto', 'd')
                            ->where('d.pkidasignacionpuesto = :pkidasignacionpuesto')
                            ->andwhere('d.asignacionpuestoactivo = :asignacionpuestoactivo')
                            ->setParameter('pkidasignacionpuesto', $asignacion->getPkidasignacionpuesto())
                            ->setParameter('asignacionpuestoactivo', true)
                            ->getQuery()
                            ->getOneOrNullResult();
                        //verifica si tiene un acuerdo activo

                        //verifica si tiene una multa activo
                        $multasActivas = $em->createQueryBuilder()
                            ->select("t")
                            ->from('ModeloBundle:Tmulta', 't')
                            ->innerJoin('t.fkidasignacionpuesto', 'b')
                            ->where('b.pkidasignacionpuesto = :pkidasignacionpuesto')
                            ->andwhere('t.multaactivo = :multaactivo')
                            ->setParameter('pkidasignacionpuesto', $asignacion->getPkidasignacionpuesto())
                            ->setParameter('multaactivo', true)
                            ->getQuery()
                            ->getResult();
                        //verifica si tiene una multa activo

                        //si el puesto tiene facturas (puede ser que sea una asignación nueva)

                        $facturaAnterior = $em->createQueryBuilder()
                            ->select("t")
                            ->from('ModeloBundle:Tfactura', 't')
                            ->innerJoin('t.fkidasignacionpuesto', 'b')
                            ->where('b.pkidasignacionpuesto = :pkidasignacionpuesto')
                            ->setParameter('pkidasignacionpuesto', $asignacion->getPkidasignacionpuesto())
                            ->orderBy('t.pkidfactura', 'DESC')
                            ->setMaxResults(1)
                            ->getQuery()
                            ->getOneOrNullResult();
                        //si el puesto tiene facturas (puede ser que sea una asignación nueva)

                        //si el puesto no tiene facturas o el id de asignación del puesto de la última factura es diferente a la asignación actual (nueva asignación - se asignó a alguien más)
                        $ban = false;
                        if ($facturaAnterior == null || $facturaAnterior->getFkidasignacionpuesto()->getPkidasignacionpuesto() != $asignacion->getPkidasignacionpuesto()) {

                            $facturaAnterior = RecaudoController::armarFacturaAnterior($asignacion, $acuerdo, $multasActivas, $token);

                            $ban = true;
                            //$facturaAnterior->setPkidfactura(-1);

                        }

                        $facturaNueva = RecaudoController::armarFacturaAnterior($asignacion, $acuerdo, $multasActivas, $token);

                        //aqui quede
                        //si el puesto no tiene facturas o el id de asignación del puesto de la última factura es diferente a la asignación actual (nueva asignación - se asignó a alguien más)

                        $saldoIntegral = $facturaAnterior->getTotalpagado() + $facturaAnterior->getSaldoasignacion();

                        $asignacion->setSaldo($saldoIntegral);
                        $facturaNueva->setSaldoasignacion($saldoIntegral);

                        $totalPagado = $facturaAnterior->getTotalpagado();

                        if ($ban != true) {
                            /*
                             * se realizan los pagos base, es decir se cubre lo que hay que cubrir y se debe cubrir en este orden:
                             * Siempre primero todas las deudas y multas
                             * Luego las cuotas que debe pagar por el mes actual
                             * Finalmente si sobra algo, se cubre los saldos del acuerdo si lo tiene o la cuota actual
                             */

                            $totalPagado = RecaudoController::realizarPagos($asignacion, $facturaAnterior, $facturaNueva, $acuerdo, $multasActivas, $totalPagado, $token);

                            if ($totalPagado > 0) {
                                $totalPagado = RecaudoController::aplicarSaldoAFavor($asignacion, $facturaAnterior, $facturaNueva, $acuerdo, $multasActivas, $totalPagado, $token);

                            }

                        }

                        $mesandyear = $em->createQueryBuilder()
                            ->select("t.mesfactura,t.year")
                            ->from('ModeloBundle:Tfactura', 't')
                            ->where('t.facturapagada = :facturapagada')
                            ->andwhere('t.fkidasignacionpuesto = :fkidasignacionpuesto')
                            ->setParameter('fkidasignacionpuesto', $asignacion->getPkidasignacionpuesto())
                            ->setParameter('facturapagada', false)
                            ->orderBy('t.creacionfactura', 'ASC')
                            ->setMaxResults(1)
                            ->getQuery()
                            ->getOneOrNullResult();

                        if ($mesandyear != null) {
                            $facturaNueva->setMesfactura($mesandyear['mesfactura']);
                            $facturaNueva->setYear($mesandyear['year']);
                        }

                        $facturaNueva->setNumerofactura(RecaudoController::obtenerNumeroFactura($token));
                        $em->persist($facturaNueva);
                        $em->flush();

                        return $facturaNueva;

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
                'modulo' => "Recaudo",
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

    public function obtenerNumeroFactura($token)
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

                    if (in_array("PERM_USUARIOS", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        /*
                         * usar concurrencia optimista en este proceso (luego se puede arreglar eso si es complicado de implementar, no ahora)
                         * en doctrine se usa LockMode::OPTIMISTIC
                         *
                         * La concurrencia se usa en los que puede ser que varios usaurios accedan al mismo tiempo a la bd, para evitar el descuadre de datos o que se afecte
                         * la integridad de la información. por ejemplo, si dos personas generan facturación en dos partes y al tiempo, entonces el puede ser que ambos tomen el mismo
                         * dato de número de factura al tiempo y las facturas van a quedar duplicadas
                         *
                         * hay estrategias para evitar eso, una es la optimista que debería producir error cuando alguien más intenta acceder a un dato que está siendo usado por alguien más
                         * la estrategia usual para abordar el error es esperar un random de milisegundos antes de volver a intentarlo, si ya no se puede arrojar error.
                         *
                         */

                        //para este proceso debería usarse un servicio interno de consulta de configuración y actualización.

                        $configuracion = $em->createQueryBuilder()
                            ->select("t")
                            ->from('ModeloBundle:Tconfiguracion', 't')
                            ->where('t.claveconfiguracion = :claveconfiguracion')
                            ->setParameter('claveconfiguracion', 'NoFactura')
                            ->getQuery()
                            ->getOneOrNullResult();

                        $numFactura = (int) ($configuracion->getValorconfiguracion()); //se convierte a entero
                        $numFactura = $numFactura + 1; //lo incremento en 1
                        $configuracion->setFechaconfiguracion(new \Datetime("now"));
                        $configuracion->setValoranteriorconfiguracion($configuracion->getValorconfiguracion());
                        $configuracion->setValorconfiguracion((string) $numFactura); //guardo el nuevo valor (el valor en el que quedó) --- aquí se debe guardar también los demás valores de config, como valor anterior y la fecha de modifcación
                        return $configuracion->getValorconfiguracion(); //retorno el nuevo valor convertido a string

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
                'modulo' => "Recaudo",
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

    public function aplicarSaldoAFavor($asignacion, $facturaAnterior, $facturaNueva, $acuerdo, $multasActivas, $totalPagado, $token)
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

                    if (in_array("PERM_USUARIOS", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        if ($totalPagado > 0) {
                            /*
                             * Se actualizan las facturas no pagadas con el pagototal realizado en la última factura y se establece si fueron completamente pagadas o no.
                             * Para esto se utiliza un update:
                             * update tfactura
                             * set totalapagarmes =  CASE WHEN (saldoporpagar - (totalPagado)) < 0 THEN 0 ELSE (saldoporpagar - (totalPagado)) END --el valor (totalPagado) se toma de la variable facturaAnterior en memoria
                             * facturapagada = (saldoporpagar - (totalPagado)) <= 0
                             * REVISAR ESTA SENTENCIA PUES NO LA MODIFIQUÉ LUEGO DE CAMBIAR EL CODIGO DE ABAJO, DEBE HACER LO DEL CODIGO DE ABAJO
                             */

                            //Obtengo las facturas que no han sido pagadas
                            $facturasNoPagadas = $em->createQueryBuilder()
                                ->select("t")
                                ->from('ModeloBundle:Tfactura', 't')
                                ->innerJoin('t.fkidasignacionpuesto', 'b')
                                ->where('t.facturapagada = :facturapagada')
                                ->andwhere('b.pkidasignacionpuesto = :pkidasignacionpuesto')
                                ->setParameter('pkidasignacionpuesto', $asignacion->getPkidasignacionpuesto())
                                ->setParameter('facturapagada', false)
                                ->getQuery()
                                ->getResult();

                            foreach ($facturasNoPagadas as $facturasNoPagada) {
                                $facturasNoPagada->setSaldoporpagar(($facturasNoPagada->getSaldoporpagar() + $totalPagado) > 0 ? 0 : $facturasNoPagada->getSaldoporpagar() + $totalPagado);
                                $facturasNoPagada->setFacturapagada($facturasNoPagada->getSaldoporpagar() >= 0);

                            }

                        }

                        /*
                         * Prioridad:
                         * 1. acuerdo
                         * 2. deuda
                         * 3. multa
                         * 4. interés de multa
                         * 5. tarifa del mes
                         */
                        if ($acuerdo != null) {
                            $cuotasSumadas = (int) ($totalPagado / $acuerdo->getValorcuotamensual()); //se calcula cuántas cuotas alcanza a pagar con el saldo a favor
                            $acuerdo->setCuotasPagadas($acuerdo->getCuotasPagadas() + $cuotasSumadas); //se suman las cuotas pagadas
                            $acuerdo->setCuotasIncumplidas($acuerdo->getCuotasIncumplidas() - $cuotasSumadas); //se restan las cuotas pagaddas a las incumplidas
                            if ($acuerdo->getCuotasIncumplidas() < 0) {
                                $acuerdo->setCuotasIncumplidas(0);
                            }
                            //si las cuotas  incumplidas ya fueron cubiertas

                            if ($acuerdo->getSaldoacuerdo() < 0) //si tiene saldo de acuerdo
                            {
                                if ($acuerdo->getSaldoacuerdo() + $totalPagado < 0) //si no alcanza cubrir todo el saldo
                                {
                                    $acuerdo->setSaldoacuerdo($acuerdo->getSaldoacuerdo() + $totalPagado);

                                    $totalPagado = 0;
                                } else //si sí cubre todo el saldo
                                {
                                    $totalPagado = $totalPagado + $acuerdo->getSaldoacuerdo();
                                    $acuerdo->setSaldoacuerdo(0);

                                    $acuerdo->setSaldodeudaacuerdo(0);
                                    $acuerdo->setAcuerdoactivo(false);
                                    $acuerdo->setFechapagototal(new \Datetime("now"));
                                }
                            }
                            //se reflejan los nuevos saldos
                            $facturaNueva->setSaldoacuerdo($acuerdo->getSaldoacuerdo());
                            $facturaNueva->setSaldodeudaAcuerdo($acuerdo->getSaldodeudaacuerdo());
                        }

                        //se calcula el nuevo saldo --esta parte qwuiza sobre pero por el momento dejarla ahí mientras se hacen más pruebas
                        $asignacion->setSaldo($asignacion->getSaldodeuda() + $facturaNueva->getSaldodeudaAcuerdo() + $facturaNueva->getSaldomultas());

                        $facturaNueva->setSaldoasignacion($asignacion->getSaldo());

                        $facturaNueva->setTotalapagarmes(($facturaNueva->getValorcuotaacuerdo()
                             + $facturaNueva->getTarifapuesto()) - $facturaNueva->getSaldodeudaAcuerdo() - $facturaNueva->getSaldodeuda() - $facturaNueva->getSaldomultas());

                        //$facturaNueva->setSaldoporpagar($totalPagado - $facturaNueva->getTotalapagarmes());

                        if ($totalPagado > 0) //si despues de aplicar saldo al acuerdo aun tiene saldo a favor (o si no tiene acuerdo)
                        {
                            if ($facturaNueva->getSaldoporpagar() + $totalPagado > 0) //se verifica si cubre el saldo por pagar (que obedece a la cuota actual)
                            {
                                $totalPagado = $totalPagado - $facturaNueva->getSaldoporpagar(); //se calcula lo que sobra por el mes
                                $facturaNueva->setTotalpagado($totalPagado);
                                $facturaNueva->setSaldoporpagar(0);
                            } else //si no lo cubre sólo se disminuye el saldo por pagar del mes, no genera deuda porque es la cuota del mes actual
                            {
                                $facturaNueva->setSaldoporpagar($facturaNueva->getSaldoporpagar() + $totalPagado);
                                $totalPagado = 0;
                            }
                        }

                        return $totalPagado;

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
                'modulo' => "Recaudo",
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

    public function realizarPagos($asignacion, $facturaAnterior, $facturaNueva, $acuerdo, $multasActivas, $totalPagado, $token)
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

                    if (in_array("PERM_USUARIOS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        if ($totalPagado > 0) {
                            /*
                             * Se actualizan las facturas no pagadas con el pagototal realizado en la última factura y se establece si fueron completamente pagadas o no.
                             * Para esto se utiliza un update:
                             * update tfactura
                             * set totalapagarmes =  CASE WHEN (saldoporpagar - (totalPagado)) < 0 THEN 0 ELSE (saldoporpagar - (totalPagado)) END --el valor (totalPagado) se toma de la variable facturaAnterior en memoria
                             * facturapagada = (saldoporpagar - (totalPagado)) <= 0
                             * REVISAR ESTA SENTENCIA PUES NO LA MODIFIQUÉ LUEGO DE CAMBIAR EL CODIGO DE ABAJO, DEBE HACER LO DEL CODIGO DE ABAJO
                             */

                            //Obtengo las facturas que no han sido pagadas
                            $facturasNoPagadas = $em->createQueryBuilder()
                                ->select("t")
                                ->from('ModeloBundle:Tfactura', 't')
                                ->innerJoin('t.fkidasignacionpuesto', 'b')
                                ->where('t.facturapagada = :facturapagada')
                                ->andwhere('b.pkidasignacionpuesto = :pkidasignacionpuesto')
                                ->setParameter('pkidasignacionpuesto', $asignacion->getPkidasignacionpuesto())
                                ->setParameter('facturapagada', false)
                                ->getQuery()
                                ->getResult();

                            foreach ($facturasNoPagadas as $facturasNoPagada) {
                                $facturasNoPagada->setSaldoporpagar(($facturasNoPagada->getSaldoporpagar() + $totalPagado) > 0 ? 0 : $facturasNoPagada->getSaldoporpagar() + $totalPagado);
                                $facturasNoPagada->setFacturapagada($facturasNoPagada->getSaldoporpagar() >= 0);

                            }

                        }

                        /*
                         * Prioridad: Orden de pagos y descuentos (siempre deuda primero y pago del mes luego)
                         * 1. acuerdo
                         * 2. deuda
                         * 3. multa
                         * 4. interés de multa
                         * 5. tarifa del mes
                         */
                        if ($acuerdo != null) //si tiene un acuerdo (los datos del acuerdo  ya están cargados)
                        {

                            if ($acuerdo->getSaldodeudaacuerdo() < 0) //si tiene deuda en el acuerdo
                            {
                                if ($acuerdo->getSaldodeudaacuerdo() + $totalPagado < 0) //si el pago actual no alcanza a cubrir toda la deuda
                                {
                                    $acuerdo->setSaldoacuerdo($acuerdo->getSaldoacuerdo() + $totalPagado); //se disminuye el saldo total del acuerdo
                                    $acuerdo->setSaldodeudaacuerdo($acuerdo->getSaldodeudaacuerdo() + $totalPagado); //se disminuye el saldo de la deuda del acuerdo
                                    $totalPagado = 0; //no queda pago para aplicar
                                } else //si el total pagado sí cubre toda la deuda
                                {
                                    $totalPagado = $totalPagado + $acuerdo->getSaldodeudaacuerdo(); //se obtiene lo que sobra después de pagar la deuda
                                    $acuerdo->setSaldoacuerdo($acuerdo->getSaldoacuerdo() - $acuerdo->getSaldodeudaacuerdo()); //se disminuye el saldo del acuerdo total en el valor cubierto o pagado
                                    $acuerdo->setSaldodeudaacuerdo(0); //no queda deuda
                                }
                            }
                        }

                        //si tiene deuda por fuera del acuerdo
                        if ($asignacion->getSaldodeuda() < 0) {
                            //si el total pagado no cubre la totalidad de la deuda
                            if ($asignacion->getSaldodeuda() + $totalPagado < 0) {
                                $asignacion->setSaldodeuda($asignacion->getSaldodeuda() + $totalPagado); //se abona todo a la deuda
                                $totalPagado = 0;
                            } else //si el total pagado cubre tota la deuda (es mayor o igual que la deuda)
                            {
                                $totalPagado = $totalPagado + $asignacion->getSaldodeuda(); //se calcula lo que sobra luego de pagar la deuda
                                $asignacion->setSaldodeuda(0); //se cancela la deuda
                            }
                        }

                        //si el puesto tiene multas activas
                        if ($multasActivas != null) {
                            //dado que puede tener una o más multas, se pasa a sumar los saldos de la multa
                            $saldoMultasTotal = 0;
                            $valorMultasTotal = 0;

                            //las multas deben estar organizadas u ordenadas por fecha de la más antigua a la más neuva
                            foreach ($multasActivas as $multa) {
                                /********* crear saldo multa al crear la multa (valro multa + interés multa) *************/

                                if ($multa->getSaldomulta() + $totalPagado < 0) //si el total pagado no cubre toda la multa
                                {
                                    $multa->setSaldomulta($multa->getSaldomulta() + $totalPagado); //se disminuye el saldo de la multa
                                    $totalPagado = 0;
                                } else //si si cubre el total de la multa
                                {
                                    $totalPagado = $totalPagado + $multa->getSaldomulta(); //se calcula lo que sobra luego de pagar la multa
                                    $multa->setSaldomulta(0);
                                    //se paga la multa
                                    $multa->SetFechapagototal(new \Datetime("now"));
                                    $multa->setMultaactivo(false);
                                }
                                $saldoMultasTotal = $saldoMultasTotal + $multa->getSaldomulta(); //se suma el saldo multa que queda al saldo multa total
                                $valorMultasTotal = $valorMultasTotal + $multa->getValormulta() + $multa->getInteres(); //se suma el valor de la multa (el total de la multa  pagar es estatico)
                            }
                            $facturaNueva->setSaldomultas($saldoMultasTotal); //se establece en la factura a generar el saldo de multas que debe pagar
                            $facturaNueva->setValormultas($valorMultasTotal); //se establece el valor de multas totales
                        }

                        //luego de pagar deudas y multas, se pasa a realizar los pagos de las cuotas actuales del mes actual
                        if ($acuerdo != null) //si tiene acuerdo
                        {

                            //despues de pagar la deuda del acuerdo se verifica si después de pagarla se pagó todo el acuerdo ya
                            if ($acuerdo->getSaldoacuerdo() >= 0) {
                                $acuerdo->setSaldoacuerdo(0);
                                $acuerdo->setSaldodeudaacuerdo(0);
                                $acuerdo->setAcuerdoactivo(false); //acuerdo pagado
                                $acuerdo->setFechapagototal(new \Datetime("now")); //fecha de cuándo fue pagado el acuerdo
                            } else //sino se pasa a realizar el pago de la cuota del mes del acuerdo
                            {
                                if ($acuerdo->getSaldoacuerdo() + $facturaAnterior->getValorcuotaacuerdo() <= 0) //si el total que se debe del acuerdo es mayor a la cuota del mes
                                {
                                    //si el total pagado es menor que la cuota del mes
                                    if ($totalPagado - $facturaAnterior->getValorcuotaacuerdo() < 0) {
                                        $acuerdo->setSaldoacuerdo($acuerdo->getSaldoacuerdo() + $totalPagado); //se disminuye el acuerdo en el valor pagado
                                        $totalPagado = $totalPagado - $facturaAnterior->getValorcuotaacuerdo(); //se le resta al total pagado la cuota actual para calcular la deuda con la que queda
                                        $acuerdo->setSaldodeudaacuerdo($acuerdo->getSaldodeudaacuerdo() + $totalPagado); //se le suma lo que ya debía la deuda que quedó
                                        $totalPagado = 0; //ya no queda pago o saldo  a favor
                                    } else if ($totalPagado > 0) //si el total pagado es mayor o igual que la cuota del mes
                                    {
                                        //se reduce el saldo total en el valor de la cuota del mes... no se reduce el total pagado porque puede ser superior a la cuota del mes
                                        $acuerdo->setSaldoacuerdo($acuerdo->getSaldoacuerdo() + $facturaAnterior->getValorcuotaacuerdo());
                                        $totalPagado = $totalPagado - $facturaAnterior->getValorcuotaacuerdo(); //se le quita la total pagado el valor de la cuota
                                    }
                                } else //si el total que se debe en el acuerdo es menor que la cuota del mes (ej. la cuota a pagar mensual es de 10.000 pero lo que le resta para terminar por pagar el acuerdo es de 5.000)
                                {
                                    //si el total pagado no cubre la totalidad del saldo
                                    if ($totalPagado + $acuerdo->getSaldoacuerdo() < 0) {
                                        $acuerdo->setSaldoacuerdo($acuerdo->getSaldoacuerdo() + $totalPagado); //se disminuye el saldo total

                                        $acuerdo->setSaldodeudaacuerdo($acuerdo->getSaldoacuerdo()); //la deuda pasa a ser igual a lo que falta por cubrir porque el saldo total es menor que la cuota del mes
                                        $totalPagado = 0; //no quedan pago para aplicar
                                    } else if ($totalPagado > 0) //si el total pagado cubre todo el saldo del acuerdo
                                    {
                                        $totalPagado = $totalPagado + $acuerdo->getSaldoacuerdo(); //se calcula cuanto sorba después de pagar el saldo
                                        //se paga por completo el acuerdo
                                        $acuerdo->setSaldoacuerdo(0);
                                        $acuerdo->setSaldodeudaacuerdo(0);
                                        $acuerdo->setAcuerdoactivo(false);
                                        $acuerdo->setFechapagototal(new \Datetime("now"));
                                    }
                                }
                            }
                            //la factura nueva (la que se va a generar) refleja los saldos del acuerdo, el saldo total y lo que debe
                            $facturaNueva->setSaldoacuerdo($acuerdo->getSaldoacuerdo());
                            $facturaNueva->setSaldodeudaAcuerdo($acuerdo->getSaldodeudaacuerdo());
                        }

                        //se calcula la deuda actual luego de pagar todo y pagando la tarifa del mes
                        $deudaActual = $totalPagado - $facturaAnterior->getTarifapuesto() + $asignacion->getSaldodeuda();

                        $totalPagado = $totalPagado - $facturaAnterior->getTarifapuesto() + $asignacion->getSaldodeuda(); //se calcula lo que queda despues de pagar todo

                        if ($totalPagado < 0) {
                            $totalPagado = 0;
                        }
                        //si no queda nada o saldo negativo

                        $asignacion->setSaldodeuda($deudaActual >= 0 ? 0 : $deudaActual); //si la deuda es positiva o 0 se cancela, sino se guarda el valor de la deuda (negativo)

                        $facturaNueva->setSaldodeuda($asignacion->getSaldodeuda()); //se establece en la factura a geenrar el nuevo saldo de deuda

                        $asignacion->setSaldo($asignacion->getSaldodeuda() + $facturaNueva->getSaldodeudaAcuerdo() + $facturaNueva->getSaldomultas()); //se establece el saldo total de ese puesto que incluye todo lo que puede deber (sin incluir las cuotas del mes actual porque aun no son deuda)

                        $facturaNueva->setSaldoasignacion($asignacion->getSaldo()); //se refleja el nuevo saldo en la factura

                        //se calcula el total a pagar del mes que viene siendo todas la deudas + multas + tarifas y cuotas del mes
                        $facturaNueva->setTotalapagarmes(($facturaNueva->getValorcuotaacuerdo()
                             + $facturaNueva->getTarifapuesto()) - $facturaNueva->getSaldodeudaAcuerdo() - $facturaNueva->getSaldodeuda() - $facturaNueva->getSaldomultas());

                        //se calcula el saldo por pagar
                        $facturaNueva->setSaldoporpagar($facturaNueva->getTotalpagado() - $facturaNueva->getTotalapagarmes());

                        if ($acuerdo != null) {
                            /*
                             * se verifica si ha incumplido el acuerdo o no, las cuotas pagadas.
                             * si tiene deuda, es porque incumplió, pues para cumplir con el acuerdo debe pagar la cuota del acuerdo + arriendo
                             *
                             */

                            if ($asignacion->getSaldodeuda() < 0) {
                                $acuerdo->setCuotasincumplidas($acuerdo->getCuotasincumplidas() + 1);
                            } else //si cumplió la cuota y quedó sin deduda
                            {
                                $acuerdo->setCuotaspagadas($acuerdo->getCuotaspagadas() + 1); //se suma 1 por la cuota actual
                                $acuerdo->setCuotaspagadas($acuerdo->getCuotaspagadas() + $acuerdo->getCuotasincumplidas()); //pero también se le suman las incumplidas pues puede haber debido las 2 anteriores y al pagar todo queda al día
                                $acuerdo->setCuotasincumplidas(0);
                            }
                        }

                        return $totalPagado;

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
                'modulo' => "Meses",
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

    public function armarFacturaAnterior($asignacion, $acuerdo, $multasActivas, $token)
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

                    if (in_array("PERM_USUARIOS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        $factura = new Tfactura();
                        $factura->setValorcuotaacuerdo(0);
                        $factura->setDeberyear(0);
                        $factura->setDebermes(0);
                        $factura->setSaldodeudaacuerdo(0);
                        $factura->setCuotaspagadas(0);
                        $factura->setSaldomultas(0);
                        $factura->setValormultas(0);
                        $factura->setSaldoacuerdo(0);
                        $factura->setTotalpagado(0);
                        $factura->setDeudatotal(0);
                        $factura->setCuotasIncumplidas(0);
                        $factura->setMesfactura("");
                        $factura->setFacturapagada("false");
                        $asignacionpuesto = $em->getRepository('ModeloBundle:Tasignacionpuesto')->findOneBy(array(
                            "pkidasignacionpuesto" => $asignacion->getPkidasignacionpuesto(),
                        ));
                        $factura->setFkidasignacionpuesto($asignacionpuesto);
                        $plaza = $em->getRepository('ModeloBundle:Tplaza')->findOneBy(array(
                            "pkidplaza" => $asignacion->getFkidpuesto()->getFkidsector()->getFkidzona()->getFkidplaza(),
                        ));
                        $factura->setFkidplaza($plaza);
                        $factura->setNombreplaza($asignacion->getFkidpuesto()->getFkidsector()->getFkidzona()->getFkidplaza()->getNombreplaza());
                        $zona = $em->getRepository('ModeloBundle:Tzona')->findOneBy(array(
                            "pkidzona" => $asignacion->getFkidpuesto()->getFkidsector()->getFkidzona(),
                        ));
                        $factura->setFkidzona($zona);
                        $factura->setNombrezona($asignacion->getFkidpuesto()->getFkidsector()->getFkidzona()->getNombrezona());
                        $sector = $em->getRepository('ModeloBundle:Tsector')->findOneBy(array(
                            "pkidsector" => $asignacion->getFkidpuesto()->getFkidsector(),
                        ));
                        $factura->setFkidsector($sector);
                        $factura->setNombresector($asignacion->getFkidpuesto()->getFkidsector()->getNombresector());
                        $puesto = $em->getRepository('ModeloBundle:Tpuesto')->findOneBy(array(
                            "pkidpuesto" => $asignacion->getFkidpuesto(),
                        ));
                        $factura->setFkidpuesto($puesto);
                        $factura->setNombrepuesto($asignacion->getFkidpuesto()->getNumeropuesto());
                        $factura->setIdentificacionbeneficiario($asignacion->getFkidbeneficiario()->getIdentificacionbeneficiario());
                        $factura->setNombrebeneficiario($asignacion->getFkidbeneficiario()->getNombrebeneficiario());
                        $factura->setTarifapuesto($asignacion->getFkidtarifapuesto()->getValortarifapuesto());
                        $factura->setSaldoasignacion($asignacion->getSaldo());

                        $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto",
                            "Septiembre", "Octubre", "Noviembre", "Diciembre");
                        $mes = date("n");
                        $año = date("Y");

                        $mesLetras = $meses[$mes - 1];

                        $factura->setMesfacturanumero($mes);
                        $factura->setMesfacturaletras($mesLetras);
                        $factura->setYear($año);
                        $factura->setCreacionfactura(new \Datetime("now"));
                        $factura->setModificacionfactura(new \Datetime("now"));
                        $factura->setSaldodeuda($asignacion->getSaldodeuda());

                        if (!empty($acuerdo)) {
                            $acuerdofk = $em->getRepository('ModeloBundle:Tacuerdo')->findOneBy(array(
                                "pkidacuerdo" => $acuerdo->getPkidacuerdo(),
                            ));
                            $factura->setFkidacuerdo($acuerdofk);
                            $factura->setValorcuotaacuerdo($acuerdo->getValorcuotamensual());
                            $factura->setSaldoacuerdo($acuerdo->getSaldoacuerdo());
                            $factura->setCuotasincumplidas($acuerdo->getCuotasincumplidas());
                            $factura->setCuotaspagadas($acuerdo->getCuotaspagadas());
                            $factura->setSaldodeudaacuerdo($acuerdo->getSaldodeudaacuerdo());
                            $factura->setNumeroacuerdo($acuerdo->getNumeroacuerdo());
                        }

                        $saldomultas = 0;
                        $valormultas = 0;
                        if (!empty($multasActivas)) {
                            foreach ($multasActivas as $multas) {
                                $valormultas += $multas->getValormulta() + $multas->getInteres();
                                $saldomultas += $multas->getSaldomulta();
                            }
                            $factura->setValormultas($valormultas);
                            $factura->setSaldomultas($saldomultas);
                        }
                        //multa

                        //asignacion
                        $saldo = $factura->getSaldodeuda() + $factura->getSaldomultas() + $factura->getSaldodeudaacuerdo();
                        $asignacion->setSaldo($saldo);

                        //
                        $factura->setSaldoasignacion($saldo);

                        /*
                         * Restando el saldo se calcula el total a pagar del mes.
                         * Se resta porque si el saldo es de deuda es negativo, al restarlo se sumará al total a pagar,
                         * pero si es positivo (saldo a favor) se restará del total a pagar
                         */
                        $totalapagarmes = ($factura->getValorcuotaacuerdo() + $factura->getTarifapuesto()) - $factura->getSaldodeudaacuerdo() - $factura->getSaldodeuda() - $factura->getSaldomultas();
                        $factura->setTotalapagarmes($totalapagarmes);

                        $saldoporpagar = $factura->getTotalpagado() - $factura->getTotalapagarmes();

                        $factura->setSaldoporpagar($saldoporpagar);

                        return $factura;
                        // aqui quedé retornando la factura tire el error
                        //Could not convert database value "16:43:27.024978" to Doctrine Type time. Expected format: H:i:s

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
                'modulo' => "Meses",
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

    /**
     * @Route("/mes")
     */
    public function mesAction(Request $request)
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

                        $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto",
                            "Septiembre", "Octubre", "Noviembre", "Diciembre");

                        $mes = date("n");
                        $año = date("Y");

                        $mesLetras = $meses[$mes - 1] . " (" . $mes . ")";

                        $data = array(
                            'status' => 'Exito',
                            'Mes_actual' => $mesLetras,
                            'Anio_actual' => $año,
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
                'modulo' => "Meses",
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
