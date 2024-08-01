<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ReportResource",
 *     type="object",
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Response status"
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         description="Response message"
 *     ),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         description="Data payload",
 *         @OA\Property(
 *             property="current_page",
 *             type="integer",
 *             description="Current page number"
 *         ),
 *         @OA\Property(
 *             property="data",
 *             type="array",
 *             @OA\Items(type="object")
 *         ),
 *         @OA\Property(
 *             property="first_page_url",
 *             type="string",
 *             description="First page URL"
 *         ),
 *         @OA\Property(
 *             property="from",
 *             type="integer",
 *             description="Start of records"
 *         ),
 *         @OA\Property(
 *             property="last_page",
 *             type="integer",
 *             description="Last page number"
 *         ),
 *         @OA\Property(
 *             property="last_page_url",
 *             type="string",
 *             description="Last page URL"
 *         ),
 *         @OA\Property(
 *             property="links",
 *             type="array",
 *             @OA\Items(type="object")
 *         ),
 *         @OA\Property(
 *             property="next_page_url",
 *             type="string",
 *             description="Next page URL"
 *         ),
 *         @OA\Property(
 *             property="path",
 *             type="string",
 *             description="API path"
 *         ),
 *         @OA\Property(
 *             property="per_page",
 *             type="integer",
 *             description="Records per page"
 *         ),
 *         @OA\Property(
 *             property="prev_page_url",
 *             type="string",
 *             description="Previous page URL"
 *         ),
 *         @OA\Property(
 *             property="to",
 *             type="integer",
 *             description="End of records"
 *         ),
 *         @OA\Property(
 *             property="total",
 *             type="integer",
 *             description="Total records"
 *         )
 *     ),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         description="Error details"
 *     )
 * )
 */
class ReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => 'success',
            'message' => 'Report generated successfully.',
            'data' => $this->resource,
            'errors' => null
        ];
    }
}
