<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CelebrityInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [

            'name'                  => $this->resource['Name'],
            'urls'                  => $this->resource['Urls'],
            'imageId'               => $this->resource['imageId'],
            'imageUrl'              => 'https://swipe-who-dis-celebs.s3.eu-west-1.amazonaws.com/'.$this->resource['imageId'],
            $this->mergeWhen((isset($this->resource['wikiData']->description)), [
                'description'        => $this->resource['wikiData']->description
            ]),   
        ];
    }
}
