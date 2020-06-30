<?php

namespace App\Http\Controllers;

use App\Services\CommunicateToTauri;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class GenerateFileController extends Controller
{
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

    protected $servers = [
        '[EN] Evermoon',
        '[HU] Tauri WoW Server',
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->tauri = new CommunicateToTauri();
    }

    /**
     * Handle the incoming data.
     *
     * @param  array $data
     * @return \Illuminate\Http\Response
     */
    public function __invoke($data)
    {
        $auctionsByFaction = collect($data['response']['auctions']);
        $lastModified = $data['response']['lastModified'];

        $array = $auctionsByFaction->mapWithKeys(function ($auctions, $faction) {
            // If neutral auction continue
            if ($faction == 'auctioner_7') {
                return [];
            }

            $data = collect($auctions)->map(function ($auction) {
                //Price per item
                $auction['buyout'] = $auction['buyout']/$auction['stackCount'];

                //Get only i need
                return array_intersect_key($auction, array_flip([
                    'item',
                    'buyout',
                ]));
            })
            ->groupBy('item')
            ->map(function ($auctions) {
                /**
                 * m => Marker Value.
                 * b => Min Buyout.
                 * n => Number of auctions.
                 */
                return [
                    'm' => $this->calculateMarketValue($auctions->pluck('buyout')),
                    'b' => $auctions->min('buyout'),
                    'n' => $auctions->count(),
                ];
            });

            //Get the current faction name
            $faction = [
                'auctioner_2' => 'alliance',
                'auctioner_6' => 'horde',
            ][$faction];

            return [$faction => $data];
        })->filter();

        $appData = '';

        foreach ($this->servers as $server) {
            $appData .= "[\"{$server}-Both-{$lastModified}\"] = '{$array->toJson()}',";
        }

        $contents = "-- This file is populated automatically by the TradeSkillMaster Application\n-- and should not be manually modified.\nlocal TSM = select(2, ...)\nTSM.AppData = {\n\t{$appData}\n}";

        Storage::disk('public')->put('AppData.lua', $contents);

        return 'done';
    }

    /**
     * Calculate the market value of a given values
     *
     * @param \Illuminate\Support\Collection $prices
     * @return int
     */
    public function calculateMarketValue(Collection $prices)
    {
        $previousPrice = 0;
        $price = 0;
        $passes = collect();

        for ($i=0; $i < $prices->count(); $i++) {
            $previousPrice = $price;
            $price = $prices[$i];

            if ($i < $prices->count() * 0.15 || $previousPrice * 1.2 >= $price) {
                $passes->push($price);
                continue;
            }
            break;
        }

        $passes = $this->deleteAtypical($passes);

        return round($passes->avg(), 0);
    }

    /**
     * Throws out any data points that are more than 1.5 times
     * the standard deviation away from the average.
     *
     * @param \Illuminate\Support\Collection $prices
     * @return \Illuminate\Support\Collection
     */
    public function deleteAtypical(Collection $prices)
    {
        $average = $prices->sum()/$prices->count();
        $standardDeviation = $this->standardDeviation($prices->toArray());
        $standardDeviation *= 1.5;

        return collect($prices)->reject(function ($price) use ($average, $standardDeviation) {
            return !(abs($price - $average) >= $standardDeviation);
        });
    }

    /**
     * Function to calculate the standard deviation
     * of array elements.
     *
     * @param array $prices
     * @return float
     */
    public function standardDeviation($prices)
    {
        $numOfElements = count($prices);

        $variance = 0.0;

        // Calculating mean using array_sum() method
        $average = array_sum($prices)/$numOfElements;

        foreach ($prices as $price) {
            // sum of squares of differences between
            // all prices and means.
            $variance += pow(($price - $average), 2);
        }

        return (float)sqrt($variance/$numOfElements);
    }
}
