<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Models\SpAssocSite;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AssocSiteController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = SpAssocSite::when($search, function ($query, $search) {
            $query->where('title', 'ILIKE', "%{$search}%");
        })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'total' => $data->total()
            ]
        ], Response::HTTP_OK);
    }

    // ----------------------------------------

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'max:255', function ($attribute, $value, $fail) {
                $inputSlug = Str::slug($value);
                if (SpAssocSite::where('slug', $inputSlug)->exists()) {
                    $fail('Site exists');
                }
            }],
            'url' => 'required|url|regex:/^https?:\/\/([a-z0-9\-]+\.)+[a-z]{2,}(\/[^\s]*)?$/i',
        ], [
            'regex' => 'Invalid URL or domain',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        SpAssocSite::create([
            'title' => trim($request->title),
            'slug' => Str::slug($request->title),
            'url' => $request->url,
        ]);
        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // ----------------------------------------

    public function update(Request $request, string $id)
    {
        Validator::extend('valid_domain', function ($attribute, $value, $parameters, $validator) {
            return checkdnsrr(parse_url($value, PHP_URL_HOST));
        });

        $validator = Validator::make($request->all(), [
            'title' => ['required', 'max:255', function ($attribute, $value, $fail) use ($id) {
                $inputSlug = Str::slug($value);
                if (SpAssocSite::where('slug', $inputSlug)
                    ->where('id', '!=', $id)
                    ->exists()
                ) {
                    $fail('Site exists');
                }
            }],
            'url' => 'required|url|regex:/^https?:\/\/([a-z0-9\-]+\.)+[a-z]{2,}(\/[^\s]*)?$/i',
        ], [
            'regex' => 'Invalid URL or domain',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        SpAssocSite::whereId($id)->update([
            'title' => trim($request->title),
            'slug' => Str::slug($request->title),
            'url' => $request->url,
        ]);
        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // ----------------------------------------

    public function destroy(string $id)
    {
        SpAssocSite::whereId($id)->delete();

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // ----------------------------------------

    public function toggle(Request $request, string $id)
    {
        SpAssocSite::whereId($id)->update(['is_active' => $request->checked]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}
