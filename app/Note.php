<?php

namespace App;

use App\Contracts\PdfReport;
use App\Contracts\PdfReportDispatcher;
use Illuminate\Database\Eloquent\Model;

class Note extends Model implements PdfReport
{
    protected $table = 'notes';

    protected $fillable = [
        'patient_id',
        'author_id',
        'logger_id',        
        'body',
        'isTCM',
        'type',
        'performed_at'
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

        $file_name = base_path('storage/pdfs/notes/' . str_random(40) . '.pdf');
        $pdf->save($file_name, true);

        return $file_name;
    }

    /**
     * Dispatch the PDF report.
     *
     * @return mixed
     */
    public function pdfDispatch()
    {
        $this->pdfDispatcher()
            ->pdfDispatch($this);
    }

    /**
     * Get the PDF dispatcher.
     *
     * @return PdfReportDispatcher
     */
    public function pdfDispatcher() : PdfReportDispatcher
    {
        return $this->patient
            ->primaryPractice
            ->ehr
            ->pdf_dispatcher;
    }
}
