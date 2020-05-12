<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use CircleLinkHealth\SharedModels\Entities\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Storage;

class PatientCarePlanController extends Controller
{
    public function deletePdf($pdfId)
    {
        Pdf::destroy($pdfId);

        return response()->json($pdfId);
    }

    public function downloadPdf($fileName)
    {
        $pdf = Pdf::whereFilename($fileName)->first();

        if ( ! $pdf) {
            return "Could not find PDF with filename: ${fileName}";
        }

        return response(base64_decode($pdf->file), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$fileName.'"',
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param mixed $patientId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($patientId)
    {
        $cp = CarePlan::with('pdfs')
            ->where('user_id', '=', $patientId)
            ->first();

        if ( ! $cp) {
            return response()->json([
                'message' => 'Careplan not found.',
            ], 404);
        }

        foreach ($cp->pdfs as $pdf) {
            $pdf->url   = route('download.pdf.careplan', [$pdf->filename]);
            $pdf->label = "CarePlan uploaded on {$pdf->created_at->format('m/d/Y')} at {$pdf->created_at->format('g:i:s A T')}";
        }

        return response()->json($cp);
    }

    public function uploadPdfs(Request $request, $careplanId)
    {
        $carePlan = CarePlan::with('patient')->whereId($careplanId)->first();

        if ( ! $carePlan) {
            return 'careplan not found';
        }

        $created = [];

        foreach ($request->file()['file'] as $file) {
            $now      = Carbon::now()->toDateTimeString();
            $hash     = Str::random();
            $filename = sha1("{$carePlan->patient->getFirstName()}_{$carePlan->patient->getLastName()}-{$hash}-{$now}-CarePlan").'.pdf';
            Storage::disk('storage')
                ->makeDirectory('patient/pdf-careplans');
            file_put_contents(storage_path("patient/pdf-careplans/${filename}"), file_get_contents($file));

            $pdf = Pdf::create([
                'uploaded_by'  => auth()->user()->id,
                'pdfable_type' => CarePlan::class,
                'pdfable_id'   => $careplanId,
                'filename'     => $filename,
                'file'         => base64_encode(file_get_contents($file)),
            ]);

            $pdf->url   = route('download.pdf.careplan', [$pdf->filename]);
            $pdf->label = "CarePlan uploaded on {$pdf->created_at->format('m/d/Y')} at {$pdf->created_at->format('g:i:s A T')}";

            $created[] = $pdf;
        }

        if (CarePlan::WEB == $carePlan->mode) {
            $carePlan->mode = CarePlan::PDF;
            $carePlan->save();
        }

        return response()->json($created);
    }
}
