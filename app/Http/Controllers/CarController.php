<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CarController extends Controller
{
    // READ – anyone can view
    public function index()
    {
        $cars = Car::latest()->get();
        return view('cars.index', compact('cars'));
    }

    // CREATE FORM – admin only (guarded by middleware)
    public function create()
    {
        return view('cars.create');
    }

    // STORE – admin only
    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand'         => 'required|string|max:100',
            'model'         => 'required|string|max:100',
            'seat_capacity' => 'required|integer|min:1|max:20',
            'plate_number'  => 'required|string|max:20|unique:cars,plate_number',
            'year'          => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'price_per_day' => 'required|numeric|min:1',
            'status'        => 'required|in:available,rented',
            'description'   => 'nullable|string|max:500',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'image_url'     => 'nullable|url|max:500',
        ]);

        // Uploaded file takes priority over URL
        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('cars', 'public');
        }

        if (!$request->hasFile('image') && $request->filled('image_url')) {
            $validated['image_url'] = $request->image_url;
        }

        unset($validated['image']);
        Car::create($validated);

        return redirect()->route('cars.index')
                         ->with('success', 'Car added to the fleet successfully!');
    }

    // EDIT FORM – admin only
    public function edit(Car $car)
    {
        return view('cars.edit', compact('car'));
    }

    // UPDATE – admin only
    public function update(Request $request, Car $car)
    {
        $validated = $request->validate([
            'brand'         => 'required|string|max:100',
            'model'         => 'required|string|max:100',
            'seat_capacity' => 'required|integer|min:1|max:20', // ADDED THIS LINE
            'plate_number'  => 'required|string|max:20|unique:cars,plate_number,' . $car->id,
            'year'          => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'price_per_day' => 'required|numeric|min:1',
            'status'        => 'required|in:available,rented',
            'description'   => 'nullable|string|max:500',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'image_url'     => 'nullable|url|max:500',
        ]);

        if ($request->hasFile('image')) {
            if ($car->image_path) {
                Storage::disk('public')->delete($car->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('cars', 'public');
            $validated['image_url']  = null;
        } elseif ($request->filled('image_url')) {
            if ($car->image_path) {
                Storage::disk('public')->delete($car->image_path);
            }
            $validated['image_path'] = null;
            $validated['image_url']  = $request->image_url;
        } else {
            unset($validated['image_url']);
        }

        unset($validated['image']);
        $car->update($validated);

        return redirect()->route('cars.index')
                         ->with('success', 'Car updated successfully!');
    }

    // DELETE – admin only
    public function destroy(Car $car)
    {
        if ($car->image_path) {
            Storage::disk('public')->delete($car->image_path);
        }

        $car->delete();

        return redirect()->route('cars.index')
                         ->with('success', 'Car removed from the fleet.');
    }
}