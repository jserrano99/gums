<?php

include_once __DIR__.'/funcionesDAO.php';

function selectFco($codigo) {
    global $connGums;
    try {
        $sentencia = " select * from gums_fco where codigo = :codigo";
        $query = $connGums->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $query->execute($params);
        $res = $query->fetch(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $ex) {
        echo "** ERROR EN SELECT GUMS_FCO CODIGO= ".$codigo." ".$ex->getMessage()."\n";
        return null;
    }
}

function selectEqFco($codigo){
    global $connInte;
    try {
        $sentencia = " select * from eq_fco where codigo_uni = :codigo";
        $query = $connInte->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $query->execute($params);
        $res = $query->fetchALL(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $ex) {
        echo "** ERROR EN SELECT EQ_FCO CODIGO= ".$codigo." ".$ex->getMessage()."\n";
        return null;
    }
}
/*
 * Carga Inicial de la Tabla Fco y la correspondiente Tabla de Equivalencias
 */


/*
 * Cuerpo Principal 
 */

echo " -- CARGA INICIAL TABLA: GUMS_FCO " ."\n";
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
    $sentencia = " delete from gums_eq_fco";
    $query = $connGums->prepare ($sentencia);
    $query->execute();
} catch (PDOException $ex) {
    echo "***PDOERROR EN DELETE GUMS_EQ_FCO ".$ex->getMessage()."\n";
    exit(1);
}

try {
    $sentencia = " delete from gums_fco";
    $query = $connGums->prepare ($sentencia);
    $query->execute();
} catch (PDOException $ex) {
    echo "***PDOERROR EN DELETE GUMS_FCO ".$ex->getMessage()."\n";
    exit(1);
}


try {
    $sentencia = " select * from fco";
    $query = $connUnif->prepare ($sentencia);
    $query->execute();
    $FcoAll = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    echo "***PDOERROR EN LA SELECT DE FCO BASE DE DATOS UNIFICADA ".$ex->getMessage()."\n";
    exit(1);
}

echo " Registros a Cargar = ". count($FcoAll)."\n";
foreach ($FcoAll as $Fco) {
    $yaExiste = selectFco($Fco["CODIGO"]);
    if (!$yaExiste) {
        try {
            $sentencia = " insert into gums_fco "
                    ." ( codigo, descrip, propietario, soli_origen, enuso, fcorptid, fcorpt_codigo, fcorpt_descripcion) values "
                    ." ( :codigo, :descrip, :propietario, :soli_origen, :enuso, :fcorptid, :fcorpt_codigo, :fcorpt_descripcion)";
                    
            $query = $connGums->prepare($sentencia);
            $params = array(":codigo" => $Fco["CODIGO"],
                            ":descrip" => $Fco["DESCRIP"],
                            ":propietario" => $Fco["PROPIETARIO"],
                            ":soli_origen" => $Fco["SOLI_ORIGEN"], 
                            ":enuso" => $Fco["ENUSO"], 
                            ":fcorptid" => $Fco["FCORPTID"], 
                            ":fcorpt_codigo" => $Fco["FCORPT_CODIGO"], 
                            ":fcorpt_descripcion" => $Fco["FCORPT_DESCRIPCION"]);
            $res = $query->execute($params);
            if ($res == 0 ) {
                echo "**ERROR EN INSERT GUMS_FCO CODIGO= ".$Fco["CODIGO"]." \n";
                continue;
            }
            $Fco["ID"] = $connGums->lastInsertId();
            echo " CREADO GUMS_FCO ID= " .$Fco["ID"]. " CODIGO= ".$Fco["CODIGO"]." ". $Fco["DESCRIP"]."\n";
            
            $eqFcoAll = selectEqFco($Fco["CODIGO"]);
            foreach ($eqFcoAll as $eqFco) {
                $edificio = selectEdificio($eqFco["EDIFICIO"]);
                try {
                    $sentencia = " insert into gums_eq_fco "
                            ." (edificio_id, codigo_loc, fco_id ) values  ( :edificio_id, :codigo_loc, :fco_id )";
                    $insert = $connGums->prepare($sentencia);
                    $params = array(":edificio_id" => $edificio["id"],
                                    ":codigo_loc" => $eqFco["CODIGO_LOC"],
                                    ":fco_id" => $Fco["ID"]);
                    $res = $insert->execute($params); 
                    if ($res == 0 ) {
                        echo "***ERROR EN INSERT GUMS_EQ_FCO EDIFICI O= ".$edificio["id"]. " CODIGO_LOC = ".$eqFco["CODIGO_LOC"]. "\n";
                    }
                    echo "GENERADA EQUIVALENCIA GUMS_EQ_FCO EDIFICIO = ".$edificio["id"]. " CODIGO_LOC = ".$eqFco["CODIGO_LOC"]. "\n";
                } catch (PDOException $ex) {
                    echo "***PDOERROR EN INSERT GUMS_EQ_FCO ". $ex->getMessage(). "\n";
                    continue;
                }
            }
        } catch (PDOException $ex) {
            echo "***PDOERROR EN INSERT GUMS_FCO CODIGO= ".$Fco["CODIGO"]. " ".$ex->getMessage()."\n";
        }
    }
}

echo " TERMINADA LA CARGA DE FCO ". "\n";
exit(0);

