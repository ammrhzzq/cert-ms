<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Client</title>
    <link rel="stylesheet" href="{{ asset('css/data-entry.css') }}">
</head>
<body>
    <div class="container">
        <h1>Create Client</h1>
        
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
            
            <form action="{{ route('clients.store') }}" method="POST">
                @csrf
                @method('POST')
                
                <div class="form-group">
                    <label>Company Name</label>
                    <input type="text" name="comp_name" placeholder="Company Name" required>
                </div>
                
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="comp_address1" placeholder="Address Line 1" required>
                </div>
                
                <div class="form-group">
                    <input type="text" name="comp_address2" placeholder="Address Line 2">
                </div>
                
                <div class="form-group">
                    <input type="text" name="comp_address3" placeholder="Address Line 3">
                </div>
                
                <div class="form-row">
                    <div class="form-column">
                        <div class="form-group">
                            <label>Contact Number 1</label>
                            <input type="text" name="comp_phone1" placeholder="Contact Number 1">
                        </div>
                        <div class="form-group">
                            <label>Contact Person Name</label>
                            <input type="text" name="phone1_name" placeholder="Contact Person Name">
                        </div>
                    </div>
                    <div class="form-column">
                        <div class="form-group">
                            <label>Contact Number 2</label>
                            <input type="text" name="comp_phone2" placeholder="Contact Number 2">
                        </div>
                        <div class="form-group">
                            <label>Contact Person Name</label>
                            <input type="text" name="phone2_name" placeholder="Contact Person Name">
                        </div>
                    </div>
                </div>
                
                <div class="button-group">
                    <button type="button" class="btn-cancel">Cancel</button>
                    <input type="submit" value="Create Client"/>
                </div>
            </form>
        </div>
    </div>
</body>
</html>