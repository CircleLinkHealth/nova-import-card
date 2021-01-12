# Introduction
This package allows you to add synonyms for a specific Model and Field. Frequently we get variations of how a practice's name is spelled. The same applies for locations, and providers. This package is a essentially a model (Synonym) and with a many to one polymorphic relationship setup.

# How to use

### Make a Model synonymable
```$php
class Location extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use Synonymable;
``` 

### Retrieve a Model by its name or synonym
```$php
$location = Location::whereColumnOrSynonym('name', $custodianName)->first();
```
