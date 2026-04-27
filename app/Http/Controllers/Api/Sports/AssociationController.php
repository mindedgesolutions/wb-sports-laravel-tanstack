<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sports\AssociationRequest;
use App\Models\SpAssociation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssociationController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = SpAssociation::when($search, function ($query, $search) {
            $query->where('name', 'ILIKE', "%{$search}%")
                ->orWhere('address', 'ILIKE', "%{$search}%")
                ->orWhere('website', 'ILIKE', "%{$search}%")
                ->orWhere('email', 'ILIKE', "%{$search}%")
                ->orWhere('phone_1', 'ILIKE', "%{$search}%")
                ->orWhere('phone_2', 'ILIKE', "%{$search}%")
                ->orWhere('fax', 'ILIKE', "%{$search}%");
        })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'total' => $data->total(),
            ]
        ], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function store(AssociationRequest $request)
    {
        $data = SpAssociation::create([
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'address' => $request->address ? trim($request->address) : null,
            'website' => $request->website ? trim($request->website) : null,
            'email' => $request->email ? trim($request->email) : null,
            'phone_1' => $request->phone_1 ? trim($request->phone_1) : null,
            'phone_2' => $request->phone_2 ? trim($request->phone_2) : null,
            'fax' => $request->fax ? trim($request->fax) : null,
        ]);

        if ($request->hasFile('newImg') && $request->file('newImg')->getSize() > 0) {
            $file = $request->file('newImg');
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/sports/associations';

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            $filePath = $file->storeAs($directory, $filename, 'public');

            SpAssociation::whereId($data->id)->update([
                'logo' => Storage::url($filePath)
            ]);
        }
        return response()->json(['data' => $data], Response::HTTP_CREATED);
    }

    // --------------------------------------------

    public function update(Request $request, string $id)
    {
        $data = SpAssociation::findOrFail($id);

        SpAssociation::create([
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'address' => $request->address ? trim($request->address) : null,
            'website' => $request->website ? trim($request->website) : null,
            'email' => $request->email ? trim($request->email) : null,
            'phone_1' => $request->phone_1 ? trim($request->phone_1) : null,
            'phone_2' => $request->phone_2 ? trim($request->phone_2) : null,
            'fax' => $request->fax ? trim($request->fax) : null,
        ]);

        if ($request->hasFile('logo') && $request->file('logo')[0]->getSize() > 0) {
            $file = $request->file('logo')[0];
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/sports/associations';

            if ($data->logo) {
                $deletePath = str_replace('/storage', '', $data->logo);
                if (Storage::disk('public')->exists($deletePath)) {
                    Storage::disk('public')->delete($deletePath);
                }
            }

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            $filePath = $file->storeAs($directory, $filename, 'public');

            SpAssociation::whereId($data->id)->update([
                'logo' => Storage::url($filePath)
            ]);
        }
        return response()->json(['data' => $data], Response::HTTP_CREATED);
    }

    // --------------------------------------------

    public function destroy(string $id)
    {
        $data = SpAssociation::findOrFail($id);

        if ($data->logo) {
            $deletePath = str_replace('/storage', '', $data->logo);
            if (Storage::disk('public')->exists($deletePath)) {
                Storage::disk('public')->delete($deletePath);
            }
        }
        $data->delete();

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function toggle(Request $request, $id)
    {
        SpAssociation::whereId($id)->update(['is_active' => $request->checked]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}
