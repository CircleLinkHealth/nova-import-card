<div class="form-group">

    <div class="col-md-4">
        <input class="form-control" name="date"
               type="text" data-field="date"
               id="date"
               placeholder="Date"
               value="{{ isset($window) ? $window->date->format('m-d-Y') : '' }}"
               required>
    </div>

    <div class="col-md-3">
        <input class="form-control" name="window_time_start"
               type="text" data-field="time"
               id="window_time_start"
               placeholder="Window Time Start {{ empty($tzAbbr) ? '' : "($tzAbbr)" }}"
               value="{{ isset($window) ? $window->window_time_start : '' }}"
               required>
    </div>

    <div class="col-md-3">
        <input class="form-control" name="window_time_end"
               type="text" data-field="time"
               id="window_time_end"
               placeholder="Window Time End {{ empty($tzAbbr) ? '' : "($tzAbbr)" }}"
               value="{{ isset($window) ? $window->window_time_end : '' }}"
               required>
    </div>

    <div class="col-md-2">
        <input type="submit" class="btn btn-primary" value="{{ isset($submitBtnText) ? $submitBtnText : 'Store Window' }}"
               name="submit" id="store-window">
    </div>
</div>