<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sports\ContactRequest;
use App\Models\SpContact;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function index()
    {
        $data = SpContact::orderBy('show_order')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function store(ContactRequest $request)
    {
        SpContact::create([
            'name' => trim($request->name),
            'designation' => trim($request->designation),
            'department' => trim($request->department),
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
            'department' => trim($request->department),
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

    public function activate(Request $request, string $id)
    {
        SpContact::whereId($id)->update(['is_active' => $request->is_active]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function contactsSetOrder(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            SpContact::where('id', $value['id'])->update(['show_order' => $key]);
        }

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}
