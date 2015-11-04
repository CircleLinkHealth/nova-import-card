<?php

namespace App\CLH\DataTemplates;


use App\WpUser;

class UserMetaTemplate extends DataTemplate
{
    public $first_name;
    public $last_name;
    public $nickname;
    public $description;
    public $admin_color = 'fresh';
    public $cur_month_activity_time = 0;
    public $show_admin_bar_front = false;
    public $careplan_approved;
    public $careplan_approver;
    public $ccm_enabled;
    public $careplan_status;
}