@extends('layouts.app')
@section('title', 'Add Car')

@section('content')

<div class="page-header">
    <h1>Add New Car</h1>
    <p>Fill in the details below to add a car to the fleet.</p>
</div>

<div class="form-card">
    <form method="POST" action="{{ route('cars.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label for="brand">Brand</label>
            <input type="text" id="brand" name="brand"
                   value="{{ old('brand') }}" placeholder="e.g. Toyota" required>
            @error('brand')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="model">Model</label>
            <input type="text" id="model" name="model"
                   value="{{ old('model') }}" placeholder="e.g. Vios" required>
            @error('model')<div class="form-error">{{ $message }}</div>@enderror
        </div>

         <div class="form-group">
            <label for="seat_capacity">Seat Capacity</label>
            <input type="number" id="seat_capacity" name="seat_capacity"
                   value="{{ old('seat_capacity') }}" placeholder="e.g. 5" min="1" max="20" required>
            @error('seat_capacity')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="fuel_type">Fuel Type</label>
            <select id="fuel_type" name="fuel_type" required>
                <option value="" disabled {{ old('fuel_type') ? '' : 'selected' }}>Select fuel type...</option>
                <option value="gasoline"  {{ old('fuel_type') === 'gasoline'  ? 'selected' : '' }}> Gasoline</option>
                <option value="diesel"    {{ old('fuel_type') === 'diesel'    ? 'selected' : '' }}> Diesel</option>
                <option value="electric"  {{ old('fuel_type') === 'electric'  ? 'selected' : '' }}> Electric</option>
                <option value="hybrid"    {{ old('fuel_type') === 'hybrid'    ? 'selected' : '' }}> Hybrid</option>
                <option value="lpg"       {{ old('fuel_type') === 'lpg'       ? 'selected' : '' }}> LPG</option>
            </select>
            @error('fuel_type')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="plate_number">Plate Number</label>
                <input type="text" id="plate_number" name="plate_number"
                       value="{{ old('plate_number') }}" placeholder="e.g. ABC 1234" required>
                @error('plate_number')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label for="year">Year</label>
                <input type="number" id="year" name="year"
                       value="{{ old('year') }}" placeholder="e.g. 2022"
                       min="2000" max="{{ date('Y') + 1 }}" required>
                @error('year')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="price_per_day">Price Per Day (&#8369;)</label>
                <input type="number" id="price_per_day" name="price_per_day"
                       value="{{ old('price_per_day') }}" placeholder="e.g. 1500" min="1" required>
                @error('price_per_day')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="available" {{ old('status') === 'available' ? 'selected' : '' }}>Available</option>
                    <option value="rented"    {{ old('status') === 'rented'    ? 'selected' : '' }}>Rented</option>
                </select>
                @error('status')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-group">
            <label for="description">Description <span class="optional">(optional)</span></label>
            <textarea id="description" name="description"
                      placeholder="Brief description of the car...">{{ old('description') }}</textarea>
        </div>

        {{-- IMAGE SECTION --}}
        <div class="image-section">
            <div class="image-section-title">Car Image <span class="optional">(optional)</span></div>

            <div class="form-group">
                <label for="image">Upload Image</label>
                <div class="file-upload-wrap" onclick="document.getElementById('image').click()">
                    <input type="file" id="image" name="image"
                           accept="image/jpg,image/jpeg,image/png,image/webp"
                           style="display:none"
                           onchange="previewUpload(event)">
                    <div class="file-upload-label" id="uploadLabel">
                        <span class="upload-icon">📁</span>
                        <span id="uploadText">Click to upload &mdash; JPG, PNG, WEBP, max 2MB</span>
                    </div>
                    <img id="imagePreview" src="" alt="Preview" class="image-preview" style="display:none;">
                </div>
                @error('image')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="image-or">— or paste an image URL —</div>

            <div class="form-group">
                <label for="image_url">Image URL</label>
                <input type="url" id="image_url" name="image_url"
                       value="{{ old('image_url') }}"
                       placeholder="https://example.com/car.jpg">
                @error('image_url')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Add Car</button>
            <a href="{{ route('cars.index') }}" class="btn btn-cancel">Cancel</a>
        </div>
    </form>
</div>

<script>
function previewUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    const preview = document.getElementById('imagePreview');
    const label   = document.getElementById('uploadLabel');
    preview.src   = URL.createObjectURL(file);
    preview.style.display = 'block';
    label.style.display   = 'none';
    document.getElementById('image_url').value = '';
}
</script>

@endsection