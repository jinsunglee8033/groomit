@extends('includes.admin_default')
@section('contents')


    <script type="text/javascript">
        window.onload = function() {
            $( "#dob" ).datetimepicker({
                format: 'YYYY-MM-DD'
            });

            $( "#vaccinated_exp_date" ).datetimepicker({
                format: 'YYYY-MM-DD'
            });

            // Update pet
            $('#update_pet_submit').click(function() {

                var form = document.getElementById('update_pet_form');
                var data = new FormData(form);

                $.ajax({
                    url: '/admin/pet/update',
                    data: data,
                    cache: false,
                    type: 'POST',
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res === 'Success') {
                            alert( res );
                            window.location.reload();
                        } else {
                            alert( res );
                        }
                    }
                });
            });

            $('#delete_pet').click(function() {

                var r = confirm ('Are you sure?');

                if (r == true) {
                    $.get( "/admin/pet/remove/" + $('input[name=id]').val() , function( res ) {
                        alert( res );
                        window.location = '/admin/pets';
                    });
                } else {
                    return;
                }

            });
        };

    </script>

    <div class="container-fluid top-cont">
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />Pet Detail</h3>
    </div>

    <div class="well filter" style="padding-bottom:5px;margin: 30px;">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <a href="/admin/pets" class="btn btn-info">Back</a>
                </div>
            </div>
            <div class="col-md-8 text-right">
                <div class="form-group">
                    <div class="btn-right btn btn-warning" id="delete_pet">Delete</div>
                    <div class="btn-right btn btn-info" data-toggle="modal" data-target="#update_pet">Update</div>
                </div>
            </div>
        </div>
    </div>

    <hr>

    <div class="detail">

        @if ($pet->photo)
            <div class="col text-center">
                <img src="data:image/png;base64,{{ $pet->photo }}"/>
            </div>
        @endif

            <div class="row">
                <div class="col-xs-3">Type</div>
                <div class="col-xs-3">{{ $pet->type }}</div>
            </div>
        <div class="row">
            <div class="col-xs-3">Name</div>
            <div class="col-xs-3">{{ $pet->name }}</div>
        </div>
        <div class="row">
            <div class="col-xs-3">Owner</div>
            <div class="col-xs-9">{{ $pet->owner }}</div>
        </div>
        <div class="row">
            <div class="col-xs-3">Age (D.O.B.)</div>
            <div class="col-xs-9">{{ $pet->new_dob }}</div>
        </div>
            @if ($pet->size_name)
        <div class="row">
            <div class="col-xs-3">Size</div>
            <div class="col-xs-9">{{ $pet->size_name }}</div>
        </div>
            @endif
            @if ($pet->breed_name)
        <div class="row">
            <div class="col-xs-3">Breed</div>
            <div class="col-xs-9">{{ $pet->breed_name }}</div>
        </div>
            @endif
        <div class="row">
            <div class="col-xs-3">Gender</div>
            <div class="col-xs-9">{{ $pet->gender }}</div>
        </div>
        <div class="row">
            <div class="col-xs-3">Vaccinated</div>
            <div class="col-xs-9">{{ $pet->vaccinated }}</div>
        </div>
        <div class="row">
            <div class="col-xs-3">Vaccinated Expire Date</div>
            <div class="col-xs-9">
                @if (!empty($pet->vaccinated_exp_date))
                    {{ $pet->vaccinated_exp_date }}
                @else
                    N/A
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-xs-3">Vaccinated Document</div>
            <div class="col-xs-9">
                @if (!empty($pet->vaccinated_image))
                    <a href="/admin/pet/document/{{ $pet->pet_id }}" target="_blank">View Document</a>
                @else
                    N/A
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-xs-3">Vet Name</div>
            <div class="col-xs-9">{{ $pet->vet }}</div>
        </div>
        <div class="row">
            <div class="col-xs-3">Vet Contact</div>
            <div class="col-xs-9">{{ $pet->vet_phone }}</div>
        </div>
            @if ($pet->type != 'cat')
        <div class="row">
            <div class="col-xs-3">Coat Type</div>
            <div class="col-xs-9">{{ $pet->coat_type }}</div>
        </div>
        <div class="row">
            <div class="col-xs-3">Last Groom</div>
            <div class="col-xs-9">{{ $pet->last_groom }}</div>
        </div>
            @endif
        <div class="row">
            <div class="col-xs-3">Temperament</div>
            <div class="col-xs-9">{{ $pet->temperament }}</div>
        </div>
            @if ($pet->special_note)
        <div class="row">
            <div class="col-xs-3">Special Note</div>
            <div class="col-xs-9">{{ $pet->special_note }}</div>
        </div>
            @endif
    </div>

    <hr>
    <div class="detail">
        <div class="row">
            <div class="col-12">
                <h4>Groomer Notes</h4>
                <table class="table table-bordered">
                    <thead>
                        <th>Appointment #</th>
                        <th>Groomer</th>
                        <th>Note</th>
                        <th>Date</th>
                    </thead>
                    <tbody>
                        @forelse ($groomer_notes as $note)
                        <tr>
                            <td>{{ $note->appointment_id }}</td>
                            <td>{{ $note->groomer_id . ', ' . $note->groomer_name }}</td>
                            <td>{{ $note->groomer_note }}</td>
                            <td>{{ $note->cdate }}</td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center;">Empty</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Assign Groomer Modal Start -->
    <div class="modal" id="update_pet" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Update Pet Information</h4>
                </div>
                <div class="modal-body">

                    <div id="result"></div>

                    <form method="post" id="update_pet_form" class="form-group" enctype="multipart/form-data">
                        {!! csrf_field() !!}
                        <div class="row">
                            <div class="col-xs-4 text-right">Owner</div>
                            <div class="col-xs-7">{{ $pet->owner }}</div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-xs-4 text-right">Pet Name</div>
                            <div class="col-xs-7">
                                <input type="text" class="form-control" name="name" value="{{ old('name', $pet->name) }}"/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-4 text-right">D.O.B.</div>
                            <div class="col-xs-7">
                                <input type="text" class="form-control" id="dob" name="dob" value="{{ old('dob', $pet->dob_input) }}"/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-4 text-right">Size</div>
                            <div class="col-xs-7">
                                <select class="form-control" name="size" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                    @foreach($sizes as $k=>$v)
                                        <option value="{{ $v->size_id }}" {{ old('size', $pet->size) == $v->size_id ? 'selected' : '' }}>{{ $v->size_name . ' ' . $v->size_desc }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-4 text-right">Gender</div>
                            <div class="col-xs-7">
                                <select class="form-control" name="gender">
                                    <option value="F" {{ old('gender', $pet->gender) == 'F' ? 'selected' : '' }}>Female</option>
                                    <option value="M" {{ old('gender', $pet->gender) == 'M' ? 'selected' : '' }}>Male</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-4 text-right">Breed</div>
                            <div class="col-xs-7">
                                <select class="form-control" name="breed" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                    @foreach($breeds as $k=>$v)
                                        <option value="{{ $v->breed_id }}" {{ old('breed', $pet->breed) == $v->breed_id ? 'selected' : '' }}>{{ $v->breed_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-4 text-right">Vaccinated</div>
                            <div class="col-xs-7">
                                <select class="form-control" name="vaccinated">
                                    <option value="Y" {{ old('vaccinated', $pet->vaccinated) == 'Y' ? 'selected' : '' }}>Vaccinated</option>
                                    <option value="N" {{ old('vaccinated', $pet->vaccinated) == 'N' ? 'selected' : '' }}>Not Vaccinated</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-4 text-right">Vaccinated Expire Date</div>
                            <div class="col-xs-7">
                                <input type="text" name="vaccinated_exp_date" id="vaccinated_exp_date" value="{{ old('vaccinated_exp_date', $pet->vaccinated_exp_date) }}"/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-4 text-right">Vaccinated Document</div>
                            <div class="col-xs-7">
                                @if (!empty($pet->vaccinated_image))
                                    <a href="/admin/pet/document/{{ $pet->pet_id }}" target="_blank">View Document</a><br/>
                                @endif
                                <input type="file" name="vaccinated_image" id="vaccinated_image"/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-4 text-right">Vet Name</div>
                            <div class="col-xs-7">
                                <input type="text" class="form-control" id="vet" name="vet" value="{{ old('vet', $pet->vet) }}"/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-4 text-right">Vet Contact</div>
                            <div class="col-xs-7">
                                <input type="text" class="form-control" id="vet_phone" name="vet_phone" value="{{ old('vet_phone', $pet->vet_phone) }}"/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-4 text-right">Coat Type</div>
                            <div class="col-xs-7">
                                <div class="col s2 center-align">
                                    <input {{ old('coat_type', $pet->coat_type) == 'Silky' ? 'checked' : '' }} name="coat_type" type="radio" value="Silky">
                                    <label for="type-silky">Silky</label>
                                </div>
                                <div class="col s3 center-align">
                                    <input {{ old('coat_type', $pet->coat_type) == 'Wiry' ? 'checked' : '' }} name="coat_type" type="radio" value="Wiry">
                                    <label for="type-wiry">Wiry</label>
                                </div>
                                <div class="col s4 center-align">
                                    <input {{ old('coat_type', $pet->coat_type) == 'Double Coat' ? 'checked' : '' }} name="coat_type" type="radio" value="Double Coat">
                                    <label for="type-double-coat">Double Coat</label>
                                </div>
                                <div class="col s3 center-align">
                                    <input {{ old('coat_type', $pet->coat_type) == 'Curly' ? 'checked' : '' }} name="coat_type" type="radio" value="Curly">
                                    <label for="type-curly">Curly</label>
                                </div>

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-4 text-right">Last Groom</div>
                            <div class="col-xs-7">
                                <select class="form-control" name="last_groom">
                                    <option value="" {{ old('last_groom', $pet->last_groom) == '' ? 'selected' : '' }}>Select</option>
                                    <option value="< 6 weeks" {{ old('last_groom', $pet->last_groom) == '< 6 weeks' ? 'selected' : '' }}>< 6 weeks</option>
                                    <option value="< 6 months" {{ old('last_groom', $pet->last_groom) == '< 6 months' ? 'selected' : '' }}>< 6 months</option>
                                    <option value="> 6 months" {{ old('last_groom', $pet->last_groom) == '> 6 months' ? 'selected' : '' }}>> 6 months</option>
                                    <option value="Never" {{ old('last_groom', $pet->last_groom) == 'Never' ? 'selected' : '' }}>Never</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-4 text-right">Temperament</div>
                            <div class="col-xs-7">
                                <select class="form-control" name="temperament">
                                    <option value="" {{ old('temperament', $pet->temperament) == '' ? 'selected' : '' }}>Select</option>
                                    <option value="Anxious" {{ old('temperament', $pet->temperament) == 'Anxious' ? 'selected' : '' }}>Anxious</option>
                                    <option value="Fatigue" {{ old('temperament', $pet->temperament) == 'Fatigue' ? 'selected' : '' }}>Fatigue</option>
                                    <option value="Aggressive" {{ old('temperament', $pet->temperament) == 'Aggressive' ? 'selected' : '' }}>Aggressive</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xs-4 text-right">Special Note</div>
                            <div class="col-xs-7">
                                <textarea cols="4" class="form-control" id="special_note" name="special_note">{{ old('special_note', $pet->special_note) }}</textarea>
                            </div>
                        </div>
                        <input type="hidden" name="id" value="{{$pet->pet_id}}" />
                    </form>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="update_pet_submit">Submit</button>
                    <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Assign Groomer Modal End -->

@stop