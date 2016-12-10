<?php

use Illuminate\Database\Seeder;
use App\GoVoyage\Library\TransaviaApi;
use App\Airport;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // fetch airports from transavia API
        $transavia = new TransaviaApi(env('TRANSAVIA_API_ENDPOINT'), env('TRANSAVIA_API_ID'), env('TRANSAVIA_API_KEY'));
        $res = $transavia->request('/v1/airports', []);
        foreach ($res->data as $airport) {
            Airport::create([
                'code' => $airport->locationCode,
                'name' => $airport->name,
                'city' => $airport->city,
                'country_code' => $airport->country->code,
                'country_name' => $airport->country->name,
                'latitude' => $airport->geoCoordinates->latitude,
                'longitude' => $airport->geoCoordinates->longitude,
            ]);
        }

        // $this->call(UsersTableSeeder::class);
    }
}
