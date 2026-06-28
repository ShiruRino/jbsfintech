<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TransactionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => TransactionResource::collection($this->collection)
        ];
    }
    public function paginationInformation($request, $paginated, $default){
        $default['meta'] = [
            'current_page' => $paginated['current_page'],
            'last_page' => $paginated['last_page'],
            'per_page' => $paginated['per_page'],
            'total' => $paginated['total'],
        ];
        $default['links'] = [
            'next' => $paginated['next_page_url'],
            'prev' => $paginated['prev_page_url'],
        ];
        return $default;
    }
}
