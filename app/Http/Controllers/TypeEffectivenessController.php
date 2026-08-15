<?php
	
	namespace App\Http\Controllers;
	
	use App\Models\TypeEffectiveness;
	use Illuminate\Http\Request;
	
	class TypeEffectivenessController extends Controller
	{
		public function update(Request $request)
		{
			$validated = $request->validate([
            'attacker'   => 'required|string',
            'defender'   => 'required|string',
            'multiplier' => 'required|numeric',
			]);
			
			TypeEffectiveness::updateOrCreate(
            [
			'attacker_type' => strtolower($validated['attacker']),
			'defender_type' => strtolower($validated['defender']),
            ],
            [
			'multiplier' => (float)$validated['multiplier'],
            ]
			);
			
			return response()->json(['success' => true]);
		}
		
		// NEW: Truncate table to reset all multipliers back to default 1.0
		public function reset()
		{
			TypeEffectiveness::truncate();
			
			return response()->json(['success' => true]);
		}
	}
	
