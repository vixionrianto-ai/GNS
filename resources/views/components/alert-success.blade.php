@if(session('success'))

<div class="mb-4 rounded-lg border border-green-300 bg-green-100 px-4 py-3 text-green-800 shadow-sm">

    <div class="flex items-center">

        <span class="mr-2 text-lg">✅</span>

        <span>{{ session('success') }}</span>

    </div>

</div>

@endif