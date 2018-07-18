<?php

include_once __DIR__.'/funcionesDAO.php';

function selectModOcupa($codigo) {
    global $connGums;
    try {
        $sentencia = " select * from gums_modocupa where codigo = :codigo";
        $query = $connGums->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $query->execute($params);
        $res = $query->fetch(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $ex) {
        echo "** ERROR EN SELECT GUMS_MODOCUPA CODIGO= ".$codigo." ".$ex->getMessage()."\n";
        return null;
    }
}

function selectEqModOcupa($codigo){
    global $connInte;
    try {
        $sentencia = " select * from eq_modocupa where codigo_uni = :codigo";
        $query = $connInte->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $query->execute($params);
        $res = $query->fetchALL(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $ex) {
        echo "** ERROR EN SELECT EQ_MODOCUPA CODIGO= ".$codigo." ".$ex->getMessage()."\n";
        return null;
    }
}
/*
 * Carga Inicial de la Tabla ModOcupa y la correspondiente Tabla de Equivalencias
 */


/*
 * Cuerpo Principal 
 */

echo " -- CARGA INICIAL TABLA: gums_modocupa " ."\n";
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
    $sentencia = " delete from gums_eq_modocupa";
    $query = $connGums->prepare ($sentencia);
    $query->execute();
} catch (PDOException $ex) {
    echo "***PDOERROR EN DELETE GUMS_EQ_MODOCUPA ".$ex->getMessage()."\n";
    exit(1);
}

try {
    $sentencia = " delete from gums_modocupa";
    $query = $connGums->prepare ($sentencia);
    $query->execute();
} catch (PDOException $ex) {
    echo "***PDOERROR EN DELETE GUMS_MODOCUPA ".$ex->getMessage()."\n";
    exit(1);
}


try {
    $sentencia = " select * from modocupa";
    $query = $connUnif->prepare ($sentencia);
    $query->execute();
    $modOcupaAll = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    echo "***PDOERROR EN LA SELECT DE MODOCUPA BASE DE DATOS UNIFICADA ".$ex->getMessage()."\n";
    exit(1);
}

echo " Registros a Cargar = ". count($modOcupaAll)."\n";
foreach ($modOcupaAll as $modOcupa) {
    $yaExiste = selectModOcupa($modOcupa["CODIGO"]);
    if (!$yaExiste) {
        try {
            $sentencia = " insert into gums_modocupa "
                    ." ( codigo, descrip, fie ) values ( :codigo,:descrip, :fie) ";
            $insert = $connGums->prepare($sentencia);
            $params = array(":codigo" => $modOcupa["CODIGO"],
                            ":descrip" => $modOcupa["DESCRIP"],
                            ":fie" => $modOcupa["FIE"]);
            $res = $insert->execute($params);
            if ($res == 0 ) {
                echo "**ERROR EN INSERT GUMS_MODOCUPA CODIGO= ".$modOcupa["CODIGO"]." \n";
                continue;
            }
            $modOcupa["ID"] = $connGums->lastInsertId();
            echo " CREADO GUMS_MODOCUPA ID= " .$modOcupa["ID"]. " CODIGO= ".$modOcupa["CODIGO"]." ". $modOcupa["DESCRIP"]."\n";
            
            $eqModOcupaAll = selectEqModOcupa($modOcupa["CODIGO"]);
            foreach ($eqModOcupaAll as $eqModOcupa) {
                $edificio = selectEdificio($eqModOcupa["EDIFICIO"]);
                try {
                    $sentencia = " insert into gums_eq_modocupa "
                            ." (edificio_id, codigo_loc, modocupa_id ) values  ( :edificio_id, :codigo_loc, :modocupa_id )";
                    $insert = $connGums->prepare($sentencia);
                    $params = array(":edificio_id" => $edificio["id"],
                                    ":codigo_loc" => $eqModOcupa["CODIGO_LOC"],
                                    ":modocupa_id" => $modOcupa["ID"]);
                    $res = $insert->execute($params); 
                    if ($res == 0 ) {
                        echo "***ERROR EN INSERT GUMS_EQ_MODOCUPA EDIFICI O= ".$edificio["id"]. " CODIGO_LOC = ".$eqModOcupa["CODIGO_LOC"]. "\n";
                    }
                    echo "GENERADA EQUIVALENCIA GUMS_EQ_MODOCUPA EDIFICIO = ".$edificio["id"]. " CODIGO_LOC = ".$eqModOcupa["CODIGO_LOC"]. "\n";
                } catch (PDOException $ex) {
                    echo "***PDOERROR EN INSERT GUMS_EQ_MODOCUPA ". $ex->getMessage(). "\n";
                    continue;
                }
            }
        } catch (PDOException $ex) {
            echo "***PDOERROR EN INSERT GUMS_MODOCUPA CODIGO= ".$modOcupa["CODIGO"]. " ".$ex->getMessage()."\n";
        }
    }
}

echo " TERMINADA LA CARGA DE MODOCUPA ". "\n";
exit(0);

