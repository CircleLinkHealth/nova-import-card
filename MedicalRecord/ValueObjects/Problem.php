<?php

namespace CircleLinkHealth\Eligibility\MedicalRecord\ValueObjects;

use stdClass;

class Problem
{
    private $comment;
    private $reference;
    private $referenceTitle;
    private $name;
    private $status;
    private $age;
    private $code;
    private $codeSystem;
    private $codeSystemName;
    private $translations;
    private $startDate;
    private $endDate;
    
    public function toObject()
    {
        $obj = $this->arrayToObject($this->toArray());
        
        $std= new stdClass();
        $std->start = $obj->date_range['start'];
        $std->end = $obj->date_range['end'];
        
        $obj->date_range = $std;
        
        return $obj;
    }
        
        public function toArray() {
        return [
            'reference'        => $this->getReference(),
            'reference_title'  => $this->getReferenceTitle(),
            'date_range'       => [
                'start' => $this->getStartDate(),
                'end'   => $this->getEndDate(),
            ],
            'name'             => $this->getName(),
            'status'           => $this->getStatus(),
            'age'              => $this->getAge(),
            'code'             => $this->getCode(),
            'code_system'      => $this->getCodeSystem(),
            'code_system_name' => $this->getCodeSystemName(),
            'translations'     => [
                $this->getTranslations()
            ],
            'comment'          => $this->getComment(),
        ];
    }
    
    /**
     * @param mixed $codeSystemName
     *
     * @return Problem
     */
    public function setCodeSystemName($codeSystemName)
    {
        $this->codeSystemName = $codeSystemName;
        
        return $this;
}
    
    /**
     * @param mixed $comment
     *
     * @return Problem
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        
        return $this;
}
    
    /**
     * @param mixed $reference
     *
     * @return Problem
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
        
        return $this;
}
    
    /**
     * @param mixed $referenceTitle
     *
     * @return Problem
     */
    public function setReferenceTitle($referenceTitle)
    {
        $this->referenceTitle = $referenceTitle;
        
        return $this;
}
    
    /**
     * @param mixed $name
     *
     * @return Problem
     */
    public function setName($name)
    {
        $this->name = $name;
        
        return $this;
}
    
    /**
     * @param mixed $status
     *
     * @return Problem
     */
    public function setStatus($status)
    {
        $this->status = $status;
        
        return $this;
}
    
    /**
     * @param mixed $code
     *
     * @return Problem
     */
    public function setCode($code)
    {
        $this->code = $code;
        
        return $this;
}
    
    /**
     * @param mixed $codeSystem
     *
     * @return Problem
     */
    public function setCodeSystem($codeSystem)
    {
        $this->codeSystem = $codeSystem;
        
        return $this;
}
    
    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }
    
    /**
     * @return mixed
     */
    public function getReference()
    {
        return $this->reference;
    }
    
    /**
     * @return mixed
     */
    public function getReferenceTitle()
    {
        return $this->referenceTitle;
    }
    
    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }
    
    /**
     * @return mixed
     */
    public function getCodeSystem()
    {
        return $this->codeSystem;
    }
    
    /**
     * @return mixed
     */
    public function getCodeSystemName()
    {
        return $this->codeSystemName;
    }
    
    /**
     * @param mixed $age
     *
     * @return Problem
     */
    public function setAge($age)
    {
        $this->age = $age;
        
        return $this;
}
    
    /**
     * @return mixed
     */
    public function getAge()
    {
        return $this->age;
    }
    
    private function getTranslations()
    {
        return $this->translations;
    }
    
    private function arrayToObject(array $array, $convertNestedArraysToStd = false): object
    {
        $object = new stdClass();
        
        foreach ($array as $key => $value) {
            if (true === $convertNestedArraysToStd && is_array($value)) {
                $value = $this->arrayToObject($value);
            }
            $object->$key = $value;
        }
        
        return $object;
    }
    
    /**
     * @param mixed $translations
     *
     * @return Problem
     */
    public function setTranslations(array $translations)
    {
//        Expected Format:
//        [
//            'name'             => null,
//            'code'             => null,
//            'code_system'      => null,
//            'code_system_name' => null,
//        ]
        
        $this->translations = $translations;
        
        return $this;
}
    
    /**
     * @param mixed $startDate
     *
     * @return Problem
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
        
        return $this;
}
    
    /**
     * @param mixed $endDate
     *
     * @return Problem
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
        
        return $this;
}
    
    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }
    
    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->endDate;
    }
}