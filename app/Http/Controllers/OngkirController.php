<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OngkirController extends Controller
{
    /**
     * Get the list of provinces.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProvinsi()
    {
        $response = Http::withHeaders([
            'key' => 'SorsF6KTe3c35e2d68738147EM9ieCYO',
        ])->get('https://api.rajaongkir.com/starter/province');
        if ($response->failed()) {
            return response()->json(['error' => 'Failed to fetch provinces'], 500);
        }
        return response()->json($response['rajaongkir']['results']);
    }

    /**
     * Get the list of cities based on province ID.
     *
     * @param  int  $prov_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getKota($prov_id)
    {
        $response = Http::withHeaders([
            'key' => 'SorsF6KTe3c35e2d68738147EM9ieCYO',
        ])->get("https://api.rajaongkir.com/starter/city?province={$prov_id}");
        if ($response->failed()) {
            return response()->json(['error' => 'Failed to fetch cities'], 500);
        }
        return response()->json($response['rajaongkir']['results']);
    }
}
