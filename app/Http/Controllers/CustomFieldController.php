<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use Illuminate\Http\Request;

class CustomFieldController extends Controller
{
    public function index()
    {
        return view('custom_fields.index');
    }

    public function fetch()
    {
        $fields = CustomField::all();

        return response()->json([
            'status' => 'success',
            'fields' => $fields
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,textarea,date,number',
        ]);

        $data = $validated + [
            'is_required' => $request->has('is_required'),
            'show_on_table' => $request->has('show_on_table'),
        ];

        $field = CustomField::updateOrCreate(['id' => $request->id], $data);

        return response()->json(['status' => 'success', 'field' => $field]);
    }

    public function destroy($id)
    {
        $field = CustomField::findOrFail($id);
        $field->delete();

        return response()->json(['status' => 'success', 'message' => 'Field deleted successfully.']);
    }
}
