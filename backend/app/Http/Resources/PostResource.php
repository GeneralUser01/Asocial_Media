<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        /*
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
        */
        $data = parent::toArray($request);
        $data['body'] = $this->user()->first()->scrambleText($enhancedContent = $data['body'], $request->user());

        $startAndEndPreservedWithTheRestRandom = $enhancedContent;
        $startAndEndPreservedWithTheRestRandom = preg_replace("/^-(.*)-$/", Str::random(), $startAndEndPreservedWithTheRestRandom, -1);

        $extraConsonantForDoubleConsonants = $enhancedContent;
        $extraConsonantForDoubleConsonants = preg_replace('"är"', 'ä', $extraConsonantForDoubleConsonants);
        $extraConsonantForDoubleConsonants = preg_replace('"Är"', 'Ä', $extraConsonantForDoubleConsonants);
        $extraConsonantForDoubleConsonants = preg_replace('/e/i', 'ä', $extraConsonantForDoubleConsonants);

        $uwuify = $enhancedContent;
        $faces = [" (・`ω´・) "," ;;w;; "," owo "," UwU "," >w< "," ^w^ "];
        $uwuify = preg_replace('/(?:r|l)/', "w", $uwuify);
        $uwuify = preg_replace('/(?:R|L)/', "W", $uwuify);
        $uwuify = preg_replace('/n([aeiou])/', 'ny$1', $uwuify);
        $uwuify = preg_replace('/N([aeiou])/', 'Ny$1', $uwuify);
        $uwuify = preg_replace('/N([AEIOU])/', 'Ny$1', $uwuify);
        $uwuify = preg_replace('/ove/', "uv", $uwuify);
        $uwuify = preg_replace('/\!+/', $faces[rand(0, 5)], $uwuify);

        $allCaps = $enhancedContent;
        $allCaps = preg_replace_callback('/"[a-z]/', function ($matches) {
            return strtoLower($matches[0]);
        }, strtoUpper($allCaps));

        $enhancedContent = [$startAndEndPreservedWithTheRestRandom, $extraConsonantForDoubleConsonants, $uwuify, $allCaps][rand(0, 3)];

        // Use the post's author to scramble the post's content (optionally customizing based on viewer):
        $data['body'] = $this->user()->first()->scrambleText($enhancedContent, $request->user());
        return $data;
    }
}