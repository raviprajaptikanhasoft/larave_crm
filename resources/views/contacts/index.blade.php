@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-4">Manage Contacts</h4>
        <button class="btn btn-success" onclick="openAddContactModal()">
            <i class="bi bi-person-plus-fill me-1"></i> Add Contact
        </button>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" id="search_name" class="form-control" placeholder="Search by Name">
                </div>
                <div class="col-md-3">
                    <input type="text" id="search_email" class="form-control" placeholder="Search by Email">
                </div>
                <div class="col-md-3">
                    <select id="search_gender" class="form-select">
                        <option value="">All Genders</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary w-100" id="clear_filters">
                        <i class="bi bi-x-circle"></i> Clear Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Contacts Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover align-middle" id="contactsTable">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        @foreach ($customFields as $field)
                            @if ($field->show_on_table)
                                <th>{{ $field->name }}</th>
                            @endif
                        @endforeach
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- Contact Modal -->
    <div class="modal fade" id="contactModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form id="contactForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="contactModalTitle">Add</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" name="id" id="contact_id">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Name">
                                <span class="text-danger error-text name_error"></span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="Email">
                                <span class="text-danger error-text email_error"></span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" placeholder="Phone">
                                <span class="text-danger error-text phone_error"></span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                                <span class="text-danger error-text gender_error"></span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Profile Image</label>
                                <input type="file" name="profile_image" class="form-control">
                                <span class="text-danger error-text profile_image_error"></span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Additional File</label>
                                <input type="file" name="additional_file" class="form-control">
                                <span class="text-danger error-text additional_file_error"></span>
                            </div>
                        </div>

                        <div id="custom_fields_area" class="mt-3">
                            <div class="row g-3">
                                @foreach ($customFields as $field)
                                    <div class="col-md-6">
                                        <label class="form-label">{{ $field->name }}</label>
                                        @if ($field->type === 'text')
                                            <input type="text" class="form-control"
                                                name="custom_fields[{{ $field->id }}]">
                                            <span
                                                class="text-danger error-text custom_fields_{{ $field->id }}_error"></span>
                                        @elseif($field->type === 'name')
                                            <input type="name" class="form-control"
                                                name="custom_fields[{{ $field->id }}]">
                                            <span
                                                class="text-danger error-text custom_fields_{{ $field->id }}_error"></span>
                                        @elseif($field->type === 'date')
                                            <input type="date" class="form-control"
                                                name="custom_fields[{{ $field->id }}]">
                                            <span
                                                class="text-danger error-text custom_fields_{{ $field->id }}_error"></span>
                                        @elseif($field->type === 'textarea')
                                            <textarea class="form-control" name="custom_fields[{{ $field->id }}]"></textarea>
                                            <span
                                                class="text-danger error-text custom_fields_{{ $field->id }}_error"></span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Save Contact
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Merge Modal --}}
    <div class="modal fade" id="mergeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form id="mergeForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Merge Contacts</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Select the master contact:</p>
                        <input type="hidden" name="secondary_id" id="secondary_id">
                        <select class="form-select" name="master_id" id="master_id">
                            @foreach ($contacts as $contact)
                                <option value="">Select</option>
                                <option value="{{ $contact->id }}">{{ $contact->name }} ({{ $contact->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" type="submit">Confirm Merge</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection


@section('scripts')
    <script>
        // Debounce function to limit frequent requests while typing
        function debounce(func, delay) {
            let timeout;
            return function() {
                clearTimeout(timeout);
                timeout = setTimeout(func, delay);
            };
        }

        function openAddContactModal() {
            $('#contactModalTitle').text('Add Contact');
            $('#contactForm')[0].reset(); // Reset the entire form
            $('#contact_id').val(''); // Clear the hidden ID field
            $('.error-text').text(''); // Clear validation errors if needed
            // Open the modal manually
            const modal = new bootstrap.Modal(document.getElementById('contactModal'));
            modal.show();
        }

        // Get all recode
        function fetchContacts() {
            $.post("{{ route('contacts.fetch') }}", {
                _token: "{{ csrf_token() }}",
                name: $('#search_name').val(),
                email: $('#search_email').val(),
                gender: $('#search_gender').val()
            }, function(res) {
                let rows = '';
                if (res.data.length > 0) {
                    $.each(res.data, function(_, contact) {
                        rows += `<tr>
                            <td>${contact.name} ${contact.merged_to_id ? '<span class="badge bg-secondary ms-2">Merged</span>' : ''}</td>
                            <td>${contact.email}</td>
                            <td>${contact.phone}</td>
                            <td>${contact.gender}</td>
                            ${contact.custom_fields.filter(cf => cf.custom_field.show_on_table)
                            .map(cf => `<td>${cf.value}</td>`)
                            .join('')}
                            <td>
                                ${contact.merged_to_id
                                ? `<button class="btn btn-sm btn-info view-merged-btn" data-id="${contact.merged_to_id}">
                                        View Merged Into
                                    </button>`
                                : `<button class="btn btn-sm btn-secondary merge-btn" data-id="${contact.id}">
                                        <i class="bi bi-link"></i> Merge
                                    </button>`
                                }
                                <button class="btn btn-sm btn-warning" onclick="editContact(${contact.id})">Edit</button>
                                <form method="POST" class="deleteForm d-inline" data-id="${contact.id}">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>`;
                    });
                } else {
                    // Update this colspan if your table has more columns
                    const colCount = $('#contactsTable thead th').length;
                    rows = `<tr><td colspan="${colCount}" class="text-center text-muted">No data found</td></tr>`;
                }
                $('#contactsTable tbody').html(rows);
            });
        }

        // Attach live filter handlers
        $('#search_name, #search_email').on('keyup', debounce(fetchContacts, 500));
        $('#search_gender').on('change', fetchContacts);

        // Clear button handler
        $('#clear_filters').on('click', function() {
            $('#search_name').val('');
            $('#search_email').val('');
            $('#search_gender').val('');
            fetchContacts();
        });

        // Add and Update
        $('#contactForm').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajax({
                url: "{{ route('contacts.store') }}",
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    $('#contactModal').modal('hide');
                    fetchContacts();
                    Swal.fire('Success', res.message, 'success');
                    $('#contactForm')[0].reset();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, val) {
                            $('.' + key.replace(/\./g, '_') + '_error').text(val[0]);
                        });
                    } else {
                        alert('Something went wrong!');
                    }
                }
            });
        });

        // Delete
        $(document).on('submit', '.deleteForm', function(e) {
            e.preventDefault();
            let id = $(this).data('id');
            let form = this;

            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to delete this contact.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!'
            }).then(result => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/contacts/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: res => {
                            Swal.fire('Deleted', res.message, 'success');
                            fetchContacts();
                        }
                    });
                }
            });
        });

        // Edit 
        function editContact(id) {
            $.get(`/contacts/${id}`, function(res) {
                const c = res.contact;

                // Change modal title to Edit
                $('#contactModalTitle').text('Edit Contact');
                $('.error-text').text('');

                $('#contact_id').val(c.id);
                $('[name="name"]').val(c.name);
                $('[name="email"]').val(c.email);
                $('[name="phone"]').val(c.phone);
                $('[name="gender"]').val(c.gender);

                // Clear and reload custom fields
                @foreach ($customFields as $field)
                    @if ($field->type === 'text' || $field->type === 'date')
                        $(`[name="custom_fields[{{ $field->id }}]"]`).val('');
                    @elseif ($field->type === 'textarea')
                        $(`[name="custom_fields[{{ $field->id }}]"]`).text('');
                    @endif
                @endforeach

                // Load values
                if (c.custom_fields.length) {
                    c.custom_fields.forEach(function(cf) {
                        $(`[name="custom_fields[${cf.custom_field_id}]"]`).val(cf.value);
                    });
                }

                $('#contactModal').modal('show');
            });
        }

        $(document).on('click', '.merge-btn', function() {
            const secondaryId = $(this).data('id');
            $('#secondary_id').val(secondaryId);
            $('#mergeModal').modal('show');
        });

        $('.btn[data-bs-dismiss="modal"]').on('click', function() {
            $('#secondary_id').val('');
            $('#master_id').val('');
        });

        $('#mergeForm').submit(function(e) {
            e.preventDefault();
            const masterId = $('#master_id').val();
            const secondaryId = $('#secondary_id').val();

            if (!masterId || !secondaryId) {
                return;
            }

            if (masterId === secondaryId) {
                Swal.fire('Error', 'Cannot merge a contact with itself.', 'error');
                return;
            }

            $.ajax({
                url: "{{ route('contacts.merge') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    $('#mergeModal').modal('hide');
                    Swal.fire('Success', res.message, 'success');
                    fetchContacts();
                },
                error: () => Swal.fire('Error', 'Failed to merge contacts.', 'error')
            });
        });

        $(document).on('click', '.view-merged-btn', function() {
            const mergedId = $(this).data('id');

            $.get(`/contacts/${mergedId}/merged-info`, function(res) {
                if (res.status === 'success') {
                    const c = res.contact;
                    Swal.fire({
                        title: 'Merged Into Contact',
                        html: `
                    <strong>Name:</strong> ${c.name}<br>
                    <strong>Email:</strong> ${c.email}<br>
                    <strong>Phone:</strong> ${c.phone}<br>
                    <strong>Gender:</strong> ${c.gender}
                `,
                        icon: 'info'
                    });
                }
            }).fail(() => {
                Swal.fire('Error', 'Could not fetch merged contact details.', 'error');
            });
        });



        // Call on load
        fetchContacts();
    </script>
@endsection
