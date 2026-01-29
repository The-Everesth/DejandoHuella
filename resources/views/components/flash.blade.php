@if(session('success'))
    <div class="mb-4 p-3 border rounded-xl">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-4 p-3 border rounded-xl">
        <div class="font-semibold mb-2">Revisa lo siguiente:</div>
        <ul class="list-disc ml-5 space-y-1">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif
