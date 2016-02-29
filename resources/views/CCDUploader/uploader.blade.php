<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CCD Importer</title>

    <!-- Material Design Lite -->
    <script src="https://code.getmdl.io/1.1.1/material.min.js"></script>

    <link rel="stylesheet" href="https://code.getmdl.io/1.1.0/material.teal-blue.min.css"/>
    <!-- Material Design icon font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <style>
        .full-width {
            width: 100%;
        }

        .hide {
            display: none;
        }

        .quote {
            position: relative;
            font-size: 16px;
            font-weight: 300;
            font-style: italic;
            line-height: 1.35;
            letter-spacing: .08em;
        }

        .dropzone {
            width: 100%;
            height: 300px;
            border: 2px dashed #ccc;
            color: #ccc;
            line-height: 300px;
            text-align: center;
            background-color: rgba(174, 219, 239, 0.21);
        }

        .dropzone.dragover {
            border-color: #000;
            color: #000;
        }
    </style>
    <link href="/img/favicon.png" rel="icon">

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <!-- Scripts -->
    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
</head>
<body>
<nav class="mdl-grid">
    <div class="mdl-cell mdl-cell--12-col">
        <div class="mdl-typography--text-center">
            <img src="/img/cpm-logo.png" height="50" width="87.5">
        </div>
        <div class="mdl-typography--text-center mdl-cell mdl-cell--12-col">
            <h5><b>CCD Importer</b> v3.0</h5>
            <h6 class="quote">"{{ Inspiring::quote() }}"</h6>
        </div>
    </div>
</nav>

<div id="ccd-uploader" class="mdl-grid">

    <div v-bind:class="{ 'hide': groupHide }" class="mdl-cell mdl-cell--12-col">
        <div class="mdl-cell mdl-cell--12-col">
            <mdl-progress :progress="progress" :buffer="buffer" class="mdl-cell mdl-cell--12-col"></mdl-progress>
            <p :message="message" class="mdl-cell mdl-cell--12-col mdl-typography--text-left">@{{ message }}</p>
        </div>

        <form method="POST" v-on:submit="onSubmitForm" enctype="multipart/form-data" class="mdl-cell mdl-cell--12-col">

            <div class="mdl-cell mdl-cell--12-col">
                @if(!empty($ccdVendors))
                    <h5>Please choose an Import profile for this CCD.</h5>

                    @foreach($ccdVendors as $vendor)
                        <mdl-radio class="mdl-cell--4-col" :checked.sync="ccdVendor" value="{{ $vendor->id }}"
                                   required>{{ $vendor->vendor_name }}</mdl-radio>
                    @endforeach
                @endif
            </div>

            <div v-bind:class="{ 'hide': formCssHide }" class="mdl-cell mdl-cell--12-col">

                <input type="file" id="ccd" class="dropzone" multiple>

                <div class="mdl-typography--text-center mdl-cell mdl-cell--12-col">
                    <mdl-button primary raised v-mdl-ripple-effect type="submit" :disabled="!enabled">
                        Upload CCD Records
                    </mdl-button>
                </div>
            </div>
        </form>
    </div>

    <table v-bind:class="{ 'hide': tableHide }" class="mdl-data-table mdl-js-data-table mdl-shadow--2dp full-width">
        <thead>
        <tr>
            <th class="mdl-data-table__cell--non-numeric">Import</th>
            <th class="mdl-data-table__cell--non-numeric">Name</th>
            <th>Allergies</th>
            <th>Medications</th>
            <th>Problems</th>
        </tr>
        </thead>
        <tbody>

        <tr v-for="qaSummary in qaSummaries" id="row-@{{ qaSummary.qa_output_id }}">
            <td id="checkbox-@{{ qaSummary.qa_output_id }}">
                <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect"
                       for="input-@{{ qaSummary.qa_output_id }}">
                    <input v-model="okToImport" value="@{{ qaSummary.qa_output_id }}" type="checkbox"
                           id="input-@{{ qaSummary.qa_output_id }}" class="mdl-checkbox__input">
                    <span class="mdl-checkbox__label"></span>
                </label>
            </td>
            <td class="mdl-data-table__cell--non-numeric">@{{ qaSummary.name }}</td>
            <td>@{{ qaSummary.allergies }}</td>
            <td>@{{ qaSummary.medications }}</td>
            <td>@{{ qaSummary.problems }}</td>
        </tr>
        </tbody>
    </table>

    <div v-bind:class="{ 'hide': tableHide }" class="mdl-typography--text-center mdl-cell mdl-cell--12-col">
        <mdl-button primary raised v-mdl-ripple-effect type="submit" v-on:click="importCcds"
                    :disabled="!okToImport.length" id="importCcdsBtn">
            Import Checked CCDs
        </mdl-button>
    </div>

</div>

<script src="{{ asset('/js/scripts.js') }}"></script>
<script src="/js/uploader.js"></script>
