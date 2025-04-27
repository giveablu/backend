<?php

namespace App\Http\Controllers\Api\Other;

use App\Models\Tag;
use Illuminate\Http\Request;
use App\Http\Resources\TagResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class SendDataController extends Controller
{
    public function sendTag()
    {
        $tags = Tag::get();
        if ($tags->count() > 0) {
            return TagResource::collection($tags)->additional([
                'response' => true,
                'message' => ['All Tags Found']
            ]);
        } else {
            return response()->json(['response' => false, 'message' => 'No Tags Found']);
        }
    }

    public function sendCountry()
    {
        $countries = collect(json_decode(File::get(storage_path('app/json-db/countries.json')), true));
        return response()->json(['response' => true, 'data' => $countries, 'message' => 'Country List']);
    }
}
