<?php

namespace App\Http\Controllers;

use App\Ccda;
use App\Services\CcdaService;
use Illuminate\Http\Request;

class CcdaController extends Controller
{
    private $ccdaService;

    public function __construct(CcdaService $ccdaService) {
        $this->ccdaService = $ccdaService;
    }

    public function index() {
        return $this->ccdaService->ccda();
    }

    public function show($id) {
        return $this->ccdaService->ccda($id);
    }
}
