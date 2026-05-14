<?php

namespace App\Http\Controllers;

use App\Models\GoogleSheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class GoogleSheetController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $sheets = Auth::user()->googleSheets;
        return view('sheets.index', compact('sheets'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', GoogleSheet::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
        ]);

        Auth::user()->googleSheets()->create($validated);

        return redirect()->route('sheets.index')->with('success', 'Google Sheet linked successfully.');
    }

    public function show(GoogleSheet $sheet)
    {
        $this->authorize('view', $sheet);

        // Convert the sharing link to an embed link if possible
        $embedUrl = $sheet->url;
        if (str_contains($embedUrl, '/edit')) {
            $embedUrl = str_replace('/edit', '/preview', $embedUrl);
        } elseif (!str_contains($embedUrl, 'preview')) {
            // Append preview if not present
            $embedUrl = rtrim($embedUrl, '/') . '/preview';
        }

        return view('sheets.show', compact('sheet', 'embedUrl'));
    }

    public function destroy(GoogleSheet $sheet)
    {
        $this->authorize('delete', $sheet);

        $sheet->delete();

        return redirect()->route('sheets.index')->with('success', 'Google Sheet link removed.');
    }
}
