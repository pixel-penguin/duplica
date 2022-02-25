<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadPhotoRequest;
use App\Http\Requests\CelebrityLookupRequest;

use Illuminate\Support\Facades\Storage;

use Wikidata\Wikidata;

use Illuminate\Http\Request;

use Aws\Rekognition\RekognitionClient;

use App\Http\Resources\CelebrityLookAlikeCollection;
use App\Http\Resources\CelebrityInfoCollection;

use Aws\Rekognition\Exception\RekognitionException;

use Exception;

class UploadPhotoController extends Controller
{

    private $duplicateCelebrities = [];

    //controller to upload the photo
    public function uploadPhoto(UploadPhotoRequest $request)
    {

        $file = $request->imageName;

        $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $file));

        $name = time() . 'image.jpg';

        $filePath = env('APP_ENV') . '/images/' . $name;

        Storage::disk('s3')->put($filePath, $image);

        $url = Storage::temporaryUrl(
            $filePath,
            now()->addMinutes(5)
        );

        $results = $this->returnCelebrityLookalike($filePath);

        if (isset($results['FaceMatches'][0])) {

            $facesArray = [];

            $facesArray = $this->addToFaces($facesArray, $results['FaceMatches'], 0);
            $facesArray = $this->addToFaces($facesArray, $results['FaceMatches'], 19);
            $facesArray = $this->addToFaces($facesArray, $results['FaceMatches'], 39);
            $facesArray = $this->addToFaces($facesArray, $results['FaceMatches'], 59);
            $facesArray = $this->addToFaces($facesArray, $results['FaceMatches'], 79);
            $facesArray = $this->addToFaces($facesArray, $results['FaceMatches'], 99);

            return new CelebrityLookAlikeCollection($facesArray);
        }
    }

    //controller for the celebrity lookup api
    public function celebrityLookup(CelebrityLookupRequest $request)
    {

        $images = explode(',', $request->images);


        $celebrities = [];

        foreach ($images as $image) {

            $celebrity = $this->returnCelebrityMatch($image);

            if ($celebrity != false) {
                $celebrities[] = $celebrity;
            }
        }

        return new CelebrityInfoCollection($celebrities);
    }

    //function to add a face to an array
    private function addToFaces($array, $result, $index)
    {

        if (isset($result[$index])) {
            $array[] = $result[$index];
        }

        return $array;
    }

    //function to get the 100 closest matches of celebrity look alikes
    private function returnCelebrityLookalike($filePath)
    {

        try {
            $client = new RekognitionClient([
                'region'    => 'eu-west-1',
                'version'   => 'latest'
            ]);

            $results = $client->searchFacesByImage([
                "CollectionId" => "swipe-hack",
                "Image" => [
                    'S3Object' => [
                        'Bucket' => 'swipe-who-dis-vapor',
                        'Name' => $filePath
                    ]
                ],
                "FaceMatchThreshold" => 2,
                "MaxFaces" => 100
            ]);
        } catch (RekognitionException  $e) {

            abort(405, $e->getAwsErrorMessage());
           
        }


        return $results;
    }

    //Function to get the celebrity based on the file path which is was from the celebrity collection
    private function returnCelebrityMatch($filePath)
    {
        $client = new RekognitionClient([
            'region'    => 'eu-west-1',
            'version'   => 'latest'
        ]);

        $results = $client->recognizeCelebrities([
            "Image" => [
                'S3Object' => [
                    'Bucket' => 'swipe-who-dis-celebs',
                    'Name' => $filePath
                ]
            ],
            "FaceMatchThreshold" => 10,
            "MaxFaces" => 10
        ]);

        if (isset($results['CelebrityFaces'][0])) {

            $url = $results['CelebrityFaces'][0]['Urls'][0];

            if (!isset($this->duplicateCelebrities[$url])) {
                $this->duplicateCelebrities[$url] = true;

                $results['CelebrityFaces'][0]['imageId'] = $filePath;
                $results['CelebrityFaces'][0]['wikiData'] = $this->getWikiData($url);
                return $results['CelebrityFaces'][0];
            }
        }

        return false;
    }

    //This is a small function where it take the last part of the url which is the ID and get the Celebrity info based on the ID
    private function getWikiData($url)
    {
        $id = array_slice(explode('/', $url), -1)[0];

        $wikidata = new Wikidata();

        $result = $wikidata->get($id);

        return $result;
    }

    public function searchWiki(Request $request)
    {


        $wikidata = new Wikidata();

        $results = $wikidata->get('Q25481');

        dd($results);
    }
}
