<?php

include_once __DIR__.'/funcionesDAO.php';

function selectTipoIlt($codigo) {
    global $connGums;
    try {
        $sentencia = " select * from gums_tipo_ilt where codigo = :codigo";
        $query = $connGums->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $query->execute($params);
        $res = $query->fetch(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $ex) {
        echo "** ERROR EN SELECT GUMS_TIPO_ILT CODIGO= ".$codigo." ".$ex->getMessage()."\n";
        return null;
    }
}

function selectEqTipoIlt($codigo){
    global $connInte;
    try {
        $sentencia = " select * from eq_tipo_ilt where codigo_uni = :codigo";
        $query = $connInte->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $query->execute($params);
        $res = $query->fetchALL(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $ex) {
        echo "** ERROR EN SELECT EQ_TIPO_ILT CODIGO= ".$codigo." ".$ex->getMessage()."\n";
        return null;
    }
}
/*
 * Carga Inicial de la Tabla TipoIlt y la correspondiente Tabla de Equivalencias
 */


/*
 * Cuerpo Principal 
 */

echo " -- CARGA INICIAL TABLA: GUMS_TIPO_ILT " ."\n";
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
    $sentencia = " delete from gums_eq_tipo_ilt";
    $query = $connGums->prepare ($sentencia);
    $query->execute();
} catch (PDOException $ex) {
    echo "***PDOERROR EN DELETE GUMS_EQ_TIPO_ILT ".$ex->getMessage()."\n";
    exit(1);
}

try {
    $sentencia = " delete from gums_tipo_ilt";
    $query = $connGums->prepare ($sentencia);
    $query->execute();
} catch (PDOException $ex) {
    echo "***PDOERROR EN DELETE GUMS_TIPO_ILT ".$ex->getMessage()."\n";
    exit(1);
}


try {
    $sentencia = " select * from tipo_ilt";
    $query = $connUnif->prepare ($sentencia);
    $query->execute();
    $tipoIltAll = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    echo "***PDOERROR EN LA SELECT DE TIPO_ILT BASE DE DATOS UNIFICADA ".$ex->getMessage()."\n";
    exit(1);
}

echo " Registros a Cargar = ". count($tipoIltAll)."\n";
foreach ($tipoIltAll as $tipoIlt) {
    $yaExiste = selectTipoIlt($tipoIlt["CODIGO"]);
    if (!$yaExiste) {
        try {
            $sentencia = " insert into gums_tipo_ilt "
                    ." ( codigo, descrip ) values ( :codigo,:descrip) ";
            $insert = $connGums->prepare($sentencia);
            $params = array(":codigo" => $tipoIlt["CODIGO"],
                            ":descrip" => $tipoIlt["DESCRIP"]);
            $res = $insert->execute($params);
            if ($res == 0 ) {
                echo "**ERROR EN INSERT GUMS_TIPO_ILT CODIGO= ".$tipoIlt["CODIGO"]." \n";
                continue;
            }
            $tipoIlt["ID"] = $connGums->lastInsertId();
            echo " CREADO GUMS_TIPO_ILT ID= " .$tipoIlt["ID"]. " CODIGO= ".$tipoIlt["CODIGO"]." ". $tipoIlt["DESCRIP"]."\n";
            
            $eqTipoIltAll = selectEqTipoIlt($tipoIlt["CODIGO"]);
            foreach ($eqTipoIltAll as $eqTipoIlt) {
                $edificio = selectEdificio($eqTipoIlt["EDIFICIO"]);
                try {
                    $sentencia = " insert into gums_eq_tipo_ilt "
                            ." (edificio_id, codigo_loc, tipoIlt_id ) values  ( :edificio_id, :codigo_loc, :tipoIlt_id )";
                    $insert = $connGums->prepare($sentencia);
                    $params = array(":edificio_id" => $edificio["id"],
                                    ":codigo_loc" => $eqTipoIlt["CODIGO_LOC"],
                                    ":tipoIlt_id" => $tipoIlt["ID"]);
                    $res = $insert->execute($params); 
                    if ($res == 0 ) {
                        echo "***ERROR EN INSERT GUMS_EQ_TIPO_ILT EDIFICIO= ".$edificio["id"]. " CODIGO_LOC = ".$eqTipoIlt["CODIGO_LOC"]. "\n";
                    }
                    echo "GENERADA EQUIVALENCIA GUMS_EQ_TIPO_ILT EDIFICIO = ".$edificio["id"]. " CODIGO_LOC = ".$eqTipoIlt["CODIGO_LOC"]. "\n";
                } catch (PDOException $ex) {
                    echo "***PDOERROR EN INSERT GUMS_EQ_TIPO_ILT ". $ex->getMessage(). "\n";
                    continue;
                }
            }
        } catch (PDOException $ex) {
            echo "***PDOERROR EN INSERT GUMS_TIPO_ILT CODIGO= ".$tipoIlt["CODIGO"]. " ".$ex->getMessage()."\n";
        }
    }
}

echo " TERMINADA LA CARGA DE GUMS_TIPO_ILT ". "\n";
exit(0);

