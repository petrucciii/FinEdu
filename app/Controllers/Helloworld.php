<?php

namespace App\Controllers;

class Helloworld extends BaseController
{
public function index()
    {
        echo 'Hello World! <br> 5Bin 08/02/2025';
    }
public function View()
    {
        echo 'Hello World!';
    }
public function Altro()
    {
        echo 'altro metodo';
    }
public function Metodo($parametro, $valore)
    {
        echo 'controller/metodo/parametro/valore';
        echo '<br>';
        echo $parametro;
        echo '<br>';
        echo $valore;
    }
}