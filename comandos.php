<?php
class Comandos {

    private $comandosTextos = [];

    public function __construct($rutaArchivo) {
        $comandos = array_map('trim', file($rutaArchivo));

        foreach ($comandos as $linea) {
            list($comando, $texto) = explode('#', $linea);
            $this->comandosTextos[$comando] = $texto;
        }
    }

    public function getTextoComando($comando) {
        return isset($this->comandosTextos[$comando]) ? $this->comandosTextos[$comando] : false;
    } 
}
?>
