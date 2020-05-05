<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Permission extends \Spatie\Permission\Models\Permission
{
    public static function defaultPermissions()
    {
        return [
            'view_users',
            'add_users',
            'edit_users',
            'delete_users',

            'view_roles',
            'add_roles',
            'edit_roles',
            'delete_roles',

            'view_permissions',
            'add_permissions',
            'edit_permissions',
            'delete_permissions',

            'view_configurations',
            'add_configurations',
            'edit_configurations',
            'delete_configurations',

            'view_sms_templates',
            'add_sms_templates',
            'edit_sms_templates',
            'delete_sms_templates',

            'view_consultant_types',
            'add_consultant_types',
            'edit_consultant_types',
            'delete_consultant_types',
        ];
    }
}
