<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 11/22/2017
 * Time: 10:50 AM
 */

namespace App\View;

class MetaTag
{
    public $severity;
    public $title;
    public $tooltip;

    public function __construct($severity, $title, $tooltip = null)
    {
        $this->severity = $severity;
        $this->title = $title;
        $this->tooltip = $tooltip;
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
