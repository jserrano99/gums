<?php

include_once __DIR__ . '/funcionesDAO.php';

function selectEqMoviPat($edificio, $codigo_uni) {
    global $connInte;
    try {
        $sentencia = " select * from eq_movipat  "
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
            echo "** ERROR EN SELECT EQ_MOVIPAT CODIGO_UNI = " . $codigo_uni . " EDIFICIO= " . $edificio . "\n";
            return null;
        }
    } catch (PDOException $ex) {
        echo "**PDOERROR EN SELECT EQ_MOVIPAT CODIGO_UNI = " . $codigo_uni . " EDIFICIO= " . $edificio . " " . $ex->getMessage() . "\n";
        return null;
    }
}

function deleteEqMoviPat($MoviPat) {
    global $connInte;
    try {
        $sentencia = " delete from eq_movipat where codigo_uni = :codigo_uni";
        $query = $connInte->prepare($sentencia);
        $params = array(":codigo_uni" => $MoviPat["codigo"]);
        $res = $query->execute($params);
        if ($res == 0) {
            echo "**ERROR EN DELETE EQ_MOVIPAT CODIGO_UNI=" . $MoviPat["codigo"] . "\n";
        }
        echo "DELETE EQ_MOVIPAT CODIGO_UNI=" . $MoviPat["codigo"] . "\n";
    } catch (PDOException $ex) {
        echo "**PDOERROR EN DELETE EQ_MOVIPAT EDIFICIO= " . $i
        . " CODIGO_LOC= " . $MoviPat["codigo"]
        . " CODIGO_UNI=" . $MoviPat["codigo"]
        . " \t " . $ex->getMessage()
        . " \n";
    }
}

function insertEqMoviPat($MoviPat) {
    global $connInte;
    for ($i = 0; $i < 12; $i++) {
        try {
            $sentencia = "insert into eq_movipat "
                    . " (edificio, codigo_loc, codigo_uni) "
                    . " values "
                    . " (:edificio, :codigo_loc, :codigo_uni) ";
            $query = $connInte->prepare($sentencia);
            $params = array(":edificio" => $i,
                ":codigo_loc" => $MoviPat["codigo"],
                ":codigo_uni" => $MoviPat["codigo"]);
            $res = $query->execute($params);
            if ($res == 0) {
                echo "**ERROR EN INSERT EQ_MOVIPAT EDIFICIO= " . $i . " CODIGO_LOC= " . $MoviPat["codigo"] . " CODIGO_UNI=" . $MoviPat["codigo"] . "\n";
            }
            echo "INSERT EQ_MOVIPAT EDIFICIO= " . $i . " CODIGO_LOC= " . $MoviPat["codigo"] . " CODIGO_UNI=" . $MoviPat["codigo"] . "\n";
        } catch (PDOException $ex) {
            echo "**PDOERROR EN INSERT EQ_MOVIPAT EDIFICIO= " . $i
            . " CODIGO_LOC= " . $MoviPat["codigo"]
            . " CODIGO_UNI=" . $MoviPat["codigo"]
            . " \t  " . $ex->getMessage()
            . " \n";
        }
    }
}

function procesoUpdate($MoviPat) {
    global $BasesDatos, $connUnif;
    updateMoviPat($connUnif, $MoviPat);
    foreach ($BasesDatos as $baseDatos) {
        $alias = $baseDatos["alias"];
        $datosConexion["maquina"] = $baseDatos["maquina"];
        $datosConexion["puerto"] = $baseDatos["puerto"];
        $datosConexion["servidor"] = $baseDatos["servidor"];
        $datosConexion["esquema"] = $baseDatos["esquema"];
        $datosConexion["usuario"] = $baseDatos["usuario"];
        $datosConexion["password"] = $baseDatos["password"];
        $conexion = conexionPDO($datosConexion);
        updateMoviPat($conexion, $MoviPat);
    }
}

function procesoInsert($MoviPat) {
    global $BasesDatos, $connUnif;
    insertMoviPat($connUnif, $MoviPat);
    foreach ($BasesDatos as $baseDatos) {
        $alias = $baseDatos["alias"];
        $datosConexion["maquina"] = $baseDatos["maquina"];
        $datosConexion["puerto"] = $baseDatos["puerto"];
        $datosConexion["servidor"] = $baseDatos["servidor"];
        $datosConexion["esquema"] = $baseDatos["esquema"];
        $datosConexion["usuario"] = $baseDatos["usuario"];
        $datosConexion["password"] = $baseDatos["password"];
        $conexion = conexionPDO($datosConexion);
        insertMoviPat($conexion, $MoviPat);
    }
    insertEqMoviPat($MoviPat);
}

function procesoDelete($MoviPat) {
    global $BasesDatos, $connUnif;
    deleteMoviPat($connUnif, $MoviPat["codigo"]);

    foreach ($BasesDatos as $baseDatos) {
        $alias = $baseDatos["alias"];
        $datosConexion["maquina"] = $baseDatos["maquina"];
        $datosConexion["puerto"] = $baseDatos["puerto"];
        $datosConexion["servidor"] = $baseDatos["servidor"];
        $datosConexion["esquema"] = $baseDatos["esquema"];
        $datosConexion["usuario"] = $baseDatos["usuario"];
        $datosConexion["password"] = $baseDatos["password"];
        $conexion = conexionPDO($datosConexion);
        $codigo = selectEqMoviPat($baseDatos["edificio"], $MoviPat["codigo"]);
        if ($codigo) {
            deleteMoviPat($conexion, $codigo);
        }
    }
    deleteEqMoviPat($MoviPat);
}

function updateMoviPat($conexion, $MoviPat) {
    try {
        $sentencia = " update movipat set "
                . " descrip = :descrip"
                . " ,cif = :cif"
                . " ,pat_contin = :pat_contin"
                . " ,obr_contin = :obr_contin"
                . " ,pat_he= :pat_he"
                . " ,obr_he = :obr_he"
                . " ,pat_acc = :pat_acc"
                . " ,obr_acc = :obr_acc"
                . " ,pat_fp = :pat_fp"
                . " ,obr_fp = :obr_fp"
                . " ,fogasa = :fogasa"
                . " ,numeroseg = :numeroseg"
                . " ,empresa = :empresa"
                . " ,pat_munpal = :pat_munpal"
                . " ,obr_munpal = :obr_munpal"
                . " ,pat_integra = :pat_integra"
                . " ,enuso = :enuso"
                . " ,clave = :clave"
                . " ,eventual = :eventual"
                . " ,porcent = :porcent" 
                . " ,pat_acc_ant = :pat_acc_ant"
                . " ,forzar_l00 = :forzar_l00"
                . " where codigo = :codigo";
                
        $query = $conexion->prepare($sentencia);
        $params = array(":codigo" => $MoviPat["codigo"],
            ":descrip" => $MoviPat["descrip"],
            ":cif" => $MoviPat["cif"],
            ":pat_contin" => $MoviPat["pat_contin"],
            ":obr_contin" => $MoviPat["obr_contin"],
            ":pat_he" => $MoviPat["pat_he"],
            ":obr_he" => $MoviPat["obr_he"],
            ":pat_acc" => $MoviPat["pat_acc"],
            ":obr_acc" => $MoviPat["obr_acc"],
            ":pat_fp" => $MoviPat["pat_fp"],
            ":obr_fp" => $MoviPat["obr_fp"],
            ":fogasa" => $MoviPat["fogasa"],
            ":numeroseg" => $MoviPat["numeroseg"],
            ":empresa" => $MoviPat["empresa"],
            ":pat_munpal" => $MoviPat["pat_munpal"],
            ":obr_munpal" => $MoviPat["obr_munpal"],
            ":pat_integra" => $MoviPat["pat_integra"],
            ":enuso" => $MoviPat["enuso"],
            ":clave" => $MoviPat["clave"],
            ":eventual" => $MoviPat["eventual"],
            ":porcent" => $MoviPat["porcent"],
            ":pat_acc_ant" => $MoviPat["pat_acc_ant"],
            ":forzar_l00" => $MoviPat["forzar_l00"]);
        $res = $query->execute($params);
        if ($res == 0) {
            echo "**ERROR EN UPDATE MOVIPAT CODIGO= " . $MoviPat["codigo"] . " DESCRIPCION= " . $MoviPat["descrip"] . "\n";
            return null;
        }
        echo "MODIFICADA CUENTA COTIZACIÓN MOVIPAT CODIGO= " . $MoviPat["codigo"] . " DESCRIPCION= " . $MoviPat["descrip"] . "\n";
        return true;
    } catch (PDOException $ex) {
        echo "**PDOERROR EN UPDATE MOVIPAT CODIGO= " . $MoviPat["codigo"]
        . " DESCRIPCION= " . $MoviPat["descrip"]
        . " \n  "
        . $ex->getMessage() . "\n";
        return null;
    }
}

function insertMoviPat($conexion, $MoviPat) {
    try {
        $sentencia = " insert into movipat "
                . " (codigo, descrip, cif, pat_contin, obr_contin, pat_he, obr_he, pat_acc, obr_acc  "
                . " ,pat_fp ,obr_fp ,fogasa ,numeroseg, empresa, pat_munpal, obr_munpal, pat_integra  "
                . " ,enuso ,clave ,eventual, porcent, pat_acc_ant, forzar_l00 ) values "
                . " (:codigo, :descrip, :cif, :pat_contin, :obr_contin, :pat_he, :obr_he, :pat_acc, :obr_acc  "
                . " , :pat_fp , :obr_fp , :fogasa , :numeroseg, :empresa, :pat_munpal, :obr_munpal, :pat_integra  "
                . " , :enuso , :clave , :eventual, :porcent, :pat_acc_ant, :forzar_l00 )";
        $query = $conexion->prepare($sentencia);
        $params = array(":codigo" => $MoviPat["codigo"],
            ":descrip" => $MoviPat["descrip"],
            ":cif" => $MoviPat["cif"],
            ":pat_contin" => $MoviPat["pat_contin"],
            ":obr_contin" => $MoviPat["obr_contin"],
            ":pat_he" => $MoviPat["pat_he"],
            ":obr_he" => $MoviPat["obr_he"],
            ":pat_acc" => $MoviPat["pat_acc"],
            ":obr_acc" => $MoviPat["obr_acc"],
            ":pat_fp" => $MoviPat["pat_fp"],
            ":obr_fp" => $MoviPat["obr_fp"],
            ":fogasa" => $MoviPat["fogasa"],
            ":numeroseg" => $MoviPat["numeroseg"],
            ":empresa" => $MoviPat["empresa"],
            ":pat_munpal" => $MoviPat["pat_munpal"],
            ":obr_munpal" => $MoviPat["obr_munpal"],
            ":pat_integra" => $MoviPat["pat_integra"],
            ":enuso" => $MoviPat["enuso"],
            ":clave" => $MoviPat["clave"],
            ":eventual" => $MoviPat["eventual"],
            ":porcent" => $MoviPat["porcent"],
            ":pat_acc_ant" => $MoviPat["pat_acc_ant"],
            ":forzar_l00" => $MoviPat["forzar_l00"]);
        $res = $query->execute($params);
        if ($res == 0) {
            echo "**ERROR EN INSERT MOVIPAT CODIGO= " . $MoviPat["codigo"] . " DESCRIPCION= " . $MoviPat["descrip"] . "\n";
            return null;
        }
        echo "INSERT MOVIPAT CODIGO= " . $MoviPat["codigo"] . " DESCRIPCION= " . $MoviPat["descrip"] . "\n";
        return true;
    } catch (PDOException $ex) {
        echo "**PDOERROR EN INSERT MOVIPAT CODIGO= " . $MoviPat["codigo"]
        . " DESCRIPCION= " . $MoviPat["descrip"]
        . " \t  "
        . $ex->getMessage() . "\n";
        return null;
    }
}

function deleteMoviPat($conexion, $codigo) {
    try {
        $sentencia = "delete from movipat "
                . " where codigo = :codigo ";

        $query = $conexion->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $res = $query->execute($params);
        if ($res == 0) {
            echo "**ERROR EN DELETE MOVIPAT CODIGO= " . $codigo . "\n";
            return null;
        }
        echo "DELETE MOVIPAT CODIGO= " . $codigo . "\n";
        return true;
    } catch (PDOException $ex) {
        echo "**PDOERROR EN DELETE MOVIPAT CODIGO= " . $codigo . "    " . $ex->getMessage() . "\n";
        return null;
    }
}

echo " ++++++ COMIENZA PROCESO SINCRONIZACIÓN MOVIPAT +++++++++++ \n";
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
$movipat_id = $argv[2];
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

$MoviPat = selectMoviPatById($movipat_id);
if (!$MoviPat) {
    exit(1);
}

echo " SINCRONIZACIÓN : ID=" . $MoviPat["id"]
 . " CÓDIGO=" . $MoviPat["codigo"]
 . " DESCRIPCIÓN= " . $MoviPat["descrip"]
 . " ACCION =" . $accion
 . "\n";

if ($accion == 'DELETE') {
    if (!procesoDelete($MoviPat)) {
        exit(1);
    }
}

if ($accion == 'INSERT') {
    if (!procesoInsert($MoviPat)) {
        exit(1);
    }
}

if ($accion == 'UPDATE') {
    if (!procesoUpDate($MoviPat)) {
        exit(1);
    }
}


echo " FIN SINCRONIZACIÓN " . "\n";
exit(0);
