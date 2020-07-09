<?php

namespace App\Console\Commands;

use App\Http\Controllers\GenerateFileController;
use App\Services\CommunicateToTauri;
use Illuminate\Console\Command;

class GetDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:auctions-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the auction data from the tauri api';

    /**
     * Params of the request
     *
     * @var array
     */
    protected $request = [
        'url'    => 'auctions-data',
        'params' => [
            "r" => "[HU] Tauri WoW Server"
        ]
    ];

    /**
     * Tauri service to communicate to the API.
     *
     * @var \App\Services\CommunicateToTauri
     */
    protected $tauri;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->tauri = new CommunicateToTauri();
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $response = $this->tauri->communicate($this->request);
        //For local purposes
        // $response = json_decode(Storage::disk('public')->get('response.json'), true);

        $generate = new GenerateFileController();
        $generate->__invoke($response);
    }
}
