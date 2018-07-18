<?php

include_once __DIR__ . '/funcionesDAO.php';


function selectIdModoPago($codigo) {
    global $connGums;
    try {
        $sentencia = " select id from gums_modopago where codigo = :codigo";
        $query = $connGums->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $query->execute($params);
        $res = $query->fetch(PDO::FETCH_ASSOC);
        return $res["id"];
    } catch (PDOException $ex) {
        echo "** ERROR EN SELECT GUMS_MODOPAGO CODIGO= " . $codigo . " " . $ex->getMessage() . "\n";
        return null;
    }
}


function selectAltas($codigo) {
    global $connGums;
    try {
        $sentencia = " select * from gums_altas where codigo = :codigo";
        $query = $connGums->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $query->execute($params);
        $res = $query->fetch(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $ex) {
        echo "** ERROR EN SELECT GUMS_ALTAS CODIGO= " . $codigo . " " . $ex->getMessage() . "\n";
        return null;
    }
}

function selectEqAltas($codigo) {
    global $connInte;
    try {
        $sentencia = " select * from eq_altas where codigo_uni = :codigo";
        $query = $connInte->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $query->execute($params);
        $res = $query->fetchALL(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $ex) {
        echo "** ERROR EN SELECT EQ_ALTAS CODIGO= " . $codigo . " " . $ex->getMessage() . "\n";
        return null;
    }
}

/*
 * Carga Inicial de la Tabla Altas y la correspondiente Tabla de Equivalencias
 */


/*
 * Cuerpo Principal 
 */

echo " -- CARGA INICIAL TABLA: GUMS_ALTAS " . "\n";
$connGums = connGums();
if (!$connGums) {
    exit(111);
}

/*
 * recogemos el parametro para ver si estamos en pruebas en validación o en producción
 */
$tipo = $argv[1];

if ($tipo == 'REAL') {
    echo " ENTORNO = PRODUCCIÓN \n";
    $connInte = conexionPDO(SelectBaseDatos(2, 'I'));
    $connUnif = conexionPDO(SelectBaseDatos(2, 'U'));
} else {
    echo " ENTORNO = VALIDACIÓN \n";
    $connInte = conexionPDO(SelectBaseDatos(1, 'I'));
    $connUnif = conexionPDO(SelectBaseDatos(1, 'U'));
}

try {
    $sentencia = " delete from gums_eq_altas";
    $query = $connGums->prepare($sentencia);
    $query->execute();
} catch (PDOException $ex) {
    echo "***PDOERROR EN DELETE GUMS_EQ_ALTAS " . $ex->getMessage() . "\n";
    exit(1);
}

try {
    $sentencia = " delete from gums_altas";
    $query = $connGums->prepare($sentencia);
    $query->execute();
} catch (PDOException $ex) {
    echo "***PDOERROR EN DELETE GUMS_ALTAS " . $ex->getMessage() . "\n";
    exit(1);
}


try {
    $sentencia = " select * from altas";
    $query = $connUnif->prepare($sentencia);
    $query->execute();
    $altasAll = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    echo "***PDOERROR EN LA SELECT DE ALTAS BASE DE DATOS UNIFICADA " . $ex->getMessage() . "\n";
    exit(1);
}

echo " Registros a Cargar = " . count($altasAll) . "\n";
foreach ($altasAll as $altas) {
    $yaExiste = selectAltas($altas["CODIGO"]);
    if (!$yaExiste) {
        try {
            $sentencia = " insert into gums_altas "
                    . " ( codigo, descrip, btc_mcon_codigo, btc_tipocon, subaltas_afi "
                    . "  , subaltas_certi, certificar, enuso, motivoaltarptid"
                    . "  ,malrpt_codigo, malrpt_descripcion, l13, rel_juridica, pagar_tramo"
                    . "  ,destino, modocupa_id, modopago_id, movipat_id ) values "
                    . " ( :codigo, :descrip, :btc_mcon_codigo, :btc_tipocon, :subaltas_afi "
                    . "  ,:subaltas_certi, :certificar, :enuso, :motivoaltarptid"
                    . "  ,:malrpt_codigo, :malrpt_descripcion, :l13, :rel_juridica, :pagar_tramo"
                    . "  ,:destino, :modocupa_id, :modopago_id, :movipat_id )";

            $query = $connGums->prepare($sentencia);

            $params = array(':codigo' => $altas["CODIGO"]
                , ':descrip' => $altas["DESCRIP"]
                , ':btc_mcon_codigo' => $altas["BTC_MCON_CODIGO"]
                , ':btc_tipocon' => $altas["BTC_TIPOCON"]
                , ':subaltas_afi' => $altas["SUBALTAS_AFI"]
                , ':subaltas_certi' => $altas["SUBALTAS_CERTI"]
                , ':certificar' => $altas["CERTIFICAR"]
                , ':enuso' => $altas["ENUSO"]
                , ':motivoaltarptid' => $altas["MOTIVOALTARPTID"]
                , ':malrpt_codigo' => $altas["MALRPT_CODIGO"]
                , ':malrpt_descripcion' => $altas["MALRPT_DESCRIPCION"]
                , ':l13' => $altas["L13"]
                , ':rel_juridica' => $altas["REL_JURIDICA"]
                , ':pagar_tramo' => $altas["PAGAR_TRAMO"]
                , ':destino' => $altas["DESTINO"]
                , ':modocupa_id' => selectIdModOcupa($altas["MODOCUPA"])
                , ':modopago_id' => selectIdModoPago($altas["MODOPAGO"])
                , ':movipat_id' => selectIdMoviPat($altas["PATRONAL"]));
            
            $res = $query->execute($params);
            if ($res == 0) {
                echo "**ERROR EN INSERT GUMS_ALTAS CODIGO= " . $altas["CODIGO"] . " \n";
                continue;
            }
            $altas["ID"] = $connGums->lastInsertId();
            echo " CREADO GUMS_ALTAS ID= " . $altas["ID"] . " CODIGO= " . $altas["CODIGO"] . " " . $altas["DESCRIP"] . "\n";

            $eqAltasAll = selectEqAltas($altas["CODIGO"]);
            foreach ($eqAltasAll as $eqAltas) {
                $edificio = selectEdificio($eqAltas["EDIFICIO"]);
                try {
                    $sentencia = " insert into gums_eq_altas "
                            . " (edificio_id, codigo_loc, altas_id ) values  ( :edificio_id, :codigo_loc, :altas_id )";
                    $insert = $connGums->prepare($sentencia);
                    $params = array(":edificio_id" => $edificio["id"],
                        ":codigo_loc" => $eqAltas["CODIGO_LOC"],
                        ":altas_id" => $altas["ID"]);
                    $res = $insert->execute($params);
                    if ($res == 0) {
                        echo "***ERROR EN INSERT GUMS_EQ_ALTAS EDIFICI O= " . $edificio["id"] . " CODIGO_LOC = " . $eqAltas["CODIGO_LOC"] . "\n";
                    }
                    echo "GENERADA EQUIVALENCIA GUMS_EQ_ALTAS EDIFICIO = " . $edificio["id"] . " CODIGO_LOC = " . $eqAltas["CODIGO_LOC"] . "\n";
                } catch (PDOException $ex) {
                    echo "***PDOERROR EN INSERT GUMS_EQ_ALTAS " . $ex->getMessage() . "\n";
                    continue;
                }
            }
        } catch (PDOException $ex) {
            echo "***PDOERROR EN INSERT GUMS_ALTAS CODIGO= " . $registro["CODIGO"] . " " . $ex->getMessage() . "\n";
        }
    }
}

echo " TERMINADA LA CARGA DE ALTAS " . "\n";
exit(0);

