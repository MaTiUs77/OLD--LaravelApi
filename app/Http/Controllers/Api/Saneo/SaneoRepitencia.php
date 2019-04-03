<?php
namespace App\Http\Controllers\Api\Saneo;

use App\Http\Controllers\Api\Utilities\ApiConsume;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SaneoRepitencia extends Controller
{
    public function artisan() {
        $artisan = Artisan::call('siep:saneo_rp', [
            'ciclo' => 2019,
            'por_pagina' => 50,
            'page' => 1
        ]);

        $status = 'Artisan::call';

        return compact('status','artisan');
    }
    public function start($ciclo=2019,$page=1,$por_pagina=10)
    {
        Log::info("SaneoRepitencia::start($ciclo,$page,$por_pagina)");

        if(request('page')) {
            $page = request('page');
        }

        $params = [
            'transform'=>'RepitentesResource',
            'ciclo' => $ciclo,
            'division' => 'con',
            'estado_inscripcion' => 'CONFIRMADA',
            'nivel_servicio' => ['Comun - Primario','Comun - Secundario'],

            'promocion' => 'sin',
            'repitencia' => 'sin',
//            'anio' => 'Sala de 4 años',
            'por_pagina' => $por_pagina,
            'page' => $page,
        ];

        // Consumo API Inscripciones
        $api = new ApiConsume();
        $api->get("inscripcion/lista",$params);
        if($api->hasError()) { return $api->getError(); }
        $response= $api->response();

        Log::info("SaneoRepitencia: ".$page." de ".$response['meta']['last_page']);
        Log::info("=============================================================================");
        Log::info("=============================================================================");

        /*        $data = collect($response['data']);
                $response = ListaAlumnosResource::collection($data);*/

        return $response;
    }
}
