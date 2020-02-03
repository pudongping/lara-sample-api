<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Support\TempValue;
use App\Models\Admin\Setting\Log;
use Cyvelnet\Laravel5Fractal\Facades\Fractal;
use App\Support\Transformer;
use Illuminate\Database\Eloquent\Collection;


class ApiTestsController extends Controller
{

    public function __construct()
    {
        $this->init();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $logs = Log::paginate(3);

        $this->response->addMetaa(['page' => [1,2,3,4,5,6], 'key2' => 'data2']);
        return $this->response->send($logs, ['client_ip', 'id']);

        $transformer = new Transformer();

        $logs = Log::paginate(3);

        $transformer->addMeta([
            'key1' => 'data1',
            'key2' => 'data2'
        ]);

        $transformer->fieldsets('id,client_ip,created_at');
        $res = $transformer->collection($logs);

        dd($res);

        $result = Log::find(1);
        dd(get_class($result));

    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
