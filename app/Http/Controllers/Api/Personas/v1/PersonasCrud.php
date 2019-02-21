<?php

namespace App\Http\Controllers\Api\Personas\v1;

use App\Ciudades;
use App\Http\Controllers\Api\Personas\v1\Request\PersonasCrudIndexReq;
use App\Http\Controllers\Api\Personas\v1\Request\PersonasCrudStoreReq;
use App\Http\Controllers\Api\Utilities\DefaultValidator;
use App\Http\Controllers\Api\Utilities\WithOnDemand;
use App\Http\Controllers\Controller;
use App\Personas;
use App\UserSocial;
use Illuminate\Http\Request;

class PersonasCrud extends Controller
{
    public function __construct(Request $req)
    {
        $this->middleware('jwt.social',['except'=>['index','show','store']]);
    }

    public function index(PersonasCrudIndexReq $req)
    {
        $with = WithOnDemand::set(['Ciudad'], request('with'));
        $persona = Personas::with($with);

        $persona->when(request('id'), function ($q, $v) {
            return $q->findOrFail($v);
        });

        $persona->when(request('documento_nro'), function ($q, $v) {
            return $q->where('documento_nro',$v);
        });

        $persona->when(request('nombres'), function ($q, $v) {
            return $q->where('nombres','like', "%$v%")
                ->orWhere('apellidos','like',"%$v%");
        });

        // Request toma el valor 0 (cero) como falso, lo que impide filtrar alumno=0
        if(request('alumno') != null){
            $persona->where('alumno',request('alumno'));
        }
        if(request('familiar') != null){
            $persona->where('familiar',request('familiar'));
        }

        return $persona->customPagination(request('por_pagina'));
    }

    // Create
    public function store(PersonasCrudStoreReq $req)
    {
        $ciudad = Ciudades::where('nombre',request('ciudad'))->first();

        // Verificar existencia de la persona, segun DNI
        $persona = Personas::where('documento_nro',request('documento_nro'))->first();

        // Si no existe la persona... se crea!
        if(!$persona) {
            // Se agrega el campo ciudad_id al request
            $req->merge(["ciudad_id"=>$ciudad->id]);
            // Se crea la persona
            $persona = Personas::create($req->all());
        }

        // Ejecutar solo cuando el TOKEN pertenezca a UserSocial
        //$this->executeUserSocialJwt($persona);

        return compact('persona');
    }

    // View
    public function show($id)
    {
        $with = WithOnDemand::set(['Ciudad'], request('with'));

        // Se validan los parametros
        $input = ['id'=>$id];
        $rules = ['id'=>'numeric'];

        if($fail = DefaultValidator::make($input,$rules)) return $fail;

        // Continua si las validaciones son efectuadas
        $persona = Personas::with($with);
        return $persona->findOrFail($id);
    }

    private function executeUserSocialJwt($persona)
    {
        // La persona existe en este punto
        // si es familiar, y no es un alumno, se relaciona con el UserSocial (tutor)
        if($persona != null && $persona->familiar && !$persona->alumno) {
            $this->updatePersonaIdFromUserSocial($persona->id);
        }

      $validationRules = [
            'id' => 'numeric'
        ];

        // Se validan los parametros
        $validator = Validator::make(['id'=>$id], $validationRules);
        if ($validator->fails()) {
            return [
                'error' => 'Parametros invalidos',
                'message' => $validator->errors()
            ];
        }

        $persona = Personas::with(['Ciudad']);
        return $persona->where('id',$id)->first();
    }

    private function updatePersonaIdFromUserSocial($persona_id) {
        // la variable jwt_user es enviada por el middleware, luego de verificar el token
        $jwt_user = (object) request('jwt_user');
        if($jwt_user->id)
        {
            $socialUser = UserSocial::where('id',$jwt_user->id)->first();
            $socialUser->persona_id = $persona_id;
            $socialUser->save();
        }
    }
}
