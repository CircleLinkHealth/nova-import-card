<?php namespace App\Console\Commands;

use App\Facades\StringManipulation;
use App\Location;
use Illuminate\Console\Command;

class FormatLocationPhone extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'format:locationphones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Formats all locations phone numbers to XXX-XXX-XXXX.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $locations = Location::all();

        foreach ( $locations as $loc ) {
            $before = $loc->phone;
            $after = StringManipulation::formatPhoneNumber( $loc->phone );

            if ( strlen( $after ) != 12 ) {
                $this->error('Error on Location with id ' . $loc->id);
                $this->error( $before . ' => ' . $after );
                continue;
            }

            $loc->phone = $after;
            $loc->save();
            $this->info( $before . ' => ' . $after );

        }

        $this->info( 'All done!' );
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
        ];
    }

}
