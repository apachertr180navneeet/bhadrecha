@extends('admin.layouts.app')

@section('style')

<style>

 .user-image{

    height: 70px;

    width: auto;

    border:1px dotted lightgray;

    padding:4px;

    margin: 0 auto;

 }  

</style>

@endsection 

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

    <h5 class="py-2 mb-2">

        <span class="text-primary fw-light">My Profile</span>

    </h5>

    <div class="row">

        <div class="col-12 col-lg-6">

            <div class="card profile-card">

                <div class="card-body pb-5">

                    <form action="{{ route('admin.update.profile') }}" method="POST" enctype="multipart/form-data">

                        @csrf

                        <div class="row g-3">

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label class="form-label">First Name*</label>

                                    <input type="text" id="" name="first_name" class="form-control" placeholder="Enter First Name" value="{{old('first_name',$user->first_name)}}" required>

                                    @error('first_name')

                                        <div class="text-danger">{{ $message }}</div>

                                    @enderror

                                </div>

                            </div>

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label class="form-label">Last Name*</label>

                                    <input type="text" name="last_name" class="form-control" placeholder="Enter Last Name" value="{{ old('last_name',$user->last_name)}}" required>

                                    @error('last_name')

                                        <div class="text-danger">{{ $message }}</div>

                                    @enderror

                                </div>

                            </div>

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label class="form-label">Phone Number*</label>

                                    <input type="tel" name="phone" class="form-control" placeholder="Enter Phone Number" value="{{ old('phone',$user->phone) }}" required>

                                    @error('phone')

                                        <div class="text-danger">{{ $message }}</div>

                                    @enderror

                                </div>

                            </div>

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label class="form-label">Email Address*</label>

                                    <input type="email" name="email" class="form-control" placeholder="Enter Email Address" value="{{ old('email',$user->email) }}" required>

                                    @error('email')

                                        <div class="text-danger">{{ $message }}</div>

                                    @enderror

                                </div>

                            </div>

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label class="form-label">Profile Picture</label>

                                    <div class="input-group">

                                        <input type="file" name="avatar" accept="image/*" class="form-control" id="avatar" aria-describedby="inputGroupFileAddon04" aria-label="Upload" onchange="document.getElementById('user-image').src = window.URL.createObjectURL(this.files[0])">

                                    </div>

                                    @error('avatar')

                                        <div class="text-danger">{{ $message }}</div>

                                    @enderror

                                </div>

                            </div>

                            <div class="col-md-6">

                                @if($user->avatar)

                                    <img src="{{asset($user->avatar)}}" class="user-image" id="user-image">

                                @else

                                    <img src="{{asset('assets/admin/img/avatars/1.png')}}" class="user-image" id="user-image">

                                @endif

                            </div>

                        </div>

                        <div class="row g-3">

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label class="form-label">State</label>

                                    <select name="state" id="state" class="form-select">

                                        <option value="">Select State</option>

                                        @foreach(array_keys($states) as $stateName)

                                            <option value="{{ $stateName }}" {{ old('state', $user->state) == $stateName ? 'selected' : '' }}>{{ $stateName }}</option>

                                        @endforeach

                                    </select>

                                </div>

                            </div>

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label class="form-label">City</label>

                                    <select name="city" id="city" class="form-select">

                                        <option value="">Select City</option>

                                    </select>

                                </div>

                            </div>

                        </div>

                        <div class="pt-4">

                            <div class="col-md-12 submit-btn">

                                <button type="submit" class="btn btn-primary">Save</button> 

                            </div>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection

@section('script')

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<style>
    .select2-container .select2-selection--single { height: 38px; border: 1px solid #d9dee3; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
    .select2-dropdown { border: 1px solid #d9dee3; border-radius: 0.375rem; }
    .select2-search__field { border: 1px solid #d9dee3; border-radius: 0.375rem; padding: 0.375rem 0.75rem; margin: 8px; width: calc(100% - 22px) !important; }
    .select2-results__option { padding: 8px 16px; }
    .select2-results__option--highlighted { background-color: rgba(67, 89, 113, 0.1) !important; color: #696cff !important; }
</style>
<script>
    $(".timezone").select2().on('select2:opening', function(e) {
        $(this).data('select2').$dropdown.find(':input.select2-search__field').attr('placeholder', 'Search your timezone')
    })

    $(document).ready(function() {
        $('#state, #city').select2({ placeholder: 'Search & Select State', width: '100%' });

        const stateSelect = document.getElementById('state');
        const citySelect = document.getElementById('city');
        const currentCity = "{{ $user->city }}";

        function loadCities() {
            const selectedState = stateSelect.value;
            citySelect.innerHTML = '<option value="">Select City</option>';
            $('#city').select2('destroy').select2({ placeholder: 'Search & Select City', width: '100%' });
            if (!selectedState) return;
            $.ajax({
                url: '{{ route("admin.users.get-cities") }}',
                type: 'GET',
                data: { state: selectedState },
                success: function(data) {
                    citySelect.innerHTML = '<option value="">Select City</option>';
                    data.forEach(city => {
                        const option = document.createElement('option');
                        option.value = city;
                        option.textContent = city;
                        if (city === currentCity) option.selected = true;
                        citySelect.appendChild(option);
                    });
                    $('#city').select2('destroy').select2({ placeholder: 'Search & Select City', width: '100%' });
                }
            });
        }

        $('#state').on('change', function() { loadCities(); });
        if (stateSelect.value) { loadCities(); }
    });
</script>

@endsection
