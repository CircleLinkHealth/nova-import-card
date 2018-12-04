<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CLH\DataTemplates;

use App\CLH\Contracts\DataTemplate;

class UserMetaTemplate extends BaseDataTemplate implements DataTemplate
{
    public $careplan_provider_approver;
    public $careplan_provider_date;
    //public $description;
    //public $admin_color = 'fresh';
    //public $show_admin_bar_front = false;
    public $careplan_qa_approver;
    public $careplan_qa_date;
    public $careplan_status = 'draft';
    public $ccm_status      = 'enrolled';
    public $first_name;
    public $last_name;
}
