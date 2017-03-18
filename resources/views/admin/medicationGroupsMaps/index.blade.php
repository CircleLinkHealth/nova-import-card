@extends('partials.adminUI')

@section('content')
    <div id="medication-group-maps"></div>

    <!-- using string template here to work around HTML <option> placement restriction -->
    <script type="text/x-template" id="med-template">
        <div class="container">

            <div class="row">
                <div class="form-group">
                    <div class="col-md-5">
                        <input class="form-control" v-model="newMap.keyword" type="text"
                               placeholder="Medication Name or Keyword" required>
                    </div>

                    <div class="col-md-5">
                        <select2 :options="options" v-model="newMap.medication_group_id"
                                 style="width: 100%;height: 100%;"></select2>
                    </div>

                    <div class="col-md-2">
                        <input type="submit" class="btn btn-primary" value="Add" name="submit" v-on:click="store">
                    </div>
                </div>
            </div>
        </div>
    </script>

    {{--select2 component template--}}
    <script type="text/x-template" id="select2-template">
        <select>
            <slot></slot>
        </select>
    </script>
@endsection


@section('javascript')
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
                    medication_group_id: [],
                },
                options: [
                    //populate select2 options
                        @foreach($medicationGroups as $group)
                    {
                        id: '{{$group->id}}', text: '{{$group->name}}'
                    },
                    @endforeach
                ]
            },

            mounted: function () {

            },

            methods: {
                store: function () {
                    this.$http.post('{{route('medication-groups-maps.store')}}', {}, {}).then(function (response) {

                    }, function (response) {

                    });
                }
            }
        });

    </script>
@endsection
