<?php

include_once __DIR__ . '/funcionesDAO.php';

function componerSelect($tabla) {

    if ($tabla == 'epiacc') {
        $sentencia = " select epigrafe as codigo, ilt, ims, ilt_ant, ims_ant, 'EPIGRAFES' as descrip from epiacc";
    }
    if ($tabla == 'ocupacion') {
        $sentencia = " select codigo as codigo, descripcion as descrip from ocupacion";
    }

    return $sentencia;
}

function componerInsert($tabla) {
    if ($tabla == 'epiacc') {
        $sentencia = " insert into gums_epiacc ( codigo, ilt, ims, ilt_ant, ims_ant )  values ( :codigo, :ilt, :ims, :ilt_ant, :ims_ant)";
    }
    if ($tabla == 'ocupacion') {
        $sentencia = " insert into gums_ocupacion ( codigo, descripcion) values (:codigo, :descripcion)";
    }

    return $sentencia;
}

function componerInsertEq($tabla) {
    if ($tabla == 'epiacc') {
        $sentencia = " insert into gums_eq_epiacc ( edificio_id, codigo_loc, epiAcc_id ) values  ( :edificio_id, :codigo_loc, :codigo_uni_id )";
    }
    if ($tabla == 'ocupacion') {
        $sentencia = " insert into gums_eq_ocupacion ( edificio_id, codigo_loc, ocupacion_id ) values  ( :edificio_id, :codigo_loc, :codigo_uni_id )";
    }

    return $sentencia;
}

function componerParams($tabla) {
    global $registro;
    if ($tabla == 'epiacc') {
        $sentencia = array(":codigo" => $registro["CODIGO"],
            ":ilt" => $registro["ILT"],
            ":ims" => $registro["IMS"],
            ":ilt_ant" => $registro["ILT_ANT"],
            ":ims_ant" => $registro["IMS_ANT"]);
    }
    if ($tabla == 'ocupacion') {
        $sentencia = array(":codigo" => $registro["CODIGO"],
            ":descripcion" => $registro["DESCRIP"]);
    }

    return $sentencia;
}


function comprobarExiste($codigo) {
    global $connGums, $tabla;
    try {
        $sentencia = " select * from gums_" . $tabla . " where codigo = :codigo";
        $query = $connGums->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $query->execute($params);
        $res = $query->fetch(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $ex) {
        echo "** ERROR EN SELECT GUMS_TIPO_ILT CODIGO= " . $codigo . " " . $ex->getMessage() . "\n";
        return null;
    }
}

function selectEquivalencias($codigo) {
    global $connInte, $tabla;
    try {
        $sentencia = " select * from eq_" . $tabla . " where codigo_uni = :codigo";
        $query = $connInte->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $query->execute($params);
        $res = $query->fetchALL(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $ex) {
        echo "** ERROR EN SELECT EQ_" . $tabla . " CODIGO= " . $codigo . " " . $ex->getMessage() . "\n";
        return null;
    }
}

/*
 * Cuerpo Principal 
 */

$tabla = $argv[1];
$tipo = $argv[2];

echo " --> CARGA INICIAL TABLA:  GUMS_" . $tabla . " MODO: " . $tipo . "\n";

$connGums = connGums();
if (!$connGums) {
    exit(111);
}
/*
 * recogemos el parametro para ver si estamos en pruebas en validación o en producción
 */

if ($tipo == 'REAL') {
    echo " ENTORNO = PRODUCCIÓN \n";
    $connInte = conexionPDO(SelectBaseDatos(2, 'I'));
    $connUnif = conexionPDO(SelectBaseDatos(2, 'U'));
} else {
    echo " ENTORNO = VALIDACIÓN \n";
    $connInte = conexionPDO(SelectBaseDatos(1, 'I'));
    $connUnif = conexionPDO(SelectBaseDatos(1, 'U'));
}
/*
 * INICIALIZAMOS LA TABLA Y SU CORRESPONDIENTE DE EQUIVALENCIAS 
 */
try {
    $sentencia = " delete from gums_eq_" . $tabla;
    $query = $connGums->prepare($sentencia);
    $query->execute();
} catch (PDOException $ex) {
    echo "***PDOERROR EN DELETE GUMS_EQ_" . $tabla . " " . $ex->getMessage() . "\n";
    exit(1);
}

try {
    $sentencia = " delete from gums_" . $tabla;
    $query = $connGums->prepare($sentencia);
    $query->execute();
} catch (PDOException $ex) {
    echo "***PDOERROR EN DELETE GUMS_" . $tabla . " " . $ex->getMessage() . "\n";
    exit(1);
}


try {
    $sentencia = componerSelect($tabla);
    $query = $connUnif->prepare($sentencia);
    $query->execute();
    $registrosAll = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    echo "***PDOERROR EN LA SELECT DE " . $tabla . " BASE DE DATOS UNIFICADA " . $ex->getMessage() . "\n";
    exit(1);
}

echo " Registros a Cargar = " . count($registrosAll) . "\n";

foreach ($registrosAll as $registro) {
    $yaExiste = comprobarExiste($registro["CODIGO"]);
    if (!$yaExiste) {
        try {
            $sentencia = componerInsert($tabla);

            $query = $connGums->prepare($sentencia);
            $params = componerParams($tabla);
            $res = $query->execute($params);

            if ($res == 0) {
                echo "**ERROR EN INSERT GUMS_" . $tabla . " CODIGO= " . $registro["CODIGO"] . " \n";
                continue;
            }
            $registro["ID"] = $connGums->lastInsertId();
            echo " CREADO GUMS_" . $tabla . " ID= " . $registro["ID"] . " CODIGO= " . $registro["CODIGO"] . " " . $registro["DESCRIP"] . "\n";

            $equivalenciasAll = selectEquivalencias($registro["CODIGO"]);
            if ($equivalenciasAll) {
                foreach ($equivalenciasAll as $equivalencia) {
                    $edificio = selectEdificio($equivalencia["EDIFICIO"]);
                    try {
                        $sentencia = componerInsertEq($tabla);
                        $query = $connGums->prepare($sentencia);
                        $params = $sentencia = array(":edificio_id" => $edificio["id"],
                            ":codigo_loc" => $equivalencia["CODIGO_LOC"],
                            ":codigo_uni_id" => $registro["ID"]);


                        $res = $query->execute($params);
                        if ($res == 0) {
                            echo "***ERROR EN INSERT GUMS_EQ_" . $tabla . " EDIFICIO= " . $edificio["id"] . " CODIGO_LOC = " . $equivalencia["CODIGO_LOC"] . "\n";
                        }
                        echo "GENERADA EQUIVALENCIA GUMS_EQ_" . $tabla . " EDIFICIO = " . $edificio["id"] . " CODIGO_LOC = " . $equivalencia["CODIGO_LOC"] . "\n";
                    } catch (PDOException $ex) {
                        echo "***PDOERROR EN INSERT GUMS_EQ_" . $tabla . " " . $ex->getMessage() . "\n";
                        continue;
                    }
                }
            }
        } catch (PDOException $ex) {
            echo "***PDOERROR EN INSERT GUMS_" . $tabla . " CODIGO= " . $registro["CODIGO"] . " " . $ex->getMessage() . "\n";
        }
    }
}

echo " --->TERMINADA LA CARGA DE GUMS_" . $tabla . "\n";
exit(0);

