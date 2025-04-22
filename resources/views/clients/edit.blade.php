<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Client</title>
    <link rel="stylesheet" href="{{ asset('css/data-entry.css') }}">
</head>

<body>
    <div class="container">
        <h1>Edit Client</h1>

        <div class="form-container">
            @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('clients.update', $client) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Company Name</label>
                    <input type="text" name="comp_name" placeholder="Company Name" value="{{ $client->comp_name }}" required>
                </div>

                <div class="form-group">
                    <label>Address1</label>
                    <input type="text" name="comp_address1" placeholder="Address" value="{{ $client->comp_address1 }}" required>
                </div>

                <div class="form-group">
                    <label>Address2</label>
                    <input type="text" name="comp_address2" placeholder="Address" value="{{ $client->comp_address2 }}" required>
                </div>

                <div class="form-group">
                    <label>Address3</label>
                    <input type="text" name="comp_address3" placeholder="Address" value="{{ $client->comp_address3 }}" required>
                </div>

                <div class="form-row">
                    <div class="form-cloumn">
                        <div class="form-group">
                            <label>Contact Number 1</label>
                            <input type="text" name="comp_phone1" placeholder="Contact Number 1" value="{{ $client->comp_phone1 }}" required>
                        </div>
                    </div>
                    <div class="form-column">
                        <div class="form-group">
                            <label>Contact Number 2</label>
                            <input type="text" name="comp_phone2" placeholder="Contact Number 2" value="{{ $client->comp_phone2 }}" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Registration Date</label>
                    <input type="date" name="reg_date" required value="{{ $client->reg_date }}">
                </div>

                <div class="button-group">
                <button type="button" class="btn-cancel">Cancel</button>
                <input type="submit" value="Edit Client" />
                </div>
            </form>
        </div>
    </div>

</body>

</html>