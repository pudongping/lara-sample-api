<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Support\TempValue;
use App\Models\Admin\Setting\Log;
use Cyvelnet\Laravel5Fractal\Facades\Fractal;
use App\Support\Transformer;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Auth\SocialAuthRepository;


class ApiTestsController extends Controller
{

    protected $socialAuthRepository;

    public function __construct(SocialAuthRepository $socialAuthRepository)
    {
        $this->init();
        $this->socialAuthRepository = $socialAuthRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $code = 'BHbbvTSzVp4i0jQJ7Yv3MFCvTTFGGpMoo_9VoQBt3fc';

        $rr = $this->socialAuthRepository->qywxUser($code);

        dd($rr);
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
