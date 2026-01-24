<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DigitalMarketingUpload;


class DigitalMarketingController extends Controller
{
    public function index()
    {
        return view('communication.digital', [
            'uploads' => DigitalMarketingUpload::latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $path = $request->file('image')
            ->store('public/digital_marketing');

        DigitalMarketingUpload::create([
            'uploaded_by' => auth()->id(),
            'image_path' => $path,
            'description' => $request->description
        ]);

        return back();
    }
}

