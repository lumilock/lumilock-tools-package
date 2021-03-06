<?php

namespace lumilock\lumilockToolsPackage\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use perrinthibaud\basesFrmgr\App\Http\Resources\AuditLightResource;
use perrinthibaud\basesFrmgr\App\Models\Frmgr_audit as Audit;

class PermissionsController extends Controller
{
    /**
     * Instantiation d'un nouveau controller AuditController.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Récupération de toutes les Audits.
     *
     * @return Response
     */
    public function getPermissions($package)
    {
        return response()->json(
            [
                'data' => Config::get("$package.permissions"),
                'status' => 'SUCCESS',
                'message' => "Permissions list for $package app."
            ],
            200
        );
    }
}
