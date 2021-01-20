@auth
    @if(!isset($isPdf) && (auth()->user()->isAdmin() || auth()->user()->isCareCoach()))
        <a target="_blank" id="issue-collector-btn" type="button" class="btn btn-danger" href="https://forms.clickup.com/f/868qg-1344/NANLQGZ93PRHYXPTBH">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bug" viewBox="0 0 16 16">
                <path d="M4.355.522a.5.5 0 0 1 .623.333l.291.956A4.979 4.979 0 0 1 8 1c1.007 0 1.946.298 2.731.811l.29-.956a.5.5 0 1 1 .957.29l-.41 1.352A4.985 4.985 0 0 1 13 6h.5a.5.5 0 0 0 .5-.5V5a.5.5 0 0 1 1 0v.5A1.5 1.5 0 0 1 13.5 7H13v1h1.5a.5.5 0 0 1 0 1H13v1h.5a1.5 1.5 0 0 1 1.5 1.5v.5a.5.5 0 1 1-1 0v-.5a.5.5 0 0 0-.5-.5H13a5 5 0 0 1-10 0h-.5a.5.5 0 0 0-.5.5v.5a.5.5 0 1 1-1 0v-.5A1.5 1.5 0 0 1 2.5 10H3V9H1.5a.5.5 0 0 1 0-1H3V7h-.5A1.5 1.5 0 0 1 1 5.5V5a.5.5 0 0 1 1 0v.5a.5.5 0 0 0 .5.5H3c0-1.364.547-2.601 1.432-3.503l-.41-1.352a.5.5 0 0 1 .333-.623zM4 7v4a4 4 0 0 0 3.5 3.97V7H4zm4.5 0v7.97A4 4 0 0 0 12 11V7H8.5zM12 6a3.989 3.989 0 0 0-1.334-2.982A3.983 3.983 0 0 0 8 2a3.983 3.983 0 0 0-2.667 1.018A3.989 3.989 0 0 0 4 6h8z"></path>
            </svg>
            Report Bug
        </a>
    @endif
@endauth

<style>
    #issue-collector-btn {
        position: fixed;
        left: 0;
        bottom: 0;
        background-color: #f15b58;
        color: #fff !important;
        font-size: 16px;
        padding: 10px;
        cursor: pointer;
        border-radius: 0;
        font-weight: 400;
        border: none;
    }

    .bug {
        margin-bottom: -2px
    }
</style>
