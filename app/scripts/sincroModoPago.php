<?php

include_once __DIR__ . '/funcionesDAO.php';

function selectEqModoPago($edificio, $codigo_uni) {
    global $connInte;
    try {
        $sentencia = " select * from eq_modopago  "
                . "  where codigo_uni = :codigo_uni "
                . " and edificio = :edificio";
        $query = $connInte->prepare($sentencia);
        $params = array(":codigo_uni" => $codigo_uni,
            ":edificio" => $edificio);
        $query->execute($params);
        $res = $query->fetch(PDO::FETCH_ASSOC);
        if ($res) {
            return $res["CODIGO_LOC"];
        } else {
            echo "** ERROR EN SELECT EQ_MODOPAGO CODIGO_UNI = " . $codigo_uni . " EDIFICIO= " . $edificio . "\n";
            return null;
        }
    } catch (PDOException $ex) {
        echo "**PDOERROR EN SELECT EQ_MODOPAGO CODIGO_UNI = " . $codigo_uni . " EDIFICIO= " . $edificio . " " . $ex->getMessage() . "\n";
        return null;
    }
}

function deleteEqModoPago($ModoPago) {
    global $connInte;
    try {
        $sentencia = " delete from eq_modopago where codigo_uni = :codigo_uni";
        $query = $connInte->prepare($sentencia);
        $params = array(":codigo_uni" => $ModoPago["codigo"]);
        $res = $query->execute($params);
        if ($res == 0) {
            echo "**ERROR EN DELETE EQ_MODOPAGO CODIGO_UNI=" . $ModoPago["codigo"] . "\n";
        }
        echo "DELETE EQ_MODOPAGO CODIGO_UNI=" . $ModoPago["codigo"] . "\n";
    } catch (PDOException $ex) {
        echo "**PDOERROR EN DELETE EQ_MODOPAGO EDIFICIO= " . $i
        . " CODIGO_LOC= " . $ModoPago["codigo"]
        . " CODIGO_UNI=" . $ModoPago["codigo"]
        . " \t " . $ex->getMessage()
        . " \n";
    }
}

function insertEqModoPago($ModoPago) {
    global $connInte;
    for ($i = 0; $i < 12; $i++) {
        try {
            $sentencia = "insert into eq_modopago "
                    . " (edificio, codigo_loc, codigo_uni) "
                    . " values "
                    . " (:edificio, :codigo_loc, :codigo_uni) ";
            $query = $connInte->prepare($sentencia);
            $params = array(":edificio" => $i,
                ":codigo_loc" => $ModoPago["codigo"],
                ":codigo_uni" => $ModoPago["codigo"]);
            $res = $query->execute($params);
            if ($res == 0) {
                echo "**ERROR EN INSERT EQ_MODOPAGO EDIFICIO= " . $i . " CODIGO_LOC= " . $ModoPago["codigo"] . " CODIGO_UNI=" . $ModoPago["codigo"] . "\n";
            }
            echo "INSERT EQ_MODOPAGO EDIFICIO= " . $i . " CODIGO_LOC= " . $ModoPago["codigo"] . " CODIGO_UNI=" . $ModoPago["codigo"] . "\n";
        } catch (PDOException $ex) {
            echo "**PDOERROR EN INSERT EQ_MODOPAGO EDIFICIO= " . $i
            . " CODIGO_LOC= " . $ModoPago["codigo"]
            . " CODIGO_UNI=" . $ModoPago["codigo"]
            . " \t  " . $ex->getMessage()
            . " \n";
        }
    }
}

function procesoInsert($ModoPago) {
    global $BasesDatos, $connUnif;
    insertModoPago($connUnif, $ModoPago);
    foreach ($BasesDatos as $baseDatos) {
        $alias = $baseDatos["alias"];
        $datosConexion["maquina"] = $baseDatos["maquina"];
        $datosConexion["puerto"] = $baseDatos["puerto"];
        $datosConexion["servidor"] = $baseDatos["servidor"];
        $datosConexion["esquema"] = $baseDatos["esquema"];
        $datosConexion["usuario"] = $baseDatos["usuario"];
        $datosConexion["password"] = $baseDatos["password"];
        $conexion = conexionPDO($datosConexion);
        insertModoPago($conexion, $ModoPago);
    }
    insertEqModoPago($ModoPago);
}

function procesoDelete($ModoPago) {
    global $BasesDatos, $connUnif;
    deleteModoPago($connUnif, $ModoPago["codigo"]);

    foreach ($BasesDatos as $baseDatos) {
        $alias = $baseDatos["alias"];
        $datosConexion["maquina"] = $baseDatos["maquina"];
        $datosConexion["puerto"] = $baseDatos["puerto"];
        $datosConexion["servidor"] = $baseDatos["servidor"];
        $datosConexion["esquema"] = $baseDatos["esquema"];
        $datosConexion["usuario"] = $baseDatos["usuario"];
        $datosConexion["password"] = $baseDatos["password"];
        $conexion = conexionPDO($datosConexion);
        $codigo = selectEqModoPago($baseDatos["edificio"], $ModoPago["codigo"]);
        if ($codigo) {
            deleteModoPago($conexion, $codigo);
        }
    }
    deleteEqModoPago($ModoPago);
}

function procesoUpdate($ModoPago) {
    global $BasesDatos, $connUnif;
    
    updateModoPago($connUnif, $ModoPago);

    foreach ($BasesDatos as $baseDatos) {
        $alias = $baseDatos["alias"];
        $datosConexion["maquina"] = $baseDatos["maquina"];
        $datosConexion["puerto"] = $baseDatos["puerto"];
        $datosConexion["servidor"] = $baseDatos["servidor"];
        $datosConexion["esquema"] = $baseDatos["esquema"];
        $datosConexion["usuario"] = $baseDatos["usuario"];
        $datosConexion["password"] = $baseDatos["password"];
        $conexion = conexionPDO($datosConexion);
        $codigo = selectEqModoPago($baseDatos["edificio"], $ModoPago["codigo"]);
        if ($codigo) {
            updateModoPago($conexion, $codigo);
        }
    }
}

function insertModoPago($conexion, $ModoPago) {
    try {
        $sentencia = "insert into modopago "
                . " ( codigo, descrip, modopago_mes ) values "
                . " ( :codigo, :descrip, :modopago_mes ) ";
        $query = $conexion->prepare($sentencia);
        $params = array(":codigo" => $ModoPago["codigo"],
            ":descrip" => $ModoPago["descrip"],
            ":modopago_mes" => $ModoPago["modopago_mes"]);
        $res = $query->execute($params);
        if ($res == 0) {
            echo "**ERROR EN INSERT MODOPAGO CODIGO= " . $ModoPago["codigo"] . " DESCRIPCION= " . $ModoPago["descrip"] . "\n";
            return null;
        }
        echo "INSERT MODOPAGO CODIGO= " . $ModoPago["codigo"] . " DESCRIPCION= " . $ModoPago["descrip"] . "\n";
        return true;
    } catch (PDOException $ex) {
        echo "**PDOERROR EN INSERT MODOPAGO CODIGO= " . $ModoPago["codigo"]
        . " DESCRIPCION= " . $ModoPago["descrip"]
        . " \t  "
        . $ex->getMessage() . "\n";
        return null;
    }
}


function updateModoPago($conexion, $ModoPago) {
    try {
        $sentencia = " update modopago set "
                . "  descrip = :descrip "
                . ", modopago_mes = :modopago_mes  "
                . " where codigo = :codigo ";
        $query = $conexion->prepare($sentencia);
        $params = array(":codigo" => $ModoPago["codigo"],
            ":descrip" => $ModoPago["descrip"],
            ":modopago_mes" => $ModoPago["modopago_mes"]);
        $res = $query->execute($params);
        if ($res == 0) {
            echo "**ERROR EN UPDATE MODOPAGO CODIGO= " . $ModoPago["codigo"] . " DESCRIPCION= " . $ModoPago["descrip"] . "\n";
            return null;
        }
        echo "MODIFIFADO MODOPAGO CODIGO= " . $ModoPago["codigo"] . " DESCRIPCION= " . $ModoPago["descrip"] . "\n";
        return true;
    } catch (PDOException $ex) {
        echo "**PDOERROR EN UPDATE MODOPAGO CODIGO= " . $ModoPago["codigo"]
        . " DESCRIPCION= " . $ModoPago["descrip"]
        . " \t  "
        . $ex->getMessage() . "\n";
        return null;
    }
}

function deleteModoPago($conexion, $codigo) {
    try {
        $sentencia = "delete from modopago "
                . " where codigo = :codigo ";

        $query = $conexion->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $res = $query->execute($params);
        if ($res == 0) {
            echo "**ERROR EN DELETE MODOPAGO CODIGO= " . $codigo . "\n";
            return null;
        }
        echo "DELETE MODOPAGO CODIGO= " . $codigo . "\n";
        return true;
    } catch (PDOException $ex) {
        echo "**PDOERROR EN DELETE MODOPAGO CODIGO= " . $codigo . "    " . $ex->getMessage() . "\n";
        return null;
    }
}

echo " ++++++ COMIENZA PROCESO SINCRONIZACIÓN MODOPAGO +++++++++++ \n";
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
$modopago_id = $argv[2];
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

$ModoPago = selectModoPagoById($modopago_id);
if (!$ModoPago) {
    exit(1);
}

echo " SINCRONIZACIÓN : ID=" . $ModoPago["id"]
 . " CÓDIGO=" . $ModoPago["codigo"]
 . " DESCRIPCIÓN= " . $ModoPago["descrip"]
 . " ACCION =" . $accion
 . "\n";

if ($accion == 'DELETE') {
    if (!procesoDelete($ModoPago)) {
        exit(1);
    }
}

if ($accion == 'INSERT') {
    if (!procesoInsert($ModoPago)) {
        exit(1);
    }
}

if ($accion == 'UPDATE') {
    if (!procesoUpdate($ModoPago)) {
        exit(1);
    }
}

echo " FIN SINCRONIZACIÓN " . "\n";
exit(0);
