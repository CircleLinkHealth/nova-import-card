<?php


namespace CircleLinkHealth\Eligibility\CcdaImporter\Traits;


use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\Settings\Hooks;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Support\Collection;

trait FiresImportingHooks
{
    public function fireImportingHook(string $hookName, User $patient, Ccda $ccda, $payload)
    {
        $patient->loadMissing('primaryPractice');
        
        if ( ! $patient->primaryPractice) {
            return null;
        }
        
        return $this->runHook($hookName, $patient->primaryPractice, $patient, $ccda, $payload);
    }
    
    public function shouldRunHook(string $hookName, Practice $practice): bool
    {
        if ( ! $practice->importing_hooks instanceof Collection) {
            return false;
        }
        
        if ( ! $practice->importing_hooks->keys()->contains($hookName)) {
            return false;
        }
        
        $args = $practice->importing_hooks->get($hookName) ?? [];
    
        if ( ! array_key_exists('listener', $args)) {
            return false;
        }
        
        if ( ! array_key_exists($args['listener'], Hooks::LISTENERS)) {
            return false;
        }
        
        return true;
    }
    
    private function runHook(string $hookName, Practice $practice, User $user, Ccda $ccda, $payload)
    {
        if ( ! $this->shouldRunHook($hookName, $practice)) {
            return null;
        }
        
        $args = $practice->importing_hooks->get($hookName);
        
        return app(Hooks::LISTENERS[$args['listener']], ['patient' => $user, 'ccda' => $ccda, 'payload' => $payload])->run();
    }
}