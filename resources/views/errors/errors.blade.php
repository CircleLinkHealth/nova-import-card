@if (isset($errors))
    @if (count($errors) > 0)
        <div class="alert alert-danger error">
            <strong>Whoops!</strong> There were some problems with your input.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
@endif

@if (isset($messages))
    @if (count($messages) > 0)
        <div class="alert alert-success error">
            <strong>Messages:</strong><br><br>
            <ul>
                @foreach ($messages as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif
@endif

<script>
    $(".error").slideDown(function() {
        setTimeout(function() {
            $(".error").slideUp();
        }, 3000);
    });
</script>