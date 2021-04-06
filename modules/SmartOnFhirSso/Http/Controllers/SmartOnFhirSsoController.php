<?php


namespace CircleLinkHealth\SmartOnFhirSso\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

interface SmartOnFhirSsoController
{
    function getAuthToken(Request $request): RedirectResponse;
    function getRedirectUrl(): string;
    function getPlatform(): string;
    function getClientId(): string;
    function getUserIdPropertyName(): string;
}
