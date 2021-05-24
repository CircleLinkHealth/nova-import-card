# Importing

### Intro
This module supports different implementations to import patients into CPM.
The entry point can be found at `MedicalRecord/MedicalRecordFactory.php`.

#### Import CCDA
1. ```
   $ccda = Ccda::create([
       'practice_id' => $practice,
       'location_id' => $location,
       'user_id'     => $uploadedBy,
       'xml'         => '<?xml <ClinicalDocument blah blah CCDA',
    ]);
   ```
2. In `MedicalRecord/MedicalRecordFactory.php`, define a create method for the CCDA you are going to import, 
   i.e. for Demo practice:
   ```
   public function createDemoMedicalRecord(User $user, ?Ccda $ccda) {
      // return the Medical Record based on the type of your implementation
      // the return class should implement the MedicalRecordTemplate interface
      return new HtmlInXmlMedicalRecrod();
   }
   ```
