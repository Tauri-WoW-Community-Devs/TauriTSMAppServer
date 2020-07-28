<?php

namespace App\Http\Controllers;

use App\Scan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class ExternalCronController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $scan = Scan::firstOrCreate([
            'name' => 'auctions-data'
        ], [
            'last_scan_at' => Carbon::now()
        ]);

        if ($scan->last_scan_at->diffInMinutes() >= 30) {
            $scan->update(['last_scan_at' => Carbon::now()]);

            Artisan::call('get:auctions-data');
        }
    }
}
