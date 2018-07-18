<?php

include_once __DIR__ . '/funcionesDAO.php';

function selectEqTipoIlt($edificio, $codigo_uni) {
    global $connInte;
    try {
        $sentencia = " select * from eq_tipo_ilt  "
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
            //echo "** ERROR EN SELECT EQ_TIPO_ILT CODIGO_UNI = " . $codigo_uni . " EDIFICIO= " . $edificio . "\n";
            return null;
        }
    } catch (PDOException $ex) {
        echo "**PDOERROR EN SELECT EQ_TIPO_ILT CODIGO_UNI = " . $codigo_uni . " EDIFICIO= " . $edificio . " " . $ex->getMessage() . "\n";
        return null;
    }
}

function deleteEqTipoIlt($TipoIlt) {
    global $connInte;
    try {
        $sentencia = " delete from eq_tipo_ilt where codigo_uni = :codigo_uni";
        $query = $connInte->prepare($sentencia);
        $params = array(":codigo_uni" => $TipoIlt["codigo"]);
        $res = $query->execute($params);
        if ($res == 0) {
            echo "**ERROR EN DELETE EQ_TIPO_ILT CODIGO_UNI=" . $TipoIlt["codigo"] . "\n";
        }
        echo "DELETE EQ_TIPO_ILT CODIGO_UNI=" . $TipoIlt["codigo"] . "\n";
    } catch (PDOException $ex) {
        echo "**PDOERROR EN DELETE EQ_TIPO_ILT EDIFICIO= " . $i
        . " CODIGO_LOC= " . $TipoIlt["codigo"]
        . " CODIGO_UNI=" . $TipoIlt["codigo"]
        . " \t " . $ex->getMessage()
        . " \n";
    }
}

function insertEqTipoIlt($TipoIlt) {
    global $connInte;
    for ($i = 0; $i < 12; $i++) {
        try {
            $sentencia = "insert into eq_tipo_ilt "
                    . " (edificio, codigo_loc, codigo_uni) "
                    . " values "
                    . " (:edificio, :codigo_loc, :codigo_uni) ";
            $query = $connInte->prepare($sentencia);
            $params = array(":edificio" => $i,
                ":codigo_loc" => $TipoIlt["codigo"],
                ":codigo_uni" => $TipoIlt["codigo"]);
            $res = $query->execute($params);
            if ($res == 0) {
                echo "**ERROR EN INSERT EQ_TIPO_ILT EDIFICIO= " . $i . " CODIGO_LOC= " . $TipoIlt["codigo"] . " CODIGO_UNI=" . $TipoIlt["codigo"] . "\n";
            }
            echo "INSERT EQ_TIPO_ILT EDIFICIO= " . $i . " CODIGO_LOC= " . $TipoIlt["codigo"] . " CODIGO_UNI=" . $TipoIlt["codigo"] . "\n";
        } catch (PDOException $ex) {
            echo "**PDOERROR EN INSERT EQ_TIPO_ILT EDIFICIO= " . $i
            . " CODIGO_LOC= " . $TipoIlt["codigo"]
            . " CODIGO_UNI=" . $TipoIlt["codigo"]
            . " \t  " . $ex->getMessage()
            . " \n";
        }
    }
}

function procesoInsert($TipoIlt) {
    global $BasesDatos, $connUnif;
    insertTipoIlt($connUnif, $TipoIlt);
    foreach ($BasesDatos as $baseDatos) {
        $alias = $baseDatos["alias"];
        $datosConexion["maquina"] = $baseDatos["maquina"];
        $datosConexion["puerto"] = $baseDatos["puerto"];
        $datosConexion["servidor"] = $baseDatos["servidor"];
        $datosConexion["esquema"] = $baseDatos["esquema"];
        $datosConexion["usuario"] = $baseDatos["usuario"];
        $datosConexion["password"] = $baseDatos["password"];
        $conexion = conexionPDO($datosConexion);
        insertTipoIlt($conexion, $TipoIlt);
    }
    insertEqTipoIlt($TipoIlt);
}

function procesoDelete($TipoIlt) {
    global $BasesDatos, $connUnif;
    deleteTipoIlt($connUnif, $TipoIlt["codigo"]);

    foreach ($BasesDatos as $baseDatos) {
        $alias = $baseDatos["alias"];
        $datosConexion["maquina"] = $baseDatos["maquina"];
        $datosConexion["puerto"] = $baseDatos["puerto"];
        $datosConexion["servidor"] = $baseDatos["servidor"];
        $datosConexion["esquema"] = $baseDatos["esquema"];
        $datosConexion["usuario"] = $baseDatos["usuario"];
        $datosConexion["password"] = $baseDatos["password"];
        $conexion = conexionPDO($datosConexion);
        $EqTipoIltALL = selectEqTipoIlt($baseDatos["edificio"], $TipoIlt["codigo"]);
        if ($EqTipoIltALL) {
            foreach ($EqTipoIltALL as $linea) {
                deleteTipoIlt($conexion, $linea["CODIGO_LOC"]);
            }
        }
    }
    deleteEqTipoIlt($TipoIlt);
}

function procesoUpdate($TipoIlt) {
    global $BasesDatos, $connUnif;
    updateTipoIlt($connUnif, $TipoIlt, $TipoIlt["codigo"]);

    foreach ($BasesDatos as $baseDatos) {
        $alias = $baseDatos["alias"];
        $datosConexion["maquina"] = $baseDatos["maquina"];
        $datosConexion["puerto"] = $baseDatos["puerto"];
        $datosConexion["servidor"] = $baseDatos["servidor"];
        $datosConexion["esquema"] = $baseDatos["esquema"];
        $datosConexion["usuario"] = $baseDatos["usuario"];
        $datosConexion["password"] = $baseDatos["password"];
        $conexion = conexionPDO($datosConexion);
        $EqTipoIltALL = selectEqTipoIlt($baseDatos["edificio"], $TipoIlt["codigo"]);
        if ($EqTipoIltALL) {
            foreach ($EqTipoIltALL as $linea) {
                updateTipoIlt($conexion, $TipoIlt, $linea["CODIGO_LOC"]);
            }
        }
    }
}

function insertTipoIlt($conexion, $TipoIlt) {
    try {
        $sentencia = "insert into tipo_ilt "
                . " ( codigo, descrip ) values "
                . " ( :codigo, :descrip ) ";
        $query = $conexion->prepare($sentencia);
        $params = array(":codigo" => $TipoIlt["codigo"],
            ":descrip" => $TipoIlt["descrip"],
            ":fie" => $TipoIlt["fie"]);
        $res = $query->execute($params);
        if ($res == 0) {
            echo "**ERROR EN INSERT TIPO_ILT CODIGO= " . $TipoIlt["codigo"] . " DESCRIPCION= " . $TipoIlt["descrip"] . "\n";
            return null;
        }
        echo "INSERT TIPO_ILT CODIGO= " . $TipoIlt["codigo"] . " DESCRIPCION= " . $TipoIlt["descrip"] . "\n";
        return true;
    } catch (PDOException $ex) {
        echo "**PDOERROR EN INSERT TIPO_ILT CODIGO= " . $TipoIlt["codigo"]
        . " DESCRIPCION= " . $TipoIlt["descrip"]
        . " \t  "
        . $ex->getMessage() . "\n";
        return null;
    }
}

function updateTipoIlt($conexion, $TipoIlt, $codigo) {
    try {
        $sentencia = "update tipo_ilt set "
                . " descrip = :descrip "
                . " where codigo = :codigo ";
        $query = $conexion->prepare($sentencia);
        $params = array(":codigo" => $codigo,
            ":descrip" => $TipoIlt["descrip"]);
        $res = $query->execute($params);
        if ($res == 0) {
            echo "**ERROR EN UPDATE TIPO_ILT CODIGO= " . $codigo . " DESCRIPCION= " . $TipoIlt["descrip"] . "\n";
            return null;
        }
        echo "UPDATE TIPO_ILT CODIGO= " . $codigo . " DESCRIPCION= " . $TipoIlt["descrip"] . "\n";
        return true;
    } catch (PDOException $ex) {
        echo "**PDOERROR EN UPDATE TIPO_ILT CODIGO= " . $codigo
        . " DESCRIPCION= " . $TipoIlt["descrip"]
        . " \t  "
        . $ex->getMessage() . "\n";
        return null;
    }
}

function deleteTipoIlt($conexion, $codigo) {
    try {
        $sentencia = "delete from tipo_ilt "
                . " where codigo = :codigo ";

        $query = $conexion->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $res = $query->execute($params);
        if ($res == 0) {
            echo "**ERROR EN DELETE TIPO_ILT CODIGO= " . $codigo . "\n";
            return null;
        }
        echo "DELETE TIPO_ILT CODIGO= " . $codigo . "\n";
        return true;
    } catch (PDOException $ex) {
        echo "**PDOERROR EN DELETE TIPO_ILT CODIGO= " . $codigo . "    " . $ex->getMessage() . "\n";
        return null;
    }
}

echo " ++++++ COMIENZA PROCESO SINCRONIZACIÓN TIPO_ILT +++++++++++ \n";
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
$tipo_ilt_id = $argv[2];
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

$TipoIlt = selectTipoIltById($tipo_ilt_id);
if (!$TipoIlt) {
    exit(1);
}

echo " SINCRONIZACIÓN TIPO_ILT: ID=" . $TipoIlt["id"]
 . " CÓDIGO=" . $TipoIlt["codigo"]
 . " DESCRIPCIÓN= " . $TipoIlt["descrip"]
 . " ACCION =" . $accion
 . "\n";

if ($accion == 'DELETE') {
    if (!procesoDelete($TipoIlt)) {
        exit(1);
    }
}

if ($accion == 'UPDATE') {
    if (!procesoUpdate($TipoIlt)) {
        exit(1);
    }
}

if ($accion == 'INSERT') {
    if (!procesoInsert($TipoIlt)) {
        exit(1);
    }
}

echo " FIN SINCRONIZACIÓN TIPO_ILT" . "\n";
exit(0);
