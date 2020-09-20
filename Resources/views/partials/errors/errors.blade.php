@if (isset($errors))
    @if (count($errors) > 0)
        <div class="alert alert-danger" style="line-height: 2">
            <ul class="list-group">
                @foreach ($errors->getMessages() as $key => $value)
                    @foreach ($value as $error)
                        <li class="{{$key}}">
                            {!! $error !!}
                            @if($key === 'outdated-browser')

                                <div class="col-md-12 text-center">
                                    <div class="col-md-12">
                                        <div class="radio">
                                            <input type="button" onclick="onClick()" value="Don't show this again"/>
                                        </div>
                                    </div>
                                </div>

                                <script>
                                    function onClick() {
                                        document.getElementsByClassName("outdated-browser")[0].style.display = "none";
                                        document.cookie = "skip_outdated_browser_check=1";
                                        hideAlertContainerIfYouMust();
                                    }

                                    function hideAlertContainerIfYouMust() {
                                        const alertContainer = document.getElementsByClassName('alert')[0];
                                        const list = alertContainer.children[0];
                                        const listItems = list.children;
                                        let allHidden = true;
                                        for (let i = 0; i < listItems.length; i++) {
                                            const item = listItems[i];
                                            if (item.style.display !== 'none') {
                                                allHidden = false;
                                                break;
                                            }
                                        }
                                        if (allHidden) {
                                            alertContainer.style.display = 'none';
                                        }
                                    }
                                </script>

                            @endif


                        </li>
                    @endforeach
                @endforeach
            </ul>
        </div>

        <!-- added the if here because the margin-bottom 5% was always applied, even if div was empty -->
        @if($errors->has('invalid-browser') || $errors->has('invalid-browser-force-switch'))
            <div class="row" style="margin-bottom: 5%;">
                @include('core::partials.errors.incompatibleBrowser')
            </div>
        @endif
    @endif
@endif