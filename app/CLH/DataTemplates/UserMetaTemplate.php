<?php

namespace App\CLH\DataTemplates;

use App\CLH\Contracts\DataTemplate;
use App\User;

class UserMetaTemplate extends BaseDataTemplate implements DataTemplate
{
    public $first_name;
    public $last_name;
    //public $description;
    //public $admin_color = 'fresh';
    //public $show_admin_bar_front = false;
    public $careplan_qa_approver;
    public $careplan_qa_date;
    public $careplan_provider_approver;
    public $careplan_provider_date;
    public $careplan_status = 'draft';
    public $ccm_status = 'enrolled';
}
