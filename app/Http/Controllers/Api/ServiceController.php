<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = Service::with('category')->where('active', true)->get();
        return response()->json($services, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'description'       => 'required|string|max:255',
            'duration_minutes'  => 'required|integer|min:0',
            'price'             => 'required|numeric|min:0',
            'active'            => 'required|boolean',
        ]);

        $service = Service::create($request->all());
        return response()->json($service, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $service = Service::findOrFail($id);
        return response()->json($service, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $service = Service::findOrFail($id);
        $service->update($request->all());
        return response()->json($service, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $service = Service::findOrFail($id);
        $service->delete();
        return response()->json(null, 204);
    }

    public function getServicesByCategory($categoryId)
    {
        $services = Service::with('category')
            ->where('active', true)
            ->where('category_id', $categoryId) // Assuming you have a `category_id` field in your `services` table
            ->get();

        return response()->json($services, 200);
    }

}
