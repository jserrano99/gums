<?php

include_once __DIR__.'/funcionesDAO.php';

function selectModoPago($codigo) {
    global $connGums;
    try {
        $sentencia = " select * from gums_modopago where codigo = :codigo";
        $query = $connGums->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $query->execute($params);
        $res = $query->fetch(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $ex) {
        echo "** ERROR EN SELECT GUMS_MODOPAGO CODIGO= ".$codigo." ".$ex->getMessage()."\n";
        return null;
    }
}

function selectEqModoPago($codigo){
    global $connInte;
    try {
        $sentencia = " select * from eq_modopago where codigo_uni = :codigo";
        $query = $connInte->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $query->execute($params);
        $res = $query->fetchALL(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $ex) {
        echo "** ERROR EN SELECT EQ_MODOPAGO CODIGO= ".$codigo." ".$ex->getMessage()."\n";
        return null;
    }
}
/*
 * Carga Inicial de la Tabla ModoPago y la correspondiente Tabla de Equivalencias
 */


/*
 * Cuerpo Principal 
 */

echo " -- CARGA INICIAL TABLA:  GUMS_MODOPAGO " ."\n";
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
    $sentencia = " delete from gums_eq_modopago";
    $query = $connGums->prepare ($sentencia);
    $query->execute();
} catch (PDOException $ex) {
    echo "***PDOERROR EN DELETE GUMS_EQ_MODOPAGO ".$ex->getMessage()."\n";
    exit(1);
}

try {
    $sentencia = " delete from gums_modopago";
    $query = $connGums->prepare ($sentencia);
    $query->execute();
} catch (PDOException $ex) {
    echo "***PDOERROR EN DELETE GUMS_MODOPAGO ".$ex->getMessage()."\n";
    exit(1);
}


try {
    $sentencia = " select * from modopago";
    $query = $connUnif->prepare ($sentencia);
    $query->execute();
    $modoPagoAll = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    echo "***PDOERROR EN LA SELECT DE MODOPAGO BASE DE DATOS UNIFICADA ".$ex->getMessage()."\n";
    exit(1);
}

echo " Registros a Cargar = ". count($modoPagoAll)."\n";
foreach ($modoPagoAll as $modoPago) {
    $yaExiste = selectModoPago($modoPago["CODIGO"]);
    if (!$yaExiste) {
        try {
            $sentencia = " insert into gums_modopago "
                    ." ( codigo, descrip, modopago_mes ) values ( :codigo,:descrip, :modopago_mes) ";
            $insert = $connGums->prepare($sentencia);
            $params = array(":codigo" => $modoPago["CODIGO"],
                            ":descrip" => $modoPago["DESCRIP"],
                            ":modopago_mes" => $modoPago["MODOPAGO_MES"]);
            $res = $insert->execute($params);
            if ($res == 0 ) {
                echo "**ERROR EN INSERT GUMS_MODOPAGO CODIGO= ".$modoPago["CODIGO"]." \n";
                continue;
            }
            $modoPago["ID"] = $connGums->lastInsertId();
            echo " CREADO GUMS_MODOPAGO ID= " .$modoPago["ID"]. " CODIGO= ".$modoPago["CODIGO"]." ". $modoPago["DESCRIP"]."\n";
            
            $eqModoPagoAll = selectEqModoPago($modoPago["CODIGO"]);
            foreach ($eqModoPagoAll as $eqModoPago) {
                $edificio = selectEdificio($eqModoPago["EDIFICIO"]);
                try {
                    $sentencia = " insert into gums_eq_modopago "
                            ." (edificio_id, codigo_loc, modopago_id ) values  ( :edificio_id, :codigo_loc, :modopago_id )";
                    $insert = $connGums->prepare($sentencia);
                    $params = array(":edificio_id" => $edificio["id"],
                                    ":codigo_loc" => $eqModoPago["CODIGO_LOC"],
                                    ":modopago_id" => $modoPago["ID"]);
                    $res = $insert->execute($params); 
                    if ($res == 0 ) {
                        echo "***ERROR EN INSERT GUMS_EQ_MODOPAGO EDIFICI O= ".$edificio["id"]. " CODIGO_LOC = ".$eqModoPago["CODIGO_LOC"]. "\n";
                    }
                    echo "GENERADA EQUIVALENCIA GUMS_EQ_MODOPAGO EDIFICIO = ".$edificio["id"]. " CODIGO_LOC = ".$eqModoPago["CODIGO_LOC"]. "\n";
                } catch (PDOException $ex) {
                    echo "***PDOERROR EN INSERT GUMS_EQ_MODOPAGO ". $ex->getMessage(). "\n";
                    continue;
                }
            }
        } catch (PDOException $ex) {
            echo "***PDOERROR EN INSERT GUMS_MODOPAGO CODIGO= ".$registro["CODIGO"]. " ".$ex->getMessage()."\n";
        }
    }
}

echo " TERMINADA LA CARGA DE MODOPAGO ". "\n";
exit(0);

