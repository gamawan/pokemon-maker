<?php

namespace App\Http\Controllers;

use App\Models\Type;
use App\Models\Pokemon;
use Illuminate\Http\Request;

class TypeController extends Controller
{
    // Create new type
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:50|unique:types,name',
            'color_hex' => 'required|string|max:7',
        ]);

        $validated['name'] = strtolower(trim($validated['name']));
        Type::create($validated);

        return redirect()->back()->with('success', 'New Element Type created!');
    }

    // Edit type & update all existing Pokemon using this type
    public function update(Request $request, Type $type)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:50|unique:types,name,' . $type->id,
            'color_hex' => 'required|string|max:7',
        ]);

        $oldName = $type->name;
        $newName = strtolower(trim($validated['name']));

        // Update Pokemon records if type name changed
        if ($oldName !== $newName) {
            Pokemon::where('type1', $oldName)->update(['type1' => $newName]);
            Pokemon::where('type2', $oldName)->update(['type2' => $newName]);
        }

        $type->update([
            'name'      => $newName,
            'color_hex' => $validated['color_hex'],
        ]);

        return redirect()->back()->with('success', 'Element Type updated across all records!');
    }

    // Delete type & remove references from existing Pokemon
    public function destroy(Type $type)
    {
        $typeName = $type->name;

        // Clear type2 entries matching this type
        Pokemon::where('type2', $typeName)->update(['type2' => null]);

        // Warn if type1 is actively in use
        $inUseCount = Pokemon::where('type1', $typeName)->count();
        if ($inUseCount > 0) {
            return redirect()->back()->with('error', "Cannot delete '{$typeName}' because {$inUseCount} Pokémon have it as Primary Type (Type 1). Reassign them first!");
        }

        $type->delete();

        return redirect()->back()->with('success', 'Element Type deleted successfully!');
    }
}
