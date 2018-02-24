<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecords\Ccda;
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

    public function store(Request $request) {
        $ccda = new Ccda();
        $ccda->json = $request->input('json');
        $ccda->xml = $request->input('xml');
        $ccda->practice_id = $request->practice_id;
        $ccda->user_id = auth()->user()->id;
        if ($ccda->user_id && ($ccda->xml || $ccda->json)) {
            return $this->ccdaService->create($ccda);
        }
        else return $this->badRequest('"user_id" and one of "xml" and "json" are compulsory fields');
    }
}
