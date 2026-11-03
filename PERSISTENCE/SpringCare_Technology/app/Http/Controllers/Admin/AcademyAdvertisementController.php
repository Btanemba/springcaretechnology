<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AcademyAdvertisement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademyAdvertisementController extends Controller
{
    public function edit(AcademyAdvertisement $advertisement): View
    {
        return view('admin.academy-advertisement', [
            'title' => 'Academy advert',
            'settings' => $advertisement->settings(),
            'visible' => $advertisement->isVisible(),
            'timezone' => AcademyAdvertisement::TIMEZONE,
        ]);
    }

    public function update(Request $request, AcademyAdvertisement $advertisement): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
            'starts_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'ends_at' => ['nullable', 'date_format:Y-m-d\TH:i', ...($request->filled('starts_at') ? ['after:starts_at'] : [])],
        ]);

        $advertisement->save($validated);

        return redirect()->route('admin.academy-advertisement.edit')->with('success', 'Academy advert settings saved.');
    }
}