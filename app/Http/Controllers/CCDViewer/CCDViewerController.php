<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\CCDViewer;

use App\CLH\Repositories\CCDImporterRepository;
use App\Http\Controllers\Controller;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaStream;

class CCDViewerController extends Controller
{
    private $repo;

    public function __construct(CCDImporterRepository $repo)
    {
        $this->repo = $repo;
        ini_set('memory_limit', '512M');
    }

    public function create()
    {
        return view('CCDViewer.old-viewer');
    }

    public function downloadXml(Request $request, $ccdaId)
    {
        $ccda = Ccda::withTrashed()
            ->with('media')
            ->findOrFail($ccdaId);

        $media = $ccda->getMedia('ccd')->first();

        return $media ? $media : abort(400, 'XML was not found.');
    }

    public function exportAllCcds($userId)
    {
        $mediaExport = Media::where('model_type', Ccda::class)->whereIn('model_id', function ($query) use ($userId) {
            $query->select('id')
                ->from((new Ccda())->getTable())
                ->wherePatientId($userId);
        })->get();

        if ($mediaExport->isNotEmpty()) {
            return MediaStream::create("Patient {$userId} CCDAs.zip")->addMedia($mediaExport);
        }

        abort(400, 'CCDA was not found.');
    }

    public function oldViewer(Request $request)
    {
        if ($request->hasFile('uploadedCcd')) {
            $xml = file_get_contents($request->file('uploadedCcd'));

            $ccd = json_decode($this->repo->toBlueButtonJson($xml));

            return view('CCDViewer.old-viewer', compact('ccd'));
        }
    }

    public function show(Request $request, $ccdaId)
    {
        $ccda = Ccda::withTrashed()
            ->with('media')
            ->findOrFail($ccdaId);

        $type = $request->input('type');

        if ('xml' == $type) {
            $media = $ccda->getMedia('ccd')->first();

            return $media ? $media : 'N/A';
        }

        if ($ccda) {
            $ccd = $ccda->bluebuttonJson();

            return view('CCDViewer.old-viewer', compact('ccd'));
        }

        abort(400, 'CCDA was not found.');
    }

    public function showByUserId($userId)
    {
        $ccda = Ccda::wherePatientId($userId)->latest()->first();

        if ($ccda) {
            $ccd = $ccda->bluebuttonJson();

            return view('CCDViewer.old-viewer', compact('ccd'));
        }

        abort(400, 'CCDA was not found.');
    }

    public function showUploadedCcd(Request $request)
    {
        if ($request->hasFile('uploadedCcd')) {
            $ccd = file_get_contents($request->file('uploadedCcd'));

            $template = view('CCDViewer.bb-ccd-viewer', compact('ccd'))->render();

            return view('CCDViewer.viewer', compact('template'));
        }
    }

    public function viewSource(Request $request)
    {
        if ($xml = urldecode($request->input('xml'))) {
            return view('CCDViewer.old-viewer', compact('xml'));
        }
    }
}
