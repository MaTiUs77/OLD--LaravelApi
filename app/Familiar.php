<?php

namespace App;

use App\Traits\CustomPaginationScope;
use Illuminate\Database\Eloquent\Model;

class Familiar extends Model
{
    use CustomPaginationScope;
    protected $table = 'familiars';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

    protected $fillable = [
        'persona_id','vinculo','conviviente','autorizado_retirar','observaciones'
    ];


    function Persona()
    {
        return $this->belongsTo('App\Personas', 'persona_id', 'id');
    }
}
