<?php

include_once __DIR__ . '/funcionesDAO.php';

function selectMoviPat($codigo) {
    global $connGums;
    try {
        $sentencia = " select * from gums_movipat where codigo = :codigo";
        $query = $connGums->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $query->execute($params);
        $res = $query->fetch(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $ex) {
        echo "** ERROR EN SELECT GUMS_MOVIPAT CODIGO= " . $codigo . " " . $ex->getMessage() . "\n";
        return null;
    }
}

function selectEqMoviPat($codigo) {
    global $connInte;
    try {
        $sentencia = " select * from eq_movipat where codigo_uni = :codigo";
        $query = $connInte->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $query->execute($params);
        $res = $query->fetchALL(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $ex) {
        echo "** ERROR EN SELECT EQ_MOVIPAT CODIGO= " . $codigo . " " . $ex->getMessage() . "\n";
        return null;
    }
}

/*
 * Carga Inicial de la Tabla MoviPat y la correspondiente Tabla de Equivalencias
 */


/*
 * Cuerpo Principal 
 */

echo " -- CARGA INICIAL TABLA: gums_movipat " . "\n";
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
    $sentencia = " delete from gums_eq_movipat";
    $query = $connGums->prepare($sentencia);
    $query->execute();
} catch (PDOException $ex) {
    echo "***PDOERROR EN DELETE GUMS_EQ_MOVIPAT " . $ex->getMessage() . "\n";
    exit(1);
}

try {
    $sentencia = " delete from gums_movipat";
    $query = $connGums->prepare($sentencia);
    $query->execute();
} catch (PDOException $ex) {
    echo "***PDOERROR EN DELETE GUMS_MOVIPAT " . $ex->getMessage() . "\n";
    exit(1);
}


try {
    $sentencia = " select * from movipat";
    $query = $connUnif->prepare($sentencia);
    $query->execute();
    $moviPatAll = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    echo "***PDOERROR EN LA SELECT DE MOVIPAT BASE DE DATOS UNIFICADA " . $ex->getMessage() . "\n";
    exit(1);
}

echo " Registros a Cargar = " . count($moviPatAll) . "\n";
foreach ($moviPatAll as $moviPat) {
    $yaExiste = selectMoviPat($moviPat["CODIGO"]);
    if (!$yaExiste) {
        try {
            $sentencia = " insert into gums_movipat "
                    . " (codigo, descrip, cif, pat_contin, obr_contin, pat_he, obr_he, pat_acc, obr_acc  "
                    . " ,pat_fp ,obr_fp ,fogasa ,numeroseg, empresa, pat_munpal, obr_munpal, pat_integra  "
                    . " ,enuso ,clave ,eventual, porcent, pat_acc_ant, forzar_l00 ) values "
                    . " (:codigo, :descrip, :cif, :pat_contin, :obr_contin, :pat_he, :obr_he, :pat_acc, :obr_acc  "
                    . " , :pat_fp , :obr_fp , :fogasa , :numeroseg, :empresa, :pat_munpal, :obr_munpal, :pat_integra  "
                    . " , :enuso , :clave , :eventual, :porcent, :pat_acc_ant, :forzar_l00 )";
            $insert = $connGums->prepare($sentencia);
            $params = array(":codigo" => $moviPat["CODIGO"], 
                            ":descrip" => $moviPat["DESCRIP"], 
                            ":cif"=> $moviPat["CIF"], 
                            ":pat_contin"=> $moviPat["PAT_CONTIN"], 
                            ":obr_contin"=> $moviPat["OBR_CONTIN"], 
                            ":pat_he" => $moviPat["PAT_HE"], 
                            ":obr_he" => $moviPat["OBR_HE"], 
                            ":pat_acc" => $moviPat["PAT_ACC"], 
                            ":obr_acc" => $moviPat["OBR_ACC"],
                            ":pat_fp" => $moviPat["PAT_FP"], 
                            ":obr_fp" => $moviPat["OBR_FP"],
                            ":fogasa" => $moviPat["FOGASA"],
                            ":numeroseg" => $moviPat["NUMEROSEG"], 
                            ":empresa" => $moviPat["EMPRESA"], 
                            ":pat_munpal" => $moviPat["PAT_MUNPAL"],
                            ":obr_munpal" => $moviPat["OBR_MUNPAL"],
                            ":pat_integra" => $moviPat["PAT_INTEGRA"],
                            ":enuso" => $moviPat["ENUSO"], 
                            ":clave" => $moviPat["CLAVE"],
                            ":eventual" => $moviPat["EVENTUAL"],
                            ":porcent" => $moviPat["PORCENT"],
                            ":pat_acc_ant" => $moviPat["PAT_ACC_ANT"],
                            ":forzar_l00" => $moviPat["FORZAR_L00"]);

            $res = $insert->execute($params);
            if ($res == 0) {
                echo "**ERROR EN INSERT GUMS_MOVIPAT CODIGO= " . $moviPat["CODIGO"] . " \n";
                continue;
            }
            $moviPat["ID"] = $connGums->lastInsertId();
            echo " CREADO GUMS_MOVIPAT ID= " . $moviPat["ID"] . " CODIGO= " . $moviPat["CODIGO"] . " " . $moviPat["DESCRIP"] . "\n";

            $eqMoviPatAll = selectEqMoviPat($moviPat["CODIGO"]);
            foreach ($eqMoviPatAll as $eqMoviPat) {
                $edificio = selectEdificio($eqMoviPat["EDIFICIO"]);
                try {
                    $sentencia = " insert into gums_eq_movipat "
                            . " (edificio_id, codigo_loc, movipat_id ) values  ( :edificio_id, :codigo_loc, :movipat_id )";
                    $insert = $connGums->prepare($sentencia);
                    $params = array(":edificio_id" => $edificio["id"],
                        ":codigo_loc" => $eqMoviPat["CODIGO_LOC"],
                        ":movipat_id" => $moviPat["ID"]);
                    $res = $insert->execute($params);
                    if ($res == 0) {
                        echo "***ERROR EN INSERT GUMS_EQ_MOVIPAT EDIFICI O= " . $edificio["id"] . " CODIGO_LOC = " . $eqMoviPat["CODIGO_LOC"] . "\n";
                    }
                    echo "GENERADA EQUIVALENCIA GUMS_EQ_MOVIPAT EDIFICIO = " . $edificio["id"] . " CODIGO_LOC = " . $eqMoviPat["CODIGO_LOC"] . "\n";
                } catch (PDOException $ex) {
                    echo "***PDOERROR EN INSERT GUMS_EQ_MOVIPAT " . $ex->getMessage() . "\n";
                    continue;
                }
            }
        } catch (PDOException $ex) {
            echo "***PDOERROR EN INSERT GUMS_MOVIPAT CODIGO= " . $registro["CODIGO"] . " " . $ex->getMessage() . "\n";
        }
    }
}

echo " TERMINADA LA CARGA DE MOVIPAT " . "\n";
exit(0);

