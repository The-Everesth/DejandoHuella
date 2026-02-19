<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <header class="mb-6">
                <h1 class="text-3xl font-bold">Clínicas y Servicios Médicos</h1>
            </header>

            <div class="mb-4 flex gap-4 items-center">
                <select id="serviceFilter" class="border rounded px-3 py-2">
                    <option value="">Todos los servicios</option>
                </select>
                <input id="q" class="border rounded px-3 py-2" placeholder="Buscar por nombre...">
                <button id="searchBtn" class="bg-teal-700 text-white px-4 py-2 rounded">Buscar</button>
            </div>

            <div id="clinicList" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="text-center py-12">
                    <p class="text-gray-500">Cargando clínicas...</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
async function loadServices() {
    try {
        const res = await fetch('/api/medical-services');
        const json = await res.json();
        const sel = document.getElementById('serviceFilter');
        if (json.success && json.data) {
            json.data.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = s.name || s.title || s.id;
                sel.appendChild(opt);
            });
        }
    } catch (e) { console.error(e); }
}

async function loadClinics() {
    const sel = document.getElementById('serviceFilter');
    const q = document.getElementById('q').value;
    let url = '/api/clinics?';
    if (sel.value) url += 'serviceId=' + encodeURIComponent(sel.value) + '&';
    if (q) url += 'q=' + encodeURIComponent(q);
    try {
        const res = await fetch(url);
        const json = await res.json();
        const list = document.getElementById('clinicList');
        list.innerHTML = '';
        if (json.success && json.data.length) {
            json.data.forEach(c => {
                const el = document.createElement('div');
                el.className = 'p-4 bg-white rounded shadow';
                el.innerHTML = `<h3 class="font-bold text-lg">${c.name}</h3><p class="text-sm text-gray-600">${c.address||''}</p><a href="/servicios-medicos/${c.id}" class="text-teal-700 mt-2 inline-block">Ver detalles</a>`;
                list.appendChild(el);
            });
        } else {
            list.innerHTML = '<div class="text-center py-12"><p class="text-gray-500">No hay clínicas.</p></div>';
        }
    } catch (e) { console.error(e); }
}

document.addEventListener('DOMContentLoaded', () => {
    loadServices().then(loadClinics);
    document.getElementById('searchBtn').addEventListener('click', loadClinics);
});
</script>
