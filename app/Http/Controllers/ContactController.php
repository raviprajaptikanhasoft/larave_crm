<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactCustomFieldValue;
use App\Models\CustomField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $contacts = Contact::all();
        $customFields = CustomField::all();
        return view('contacts.index', compact('contacts', 'customFields'));
    }

    public function fetch(Request $request)
    {
        try {
            $contacts = Contact::query();

            if ($request->name) {
                $contacts->where('name', 'like', "%{$request->name}%");
            }

            if ($request->email) {
                $contacts->where('email', 'like', "%{$request->email}%");
            }

            if ($request->gender) {
                $contacts->where('gender', $request->gender);
            }

            $data = $contacts->with(['customFields.customField'])->get();

            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            Log::error('Fetch Contacts Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch contacts.'], 500);
        }
    }

    public function show($id)
    {
        try {
            $contact = Contact::with('customFields.customField')->findOrFail($id);
            return response()->json(['contact' => $contact]);
        } catch (\Exception $e) {
            Log::error('Show Contact Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch contact.'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Base validation rules
            $rules = [
                'name' => 'required',
                'email' => ['required', 'email', Rule::unique('contacts', 'email')->ignore($request->id)],
                'phone' => 'required',
                'gender' => 'required',
                'profile_image' => 'nullable|image',
                'additional_file' => 'nullable|file',
            ];

            // Dynamically validate required custom fields
            $requiredFields = CustomField::where('is_required', true)->get();
            foreach ($requiredFields as $field) {
                $fieldKey = "custom_fields.{$field->id}";
                $rules[$fieldKey] = 'required';
                $messages["{$fieldKey}.required"] = "The {$field->name} field is required.";
            }

            $request->validate($rules, $messages);


            // Create or update contact
            $contact = $request->id ? Contact::find($request->id) : new Contact;
            $contact->name = $request->name;
            $contact->email = $request->email;
            $contact->phone = $request->phone;
            $contact->gender = $request->gender;

            if ($request->hasFile('profile_image')) {
                if ($contact->profile_image) {
                    Storage::disk('public')->delete($contact->profile_image);
                }
                $contact->profile_image = $request->file('profile_image')->store('uploads', 'public');
            }

            if ($request->hasFile('additional_file')) {
                if ($contact->additional_file) {
                    Storage::disk('public')->delete($contact->additional_file);
                }
                $contact->additional_file = $request->file('additional_file')->store('uploads', 'public');
            }

            $contact->save();

            // Save custom field values
            if ($request->has('custom_fields')) {
                foreach ($request->custom_fields as $fieldId => $value) {
                    ContactCustomFieldValue::updateOrCreate(
                        [
                            'contact_id' => $contact->id,
                            'custom_field_id' => $fieldId
                        ],
                        [
                            'value' => $value
                        ]
                    );
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => $request->id ? 'Contact updated successfully.' : 'Contact added successfully.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e; // Let Laravel handle validation error response
        } catch (\Exception $e) {
            Log::error('Store Contact Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to save contact.'], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $contact = Contact::findOrFail($id);
            // Delete files
            if ($contact->profile_image) Storage::disk('public')->delete($contact->profile_image);
            if ($contact->additional_file) Storage::disk('public')->delete($contact->additional_file);

            $contact->delete();
            return response()->json(['status' => 'deleted', 'message' => 'Contact deleted.']);
        } catch (\Exception $e) {
            Log::error('Delete Contact Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to delete contact.'], 500);
        }
    }

    public function merge(Request $request)
    {
        try {
            $request->validate([
                'master_id' => 'required|exists:contacts,id',
                'secondary_id' => 'required|exists:contacts,id|different:master_id'
            ]);

            if ($request->master_id == $request->secondary_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Master and Secondary contact cannot be the same.'
                ], 422);
            }

            $master = Contact::find($request->master_id);
            $secondary = Contact::find($request->secondary_id);

            // Merge logic
            if (!$master->email && $secondary->email) $master->email = $secondary->email;
            if (!$master->phone && $secondary->phone) $master->phone = $secondary->phone;

            // Merge custom fields
            $secondaryFields = ContactCustomFieldValue::where('contact_id', $secondary->id)->get();
            foreach ($secondaryFields as $sf) {
                ContactCustomFieldValue::updateOrCreate(
                    ['contact_id' => $master->id, 'custom_field_id' => $sf->custom_field_id],
                    ['value' => $sf->value]
                );
            }

            // Mark secondary contact as merged
            $secondary->merged_to_id = $master->id;
            $secondary->save();
            $master->save();

            return response()->json(['status' => 'success', 'message' => 'Contacts merged successfully.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Merge Contact Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to merge contacts.'], 500);
        }
    }

    public function mergedInfo($id)
    {
        $contact = Contact::with('customFields.customField')->find($id);

        if (!$contact) {
            return response()->json([
                'status' => 'error',
                'message' => 'Merged contact not found.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'contact' => $contact
        ]);
    }
}
