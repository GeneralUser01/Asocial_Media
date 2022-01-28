<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiCatchAllController extends Controller
{
    /**
     * Catches all unknown requests to the "api/" URL.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        abort(404);
    }
}
