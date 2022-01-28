<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AngularController extends Controller
{
    /**
     * Serve the Angular frontend's index file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        return File::get(public_path() . '/angular-assets/index.html');
    }
}
