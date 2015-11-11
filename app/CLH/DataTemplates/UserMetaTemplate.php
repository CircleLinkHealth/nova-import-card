<?php

namespace App\CLH\DataTemplates;


use App\WpUser;

class UserMetaTemplate extends DataTemplate
{
    public $first_name;
    public $last_name;
    public $description;
    public $admin_color = 'fresh';
    public $cur_month_activity_time = 0;
    public $show_admin_bar_front = false;
    public $careplan_qa_approver;
    public $careplan_qa_date;
    public $careplan_provider_approver;
    public $careplan_provider_date;
    public $careplan_status = 'draft';
    public $ccm_enabled;
}