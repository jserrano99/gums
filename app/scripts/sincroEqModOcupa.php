<?php

include_once __DIR__ . '/funcionesDAO.php';

function procesoDelete($EqModOcupa) {
    global $connInte;
    try {
        $sentencia = " delete from eq_modocupa where "
                . " edificio = :edificio "
                . " and codigo_loc = :codigo_loc "
                . " and codigo_uni = :codigo_uni ";
        $query = $connInte->prepare($sentencia);
        $params = array(":edificio" => $EqModOcupa["edificio"],
            ":codigo_loc" => $EqModOcupa["codigo_loc"],
            ":codigo_uni" => $EqModOcupa["codigo_uni"]);
        $delete = $query->execute($params);
        if ($delete == 0) {
            echo "***ERROR EN DELETE EQ_MODOCUPA edificio= " . $EqModOcupa["edificio"]
            . " codigo_loc = " . $EqModOcupa["codigo_loc"]
            . " codigo_uni = " . $EqModOcupa["codigo_uni"]
            . "\n";
            return null;
        }
        echo "DELETE EQ_MODOCUPA edificio= " . $EqModOcupa["edificio"]
        . " codigo_loc = " . $EqModOcupa["codigo_loc"]
        . " codigo_uni = " . $EqModOcupa["codigo_uni"]
        . "\n";
        return true;
        
    } catch (PDOException $ex) {
        echo "***PDOERROR EN DELETE EQ_MODOCUPA EN BD INTERMEDIA " . $ex->getMessage() . "\n";
        return null;
    }
}

function procesoInsert($EqModOcupa) {
    global $connInte;
    try {
        $sentencia = " insert into eq_modocupa "
                . " ( edificio, codigo_loc, codigo_uni)  values "
                . " ( :edificio, :codigo_loc, :codigo_uni) ";
        $query = $connInte->prepare($sentencia);
        $params = array(":edificio" => $EqModOcupa["edificio"],
            ":codigo_loc" => $EqModOcupa["codigo_loc"],
            ":codigo_uni" => $EqModOcupa["codigo_uni"]);
        $res = $query->execute($params);
        if ($res == 0) {
            echo "***ERROR EN INSERT EQ_MODOCUPA edificio= " . $EqModOcupa["edificio"]
            . " codigo_loc = " . $EqModOcupa["codigo_loc"]
            . " codigo_uni = " . $EqModOcupa["codigo_uni"]
            . "\n";
            return null;
        }
        echo "INSERT EQ_MODOCUPA edificio= " . $EqModOcupa["edificio"]
        . " codigo_loc = " . $EqModOcupa["codigo_loc"]
        . " codigo_uni = " . $EqModOcupa["codigo_uni"]
        . "\n";
        return true;
        
    } catch (PDOException $ex) {
        echo "***PDOERROR EN INSERT EQ_MODOCUPA EN BD INTERMEDIA " . $ex->getMessage() . "\n";
        return null;
    }
}

echo " ++++++ COMIENZA PROCESO SINCRONIZACIÓN EQ_MODOCUPA +++++++++++ \n";
/*
 * Conexión a la base de datos de Control en Mysql 
 */
$connGums = connGums();
if (!$connGums) {
    exit(111);
}

/*
 * recogemos el parametro para ver si estamos en pruebas en validación o en producción
 */
$tipo = $argv[1];
$eqmodocupa_id = $argv[2];
$accion = $argv[3];

if ($tipo == 'REAL') {
    echo " ENTORNO = PRODUCCIÓN \n";
    $connInte = conexionPDO(SelectBaseDatos(2, 'I'));
} else {
    echo " ENTORNO = VALIDACIÓN \n";
    $connInte = conexionPDO(SelectBaseDatos(1, 'I'));
}

$EqModOcupa = selectEqModOcupaById($eqmodocupa_id);
if (!$EqModOcupa) {
    exit(1);
}

echo " SINCRONIZACIÓN : ID=" . $EqModOcupa["id"]
 . " EDIFICIO=" . $EqModOcupa["edificio"]
 . " CODIGO_LOC= " . $EqModOcupa["codigo_loc"]
 . " CODIGO_UNI= " . $EqModOcupa["codigo_uni"]
 . " ACCION =" . $accion
 . "\n";

if ($accion == 'DELETE') {
    if (!procesoDelete($EqModOcupa)) {
        exit(1);
    }
}

if ($accion == 'INSERT') {
    if (!procesoInsert($EqModOcupa)) {
        exit(1);
    }
}

echo " FIN SINCRONIZACIÓN " . "\n";
exit(0);
