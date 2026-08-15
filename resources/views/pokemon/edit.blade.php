<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit {{ $pokemon->name }} - Custom Pokémon Maker</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold">Edit Pokémon: {{ $pokemon->name }}</h1>
            <a href="{{ route('pokemon.index') }}" class="text-blue-600 hover:underline">← Back to Table</a>
        </div>

        @php
            $types = ['divine', 'magic', 'cosmic', 'machine', 'vital', 'spirit', 'sea', 'sky', 'soil', 'flora', 'flame', 'frost'];
        @endphp

        <div class="bg-white p-6 rounded-lg shadow-md">
            <form action="{{ route('pokemon.update', $pokemon->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium">Name *</label>
                    <input type="text" name="name" value="{{ old('name', $pokemon->name) }}" required class="w-full border rounded p-2 mt-1">
                </div>

                <div>
                    <label class="block text-sm font-medium">Evo?</label>
                    <input type="text" name="evo" value="{{ old('evo', $pokemon->evo) }}" class="w-full border rounded p-2 mt-1">
                </div>

                <div>
                    <label class="block text-sm font-medium">Inspiration 1 (insp1)</label>
                    <input type="text" name="insp1" value="{{ old('insp1', $pokemon->insp1) }}" class="w-full border rounded p-2 mt-1">
                </div>

                <div>
                    <label class="block text-sm font-medium">Inspiration 2 (insp2)</label>
                    <input type="text" name="insp2" value="{{ old('insp2', $pokemon->insp2) }}" class="w-full border rounded p-2 mt-1">
                </div>

                <div>
                    <label class="block text-sm font-medium">Type 1 *</label>
                    <select name="type1" required class="w-full border rounded p-2 mt-1 capitalize">
                        @foreach($types as $typeOption)
                            <option value="{{ $typeOption }}" {{ strtolower($pokemon->type1) === $typeOption ? 'selected' : '' }}>
                                {{ ucfirst($typeOption) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium">Type 2 (Optional)</label>
                    <select name="type2" class="w-full border rounded p-2 mt-1 capitalize">
                        <option value="">-- None --</option>
                        @foreach($types as $typeOption)
                            <option value="{{ $typeOption }}" {{ strtolower($pokemon->type2) === $typeOption ? 'selected' : '' }}>
                                {{ ucfirst($typeOption) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium">Description</label>
                    <textarea name="description" rows="3" class="w-full border rounded p-2 mt-1">{{ old('description', $pokemon->description) }}</textarea>
                </div>

                <div class="md:col-span-2 flex justify-end gap-3">
                    <a href="{{ route('pokemon.index') }}" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Cancel</a>
                    <button type="submit" class="bg-green-600 text-white px-5 py-2 rounded hover:bg-green-700">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>