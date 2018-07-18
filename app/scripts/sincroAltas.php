<?php

include_once __DIR__ . '/funcionesDAO.php';

function selectEqAltas($edificio, $codigo_uni) {
    global $connInte;
    try {
        $sentencia = " select * from eq_altas  "
                . "  where codigo_uni = :codigo_uni "
                . " and edificio = :edificio";
        $query = $connInte->prepare($sentencia);
        $params = array(":codigo_uni" => $codigo_uni,
            ":edificio" => $edificio);
        $query->execute($params);
        $res = $query->fetchAll(PDO::FETCH_ASSOC);
        if ($res) {
            return $res;
        } else {
            //echo "** ERROR EN SELECT EQ_ALTAS CODIGO_UNI = " . $codigo_uni . " EDIFICIO= " . $edificio . "\n";
            return null;
        }
    } catch (PDOException $ex) {
        echo "**PDOERROR EN SELECT EQ_ALTAS CODIGO_UNI = " . $codigo_uni . " EDIFICIO= " . $edificio . " " . $ex->getMessage() . "\n";
        return null;
    }
}

function deleteEqAltas($Altas) {
    global $connInte;
    try {
        $sentencia = " delete from eq_altas where codigo_uni = :codigo_uni";
        $query = $connInte->prepare($sentencia);
        $params = array(":codigo_uni" => $Altas["codigo"]);
        $res = $query->execute($params);
        if ($res == 0) {
            echo "**ERROR EN DELETE EQ_ALTAS CODIGO_UNI=" . $Altas["codigo"] . "\n";
        }
        echo "DELETE EQ_ALTAS CODIGO_UNI=" . $Altas["codigo"] . "\n";
    } catch (PDOException $ex) {
        echo "**PDOERROR EN DELETE EQ_ALTAS EDIFICIO= " . $i
        . " CODIGO_LOC= " . $Altas["codigo"]
        . " CODIGO_UNI=" . $Altas["codigo"]
        . " \t " . $ex->getMessage()
        . " \n";
    }
}

function insertEqAltas($Altas) {
    global $connInte;
    for ($i = 0; $i < 12; $i++) {
        try {
            $sentencia = "insert into eq_altas "
                    . " (edificio, codigo_loc, codigo_uni) "
                    . " values "
                    . " (:edificio, :codigo_loc, :codigo_uni) ";
            $query = $connInte->prepare($sentencia);
            $params = array(":edificio" => $i,
                ":codigo_loc" => $Altas["codigo"],
                ":codigo_uni" => $Altas["codigo"]);
            $res = $query->execute($params);
            if ($res == 0) {
                echo "**ERROR EN INSERT EQ_ALTAS EDIFICIO= " . $i . " CODIGO_LOC= " . $Altas["codigo"] . " CODIGO_UNI=" . $Altas["codigo"] . "\n";
            }
            echo "INSERT EQ_ALTAS EDIFICIO= " . $i . " CODIGO_LOC= " . $Altas["codigo"] . " CODIGO_UNI=" . $Altas["codigo"] . "\n";
        } catch (PDOException $ex) {
            echo "**PDOERROR EN INSERT EQ_ALTAS EDIFICIO= " . $i
            . " CODIGO_LOC= " . $Altas["codigo"]
            . " CODIGO_UNI=" . $Altas["codigo"]
            . " \t  " . $ex->getMessage()
            . " \n";
        }
    }
}

function procesoInsert($Altas) {
    global $BasesDatos, $connUnif;
    insertAltas($connUnif, $Altas);
    foreach ($BasesDatos as $baseDatos) {
        $alias = $baseDatos["alias"];
        $datosConexion["maquina"] = $baseDatos["maquina"];
        $datosConexion["puerto"] = $baseDatos["puerto"];
        $datosConexion["servidor"] = $baseDatos["servidor"];
        $datosConexion["esquema"] = $baseDatos["esquema"];
        $datosConexion["usuario"] = $baseDatos["usuario"];
        $datosConexion["password"] = $baseDatos["password"];
        $conexion = conexionPDO($datosConexion);
        insertAltas($conexion, $Altas);
    }
    insertEqAltas($Altas);
}

function procesoDelete($Altas) {
    global $BasesDatos, $connUnif;
    deleteAltas($connUnif, $Altas["codigo"]);

    foreach ($BasesDatos as $baseDatos) {
        $alias = $baseDatos["alias"];
        $datosConexion["maquina"] = $baseDatos["maquina"];
        $datosConexion["puerto"] = $baseDatos["puerto"];
        $datosConexion["servidor"] = $baseDatos["servidor"];
        $datosConexion["esquema"] = $baseDatos["esquema"];
        $datosConexion["usuario"] = $baseDatos["usuario"];
        $datosConexion["password"] = $baseDatos["password"];
        $conexion = conexionPDO($datosConexion);
        $EqAltas = selectEqAltas($baseDatos["edificio"], $Altas["codigo"]);
        if ($EqAltas) {
            foreach ($EqAltas as $linea) {
                deleteAltas($conexion, $linea["CODIGO_LOC"]);
            }
        }
    }
    deleteEqAltas($Altas);
}

function procesoUpdate($Altas) {
    global $BasesDatos, $connUnif;
    updateAltas($connUnif, $Altas, $Altas["codigo"]);

    foreach ($BasesDatos as $baseDatos) {
        $alias = $baseDatos["alias"];
        $datosConexion["maquina"] = $baseDatos["maquina"];
        $datosConexion["puerto"] = $baseDatos["puerto"];
        $datosConexion["servidor"] = $baseDatos["servidor"];
        $datosConexion["esquema"] = $baseDatos["esquema"];
        $datosConexion["usuario"] = $baseDatos["usuario"];
        $datosConexion["password"] = $baseDatos["password"];
        $conexion = conexionPDO($datosConexion);
        $EqAltas = selectEqAltas($baseDatos["edificio"], $Altas["codigo"]);
        if ($EqAltas) {
            foreach ($EqAltas as $linea) {
                updateAltas($conexion, $Altas, $linea["CODIGO_LOC"]);
            }
        }
    }
}

function insertAltas($conexion, $Altas) {
    try {
        $sentencia = " insert into altas "
                . " ( codigo, descrip, btc_mcon_codigo, btc_tipocon, subaltas_afi "
                . "  , subaltas_certi, certificar, enuso, motivoaltarptid"
                . "  ,malrpt_codigo, malrpt_descripcion, l13, rel_juridica, pagar_tramo"
                . "  ,destino, modocupa, modopago, patronal ) values "
                . " ( :codigo, :descrip, :btc_mcon_codigo, :btc_tipocon, :subaltas_afi "
                . "  ,:subaltas_certi, :certificar, :enuso, :motivoaltarptid"
                . "  ,:malrpt_codigo, :malrpt_descripcion, :l13, :rel_juridica, :pagar_tramo"
                . "  ,:destino, :modocupa, :modopago, :movipat)";

        $query = $conexion->prepare($sentencia);

        $params = array(':codigo' => $Altas["codigo"]
            , ':descrip' => $Altas["descrip"]
            , ':btc_mcon_codigo' => $Altas["btc_mcon_codigo"]
            , ':btc_tipocon' => $Altas["btc_tipocon"]
            , ':subaltas_afi' => $Altas["subaltas_afi"]
            , ':subaltas_certi' => $Altas["subaltas_certi"]
            , ':certificar' => $Altas["certificar"]
            , ':enuso' => $Altas["enuso"]
            , ':motivoaltarptid' => $Altas["motivoaltarptid"]
            , ':malrpt_codigo' => $Altas["malrpt_codigo"]
            , ':malrpt_descripcion' => $Altas["malrpt_descripcion"]
            , ':l13' => $Altas["l13"]
            , ':rel_juridica' => $Altas["rel_juridica"]
            , ':pagar_tramo' => $Altas["pagar_tramo"]
            , ':destino' => $Altas["destino"]
            , ':modocupa' => $Altas["modocupa"]
            , ':modopago' => $Altas["modopago"]
            , ':movipat' => $Altas["movipat"]);
        $res = $query->execute($params);
        if ($res == 0) {
            echo "**ERROR EN INSERT ALTAS CODIGO= " . $Altas["codigo"] . " DESCRIPCION= " . $Altas["descrip"] . "\n";
            return null;
        }
        echo "INSERT ALTAS CODIGO= " . $Altas["codigo"] . " DESCRIPCION= " . $Altas["descrip"] . "\n";
        return true;
    } catch (PDOException $ex) {
        echo "**PDOERROR EN INSERT ALTAS CODIGO= " . $Altas["codigo"]
        . " DESCRIPCION= " . $Altas["descrip"]
        . " \t  "
        . $ex->getMessage() . "\n";
        return null;
    }
}

function updateAltas($conexion, $Altas, $codigo) {
    try {
        $sentencia = " update altas set  "
                . "  descrip = :descrip "
                . ", btc_mcon_codigo = :btc_mcon_codigo "
                . ", btc_tipocon = :btc_tipocon "
                . ", subaltas_afi = :subaltas_afi  "
                . ", subaltas_certi =  :subaltas_certi "
                . ", certificar = :certificar "
                . ", enuso = :enuso "
                . ", motivoaltarptid = :motivoaltarptid "
                . ", malrpt_codigo = :malrpt_codigo "
                . ", malrpt_descripcion = :malrpt_descripcion "
                . ", l13 = :l13 "
                . ", rel_juridica = :rel_juridica "
                . ", pagar_tramo = :pagar_tramo "
                . ", destino = :destino "
                . ", modocupa = :modocupa "
                . ", modopago = :modopago "
                . ", patronal = :movipat "
                . " where codigo = :codigo";
        $query = $conexion->prepare($sentencia);

        $params = array(':codigo' => $codigo
            , ':descrip' => $Altas["descrip"]
            , ':btc_mcon_codigo' => $Altas["btc_mcon_codigo"]
            , ':btc_tipocon' => $Altas["btc_tipocon"]
            , ':subaltas_afi' => $Altas["subaltas_afi"]
            , ':subaltas_certi' => $Altas["subaltas_certi"]
            , ':certificar' => $Altas["certificar"]
            , ':enuso' => $Altas["enuso"]
            , ':motivoaltarptid' => $Altas["motivoaltarptid"]
            , ':malrpt_codigo' => $Altas["malrpt_codigo"]
            , ':malrpt_descripcion' => $Altas["malrpt_descripcion"]
            , ':l13' => $Altas["l13"]
            , ':rel_juridica' => $Altas["rel_juridica"]
            , ':pagar_tramo' => $Altas["pagar_tramo"]
            , ':destino' => $Altas["destino"]
            , ':modocupa' => $Altas["modocupa"]
            , ':modopago' => $Altas["modopago"]
            , ':movipat' => $Altas["movipat"]);
        $res = $query->execute($params);
        if ($res == 0) {
            echo "**ERROR EN UPDATE ALTAS CODIGO= " . $codigo . " DESCRIPCION= " . $Altas["descrip"] . "\n";
            return null;
        }
        echo "UPDATE ALTAS CODIGO= " . $codigo . " DESCRIPCION= " . $Altas["descrip"] . "\n";
        return true;
    } catch (PDOException $ex) {
        echo "**PDOERROR EN UPDATE ALTAS CODIGO= " . $codigo
        . " DESCRIPCION= " . $Altas["descrip"]
        . " \t  "
        . $ex->getMessage() . "\n";
        return null;
    }
}

function deleteAltas($conexion, $codigo) {
    try {
        $sentencia = "delete from altas "
                . " where codigo = :codigo ";

        $query = $conexion->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $res = $query->execute($params);
        if ($res == 0) {
            echo "**ERROR EN DELETE ALTAS CODIGO= " . $codigo . "\n";
            return null;
        }
        echo "DELETE ALTAS CODIGO= " . $codigo . "\n";
        return true;
    } catch (PDOException $ex) {
        echo "**PDOERROR EN DELETE ALTAS CODIGO= " . $codigo . "    " . $ex->getMessage() . "\n";
        return null;
    }
}

echo " ++++++ COMIENZA PROCESO SINCRONIZACIÓN ALTAS +++++++++++ \n";
/*
 * Conexión a la base de datos de Control en Mysql 
 */
$connGums = connGums();
if (!$connGums) {
    exit(1);
}

/*
 * recogemos el parametro para ver si estamos en pruebas en validación o en producción
 */
$tipo = $argv[1];
$Altas_id = $argv[2];
$accion = $argv[3];

if ($tipo == 'REAL') {
    echo " ENTORNO = PRODUCCIÓN \n";
    $connInte = conexionPDO(SelectBaseDatos(2, 'I'));
    $connUnif = conexionPDO(SelectBaseDatos(2, 'U'));
    $BasesDatos = selectBaseDatosAreas(2);
} else {
    echo " ENTORNO = VALIDACIÓN \n";
    $connInte = conexionPDO(SelectBaseDatos(1, 'I'));
    $connUnif = conexionPDO(SelectBaseDatos(1, 'U'));
    $BasesDatos = selectBaseDatosAreas(1);
}

$Altas = selectAltasById($Altas_id);

if (!$Altas) {
    exit(1);
}

echo " SINCRONIZACIÓN ALTAS : ID=" . $Altas["id"]
 . " CÓDIGO=" . $Altas["codigo"]
 . " DESCRIPCIÓN= " . $Altas["descrip"]
 . " ACCION =" . $accion
 . "\n";

if ($accion == 'DELETE') {
    if (!procesoDelete($Altas)) {
        exit(1);
    }
}

if ($accion == 'INSERT') {
    if (!procesoInsert($Altas)) {
        exit(1);
    }
}

if ($accion == 'UPDATE') {
    if (!procesoUpdate($Altas)) {
        exit(1);
    }
}

echo " FIN SINCRONIZACIÓN " . "\n";
exit(0);
