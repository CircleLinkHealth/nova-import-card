<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

class MetaTag
{
    public $severity;
    public $title;
    public $tooltip;

    public function __construct($severity, $title, $tooltip = null)
    {
        $this->severity = $severity;
        $this->title    = $title;
        $this->tooltip  = $tooltip;
    }

    /**
     * @param mixed $severity
     *
     * @return MetaTag
     */
    public function setSeverity($severity)
    {
        $this->severity = $severity;

        return $this;
    }

    /**
     * @param mixed $title
     *
     * @return MetaTag
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param mixed $tooltip
     *
     * @return MetaTag
     */
    public function setTooltip($tooltip)
    {
        $this->tooltip = $tooltip;

        return $this;
    }
}
