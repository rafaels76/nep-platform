<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ActiveCompanyController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'string'],
        ]);

        $company = $request->user()
            ->availableCompanies()
            ->whereKey($data['company_id'])
            ->first();

        abort_unless($company, 403, 'No tienes acceso a esa empresa.');

        $request->session()->put('active_company_id', $company->id);

        return redirect()->back();
    }
}
