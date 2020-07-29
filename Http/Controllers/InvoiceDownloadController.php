<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\DownloadZippedMediaWithSignedRequest;
use CircleLinkHealth\Customer\Entities\Media;
use Spatie\MediaLibrary\MediaStream;

class InvoiceDownloadController extends Controller
{
    public function downloadZippedInvoices(DownloadZippedMediaWithSignedRequest $request)
    {
        $ids = explode(',', $request->route('media_ids'));

        $mediaExport = Media::whereIn('id', $ids)->get();

        if ($mediaExport->isEmpty()) {
            return response()->json(
                [
                    'message' => 'We are sorry, zip file does not exist.',
                ],
                400
            );
        }

        $now = now()->toDateTimeString();

        return MediaStream::create("Invoices downloaded at $now.zip")->addMedia($mediaExport);
    }
}
