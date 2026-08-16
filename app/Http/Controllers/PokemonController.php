<?php
	
	namespace App\Http\Controllers;
	
	use App\Models\Pokemon;
	use App\Models\Type;
	use App\Models\TypeEffectiveness;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Storage;
	
	class PokemonController extends Controller
	{
		public function index(Request $request)
		{
			// Default sort changed to 'slot' ascending
			$sortBy      = $request->query('sort', 'slot');
			$sortDir     = strtolower($request->query('direction', 'asc')) === 'asc' ? 'asc' : 'desc';
			
			$filterTypes = $request->query('types', []);
			if (!is_array($filterTypes)) {
				$filterTypes = array_filter(explode(',', $filterTypes));
			}
			$filterTypes = array_map('strtolower', array_map('trim', $filterTypes));
			
			$searchQuery = trim($request->query('search', ''));
			
			$allowedSorts = ['slot', 'id', 'name', 'species', 'evo_number', 'insp', 'type1', 'type2', 'created_at'];
			if (!in_array($sortBy, $allowedSorts)) {
				$sortBy = 'slot';
			}
			
			$pokemonQuery = Pokemon::query();
			
			if (!empty($filterTypes)) {
				$pokemonQuery->where(function($q) use ($filterTypes) {
					$q->whereIn('type1', $filterTypes)
					->orWhereIn('type2', $filterTypes);
				});
			}
			
			if (!empty($searchQuery)) {
				$pokemonQuery->where(function($q) use ($searchQuery) {
					$q->where('name', 'like', "%{$searchQuery}%")
					->orWhere('species', 'like', "%{$searchQuery}%")
					->orWhere('insp', 'like', "%{$searchQuery}%");
				});
			}
			
			$pokemons = $pokemonQuery->orderBy($sortBy, $sortDir)->paginate(50)->withQueryString();
			
			$dbTypes = Type::orderBy('name')->get();
			$typeNames = $dbTypes->pluck('name')->toArray();
			$typeCounts = array_fill_keys($typeNames, 0);
			
			// Symmetric Matrix Map Initialization
			$typeMatrix = [];
			foreach ($typeNames as $t1) {
				foreach ($typeNames as $t2) {
					$typeMatrix[$t1][$t2] = 0;
				}
			}
			
			$allRecords = Pokemon::select('type1', 'type2', 'species')->get();
			
			$totalCount = $allRecords->count();
			$singleTypeCount = 0;
			$dualTypeCount = 0;
			$uniqueSpecies = [];
			
			foreach ($allRecords as $record) {
				$t1 = strtolower($record->type1);
				$t2 = strtolower($record->type2 ?? '');
				
				if (array_key_exists($t1, $typeCounts)) {
					$typeCounts[$t1]++;
				}
				
				if (!empty($t2)) {
					if (array_key_exists($t2, $typeCounts)) {
						$typeCounts[$t2]++;
					}
					$dualTypeCount++;
					
					if (isset($typeMatrix[$t1][$t2])) {
						$typeMatrix[$t1][$t2]++;
					}
					if ($t1 !== $t2 && isset($typeMatrix[$t2][$t1])) {
						$typeMatrix[$t2][$t1]++;
					}
					} else {
					$singleTypeCount++;
					if (isset($typeMatrix[$t1][$t1])) {
						$typeMatrix[$t1][$t1]++;
					}
				}
				
				if (!empty($record->species)) {
					$uniqueSpecies[$record->species] = true;
				}
			}
			
			$uniqueSpeciesCount = count($uniqueSpecies);
			
			// Build initial effectiveness matrix (Default 1.0 multiplier for all pairs)
			$effectivenessMatrix = [];
			foreach ($typeNames as $att) {
				foreach ($typeNames as $def) {
					$effectivenessMatrix[$att][$def] = 1.0;
				}
			}
			
			// Overwrite with custom saved values from database
			$savedEffectiveness = TypeEffectiveness::all();
			foreach ($savedEffectiveness as $eff) {
				if (isset($effectivenessMatrix[$eff->attacker_type][$eff->defender_type])) {
					$effectivenessMatrix[$eff->attacker_type][$eff->defender_type] = (float)$eff->multiplier;
				}
			}
			
			return view('pokemon.index', compact(
            'pokemons', 
            'sortBy', 
            'sortDir', 
            'filterTypes',
            'searchQuery',
            'dbTypes',
            'typeCounts', 
            'totalCount',
            'singleTypeCount',
            'dualTypeCount',
            'uniqueSpeciesCount',
            'typeMatrix',
            'effectivenessMatrix'
			));
		}
		
		// Reorder action updating the 'slot' column
		public function reorder(Request $request)
		{
			$validated = $request->validate([
			'ids'          => 'required|array',
			'ids.*'        => 'integer|exists:pokemon,id',
			'current_page' => 'nullable|integer|min:1',
			'per_page'     => 'nullable|integer|min:1',
			]);
			
			// Calculate starting slot offset for current page (default 50 items per page)
			$currentPage = $validated['current_page'] ?? 1;
			$perPage     = $validated['per_page'] ?? 50;
			$startSlot   = (($currentPage - 1) * $perPage) + 1;
			
			foreach ($validated['ids'] as $index => $id) {
				Pokemon::where('id', $id)->update(['slot' => $startSlot + $index]);
			}
			
			return response()->json(['success' => true]);
		}
		
		public function store(Request $request)
		{
			$allowedTypes = Type::pluck('name')->implode(',');
			
			$validated = $request->validate([
			'name'        => 'required|string|max:255',
			'species'     => 'nullable|string|max:255',
			'evo_number'  => 'nullable|integer|min:1',
			'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
			'insp'        => 'nullable|string',
			'type1'       => 'required|in:' . $allowedTypes,
			'type2'       => 'nullable|in:' . $allowedTypes,
			'description' => 'nullable|string',
			'slot'        => 'nullable|integer|min:1', // <-- Added slot validation
			]);
			
			if ($request->hasFile('image')) {
				$path = $request->file('image')->store('pokemon_images', 'public');
				$validated['image_path'] = Storage::url($path);
			}
			
			$maxSlot = Pokemon::max('slot') ?? 0;
			$targetSlot = $request->filled('slot') ? (int)$request->slot : ($maxSlot + 1);
			
			// If inserted into an existing slot position, shift subsequent items up by 1
			if ($targetSlot <= $maxSlot) {
				Pokemon::where('slot', '>=', $targetSlot)->increment('slot');
				} else {
				$targetSlot = $maxSlot + 1;
			}
			
			$validated['slot'] = $targetSlot;
			
			Pokemon::create($validated);
			
			return redirect()->back()->with('success', 'Custom Pokémon added successfully!');
		}
		
		public function update(Request $request, Pokemon $pokemon)
		{
			$allowedTypes = Type::pluck('name')->implode(',');
			
			$validated = $request->validate([
			'name'        => 'required|string|max:255',
			'species'     => 'nullable|string|max:255',
			'evo_number'  => 'nullable|integer|min:1',
			'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
			'clear_image' => 'nullable|boolean',
			'insp'        => 'nullable|string',
			'type1'       => 'required|in:' . $allowedTypes,
			'type2'       => 'nullable|in:' . $allowedTypes,
			'description' => 'nullable|string',
			'slot'        => 'nullable|integer|min:1', // <-- Added slot validation
			]);
			
			if ($request->boolean('clear_image')) {
				if ($pokemon->image_path && str_contains($pokemon->image_path, '/storage/')) {
					$storagePath = str_replace('/storage/', '', $pokemon->image_path);
					Storage::disk('public')->delete($storagePath);
				}
				$validated['image_path'] = null;
			}
			
			if ($request->hasFile('image')) {
				if ($pokemon->image_path && str_contains($pokemon->image_path, '/storage/')) {
					$storagePath = str_replace('/storage/', '', $pokemon->image_path);
					Storage::disk('public')->delete($storagePath);
				}
				$path = $request->file('image')->store('pokemon_images', 'public');
				$validated['image_path'] = Storage::url($path);
			}
			
			// Handle slot shift if slot was modified
			if ($request->filled('slot')) {
				$oldSlot = $pokemon->slot ?: $pokemon->id;
				$newSlot = (int)$request->slot;
				$maxSlot = Pokemon::max('slot') ?: Pokemon::count();
				$newSlot = max(1, min($newSlot, $maxSlot));
				
				if ($oldSlot !== $newSlot) {
					if ($newSlot < $oldSlot) {
						Pokemon::whereBetween('slot', [$newSlot, $oldSlot - 1])->increment('slot');
						} else {
						Pokemon::whereBetween('slot', [$oldSlot + 1, $newSlot])->decrement('slot');
					}
					$validated['slot'] = $newSlot;
				}
			}
			
			$pokemon->update($validated);
			
			return redirect()->back()->with('success', 'Pokémon updated successfully!');
		}
		
		public function destroy(Pokemon $pokemon)
		{
			$deletedSlot = $pokemon->slot;
			
			// Delete stored image file if present
			if ($pokemon->image_path && str_contains($pokemon->image_path, '/storage/')) {
				$storagePath = str_replace('/storage/', '', $pokemon->image_path);
				Storage::disk('public')->delete($storagePath);
			}
			
			$pokemon->delete();
			
			// Shift remaining slots down by 1 to fill the deleted slot gap
			if ($deletedSlot) {
				Pokemon::where('slot', '>', $deletedSlot)->decrement('slot');
			}
			
			return redirect()->back()->with('success', 'Pokémon deleted successfully!');
		}
		
		public function export()
		{
			$fileName = 'pokemon_registry_' . date('Y-m-d') . '.csv';
			
			// Fetch all Pokémon sorted by slot ascending
			$pokemons = Pokemon::orderBy('slot', 'asc')->get();
			
			$headers = [
			"Content-type"        => "text/csv",
			"Content-Disposition" => "attachment; filename=$fileName",
			"Pragma"              => "no-cache",
			"Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
			"Expires"             => "0"
			];
			
			$callback = function() use ($pokemons) {
				$file = fopen('php://output', 'w');
				
				// CSV Header Row
				fputcsv($file, ['Slot', 'Image Path', 'Name', 'Species', 'Evo Number', 'Inspiration', 'Type 1', 'Type 2', 'Description']);
				
				foreach ($pokemons as $index => $pokemon) {
					fputcsv($file, [
					$pokemon->slot ?? ($index + 1), // Exports current custom slot (or fallback sequence)
					$pokemon->image_path ?? '',
					$pokemon->name,
					$pokemon->species ?? '',
					$pokemon->evo_number ?? '',
					$pokemon->insp ?? '',
					$pokemon->type1,
					$pokemon->type2 ?? '',
					$pokemon->description ?? ''
					]);
				}
				
				fclose($file);
			};
			
			return response()->stream($callback, 200, $headers);
		}
		
		public function import(Request $request)
		{
			$request->validate([
            'file' => 'required|mimes:csv,txt|max:2048',
			]);
			
			$file = $request->file('file');
			$handle = fopen($file->getRealPath(), 'r');
			
			// Get the current highest slot in the database
			$maxSlot = Pokemon::max('slot') ?? 0;
			
			$isFirstRow = true;
			while (($row = fgetcsv($handle, 1000, ',')) !== false) {
				if ($isFirstRow) {
					$isFirstRow = false;
					if (isset($row[1]) && strtolower(trim($row[1])) === 'name') {
						continue;
					}
				}
				
				if (empty($row[1])) {
					continue;
				}
				
				$maxSlot++;
				
				Pokemon::create([
                'image_path'  => !empty($row[0]) ? trim($row[0]) : null,
                'name'        => trim($row[1]),
                'species'     => !empty($row[2]) ? trim($row[2]) : null,
                'evo_number'  => !empty($row[3]) ? (int)$row[3] : null,
                'insp'        => !empty($row[4]) ? trim($row[4]) : null,
                'type1'       => !empty($row[5]) ? strtolower(trim($row[5])) : 'normal',
                'type2'       => !empty($row[6]) ? strtolower(trim($row[6])) : null,
                'description' => !empty($row[7]) ? trim($row[7]) : null,
                'slot'        => $maxSlot,
				]);
			}
			
			fclose($handle);
			
			return redirect()->back()->with('success', 'Pokémon list imported successfully!');
		}
		
		public function clearAll()
		{
			Pokemon::truncate();
			return redirect()->route('pokemon.index')->with('success', 'All Pokémon entries have been cleared successfully.');
		}
		
		public function moveSlot(Request $request)
		{
			$validated = $request->validate([
            'id'          => 'required|integer|exists:pokemon,id',
            'target_slot' => 'required|integer|min:1',
			]);
			
			$pokemon = Pokemon::findOrFail($validated['id']);
			$oldSlot = $pokemon->slot ?: $pokemon->id;
			$maxSlot = Pokemon::max('slot') ?: Pokemon::count();
			
			// Clamp target slot between 1 and total count
			$newSlot = max(1, min($validated['target_slot'], $maxSlot));
			
			if ($oldSlot === $newSlot) {
				return response()->json(['success' => true]);
			}
			
			// Shift slots of intermediate items
			if ($newSlot < $oldSlot) {
				Pokemon::whereBetween('slot', [$newSlot, $oldSlot - 1])
                ->increment('slot');
				} else {
				Pokemon::whereBetween('slot', [$oldSlot + 1, $newSlot])
                ->decrement('slot');
			}
			
			$pokemon->update(['slot' => $newSlot]);
			
			return response()->json(['success' => true]);
		}
	}					