<?php

namespace App;

use App\Traits\CustomPaginationScope;
use Illuminate\Database\Eloquent\Model;

class Inscripcions extends Model
{
    use CustomPaginationScope;

    protected $table = 'inscripcions';
    public $timestamps = false;

    function Alumno()
    {
        return $this->hasOne('App\Alumnos', 'id', 'alumno_id');
    }

    function Hermano()
    {
        return $this->hasOne('App\Alumnos', 'id', 'hermano_id');
    }

    function Ciclo()
    {
        return $this->hasOne('App\Ciclos', 'id', 'ciclo_id');
    }

    function Centro()
    {
        return $this->hasOne('App\Centros', 'id', 'centro_id');
    }

   function Promocion()
    {
        return $this->hasOne('App\CursosInscripcions', 'id', 'promocionado');
    }

    function CursosInscripcions()
    {
        return $this->belongsTo('App\CursosInscripcions', 'id', 'inscripcion_id');
    }
}
