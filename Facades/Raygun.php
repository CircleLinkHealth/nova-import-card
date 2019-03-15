<?php namespace CircleLinkHealth\Raygun\Facades;
 
use Illuminate\Support\Facades\Facade;
 
class Raygun extends Facade {
 
  /**
   * Get the registered name of the component.
   *
   * @return string
   */
  protected static function getFacadeAccessor() { return 'raygun'; }
 
}