<?php
$user_info = array();
$new_user = false;

$careTeamUserIds = $patient->careTeam;
$ctmsa = array();
if (!empty($patient->sendAlertTo)) {
    $ctmsa = $patient->sendAlertTo;
}
$ctbp = $patient->billingProviderID;
$ctlc = $patient->leadContactID;

if (!function_exists('buildProviderDropDown')) {
    function buildProviderDropDown($providers, $activeId = false)
    {
        $html = '<select name="provider_id" class="selectpicker ctselectpicker" data-size="10">';
        $html .= '<option value="">Choose..</option>';
        foreach ($providers as $provider) :
            $selected = '';
            if ($provider->id == $activeId) {
                $selected = 'selected="selected"';
            }
            $html .= '<option value="' . $provider->id . '" "' . $selected . '">' . ucwords(preg_replace('/[^A-Za-z0-9\-]/',
                                    '', $provider->first_name) . ' ' . preg_replace('/[^A-Za-z0-9\-]/', '',
                                    $provider->last_name)) . '</option>';
        endforeach;
        $html .= '</select>';
        return $html;
    }
}

if (!function_exists('buildProviderInfoContainers')) {
    function buildProviderInfoContainers($providers)
    {
        $html = '<div id="providerInfoContainers" style="display:none;">';
        foreach ($providers as $provider) :
// echo "<pre>"; var_export($provider); echo "</pre>";
            $html .= '<div id="providerInfo' . $provider->id . '">';
            $html .= '<strong><span id="providerName' . $provider->id . '" style="display:none;">' . ucwords($provider->first_name . ' ' . $provider->last_name) . '</span></strong>';
            $html .= '<strong>Specialty:</strong> ' . $provider->specialty;
            $html .= '<BR><strong>Tel:</strong> ' . $provider->phone;
            $html .= '</div>';
        endforeach;
        $html .= '</div>';
        return $html;
    }
}
?>

@extends('partials.providerUI')

@section('title', 'Edit/Modify Care Team')
@section('activity', 'Edit/Modify Care Plan')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/patient/careteam.js') }}"></script>
    <script>
        $(document).ready(function () {
            // CARE TEAM JS
            var ctmCount = 0;
            var ctMembers = [];
            $(".addCareTeamMember").on('click', function () {
                //alert('adding care team member ' + ctmCount);
                addCareTeamMember();
                //return false;
            });


            $('#careTeamMembers').on('click', '.removeCtm', function (event) {
                ctmId = $(this).attr('ctmId');
                name = '#ctm' + ctmId;
                $(name).detach();
                return false;
            });

            $('body').on('change', '.ctselectpicker', function (event) {
                event.preventDefault();
                // set vars
                selectpickerid = $(this).attr('id');
                providerid = $(this).val();
                ctmCountId = $(this).closest('.row').find('.ctmCountArr').val();
                $('#ctm' + ctmCountId + 'Info').html('');

                // error notification if new selection is already in ctMembers
                if (jQuery.inArray(providerid, ctMembers) !== -1) {
                    // set to choose
                    $("#" + selectpickerid + " option[value='']").prop('selected', true);
                    $("#" + selectpickerid + "").selectpicker('refresh');
                }

                // reprocess ctMembers from all selectpickers
                ctMembers = [];
                $('.carePlanMemberIds').remove();
                $(".ctselectpicker").each(function () {
                    selectpickerid = $(this).attr('id');
                    var providerid = $("#" + selectpickerid + " option:selected").val();
                    if (providerid !== undefined && providerid !== '') {
                        ctmCountId = $(this).closest('.row').find('.ctmCountArr').val();
                        ctMembers.push(providerid);
                        $('#careTeamMembers').append('<input class="carePlanMemberIds" type="hidden" name="carePlanMemberIds[]" value="' + providerid + '">');
                        providerInfoHtml = $('#providerInfo' + providerid).html();
                        $('#ctm' + ctmCountId + 'Info').html(providerInfoHtml);
                        $('#ctm' + ctmCountId + 'sa').val(providerid);
                        $('#ctm' + ctmCountId + 'bp').val(providerid);
                        $('#ctm' + ctmCountId + 'lc').val(providerid);
                    }
                });
                console.log('Selected Providers: ' + ctMembers.join("\n"));
                return false;
            });
            function addCareTeamMember() {
                ctmCount++;
                // build html
                html1 = '<div class="col-md-12 careTeamMemberContainer" id="ctm' + ctmCount + '">';
                // first row
                html1 += '<div class="row">';
                html1 += '<input class="ctmCountArr" type="hidden" name="ctmCountArr[]" value="' + ctmCount + '">';
                html1 += '<div class="col-sm-4">';
                html1 += '<?php echo buildProviderDropDown($providers); ?>';
                html1 += '</div>';
                html1 += '<div class="col-sm-5" id="ctm' + ctmCount + 'Info">';
                html1 += '';
                html1 += '</div>';
                html1 += '<div class="col-sm-3">';
                html1 += '<button type="button" class="btn btn-xs btn-orange removeCtm" ctmId="' + ctmCount + '"><i class="glyphicon glyphicon-minus-sign"></i> Remove Member</button>';
                html1 += '</div>';
                html1 += '</div>';
                // second row
                html2 = '<div class="row">';
                html2 += '<div class="col-sm-4" style="padding:20px;">';
                html2 += '<div class="radio-inline"><input type="checkbox" name="ctmsa[]" id="ctm' + ctmCount + 'sa" /><label for="ctm' + ctmCount + 'sa"><span> </span>Send Alerts</label></div>';
                html2 += '</div>';
                html2 += '<div class="col-sm-4" style="padding:20px;">';
                html2 += '<div class="radio"><input type="radio" name="ctbp" id="ctm' + ctmCount + 'bp" /><label for="ctm' + ctmCount + 'bp"><span> </span>Billing Provider</label></div>';
                html2 += '</div>';
                html2 += '<div class="col-sm-4" style="padding:20px;">';
                html2 += '<div class="radio"><input type="radio" name="ctlc" id="ctm' + ctmCount + 'lc" /><label for="ctm' + ctmCount + 'lc"><span> </span>Lead Contact</label></div>';
                html2 += '</div>';
                html2 += '</div>';
                // remove already used members from new select
                $("#careTeamMembers").append(html1 + html2);
                thisSelect = $('#ctm' + ctmCount + '').find('.ctselectpicker');
                selectName = thisSelect.attr('name', 'ctm' + ctmCount + 'provider');
                selectId = thisSelect.attr('id', 'ctm' + ctmCount + 'provider');
                //alert(thisSelect.attr('id'));
                $('.ctselectpicker').selectpicker();

                // add class (doesnt persist through append() for some reason??)
                $('#ctm' + ctmCount + '').addClass('careTeamMemberContainer');
                console.log('Selected Providers: ' + ctMembers.join("\n"));
                hasFormChanged = false;
                return false;
            }

            <?php
            if(!empty($careTeamUsers)) {
                foreach ($careTeamUsers as $careTeamUser) {
                    if(is_integer($careTeamUser)) {
                        $careTeamUser = User::find($careTeamUser);
                        if(!$careTeamUser) {
                            continue 1;
                        }
                    }
                    ?>
                    addCareTeamMember();
            $('#ctm' + ctmCount + 'provider').val(<?php echo $careTeamUser->id; ?>);
            $('#ctm' + ctmCount + 'provider').change();
            <?php
            if (in_array($careTeamUser->id, $ctmsa)) {
                echo "$( '#ctm' + ctmCount + 'sa' ).prop('checked', true);";
            }
            if ($careTeamUser->id == $ctbp) {
                echo "$( '#ctm' + ctmCount + 'bp' ).prop('checked', true);";
            }
            if ($careTeamUser->id == $ctlc) {
                echo "$( '#ctm' + ctmCount + 'lc' ).prop('checked', true);";
            }
            }
            }
            ?>
        });
    </script>

    <script>
    </script>
    {!! Form::open(array('url' => URL::route('patient.careteam.store', array('patientId' => $patient->id)), 'class' => 'form-horizontal', 'id' => 'ucpForm')) !!}
    <style>
        .careTeamMemberContainer {
            margin-top: 30px;
            border-bottom: 1px solid #ccc;
        }
    </style>
    <input type=hidden name=user_id value="{{ $patient->id }}">
    <input type=hidden name=program_id value="{{ $patient->program_id }}">
    <input id="save" name="formSubmit" type="hidden" value="Save" tabindex="0">


    <div class="row" style="margin-top:20px;">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="icon-container col-lg-12">
                @if(isset($patient) && !$new_user )
                    @include('wpUsers.patient.careplan.nav')
                @endif
            </div>
            <div class="main-form-container-last col-lg-8 col-lg-offset-2" style="margin-top:20px;">
                <div class="row">
                    @if(isset($patient) && !$new_user )
                        <div class="main-form-title col-lg-12">
                            Edit Patient Care Team
                        </div>
                        @include('partials.userheader')
                    @else
                        <div class="main-form-title col-lg-12">
                            Add Patient Care Team
                        </div>
                    @endif

                    <div class="col-sm-12">
                        <div id="careTeamMembers"></div>
                        @foreach($careTeamUsers as $careTeamUser)
                            {{--
                            <div class="col-md-12" class="careTeamMemberContainer" id="ctm' + ctmCount + '">
                                <div class="row">
                                    <input class="ctmCountArr" type="hidden" name="ctmCountArr[]" value="' + ctmCount + '">
                                    <div class="col-sm-4">';
                                        {!! Form::select('providers', $providersData, (old('providers') ? old('providers') : $careTeamUser->id ? $careTeamUser->id : ''), ['class' => 'form-control selectpicker', 'style' => 'width:50%;']) !!}
                                    </div>
                                    <div class="col-sm-5" id="ctm' + ctmCount + 'Info">
                                    </div>
                                    <div class="col-sm-3">
                                    <a href="" class="removeCtm" ctmId="' + ctmCount + '"><span class="glyphicon glyphicon-remove-sign"></span> Remove Member</a>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-4" style="padding:20px;">
                                    <div class="radio-inline"><input type="checkbox" name="ctmsa[]" id="ctm' + ctmCount + 'sa" /><label for="ctm' + ctmCount + 'sa"><span> </span>Send Alerts</label></div>
                                    </div>
                                    <div class="col-sm-4" style="padding:20px;">
                                    <div class="radio"><input type="radio" name="ctbp" id="ctm' + ctmCount + 'bp" /><label for="ctm' + ctmCount + 'bp"><span> </span>Billing Provider</label></div>
                                    </div>
                                    <div class="col-sm-4" style="padding:20px;">
                                    <div class="radio"><input type="radio" name="ctlc" id="ctm' + ctmCount + 'lc" /><label for="ctm' + ctmCount + 'lc"><span> </span>Lead Contact</label></div>
                                    </div>
                                </div>
                            </div>
                            --}}
                        @endforeach
                        {!! $phtml !!}
                        <a id="add-care-team-member" href="" class="addCareTeamMember pull-right btn btn-primary"
                           style="margin:20px;"><span class="glyphicon glyphicon-plus-sign"></span> Add Care Team Member</a>
                        <br/>
                        <br/>
                    </div>


                    <div class="modal fade" id="ctModal" tabindex="-1" role="dialog" aria-labelledby="ctModalLabel">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    Incomplete Care Team
                                </div>
                                <div class="modal-body">
                                    <p><span id="ctModalError"></span></p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" id="ctModalYes" class="btn btn-warning" data-dismiss="modal">
                                        Continue editing
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="ctConfModal" tabindex="-1" role="dialog"
                         aria-labelledby="ctConfModalLabel">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    Confirm Care Team
                                </div>
                                <div class="modal-body">
                                    <p><span id="ctConfModalError"></span></p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" id="ctConfModalNo" class="btn btn-warning"
                                            data-dismiss="modal">Continue editing
                                    </button>
                                    <button type="button" id="ctConfModalYes" class="btn btn-primary"
                                            data-dismiss="modal">Confirm and save
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php echo buildProviderInfoContainers($providers); ?>
    @include('wpUsers.patient.careplan.footer')
    <br/><br/>
    </form>
@stop

