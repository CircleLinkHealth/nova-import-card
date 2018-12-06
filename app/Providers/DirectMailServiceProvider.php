<?php

namespace App\Providers;

use App\Contracts\DirectMail;
use App\Services\PhiMail\IncomingMessageHandler;
use App\Services\PhiMail\PhiMail;
use App\Services\PhiMail\PhiMailConnector;
use Illuminate\Support\ServiceProvider;

class DirectMailServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
    
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            DirectMail::class,
            function () {
                return new PhiMail(
                    $this->initPhiMailConnection(),
                    app()->make(IncomingMessageHandler::class)
                );
            }
        );
    }
    
    /**
     * @return PhiMailConnector
     * @throws \Exception
     */
    private function initPhiMailConnection(): PhiMailConnector
    {
        $phiMailUser = config('services.emr-direct.user');
        $phiMailPass = config('services.emr-direct.password');
        
        // Use the following command to enable client TLS authentication, if
        // required. The key file referenced should contain the following
        // PEM data concatenated into one file:
        //   <your_private_key.pem>
        //   <your_client_certificate.pem>
        //   <intermediate_CA_certificate.pem>
        //   <root_CA_certificate.pem>
        //
        PhiMailConnector::setClientCertificate(
            base_path(config('services.emr-direct.conc-keys-pem-path')),
            config('services.emr-direct.pass-phrase')
        );
        
        // This command is recommended for added security to set the trusted
        // SSL certificate or trust anchor for the phiMail server.
        PhiMailConnector::setServerCertificate(base_path(config('services.emr-direct.server-cert-pem-path')));
        
        $phiMailServer = config('services.emr-direct.mail-server');
        $phiMailPort   = config('services.emr-direct.port');
        
        $connector = new PhiMailConnector($phiMailServer, $phiMailPort);
        $connector->authenticateUser($phiMailUser, $phiMailPass);
        
        return $connector;
    }
}
