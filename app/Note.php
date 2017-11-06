<?php

namespace App;

use App\Contracts\PdfReport;
use App\Models\Addendum;
use App\Traits\IsAddendumable;
use App\Traits\PdfReportTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Note extends \App\BaseModel implements PdfReport
{
    use IsAddendumable,
        PdfReportTrait;

    protected $table = 'notes';

    protected $fillable = [
        'patient_id',
        'author_id',
        'logger_id',
        'body',
        'isTCM',
        'type',
        'did_medication_recon',
        'performed_at',
    ];


    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id', 'id');
    }

    public function mail()
    {
        return $this->hasMany('App\MailLog');
    }

    public function call()
    {
        return $this->hasOne('App\Call');
    }

    public function author()
    {
        return $this->belongsTo('App\User', 'author_id', 'id');
    }

    public function program()
    {
        return $this->belongsTo('App\User', 'author_id', 'id');
    }

    /**
     * Create a PDF of this resource and return the path to it.
     *
     * @return string
     */
    public function toPdf() : string
    {
        $problems = $this->patient
            ->cpmProblems
            ->pluck('name')
            ->all();

        $pdf = app('snappy.pdf.wrapper');
        $pdf->loadView('pdfs.note', [
            'patient'  => $this->patient,
            'problems' => $problems,
            'sender'   => $this->author,
            'note'     => $this,
            'provider' => $this->patient->billingProvider(),
        ]);

        $this->fileName = Carbon::now()->toDateString() . '-' . $this->patient->fullName . '.pdf';
        $filePath = base_path('storage/pdfs/notes/' . $this->fileName);
        $pdf->save($filePath, true);

        return $filePath;
    }
}
