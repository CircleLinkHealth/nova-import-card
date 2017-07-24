<?php

namespace App\Http\Controllers\API;

use App\CarePlan;
use App\Http\Controllers\Controller;
use App\Models\Pdf;
use Carbon\Carbon;
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
            $pdf->url = route('download.pdf.careplan', ['fileName' => $pdf->filename]);
            $pdf->label = "CarePlan uploaded on {$pdf->created_at->format('m/d/Y')} at {$pdf->created_at->format('g:i A T')}";
        }

        return response()->json($cp);
    }

    public function deletePdf($pdfId) {
        Pdf::destroy($pdfId);

        return response()->json($pdfId);
    }

    public function uploadPdfs(Request $request, $careplanId) {
        $carePlan = CarePlan::with('patient')->whereId($careplanId)->first();

        if (!$carePlan) {
            return 'careplan not found';
        }

        foreach ($request->file()['files'] as $file) {
            $now = Carbon::now()->toDateTimeString();
            $filename = "{$carePlan->patient->first_name}_{$carePlan->patient->last_name}-{$now}-CarePlan.pdf";
            file_put_contents(storage_path("patient/pdf-careplans/$filename"), file_get_contents($file));

            $created = Pdf::create([
                'uploaded_by' => auth()->user()->id,
                'pdfable_type' => CarePlan::class,
                'pdfable_id' => $careplanId,
                'filename' => $filename,
                'file' => file_get_contents($file),
            ]);
        }

        return response()->json();
    }

    public function downloadPdf($filePath)
    {
        $path = storage_path("patient/pdf-careplans/$filePath");

        if (!file_exists($path)) {
            return "Could not locate file with name: $filePath";
        }

        return response()->download($path, $filePath, [
            'Content-Length: ' . filesize($path),
        ]);
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
