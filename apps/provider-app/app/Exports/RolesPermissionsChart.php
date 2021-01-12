<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Exports;

use Carbon\Carbon;
use CircleLinkHealth\Core\Traits\AttachableAsMedia;
use CircleLinkHealth\Customer\Entities\Permission;
use CircleLinkHealth\Customer\Entities\Role;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;

class RolesPermissionsChart implements FromCollection, Responsable
{
    use AttachableAsMedia;
    use Exportable;
    /**
     * @var Carbon
     */
    protected $date;

    /**
     * @var string
     */
    private $filename;

    public function __construct()
    {
        $this->date = Carbon::now();
        $this->setFilename();
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        $perms = Permission::with('roles')->get();
        $roles = Role::get();

        $columns = [];
        foreach ($roles as $role) {
            $columns[] = $role->display_name;
        }
        $roles = collect($columns);

        $rows = [];
        foreach ($perms as $perm) {
            $row               = [];
            $row['Permission'] = $perm->display_name;
            foreach ($roles as $role) {
                $input = ' ';
                if ($perm->roles->where('display_name', $role)->count() > 0) {
                    $input = 'X';
                }
                $row[$role] = $input;
            }
            $rows[] = $row;
        }

        return collect($rows);
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename(string $filename = null): RolesPermissionsChart
    {
        if ( ! $filename) {
            $dateString = $this->date->toDateTimeString();
            $filename   = 'Roles-Permissions Chart for ';

            $this->filename = "{$filename} {$dateString}.xls";

            return $this;
        }

        $this->filename = $filename;

        return $this;
    }

    public function storeAndAttachMediaTo($model)
    {
        $filepath = 'exports/'.$this->getFilename();
        $stored   = $this->store($filepath, 'storage');

        return $this->attachMediaTo($model, storage_path($filepath), "excel_report_for_roles_permissions{$this->date->toDateString()}");
    }
}
