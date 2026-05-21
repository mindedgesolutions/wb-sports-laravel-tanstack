<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sports\ContactRequest;
use App\Models\SpContact;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ContactController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = SpContact::when($search, function ($query, $search) {
            $query->where('title', 'ILIKE', "%{$search}%")
                ->orWhere('description', 'ILIKE', "%{$search}%");
        })
            ->orderBy('show_order')
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

    // --------------------------------------------

    public function store(ContactRequest $request)
    {
        SpContact::create([
            'name' => trim($request->name),
            'designation' => trim($request->designation),
            'department' => config('lookup.department'),
            'address' => $request->address ? trim($request->address) : null,
            'email' => $request->email ? $request->email : null,
            'phone_1' => $request->phone_1 ? $request->phone_1 : null,
            'phone_2' => $request->phone_2 ? $request->phone_2 : null,
            'fax' => $request->fax ? $request->fax : null,
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // --------------------------------------------

    public function update(ContactRequest $request, string $id)
    {
        SpContact::whereId($id)->update([
            'name' => trim($request->name),
            'designation' => trim($request->designation),
            'department' => config('lookup.department'),
            'address' => $request->address ? trim($request->address) : null,
            'email' => $request->email ? $request->email : null,
            'phone_1' => $request->phone_1 ? $request->phone_1 : null,
            'phone_2' => $request->phone_2 ? $request->phone_2 : null,
            'fax' => $request->fax ? $request->fax : null,
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // --------------------------------------------

    public function destroy(string $id)
    {
        SpContact::whereId($id)->delete();

        return response()->json(['message' => 'success'], Response::HTTP_NO_CONTENT);
    }

    // --------------------------------------------

    public function toggle(Request $request, string $id)
    {
        SpContact::whereId($id)->update(['is_active' => $request->checked]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function all()
    {
        $data = SpContact::orderBy('show_order')->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function sort(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            SpContact::where('id', $value['id'])->update(['show_order' => $key]);
        }

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}
