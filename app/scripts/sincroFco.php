<?php

include_once __DIR__ . '/funcionesDAO.php';

function selectEqFco($edificio, $codigo_uni) {
    global $connInte;
    try {
        $sentencia = " select * from eq_fco  "
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
            //echo "** ERROR EN SELECT EQ_FCO CODIGO_UNI = " . $codigo_uni . " EDIFICIO= " . $edificio . "\n";
            return null;
        }
    } catch (PDOException $ex) {
        echo "**PDOERROR EN SELECT EQ_FCO CODIGO_UNI = " . $codigo_uni . " EDIFICIO= " . $edificio . " " . $ex->getMessage() . "\n";
        return null;
    }
}

function deleteEqFco($Fco) {
    global $connInte;
    try {
        $sentencia = " delete from eq_fco where codigo_uni = :codigo_uni";
        $query = $connInte->prepare($sentencia);
        $params = array(":codigo_uni" => $Fco["codigo"]);
        $res = $query->execute($params);
        if ($res == 0) {
            echo "**ERROR EN DELETE EQ_FCO CODIGO_UNI=" . $Fco["codigo"] . "\n";
        }
        echo "DELETE EQ_FCO CODIGO_UNI=" . $Fco["codigo"] . "\n";
    } catch (PDOException $ex) {
        echo "**PDOERROR EN DELETE EQ_FCO"
        . " CODIGO_UNI=" . $Fco["codigo"]
        . " \t " . $ex->getMessage()
        . " \n";
    }
}

function insertEqFco($Fco) {
    global $connInte;
    for ($i = 0; $i < 12; $i++) {
        try {
            $sentencia = "insert into eq_fco "
                    . " (edificio, codigo_loc, codigo_uni) "
                    . " values "
                    . " (:edificio, :codigo_loc, :codigo_uni) ";
            $query = $connInte->prepare($sentencia);
            $params = array(":edificio" => $i,
                ":codigo_loc" => $Fco["codigo"],
                ":codigo_uni" => $Fco["codigo"]);
            $res = $query->execute($params);
            if ($res == 0) {
                echo "**ERROR EN INSERT EQ_FCO EDIFICIO= " . $i . " CODIGO_LOC= " . $Fco["codigo"] . " CODIGO_UNI=" . $Fco["codigo"] . "\n";
            }
            echo "INSERT EQ_FCO EDIFICIO= " . $i . " CODIGO_LOC= " . $Fco["codigo"] . " CODIGO_UNI=" . $Fco["codigo"] . "\n";
        } catch (PDOException $ex) {
            echo "**PDOERROR EN INSERT EQ_FCO EDIFICIO= " . $i
            . " CODIGO_LOC= " . $Fco["codigo"]
            . " CODIGO_UNI=" . $Fco["codigo"]
            . " \t  " . $ex->getMessage()
            . " \n";
        }
    }
}

function procesoInsert($Fco) {
    global $BasesDatos, $connUnif;
    insertFco($connUnif, $Fco);
    foreach ($BasesDatos as $baseDatos) {
        $alias = $baseDatos["alias"];
        $datosConexion["maquina"] = $baseDatos["maquina"];
        $datosConexion["puerto"] = $baseDatos["puerto"];
        $datosConexion["servidor"] = $baseDatos["servidor"];
        $datosConexion["esquema"] = $baseDatos["esquema"];
        $datosConexion["usuario"] = $baseDatos["usuario"];
        $datosConexion["password"] = $baseDatos["password"];
        $conexion = conexionPDO($datosConexion);
        insertFco($conexion, $Fco);
    }
    insertEqFco($Fco);
}

function procesoUpdate($Fco) {
    global $BasesDatos, $connUnif;
    updateFco($connUnif, $Fco, $Fco["codigo"]);
    foreach ($BasesDatos as $baseDatos) {
        $alias = $baseDatos["alias"];
        $datosConexion["maquina"] = $baseDatos["maquina"];
        $datosConexion["puerto"] = $baseDatos["puerto"];
        $datosConexion["servidor"] = $baseDatos["servidor"];
        $datosConexion["esquema"] = $baseDatos["esquema"];
        $datosConexion["usuario"] = $baseDatos["usuario"];
        $datosConexion["password"] = $baseDatos["password"];
        $conexion = conexionPDO($datosConexion);
        $EqFcoALL = selectEqFco($baseDatos["edificio"], $Fco["codigo"]);
        if ($EqFcoALL) {
            foreach ($EqFcoALL as $linea) {
                updateFco($conexion, $Fco, $linea["CODIGO_LOC"]);
            }
        }
    }
}

function procesoDelete($Fco) {
    global $BasesDatos, $connUnif;
    deleteFco($connUnif, $Fco["codigo"]);

    foreach ($BasesDatos as $baseDatos) {
        $alias = $baseDatos["alias"];
        $datosConexion["maquina"] = $baseDatos["maquina"];
        $datosConexion["puerto"] = $baseDatos["puerto"];
        $datosConexion["servidor"] = $baseDatos["servidor"];
        $datosConexion["esquema"] = $baseDatos["esquema"];
        $datosConexion["usuario"] = $baseDatos["usuario"];
        $datosConexion["password"] = $baseDatos["password"];
        $conexion = conexionPDO($datosConexion);
        $EqFcoALL = selectEqFco($baseDatos["edificio"], $Fco["codigo"]);
        if ($EqFcoALL) {
            foreach ($EqFcoALL as $linea) {
                deleteFco($conexion, $linea["CODIGO_LOC"]);
            }
        }
    }
    deleteEqFco($Fco);
}

function insertFco($conexion, $Fco) {
    try {
        $sentencia = "insert into fco "
                . " ( codigo, descrip, propietario, soli_origen, enuso, fcorptid, fcorpt_codigo, fcorpt_descripcion ) values "
                . " ( :codigo, :descrip, :propietario, :soli_origen, :enuso, :fcorptid, :fcorpt_codigo, :fcorpt_descripcion ) ";
        $query = $conexion->prepare($sentencia);
        $params = array(":codigo" => $Fco["codigo"],
            ":descrip" => $Fco["descrip"],
            ":propietario" => $Fco["propietario"],
            ":soli_origen" => $Fco["soli_origen"],
            ":enuso" => $Fco["enuso"],
            ":fcorptid" => $Fco["fcorptid"],
            ":fcorpt_codigo" => $Fco["fcorpt_codigo"],
            ":fcorpt_descripcion" => $Fco["fcorpt_descripcion"]);

        $res = $query->execute($params);
        if ($res == 0) {
            echo "**ERROR EN INSERT FCO CODIGO= " . $Fco["codigo"] . " DESCRIPCION= " . $Fco["descrip"] . "\n";
            return null;
        }
        echo "INSERT FCO CODIGO= " . $Fco["codigo"] . " DESCRIPCION= " . $Fco["descrip"] . "\n";
        return true;
    } catch (PDOException $ex) {
        echo "**PDOERROR EN INSERT FCO CODIGO= " . $Fco["codigo"]
        . " DESCRIPCION= " . $Fco["descrip"]
        . " \t  "
        . $ex->getMessage() . "\n";
        return null;
    }
}

function updateFco($conexion, $Fco, $codigoLoc) {
    try {
        $sentencia = " update fco  set "
                . "  descrip = :descrip "
                . ", propietario = :propietario "
                . ", soli_origen = :soli_origen "
                . ", enuso = :enuso "
                . ", fcorptid = :fcorptid "
                . ", fcorpt_codigo = :fcorpt_codigo "
                . ", fcorpt_descripcion = :fcorpt_descripcion "
                . " where codigo = :codigo ";

        $query = $conexion->prepare($sentencia);
        $params = array(":codigo" => $codigoLoc,
            ":descrip" => $Fco["descrip"],
            ":propietario" => $Fco["propietario"],
            ":soli_origen" => $Fco["soli_origen"],
            ":enuso" => $Fco["enuso"],
            ":fcorptid" => $Fco["fcorptid"],
            ":fcorpt_codigo" => $Fco["fcorpt_codigo"],
            ":fcorpt_descripcion" => $Fco["fcorpt_descripcion"]);

        $res = $query->execute($params);
        if ($res == 0) {
            echo "**ERROR EN INSERT FCO CODIGO= " . $codigoLoc . " DESCRIPCION= " . $Fco["descrip"] . "\n";
            return null;
        }
        echo "MODIFICADO FCO CODIGO= " . $codigoLoc . " DESCRIPCION= " . $Fco["descrip"] . "\n";
        return true;
    } catch (PDOException $ex) {
        echo "**PDOERROR EN INSERT FCO CODIGO= " . $codigoLoc
        . " DESCRIPCION= " . $Fco["descrip"]
        . " \t  "
        . $ex->getMessage() . "\n";
        return null;
    }
}

function deleteFco($conexion, $codigo) {
    try {
        $sentencia = "delete from fco "
                . " where codigo = :codigo ";

        $query = $conexion->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $res = $query->execute($params);
        if ($res == 0) {
            echo "**ERROR EN DELETE FCO CODIGO= " . $codigo . "\n";
            return null;
        }
        echo "DELETE FCO CODIGO= " . $codigo . "\n";
        return true;
    } catch (PDOException $ex) {
        echo "**PDOERROR EN DELETE FCO CODIGO= " . $codigo . "    " . $ex->getMessage() . "\n";
        return null;
    }
}

echo " ++++++ COMIENZA PROCESO SINCRONIZACIÓN FCO +++++++++++ \n";
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
$fco_id = $argv[2];
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

$Fco = selectFcoById($fco_id);
if (!$Fco) {
    exit(1);
}

echo " SINCRONIZACIÓN FCO ID=" . $Fco["id"]
 . " CÓDIGO=" . $Fco["codigo"]
 . " DESCRIPCIÓN= " . $Fco["descrip"]
 . " ACCION =" . $accion
 . "\n";

if ($accion == 'DELETE') {
    if (!procesoDelete($Fco)) {
        exit(1);
    }
}
if ($accion == 'UPDATE') {
    if (!procesoUpdate($Fco)) {
        exit(1);
    }
}

if ($accion == 'INSERT') {
    if (!procesoInsert($Fco)) {
        exit(1);
    }
}

echo " FIN SINCRONIZACIÓN " . "\n";
exit(0);
