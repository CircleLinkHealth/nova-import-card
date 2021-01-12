<div>
    <div class="loader-filler"></div>
    <div class="loader-container">
        <div class="loader"></div>
    </div>
</div>
@push('styles')
    <style>
        .loader {
            border: 5px solid #f3f3f3;
            -webkit-animation: spin 1s linear infinite;
            animation: spin 1s linear infinite;
            border-top: 5px solid #555;
            border-radius: 50%;
            width: 30px;
            height: 30px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        div.loader-container {
            width: 84px;
            position: absolute;
            right: -18px;
            top: 0px;
        }

        div.loader-filler {
            height: 30px;
        }
    </style>
@endpush