@extends('partials.adminUI')

@section('content')
    <div id="medication-group-maps"></div>
@endsection


@push('scripts')

    <!-- using string template here to work around HTML <option> placement restriction -->
    <script type="text/x-template" id="med-template">
        <div class="container">

            <div class="row">
                <h3>Add new Medication Group Map</h3>

                <p>For the Add button to become Enabled, fill in the keyword, and select a Medication Group.</p>
                <p>The Importer will apply pattern matching to see if medications contain the keywords, and activate the
                    relevant groups. For example 'asp' will math 'aspirin'.</p>
                <br>
                <div class="form-group">
                    <div class="col-md-5">
                        <input class="form-control" v-model="newMap.keyword" type="text"
                               placeholder="Medication Name or Keyword" required>
                    </div>

                    <div class="col-md-5">
                        <select2 :options="options" v-model="newMap.medication_group_id"
                                 style="width: 100%;height: 100%;">
                            <option selected value="0">Select a medication group</option>
                        </select2>
                    </div>

                    <div class="col-md-2">
                        <input type="submit" class="btn btn-primary" value="Add" name="submit" v-on:click="store"
                               v-bind:disabled="!isValid">
                    </div>
                </div>
            </div>

            <br><br>

            <div class="row">
                <h3>Existing Maps</h3>

                <p v-show="maps.length == 0">Nothing to see here. Get back to adding maps!</p>

                <ul style="list-style: none;">
                    <li v-for="(map, index) in maps">
                        <div class="col-md-4">@{{ map.keyword }}</div>
                        <div class="col-md-4">@{{ map.medication_group }}</div>
                        <div class="col-md-4">
                            <button class="btn btn-xs btn-danger problem-delete-btn"
                                    v-on:click.stop.prevent="remove(index, map.id)"><span><i
                                            class="glyphicon glyphicon-remove"></i></span></button>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </script>

    {{--select2 component template--}}
    <script type="text/x-template" id="select2-template">
        <select>
            <slot></slot>
        </select>
    </script>
    <script src="https://unpkg.com/vue"></script>
    <script src="https://cdn.jsdelivr.net/vue.resource/1.2.0/vue-resource.min.js"></script>

    <script>
        Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

        Vue.component('select2', {
            props: ['options', 'value'],
            template: '#select2-template',
            mounted: function () {
                var vm = this;
                $(this.$el)
                        .val(this.value)
                        // init select2
                        .select2({data: this.options})
                        // emit event on change.
                        .on('change', function () {
                            vm.$emit('input', this.value)
                        })
            },
            watch: {
                value: function (value) {
                    // update value
                    $(this.$el).val(value)
                },
                options: function (options) {
                    // update options
                    $(this.$el).select2({data: options})
                }
            },
            destroyed: function () {
                $(this.$el).off().select2('destroy')
            }
        });

        let vm = new Vue({
            el: '#medication-group-maps',

            template: '#med-template',

            data: {
                newMap: {
                    keyword: '',
                    medication_group_id: '',
                },
                maps: [
                    //populate existing maps
                        @foreach($maps as $map)
                    {
                        id: '{{$map->id}}',
                        keyword: '{{$map->keyword}}',
                        medication_group_id: '{{$map->medication_group_id}}',
                        medication_group: '{{$map->cpmMedicationGroup->name}}',
                    },
                    @endforeach
                ],
                options: [
                    //populate select2 options
                        @foreach($medicationGroups as $group)
                    {
                        id: '{{$group->id}}',
                        text: '{{$group->name}}'
                    },
                    @endforeach
                ]
            },

            computed: {
                isValid: function () {
                    return this.newMap.keyword && this.newMap.medication_group_id;
                }
            },

            methods: {
                remove: function (index, id) {
                    var formData = new FormData();
                    formData.append('id', id);

                    this.$http.delete('{{route('medication-groups-maps.destroy', ['medication_groups_map'=>''])}}/' + id, formData).then(function (response) {
                        this.maps.splice(index, 1);
                    }, function (response) {

                    });
                },

                store: function () {
                    var formData = new FormData();
                    formData.append('keyword', this.newMap.keyword);
                    formData.append('medication_group_id', this.newMap.medication_group_id);

                    this.$http.post('{{route('medication-groups-maps.store')}}', formData).then(function (response) {
                        this.maps.push({
                            id: response.data.stored.id,
                            keyword: response.data.stored.keyword,
                            medication_group_id: response.data.stored.medication_group_id,
                            medication_group: response.data.stored.cpm_medication_group.name,
                        });
                    }, function (response) {

                    });
                }
            }
        });

    </script>
@endpush
