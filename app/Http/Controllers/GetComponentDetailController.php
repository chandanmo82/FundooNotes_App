<?php

namespace App\Http\Controllers;

use http\Env\Response;
use Illuminate\Http\Request;

class GetComponentDetailController extends Controller
{
    /**
     * Get Component Name and Component Description
     *
     * @param Request $request
     * @return Response|string
     */
    public function getData(Request $request)
    {
        $userData = $request->only([
            'ComponentName',
            'ComponentDescription',
        ]);

        if (empty($userData['ComponentName']) && empty($userData['ComponentDescription'])) {
            return new \Exception('Missing data', 404);
        }
        return $userData['ComponentName'] . ' ' . $userData['ComponentDescription'];
    }
}
