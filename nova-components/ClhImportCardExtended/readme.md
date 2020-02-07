#CLH Import Card Extended
This custom Nova Card extends [Sparclex's Nova Import Card](https://github.com/Sparclex/nova-import-card). 
It gives us the ability to add one or more extra Input fields in the existing import card, using Nova Fields, which can then be accessed in the Importer Class.
The importer class is basically just an import implementation of the [laravel-excel package](https://github.com/Maatwebsite/Laravel-Excel).

##How to use

The Card takes the Resource and an array of Nova Fields as arguments upon creation.
You can add it in the cards method of your Resource.

```
    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [
            ClhImportCardExtended::make(self::class, [
                Text::make('email'),
            ]),
        ];
    }
```
You can also add multiple fields to the card. 
In case you are using a Select fields, which takes an array of `key => value` pairs as options, the **key** will be used as the value.

```
/**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function cards(Request $request)
    {
        $practices = Practice::pluck('display_name', 'id')
            ->toArray();

        return [
            new ClhImportCardExtended(self::class, [
                Select::make('practice')->options($practices)->withModel(Practice::class)
            ]),
        ];
    }
```


To pass validation rules into the card use the `->inputRules()` method, which accepts an array of rules. These rules will be passed in 
a `Validator`.

```
    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [
            ClhImportCardExtended::make(self::class, [
                Text::make('email')

                    ->inputRules(['required', 'email']),
            ]),
        ];
    }
```



If you wish the input to return a model, then you can use the `withModel()` method. This takes the Model Class Name as the first argument
and an optional second argument which specifies the key that the the Model will be queried with using the value in the input. If no key is supplied
the card will query the Model's ID.

```
 /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [
            ClhImportCardExtended::make(self::class, [
                Text::make('email')->withModel(User::class, 'email'),
            ]),
        ];
    }
```

The fields will be available in the `$resource` variable in the Import Card. Use the method `getFieldValue()` using the field's name (case insensitive) to retrieve either the input's inserted value, or the Model, 
in case you have used `withModel()`. 
```
public function __construct($resource, $attributes, $rules, $modelClass)
    {
        $this->resource       = $resource;
        $this->attributes     = $attributes;
        $this->rules          = $rules;
        $this->modelClass     = $modelClass;
        $this->user           = $resource->fields->getFieldValue('email');
    }
```


*You can also customize the title of the card by passing in a 3rd arguement to the card.*

##Examples:
See the `Practice` Nova Resource and `PatientConsentLetters` Importer class for an implementation.