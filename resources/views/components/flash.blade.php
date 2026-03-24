
@if(session('success'))
    <div id="flash-success" class="mb-4 p-4 border border-green-300 bg-green-50 text-green-800 rounded-xl shadow flex items-center gap-3 animate-fade-in">
        <svg class="w-6 h-6 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span class="font-semibold">{{ session('success') }}</span>
    </div>
    <script>
        setTimeout(() => {
            const el = document.getElementById('flash-success');
            if (el) el.style.display = 'none';
        }, 3000);
    </script>
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
