@if($errors->any())

<div class="mb-4 rounded-lg border border-red-300 bg-red-100 px-4 py-3 text-red-800 shadow-sm">

    <div class="flex items-center">

        <span class="mr-2 text-lg">❌</span>

        <span>{{ $errors->first() }}</span>

    </div>

</div>

@endif