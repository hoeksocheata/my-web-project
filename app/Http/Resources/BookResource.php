<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
    return [
            'book_id' => $this->id,
            'book_title' => $this->title,
            'author_name' => $this->author,
            'price_usd' => (double) $this->price,
            // ផ្តល់អត្ថប្រយោជន៍ដោយបោះជា URL ពេញលេញទៅឱ្យ Frontend តែម្តង
            'cover_url' => $this->image ? asset('storage/' . $this->image) : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
