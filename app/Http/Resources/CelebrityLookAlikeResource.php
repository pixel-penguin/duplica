<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CelebrityLookAlikeResource extends JsonResource
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
            'imageId'               => $this->resource['Face']['ExternalImageId'],            
            'url'                   => 'https://swipe-who-dis-celebs.s3.eu-west-1.amazonaws.com/'.$this->resource['Face']['ExternalImageId'],
            'percentageMatch'       => $this->resource['Similarity'],
        ];
    }
}
