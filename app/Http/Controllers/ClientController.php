<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;

class ClientController extends Controller
{
    public function index(Request $request) {
        // Get sort direction from request, default to 'asc'
        $sortDirection = $request->get('sort', 'asc');

        // Validate sort direction
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }

        // Sort clients by company name
        $clients = Client::orderBy('comp_name', $sortDirection)->get();
        
        return view('clients.index', [
            'clients' => $clients,
            'currentSort' => $sortDirection,
        ]);
    }

    public function create() {
        return view('clients.create');
    }

    public function store(Request $request){
        $data = $request->validate([
            'comp_name' => 'required|string|max:255',
            'comp_address1' => 'required|string|max:255',
            'comp_address2' => 'nullable|string|max:255',
            'comp_address3' => 'nullable|string|max:255',
            'comp_phone1' => 'nullable|string|max:15',
            'phone1_name' => 'nullable|string|max:255',
            'comp_phone2' => 'nullable|string|max:15',
            'phone2_name' => 'nullable|string|max:255',
        ]);

        $newClient = Client::create($data);
        return redirect()->route('clients.index')->with('success', 'Client created successfully.');
    }

    public function edit(Client $client) {
        return view('clients.edit', ['client' => $client]);
    }

    public function update(Request $request, Client $client) {
        $data = $request->validate([
            'comp_name' => 'required|string|max:255',
            'comp_address1' => 'required|string|max:255',
            'comp_address2' => 'nullable|string|max:255',
            'comp_address3' => 'nullable|string|max:255',
            'comp_phone1' => 'nullable|string|max:15',
            'phone1_name' => 'nullable|string|max:255',
            'comp_phone2' => 'nullable|string|max:15',
            'phone2_name' => 'nullable|string|max:255',
        ]);

        $client->update($data);
        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
    }

    public function destroy(Client $client) {
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }
}
