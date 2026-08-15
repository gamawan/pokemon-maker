<?php
	
	namespace App\Models;
	
	use Illuminate\Database\Eloquent\Model;
	
	class TypeEffectiveness extends Model
	{
		protected $fillable = ['attacker_type', 'defender_type', 'multiplier'];
	}
	
