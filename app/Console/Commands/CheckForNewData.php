<?php

namespace App\Console\Commands;

use App\Services\CommunicateToTauri;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class CheckForNewData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:auctions-info';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the basic information from the auction house';

    /**
     * Params of the request.
     *
     * @var array
     */
    protected $request = [
        'url'    => 'auctions-info',
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
        $data = $this->tauri->communicate($this->request);

        if ($data['success'] == false) {
            return;
        }

        if ($this->newAuctionsAvailable($data['response']['lastModified'])) {
            Artisan::call('get:auctions-data');
        }
    }

    /**
     * Determines if the auction has been updated.
     *
     * @param int $auctionLastModified
     */
    public function newAuctionsAvailable($auctionLastModified)
    {
        if (! Storage::disk('public')->exists('AppData.lua')) {
            return true;
        }

        $auctionLastModified = Carbon::createFromTimestamp(
            $auctionLastModified
        );

        $fileLastModified = Carbon::createFromTimestamp(
            Storage::disk('public')->lastModified('AppData.lua')
        );

        return $auctionLastModified->greaterThan($fileLastModified);
    }
}
