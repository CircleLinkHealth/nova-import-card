<?php

namespace App\Http\Controllers\API;

use App\CarePlan;
use App\Http\Controllers\Controller;
use App\Models\Pdf;
use Illuminate\Http\Request;

class PatientCarePlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($patientId)
    {
        $cp = CarePlan::with('pdfs')
            ->where('user_id', '=', $patientId)
            ->first();

        foreach ($cp->pdfs as $pdf) {
            $pdf->url = route('download', ['fileName' => $pdf->file]);
            $pdf->label = "CarePlan uploaded on {$pdf->created_at->format('m/d/Y')} at {$pdf->created_at->format('g:i A T')}";
        }

        return $cp;
    }

    public function deletePdf($pdfId) {
        return Pdf::destroy($pdfId);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
