@php
    $src = $car->getImageSrc();
@endphp

@if($src)
    <img src="{{ $src }}"
         alt="{{ $car->brand }} {{ $car->model }}"
         class="car-card-img"
         style="width:100%; height:160px; object-fit:cover; display:block;">
@else
    @include('cars.image', ['car' => $car])
@endif