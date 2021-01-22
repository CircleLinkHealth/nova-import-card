<div class="col-md-12">
    <form action="{{ route('get.athena.ccdas') }}" method="post" enctype="multipart/form-data">
        {{ csrf_field() }}

        <div class="form-group">
            <input type="text" placeholder="Athena Patient IDs, comma separated. id1,id2,id3,id4" name="ids"
                   class="col-sm-12 form-control">
        </div>

        <div class="form-group">
            <select name="practice_id" class="col-sm-12 form-control">
                @foreach(CircleLinkHealth\Customer\Entities\Practice::whereEhrId(2)->get() as $practice)
                    <option value="{{$practice->id}}">{{$practice->display_name}}</option>
                @endforeach
            </select>
        </div>

        <input type="submit" class="btn btn-primary" value="Grab em!" name="submit">

    </form>
</div>